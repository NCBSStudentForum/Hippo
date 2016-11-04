<?php

include_once( 'database.php' );

function requiredPrivilege( $role ) 
{
    $roles = getRoles( $_SESSION['user'] );
    return in_array( $role, explode(",", $roles ) );
}

?>
