<?php

include_once 'header.php';
include_once 'database.php';

// Get all data.

$awses = getAllAWS( );
$awsPerSpeaker = array( );

$awsYearData = array_map(
    function( $x ) { return array(date('Y', strtotime($x['date'])), 0); } , $awses
    );

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

$plotAData = array( );
$plotBData = array( );
foreach( $awsCounts as $key => $val )
{
    array_push( $plotAData,  array($val, 0) );
    for( $i = 1; $i < count( $awsDates[ $key ] ); $i++ )
    {
        $gap = (strtotime( $awsDates[ $key ][$i-1] ) - 
            strtotime( $awsDates[ $key ][$i]) )/ (30.5 * 86400);
        // We need a tuple. Second entry is dummy.
        array_push( $plotBData, array( $gap, 0 ) );
    }
}

?>

<!-- <script src="http://code.highcharts.com/highcharts.js"></script> -->
<script src="./node_modules/highcharts/highcharts.js"></script>

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
            name: 'AWS per year',
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
    
    var data = <?php echo json_encode( $plotBData ); ?>;

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

<h3></h3>
<div id="container0" style="width:100%; height:400px;"></div>

<h3></h3>
<div id="container1" style="width:100%; height:400px;"></div>

<h3> Gap between consecutive AWSs </h3>
Ideally, this value should be 12 months for all AWSs.
<div id="container2" style="width:100%; height:400px;"></div>

<a href="javascript:window.close();">Close Window</a>

