<?php

include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );


echo userHTML( );

$faculty = getFaculty( );

echo "<h3>Add a new faculty</h3>";
echo '<form method="post" action="admin_manages_faculty_submit.php">';
echo dbTableToHTMLTable( 'faculty', array()
    , array( 'email', 'first_name', 'middle_name', 'last_name'
    , 'status', 'affiliation', 'url' ), 'add'
);
echo "</form>";



echo "<h3>Update existing faculty</h3>";

foreach( $faculty as $fac )
{
    echo '<form method="post" action="admin_manages_faculty_submit.php">';
    echo dbTableToHTMLTable( 'faculty', $fac
        ,  array( 'first_name', 'middle_name', 'last_name'
        , 'status', 'url', 'affiliation' ), 'edit' );
    echo '</form>';
}

echo goBackToPageLink( "admin.php", "Go back" );

?>
