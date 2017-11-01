<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'methods.php';
include_once './check_access_permissions.php';
?>

<!-- Sweet alert -->
<script src="./node_modules/sweetalert/dist/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="./node_modules/sweetalert/dist/sweetalert.css">


<?php
if( ! (isIntranet() || isAuthenticated( ) ) )
{
    echo loginOrIntranet( );
    exit;
}


$year = getCurrentYear( );
$sem = getCurrentSemester( );
$slotCourses = array( );
$tileCourses = array( );
$runningCourses = getSemesterCourses( $year, $sem );

// Collect both metadata and other information in slotCourse array.
foreach( $runningCourses as $c )
{
    $cid = $c[ 'course_id' ];
    $course = getTableEntry( 'courses_metadata', 'id' , array('id' => $cid) ); 
    if( $course )
    {
        $slotId = $c[ 'slot' ];
        $tiles = getTableEntries( 'slots', 'groupid', "groupid='$slotId'" );
        $slotCourses[ $slotId ][ ] = array_merge( $c, $course );
        foreach( $tiles as $tile )
        {
            if( strpos( $c['ignore_tiles'], $tile[ 'id' ]) !== 0 )
            {
                $tileCourses[ $tile['id']][ ] = array_merge( $c, $course );
            }
        }
    }
}

$tileCoursesJSON = json_encode( $tileCourses );
?>

<script type="text/javascript" charset="utf-8">
function showCourseInfo( x )
{
    swal({ 
        title : x.title
        , text : "<div align=\"left\">" + x.value + "</div>"
        , type : "info"
        , html : true
        });
}

function showRunningCourse( x )
{
    var slotId = x.value;
    var courses = <?php echo $tileCoursesJSON; ?>;
    var runningCourses = courses[ slotId ];
    var title;
    var runningCoursesTxt;

    if( runningCourses && runningCourses.length > 0 )
    {
        runningCoursesTxt = runningCourses.map( 
            function(x, index) { return (1 + index) + '. ' + x.name 
            + ' at ' + x.venue ; } 
        ).join( "<br>");

        title = "Following courses are running in slot " + slotId;
    }
    else
    {
        title = "No course is running on slot " + slotId;
        runningCoursesTxt = "";
    }

    swal({ 
        title : title
        , text : runningCoursesTxt
        , type : "info"
        , html : true
        });
}
</script>

 

<?php


echo '<h1>Slots </h1>';

echo printInfo( 
    "Some courses may modify these slot timings. In case of any discrepency
    , please write to <tt>acadoffice@ncbs.res.in</tt> " 
);

//echo printInfo( "
//    <ul>
//    <li> If a course is running in slot 1, then its time is 
//    represented by tiles 1A, 1B and 1C.  </li>
//    <li> No course should overlap with any other course's slot tiles.  </li>
//    <li> No course can run on red color tiles. These are reserved tiles. </li>
//    </ul>" 
//);

echo printInfo( 
    "Click on <button class=\"invisible\" disabled>1A</button> etc to see the 
    list of courses running on this slot this semester
    ");
$table = slotTable(  );
echo $table;

/*
 * Enrollment table.
 */
$m = "<h1>Enrollment table for " . __ucwords__( $sem) . ", $year courses</h1>";
echo $m;

if( isRegistrationOpen( ) )
{
    echo alertUser(
        "Registration link is now open! After login, visit <tt>My Courses</tt> page
        to enrol. 
        "
        );

}

$showEnrollText = 'Show Enrollement';
echo printInfo(
    "Click on the button <button disabled>$showEnrollText</button>to see the 
    list of enrolled students" 
    );


$enrollments = array( );

/**
    * @name Show the courses.
    * @{ */
/**  @} */

$table = '<table class="info">';
$table .= '<tr><th>Course <br> Instructors</th><th>Schedule</th><th>Slot Tiles</th><th>Venue</th>
    <th>Enrollments</th><th>URL</th> </tr>';

ksort( $slotCourses );
foreach( $slotCourses as $slot => $courses )
{
    foreach( $courses as $c )
    {
        $cid = $c[ 'id' ];
        $whereExpr = "year='$year' AND semester='$sem' AND course_id='$cid'";
        $registrations = getTableEntries(
            'course_registration', 'student_id', $whereExpr 
        );

        $enrollments[ $cid ] = $registrations;

        $cinfo = $c[ 'description' ];
        $cname = $c[ 'name' ];
        $cr = $c[ 'credits' ];

        $note = '';
        if( $c[ 'note' ] )
            $note = colored( '* ' . $c[ 'note' ], 'blue' );

        $cinfo = "<p><strong>Credits: $cr </strong></p>" . $cinfo;
        $schedule = humanReadableDate( $c[ 'start_date' ] ) . ' - ' 
            . humanReadableDate( $c[ 'end_date' ] );

        $slotInfo = getCourseSlotTiles( $c, $slot );
        $instructors = getCourseInstructors( $cid );

        $table .= '<tr>
            <td> <button onclick="showCourseInfo(this)" class="courseInfo" 
            value="' . $cinfo . '" title="' . $cname . '" >' . $cname . '</button><br>' 
            . $instructors . '</td>
            <form method="post" action="#">
            <input type="hidden" name="course_id" value="' . $cid . '">
            <td>' .  $schedule . '</td>
            <td>' . "<strong> $slotInfo </strong> <br>" 
                  .  '<strong>' . $note . '</strong></td><td>' 
                  .  $c[ 'venue' ] . '</td>
            <td>' . count( $registrations ) . '</td>';

        // If url is found, put it in page.
        if( $c['url'] )
            $table .= '<td><a target="_blank" href="' . $c['url']  
                . '">Course page</a></td>';
        else
            $table .= '<td></td>';

        $table .= '<td> <button name="response" value="show_enrollment">
            <small>' . $showEnrollText . '</small></button></td>
            </form>';
        $table .= '</tr>';
    }
}

$table .= '</table><br/>';

echo '<div style="font-size:small">';
echo $table;
echo '</div>';

echo closePage( );

/**
    * @name Show enrollment.
    * @{ */
/**  @} */
if( $_POST )
{

    $cid = $_POST[ 'course_id'];
    $courseName = getCourseName( $cid );

    echo '<h3>Enrollment for course ' . $courseName .'</h3>';

    $table = '<table class="show_events">';
    $count = 0;
    foreach( $enrollments[$cid]  as $r )
    {
        $count += 1;
        $studentId = $r[ 'student_id' ];
        $login = loginToText( $studentId );
        $table .= '<tr>';
        $table .= '<td>' . $count . '</td><td>' . $login . '</td>';
        $table .= '<td>' . $r[ 'type' ] . "</td>";
        $table .= '</tr>';
    }

    $table .= '</table>';
    echo '<div style="font-size:small">';
    echo $table;
    echo '</div>';

    echo '<br>';
    echo closePage( );
}

echo '</div>';

?>


<!-- Prefix Mail logo on mailto links -->
<!--
<script type="text/javascript" charset="utf-8">
$( "a[href^=\"mailto:\"]" ).each( function() {
    var text = $(this).html( );
    $(this).html(  "&#9993" + " " + text );
    });
</script>
-->
