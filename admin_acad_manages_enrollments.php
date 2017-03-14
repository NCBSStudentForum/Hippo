<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );


$year = getCurrentYear( );
$sem = getCurrentSemester( );

$runningCourses = array();
foreach( getSemesterCourses( $year, $sem ) as $c )
    $runningCourses[ $c[ 'course_id' ] ] = $c;

$runningCoursesSelect = arrayToSelectList( 'course', array_keys( $runningCourses ) );
$taskSelect = arrayToSelectList( 'task', array( 'Change enrollement', 'Grade' ) );

echo '<h2>Enrollement</h2>';
echo '<form method="post" action="">';
echo "<table>
    <tr>
        <td><input type=\"text\" name=\"semester\" value=\"$sem\" ></td>
        <td><input type=\"text\" name=\"year\" value=\"$year\" ></td>
        <td>" . $runningCoursesSelect . "</td>
        <td>" . $taskSelect . "</td>
        <td><button type=\"submit\">Submit</button>
    </tr> 
    </table>";

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
