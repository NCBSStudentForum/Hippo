<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );
?>

<script>
function checkFrom(this) {
    if( this.email.value == "" )
    {
        alert( "Email empty" );
        return false;
    }
}
</script>

<?php
echo userHTML( );

echo "<h3>Adding a supervisor</h3>";

echo printInfo( "The supervisor is idenfitied by email addreess. It must be correct" );

echo "<form id=\"add_supervisor\" method=\"post\" action=\"user_add_supervisor_submit.php\">";
echo "<p> Except URL, all fields are mandatory</p>";
echo dbTableToHTMLTable( "supervisors"
    , $defaults = Array( )
    , $editables = Array( "email", "first_name", "middle_name", "last_name", "affiliation", "url" )
);
echo "</form>";

echo goBackToPageLink( "user_aws.php", "Go back to AWS" );

?>
