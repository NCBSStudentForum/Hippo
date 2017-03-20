<!-- <script src="http://code.highcharts.com/highcharts.js"></script> -->
<script src="./node_modules/highcharts/highcharts.js"></script>
<?php

include_once 'header.php';
include_once 'database.php';


$upto = dbDate( 'tomorrow' );
$requests = getTableEntries( 'bookmyvenue_requests', 'date'
                , "date >= '2017-02-28' AND date < '$upto'" );
$nApproved = 0;
$nRejected = 0;
$nCancelled = 0;
$nPending = 0;
$nOther = 0;
$timeForAction = array( );


$firstDate = $requests[0]['date'];
$lastDate = end( $requests )['date'];
$timeInterval = strtotime( $lastDate ) - strtotime( $firstDate );


foreach( $requests as $r )
{
    if( $r[ 'status' ] == 'PENDING' )
        $nPending += 1;

    else if( $r[ 'status' ] == 'APPROVED' )
        $nApproved += 1;

    else if( $r[ 'status' ] == 'REJECTED' )
        $nRejected += 1;

    else if( $r[ 'status' ] == 'CANCELLED' )
        $nCancelled += 1;
    else 
        $nOther += 1;

    // Time take to approve a request, in hours
    if( $r[ 'last_modified_on' ] )
    {
        $time = strtotime( $r['date'] . ' ' . $r[ 'start_time' ] ) 
                    - strtotime( $r['last_modified_on'] );
        $time = $time / 3600.0;
        array_push( $timeForAction, array($time, 1) ); 
    }

}


// Venue usage timne.
$events = getTableEntries( 'events', 'date', 'date
$venueUsageTime = array( );
// rate per day.
$rateOfRequests = 24 * 3600.0 * count( $requests ) / (1.0 * $timeInterval);

$bookingTable = "<table border='1'>
    <tr> <td>Total booking requests</td> <td>" . count( $requests ) . "</td> </tr>
    <tr> <td>Rate of requests (per day)</td> <td>" 
            .   $rateOfRequests . "</td> </tr>
    <tr> <td>Approved requests</td> <td> $nApproved </td> </tr>
    <tr> <td>Pending requests</td> <td> $nPending </td> </tr>
    <tr> <td>Rejected requests</td> <td> $nRejected </td> </tr>
    <tr> <td>Cancelled by user</td> <td> $nCancelled </td> </tr>
    <tr> <td>Other</td> <td> $nOther </td> </tr>
    </table>";

?>

<!-- Plot here -->
<script type="text/javascript" charset="utf-8">
$(function () {
    
    var data = <?php echo json_encode( $timeForAction ); ?>;

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

    Highcharts.chart('container3', {
        chart: { type: 'column' },
        title: { text: 'Approval/rejection time - event start time' },
        xAxis: { min : -100, max: 100 },
        yAxis: [{ title: { text: 'Number of requests' } }, ],
        series: [{
            name: 'Time taken to act on booking requests',
            type: 'column',
            data: histogram(data, 1),
            pointPadding: 0,
            groupPadding: 0,
            pointPlacement: 'between'
        }, 
    ] });
});
</script>

<script type="text/javascript" charset="utf-8">
highcharts.chart('venues_plot', {

    var venueUsage = <?php echo json_encode( $venueUsage ); ?>;
    var venues = <?php echo json_encode( $venues ); ?>;

    title: {
        text: 'Usage pattern of venues'
    },

    yAxis: {
        title: {
            text: 'Time in hours'
        }
    },
    legend: {
        layout: 'vertical',
        align: 'right',
        verticalAlign: 'middle'
    },

    series: [{
        name: 'Venue usage',
        data: venueUsage,
        pointPlacement: 'between'
    }, 
});

</script>



<?php 

$awses = getAllAWS( );
$speakers = getAWSSpeakers( );

$awsPerSpeaker = array( );

$awsYearData = array_map(
    function( $x ) { return array(date('Y', strtotime($x['date'])), 0); } , $awses
    );

// Here each valid AWS speaker initialize her count to 0.
foreach( $speakers as $speaker )
    $awsPerSpeaker[ $speaker['login'] ] = array();

// If there is already an AWS for a speaker, add to her count.
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

$numAWSPerSpeaker = array( );
$gapBetweenAWS = array( );
foreach( $awsCounts as $key => $val )
{
    array_push( $numAWSPerSpeaker,  array($val, 0) );

    for( $i = 1; $i < count( $awsDates[ $key ] ); $i++ )
    {
        $gap = (strtotime( $awsDates[ $key ][$i-1] ) - 
            strtotime( $awsDates[ $key ][$i]) )/ (30.5 * 86400);

        // We need a tuple. Second entry is dummy.
        array_push( $gapBetweenAWS, array( $gap, 0 ) );
    }
}


?>


<script type="text/javascript" charset="utf-8">
$(function () {
    
    var data = <?php echo json_encode( $awsYearData ); ?>;

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

    Highcharts.chart('container0', {
        chart: { type: 'column' },
        title: { text: 'Number of Annual Work Seminars per year' },
        xAxis: { min : 2010 },
        yAxis: [{ title: { text: 'AWS Count' } }, ],
        series: [{
            name: 'AWS this year',
            type: 'column',
            data: histogram(data, 1),
            pointPadding: 0,
            groupPadding: 0,
            pointPlacement: 'between'
        }, 
    ] });

});

</script>


<script type="text/javascript" charset="utf-8">
$(function () {
    
    var data = <?php echo json_encode( $numAWSPerSpeaker ); ?>;

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

    Highcharts.chart('container1', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Total speakers with #AWSs'
        },
        xAxis: { min : 0 },
        yAxis: [{
            title: {
                text: 'Speaker Count'
            }
        }, ],
        series: [{
            name: 'Total speakers with #AWS',
            type: 'column',
            data: histogram(data, 1),
            pointPadding: 0,
            groupPadding: 0,
            pointPlacement: 'between'
        }, 
    ] });

});

</script>


<script>

$(function () {
    
    var data = <?php echo json_encode( $gapBetweenAWS ); ?>;

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

    Highcharts.chart('container2', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Gap in months between consecutive AWSs'
        },
        xAxis: { max : 36 },
        yAxis: [{
            title: {
                text: 'AWS Count'
            }
        }, ],
        series: [{
            name: '#AWS with this gap',
            type: 'column',
            data: histogram(data, 1),
            pointPadding: 0,
            groupPadding: 0,
            pointPlacement: 'between'
        }, 
    ] });

});
</script>

<h1>Booking requests between <?php
    echo humanReadableDate( 'march 01, 2017') ?> 
    and <?php echo humanReadableDate( $upto ); ?></h1>

<?php 
echo $bookingTable;
?>

<h3></h3>
<div id="container3" style="width:100%; height:400px;"></div>

<h1>Venues </h1>
<h3></h3>
<div id="venues_plot" style="width:100%; height:400px;"></div>

<h1>Annual Work Seminars</h1>;
<h3></h3>
<div id="container0" style="width:100%; height:400px;"></div>

<h3></h3>
<div id="container1" style="width:100%; height:400px;"></div>

<h3> Gap between consecutive AWSs </h3>
Ideally, this value should be 12 months for all AWSs.
<div id="container2" style="width:100%; height:400px;"></div>

<a href="javascript:window.close();">Close Window</a>

