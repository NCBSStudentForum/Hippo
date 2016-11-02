<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

//var_dump( $_POST );

$gid = $_POST['gid'];

$editable = Array( "title", "description" );

echo "<p class=\"info\"> You can only change fields: " . implode( ", ", $editable ) 
    . " here. If you want to change some other fields, you have to delete 
    this request a create a new one. </p>";

if( strtolower($_POST['response']) == 'edit' )
{
    $requests = getRequestByGroupId( $gid );
    // We only edit once request and all other in the same group should get 
    // modified accordingly.
    $request = $requests[0];
    echo "<form method=\"post\" action=\"user_show_requests_edit_submit.php\">";
    echo requestToEditableTableHTML( $request, $editable );
    echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\" />";
    echo "<button class=\"submit\" name=\"response\" value=\"submit\">Submit</button>";
    echo "</form>";
}

else if( strtolower($_POST['response']) == 'cancel' )
{
    changeStatusOfRequests( $_POST['gid'], 'CANCELLED' );
}
else
{
    echo printWarning( "Bad response " .  $_POST['response']  );
}

echo "<div style=\"float:left\">";
echo goBackToPageLink( "user_show_requests.php", "Go back");
echo "</div>";
