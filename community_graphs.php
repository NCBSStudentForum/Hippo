<?php

include_once 'header.php';
include_once 'database.php';

if( isset( $_POST['months'] ) )
    $howManyMonths = intval( $_POST[ 'months' ] );
else
    $howManyMonths = 36;

echo '
    <form method="post" action="">
    Show AWS interaction in last <input type="text" name="months" 
        value="' . $howManyMonths . '" />
    months.
    <button name="response" value="Submit">Submit</button>
    </form>
    ';


$from = date( 'Y-m-d', strtotime( 'today' . " -$howManyMonths months"));

$fromD = date( 'M d, Y', strtotime( $from ) );
echo "<p>
    Following graph shows the interaction among faculty since $fromD.
    Number on edges are number of AWSs between two faculty, either of them is involved 
    in an AWS as co-supervisor or as a thesis committee member.

    </p>";

$awses = getAWSFromPast( $from  );
$network = array( 'nodes' => array(), 'edges' => array( ) );

echo printInfo( "Total " . count( $awses) . " AWSs found in database since $fromD" );

echo '
    <strong>
    Hover over a node to see the interaction of particular faculty.
    </strong>
    ';

$community = array();


/** 
 * Here we collect all the unique PIs. This is to make sure that we don't draw 
 * a node for external PI who is/was not on NCBS faculty list. Sometimes, we 
 * can't get all relevant PIs if we only search in AWSs given in specific time. 
 * Therefore we query the faculty table to get the list of all PIs.
 */
$faculty = getFaculty( );
$pis = array( );                                // Just the email
foreach( $faculty as $fac )
{
    $pi = $fac[ 'email' ];
    array_push( $pis, $pi );
    $community[ $pi ] = array( 
        'count' => 0  //  Number of AWS by this supervisor
        , 'edges' => array( )  // Outgoiing edges
        , 'degree' => 0 // Degree of this supervisor.
    );
}

// Each node must have a unique integer id. We use it to generate a distinct 
// color for each node in d3. Also edges are drawn from id to id.
foreach( $awses as $aws )
{
    // How many AWSs this supervisor has.
    $pi = $aws[ 'supervisor_1' ];
    if( ! in_array( $pi, $pis ) )
        continue;

    $community[ $pi ]['count'] += 1;
    $community[ $pi ]['degree'] += 1;

    // Co-supervisor is an edge
    $super2 = __get__( $aws, 'supervisor_2', '' );
    if( $super2 )
    {
        if( in_array( $super2, $pis ) )
        {
            $community[ $pi ]['edges'][] =  $super2;
            $community[ $super2 ]['degree'] += 1;
        }
    }

    // All TCM members are edges
    $foundAnyTcm = false;
    for( $i = 1; $i < 5; $i++ )
    {
        if( ! array_key_exists( "tcm_member_$i", $aws ) )
            continue;

        $tcmMember = trim( $aws[ "tcm_member_$i" ] );
        if( in_array( $tcmMember, $pis ) )
        {
            $community[ $pi ][ 'edges' ][] = $tcmMember;
            $community[ $tcmMember ][ "degree" ] += 1;
            $foundAnyTcm = true;
        }
    }

    // If not TCM member is found for this TCM. Add an edge onto PI.
    if(! $foundAnyTcm )
        $community[ $pi ][ 'edges' ][] =  $pi;
}

// Now for each PI, draw edges to other PIs.
foreach( $community as $pi => $value )
{
    // If there are not edges from or onto this PI, ignore it.
    $login = explode( '@', $pi)[0];

    // This PI is not involved in any AWS for this duration.
    if( $value[ "degree" ] < 1 )
        continue;

    // Width represent AWS per month.
    $count = $value[ 'count' ];
    $width = 0.1 + 0.5 * ( $count / $howManyMonths );
    array_push( 
        $network[ 'nodes' ], array( 'name' => $login, 'count' => $count, 'width' => $width ) 
    );

    foreach( array_count_values( $value['edges'] ) as $val => $edgeNum )
        array_push( $network[ 'edges']
            , array( 'source_email' => $pi, 'tgt_email' => $val, 'width' => $edgeNum 
                    , 'count' => $edgeNum )
        );
}

// Before writing to JSON use index of node as source and target in edges.
$nodeIds = array();
foreach( $network['nodes'] as $node )
    $nodeIds[ $node['name'] ] = count( $nodeIds );

for( $i = 0; $i < count( $network['edges']); $i++)
{
    $src = explode( '@', $network['edges'][$i][ 'source_email' ] )[0];
    $tgt = explode( '@', $network['edges'][$i][ 'tgt_email' ] )[0];
    $network['edges'][$i][ 'source' ] = $nodeIds[ $src ];
    $network['edges'][$i][ 'target' ] = $nodeIds[ $tgt ];
}

// Write the network array to json array.
$networkJSON = json_encode( $network, JSON_PRETTY_PRINT );
$networkJSONFileName = "data/network.json";
//$handle = fopen( $networkJSONFileName, "w+" );
//fwrite( $handle, $networkJSON );
//fclose( $handle );
?>

