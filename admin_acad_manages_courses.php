<?php

include_once 'header.php';

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Javascript.
$courses = getTableEntries( 'courses_metadata' );

$instructors = array();
foreach( getFaculty( ) as $fac )
    $instructors[ ] = $fac[ 'email' ];

$coursesMap = array( );
$coursesId = array_map( function( $x ) { return $x['id']; }, $courses );

foreach( $courses as $course )
    $coursesMap[ $course[ 'id' ] ] = $course;

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete speaker.
$( function() {
    var coursesDict = <?php echo json_encode( $coursesMap ) ?>;
    var courses = <?php echo json_encode( $coursesId ); ?>;
    var instructors = <?php echo json_encode( $instructors ); ?>;
    $( "#course" ).autocomplete( { source : courses }); 
    $( "#course" ).attr( "placeholder", "autocomplete" );
    $( "input[id^=courses_metadata_instructor]" ).autocomplete( { source : instructors }); 
    $( "input[id^=courses_metadata_instructor]" ).attr( "placeholder", "autocomplete" );
});
</script>

<?php

// Logic for POST requests.
$course = array( 'id' => '', 'day' => '', 'start_time' => '', 'end_time' => '' );

echo "<h1>All courses</h1>";

echo coursesTable( );

$buttonVal = 'Add';

echo '<form method="post" action="">';
echo '<input id="course" name="id" type="text" value="" >';
echo '<button type="submit" name="response" value="show">Show details</button>';
echo '</form>';


// Show speaker image here.
if( array_key_exists( 'id', $_POST ) )
{
    // Show emage.
    $course = __get__( $coursesMap, $_POST['id'], null );
    if( $course )
    {
        echo arrayToVerticalTableHTML( $course, 'course' );
        $buttonVal = 'Update';
    }
}

echo '<h3>Add/Edit course details</h3>';

echo '<form method="post" action="admin_acad_manages_courses_action.php">';


echo dbTableToHTMLTable( 'courses_metadata', $course 
    , 'id,credits,name,description,instructor_1,instructor_2,instructor_3' 
        . ',instructor_4,instructor_5,instructor_6' 
        . ',comment'
    , $buttonVal
    );

echo '<button title="Delete this entry" type="submit" onclick="AreYouSure(this)"
    name="response" value="Delete">' . $symbDelete .
    '</button>';

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
