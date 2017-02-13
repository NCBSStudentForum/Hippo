<?php

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

echo userHTML( );
echo "<br>";

?>

<?php

$requests = getRequestOfUser( $_SESSION['user'], $status = 'PENDING' );

echo "<div stle>";
foreach( $requests as $request )
{
    $tobefiltered = Array( 
        'rid', 'modified_by', 'timestamp', 'user', 'url' , 'status'
        );
    $gid = $request['gid'];
    echo "<table class=\"info\" >";
    echo "<tr>";
    echo "<td>" . arrayToTableHTML( $request, "requests", NULL, $tobefiltered );
    echo '<form method="post" action="user_show_requests_edit.php">';
    echo "</td><td><button name=\"response\" title=\"Edit this request\"
        value=\"edit\"> $symbEdit </button>";
    echo "</td><td><button name=\"response\" title=\"Cancel this request\"
        value=\"cancel\"> $symbCancel </button>";
    echo "</td></tr>";
    echo "</table>";
    echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\">";
    echo '</form>';
}

echo "<div>";
echo goBackToPageLink( "user.php", "Go back" );

?>