<!-- Use d3 to draw graph -->
<div>
<script src="https://d3js.org/d3.v3.min.js"></script>
<script>

    var w = 1000;
    var h = 1000;

    var linkDistance=200;

    var colors = d3.scale.category10();

    var graph = <?php echo $networkJSON; ?>;
 
    var svg = d3.select("body").append("svg").attr({"width":w,"height":h});

    var toggle = 0;

    var charge = -100 * <?php echo $howManyMonths; ?>;
    var gravity = 0.04 * <?php echo $howManyMonths; ?>;

    var force = d3.layout.force()
        .nodes(graph.nodes)
        .links(graph.edges)
        .size([w,h])
        .linkDistance([linkDistance])
        .charge( [ charge ] )
        .theta(0.2)
        .gravity( gravity )
        .start();

    var edges = svg.selectAll("line")
      .data(graph.edges)
      .enter()
      .append("line")
      .attr("id",function(d,i) {return 'ede'+i})
      .attr( 'stroke-width', function(e) { return e.width; } )
      .style("stroke","#ccc")
      .style("pointer-events", "none");
    
    var node = svg.selectAll("circle")
      .data(graph.nodes)
      .enter()
      .append("circle")
      .attr({"r":function(d) { return 50 * d.width; } })
      .style( "opacity", 1 )
      .style("fill",function(d,i){return colors(10);})
      .call(force.drag)
      .on( 'mouseover', connectedNodes )
      .on( 'mouseout', connectedNodes )


    var nodelabels = svg.selectAll(".nodelabel") 
       .data(graph.nodes)
       .enter()
       .append("text")
       .attr( {
                "x":     function(d){return d.x;},
               "y":      function(d){return d.y;},
               "class":  "nodelabel",
               "stroke": "blue"
              }
        )
        .text(function(d){return d.name;});

    var edgepaths = svg.selectAll(".edgepath")
        .data(graph.edges)
        .enter()
        .append('path')
        .attr({'d': function(d) {
                    return 'M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y
                },
                'class':'edgepath',
                'fill-opacity':0,
                'stroke-opacity':0,
                'fill':'blue',
                'stroke':'red',
                'id':function(d,i) {return 'edgepath'+i}}
        )
        .style("pointer-events", "none");

    var edgelabels = svg.selectAll(".edgelabel")
        .data(graph.edges)
        .enter()
        .append('text')
        .style("pointer-events", "none")
        .attr({'class':'edgelabel',
               'id':function(d,i){return 'edgelabel'+i},
               'dx':80,
               'dy':0,
               'font-size':10,
               'fill':'#acc'});

    edgelabels.append('textPath')
        .attr('xlink:href',function(d,i) {return '#edgepath'+i})
        .style("pointer-events", "none")
        .text(function(d,i){return d.count}); // Return edge level.


    svg.append('defs').append('marker')
        .attr({'id':'arrowhead',
               'viewBox':'-0 -5 10 10',
               'refX':25,
               'refY':0,
               'orient':'auto',
               'markerWidth':10,
               'markerHeight':10,
               'xoverflow':'visible'})
        .append('svg:path')
            .attr('d', 'M 0,-5 L 10 ,0 L 0,5')
            .attr('fill', '#ccc')
            .attr('stroke','#ccc');
     

    force.on("tick", function(){
        edges.attr( { 
                      "x1": function(d){return d.source.x;},
                      "y1": function(d){return d.source.y;},
                      "x2": function(d){return d.target.x;},
                      "y2": function(d){return d.target.y;}
                   });

        node.attr({"cx":function(d){return d.x;},
                    "cy":function(d){return d.y;}
        });

        nodelabels.attr("x", function(d) { return d.x; }) 
                  .attr("y", function(d) { return d.y; });

        edgepaths.attr('d', function(d) { 
            var path='M '+d.source.x+' '+d.source.y+' L '+ d.target.x +' '+d.target.y;
            return path
        });       

        edgelabels.attr('transform',function(d,i){
            if (d.target.x<d.source.x){
                bbox = this.getBBox();
                rx = bbox.x+bbox.width/2;
                ry = bbox.y+bbox.height/2;
                return 'rotate(180 '+rx+' '+ry+')';
                }
            else {
                return 'rotate(0)';
                }
        });
    });


    var linkedByIndex = {};
    for (i = 0; i < graph.nodes.length; i++) {
        linkedByIndex[i + "," + i] = 1;
    };
    graph.edges.forEach(function (d) {
        linkedByIndex[d.source.index + "," + d.target.index] = 1;
    });


    function neighboring(a, b) {
        return linkedByIndex[a.index + "," + b.index];
    }

    function connectedNodes() {
        if (toggle == 0) {
            d = d3.select(this).node().__data__;
            node.style("opacity", function (o) {
                return neighboring(d, o) | neighboring(o, d) ? 1 : 0.1;
            });
            nodelabels.text( function (o) {
                if( o.name == d.name )
                    return 'Total AWS:' + d.count;
                return neighboring(d, o) | neighboring(o, d) ? o.name : '';
            });

            edges.style("stroke", function( e ) {
                console.log( e.source );
                if( e.source.name == d.name  || e.target.name == d.name )
                    return "#800";
                else
                    return "#ccc";
            });

            edgelabels.attr( 'fill', function(e) {
                if( e.source.name == d.name || e.target.name == d.name )
                    return "#000";
                else
                    return "#acc";
            } );
            toggle = 1;
        } else {
            node.style("opacity", 1);;
            nodelabels.text(function(d){return d.name;});
            toggle = 0;
            edges.style("stroke", "#ccc")
            edgelabels.attr( 'fill', "#acc" );
        }
    }


</script>
</div>

<a href="javascript:window.close();">Close Window</a>
