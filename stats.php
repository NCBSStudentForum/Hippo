<?php

include_once 'header.php';
include_once 'database.php';

// Get all data.

$awses = getAllAWS( );
$awsPerSpeaker = array( );
foreach( $awses as $aws )
{
    $speaker = $aws[ 'speaker' ];
    if( ! array_key_exists( $speaker, $awsPerSpeaker ) )
        $awsPerSpeaker[ $speaker ] = array();
    array_push( $awsPerSpeaker[ $speaker ], $aws );
}

$awsCounts = array( );
$awsDates = array( );
foreach( $awsPerSpeaker as $speaker => $awses )
{
    $awsCounts[ $speaker ] = count( $awses );
    $awsDates[ $speaker ] = array_map( 
        function($x) { return $x['date']; }, $awses 
    );
}

//$countData = array_count_values( $awsCounts );
//

$plotAData = array( );
foreach( $awsCounts as $key => $val )
    array_push( $plotAData,  array($val, 0) );

echo json_encode( $awsDates );

?>

<script src="http://code.highcharts.com/highcharts.js"></script>

<script type="text/javascript" charset="utf-8">

$(function () {
    
    var data = <?php echo json_encode( $plotAData ); ?>;

    /**
     * Get histogram data out of xy data
     * @param   {Array} data  Array of tuples [x, y]
     * @param   {Number} step Resolution for the histogram
     * @returns {Array}       Histogram data
     */
    function histogram(data, step) {
        var histo = {},
            x,
            i,
            arr = [];

        // Group down
        for (i = 0; i < data.length; i++) {
            x = Math.floor(data[i][0] / step) * step;
            if (!histo[x]) {
                histo[x] = 0;
            }
            histo[x]++;
        }

        // Make the histo group into an array
        for (x in histo) {
            if (histo.hasOwnProperty((x))) {
                arr.push([parseFloat(x), histo[x]]);
            }
        }

        // Finally, sort the array
        arr.sort(function (a, b) {
            return a[0] - b[0];
        });

        return arr;
    }

    Highcharts.chart('container', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Per speaker AWSs'
        },
        xAxis: {
            gridLineWidth: 1
        },
        yAxis: [{
            title: {
                text: 'AWS Count'
            }
        }, ],
        series: [{
            name: 'Speakers with #AWS',
            type: 'column',
            data: histogram(data, 1),
            pointPadding: 0,
            groupPadding: 0,
            pointPlacement: 'between'
        }, 
    ]
    });
});
</script>
    
<div id="container" style="width:100%; height:400px;"></div>

<?php

echo  goBackToPageLink( "index.php", "Go back" );

?>
