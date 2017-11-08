<!-- Sweet alert -->
<script src="./node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" type="text/css" href="./node_modules/sweetalert2/dist/sweetalert.css">

<?php
include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'methods.php';
include_once './check_access_permissions.php';

if( ! (isIntranet() || isAuthenticated( ) ) )
{
    echo loginOrIntranet( );
    exit;
}

/* get this semester and next semester courses */

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

$slotUpcomingCourses = array( );
$nextSem = getNextSemester( );
$upcomingCourses = getSemesterCourses( $nextSem[ 'year' ], $nextSem['semester'] );
foreach( $upcomingCourses as $c )
{
    $cid = $c[ 'course_id' ];
    $course = getTableEntry( 'courses_metadata', 'id' , array('id' => $cid) ); 
    if( $course )
    {
        $slotId = $c[ 'slot' ];
        $tiles = getTableEntries( 'slots', 'groupid', "groupid='$slotId'" );
        $slotUpcomingCourses[ $slotId ][ ] = array_merge( $c, $course );
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
        , html : "<div align=\"left\">" + x.value + "</div>"
        , type : "info"
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
        , html : runningCoursesTxt
        , type : "info"
        });
}
</script>

 

<?php


echo '<h1>Slots </h1>';

echo printInfo( 
    "Some courses may modify these slot timings. In case of any discrepency
    please notify " . mailto( 'acadoffice@ncbs.res.in', 'Academic Office' ) . "."
);


echo printInfo( 
    "Click on tile <button class=\"invisible\" disabled>1A</button> etc to see the 
    list of courses running at this time.
    ");
$table = slotTable(  );
echo $table;

/* Enrollment table. */
echo "<h1>Running courses in " . __ucwords__( $sem) . ", $year semester</h1>";

$showEnrollText = 'Show Enrollement';
echo printInfo(
    "Click on the button <button disabled>$showEnrollText</button>to see the 
    list of enrolled students" 
    );


/**
    * @name Show the courses.
    * @{ */
/**  @} */

$table = '<table class="info">';
$table .= '<tr><th>Course <br> Instructors</th><th>Schedule</th><th>Slot Tiles</th><th>Venue</th>
    <th>Enrollments</th><th>URL</th> </tr>';
$table .= '<form method="post" action="#">';

// Go over courses and populate the entrollment array.
$enrollments = array( );
ksort( $slotCourses );
foreach( $slotCourses as $slot => $courses )
{
    foreach( $courses as $c )
    {
        $cid = $c[ 'course_id' ];
        $table .= '<tr>';
        $table .= courseToHTMLRow( $c, $slot, $sem, $year, $enrollments );
        $table .= '<td> <button name="response" value="show_enrollment">
                  <small>' . $showEnrollText . '</small></button></td>';
        $table .= '<input type="hidden" name="course_id" value="' . $cid . '">';
        $table .= '</tr>';
    }
}

$table .= '</form>';
$table .= '</table><br/>';

echo '<div style="font-size:small">';
echo $table;
echo '</div>';

/**
    * @name Show enrollment.
    * @{ */
/**  @} */

if( $_POST )
{

    echo '<h3>Enrollment for course ' . $courseName .'</h3>';

    $cid = $_POST[ 'course_id'];
    $courseName = getCourseName( $cid );
    $rows = [ ];
    $allEmails = array( );

    foreach( __get__($enrollments, $cid, array()) as $r )
    {
        $studentId = $r[ 'student_id' ];
        $info = getUserInfo( $studentId );
        $row = '';
        $row .= '<td>' . loginToText( $info, false) . '</td>';
        $row.= '<td><tt>' . mailto( $info[ 'email' ] ) . '</tt></td>';
        $row .= '<td>' . $r[ 'type' ] . "</td>";
        $rows[ $info[ 'first_name'] ] = $row;
        $allEmails[ ] = $info[ 'email'];
    }

    ksort( $rows );
    $count = 0;

    // Construct enrollment table.
    $table = '<table id="show_enrollmenents" class="show_events">';
    foreach( $rows as $fname => $row )
    {
        $count ++;
        $table .= "<tr><td>$count</td>" . $row . '</tr>';
    }
    $table .= '</table>';

    // Display it.
    echo '<div style="font-size:small">';
    echo $table;
    echo '</div>';

    // Put a link to email to all.
    if( count( $allEmails ) > 0 )
    {
        $mailtext = implode( ",", $allEmails );
        echo '<div>' .  mailto( $mailtext, 'Send email to all students' ) . "</div>";
    }
}


/*******************************************************************************
 * Upcoming courses.
 *******************************************************************************/
// Collect both metadata and other information in slotCourse array.


$table = '<table id="upcoming_courses" class="info">';
$table .= '<tr><th>Course <br> Instructors</th><th>Schedule</th><th>Slot Tiles</th><th>Venue</th>
    <th>Enrollments</th><th>URL</th> </tr>';

foreach( $slotUpcomingCourses as $slot => $ucs )
{
    foreach( $ucs as $uc )
    {
        $table .= '<tr>';
        $slot = $uc[ 'slot' ];
        $sem = getSemester( $uc[ 'end_date' ] );
        $year = getYear( $uc[ 'end_date' ] );

        $table .= courseToHTMLRow( $uc, $slot, $sem, $year, $upcomingEnrollments );
        $table .= '</tr>';
    }
}
$table .= '</table>';

// Show table.
if( count( $slotUpcomingCourses ) > 0 )
{
    echo '<h1>Upcoming courses</h1>';
    echo '<div style="font-size:small">';
    echo $table;
    echo '</div>';
}

echo '<br>';
echo closePage( );

?>
