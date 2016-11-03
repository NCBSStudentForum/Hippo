<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

//var_dump( $_POST );

$gid = $_POST['gid'];

$editable = Array( "title", "description" );

if( strtolower($_POST['response']) == 'edit' )
{
    echo "<p class=\"info\"> You can only change fields: " . implode( ", ", $editable ) 
        . " here. If you want to change some other fields, you have to delete 
        this request a create a new one. </p>";

    $requests = getRequestByGroupId( $gid );
    // We only edit once request and all other in the same group should get 
    // modified accordingly.
    $request = $requests[0];
    echo "<form method=\"post\" action=\"user_show_requests_edit_submit.php\">";
    echo dbTableToHTMLTable( "requests", $request, $editable );
    //echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\" />";
    //echo "<button class=\"submit\" name=\"response\" value=\"submit\">Submit</button>";
    echo "</form>";
}

else if( strtolower($_POST['response']) == 'cancel' )
{
    $res = changeStatusOfRequests( $_POST['gid'], 'CANCELLED' );
    if( $res )
    {
        echo printInfo( "Successfully cancelled request" );
        goToPage( "user_show_requests.php", 0 );
    }
    else
        echo printWarning( "Could not delete request " . $_POST['gid'] );

}
else
{
    echo printWarning( "Bad response " .  $_POST['response']  );
}

echo "<div style=\"float:left\">";
echo goBackToPageLink( "user_show_requests.php", "Go back");
echo "</div>";
