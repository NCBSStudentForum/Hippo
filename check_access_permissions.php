<?php

include_once( 'database.php' );

function requiredPrivilege( $role ) 
{
    $roles = getRoles( $_SESSION['user'] );
    return in_array( $role, $roles );
}

function anyOfTheseRoles( $roles )
{
    if( array_key_exists( 'user', $_SESSION ) )
    {
        $userRoles = getRoles( $_SESSION['user'] );
        foreach( $roles as $role )
            if( in_array( $role, $userRoles ) )
                return true;
    }
    return false;
}

function allOfTheseRoles( $roles )
{
    $userRoles = getRoles( $_SESSION['user'] );
    foreach( $roles as $role )
        if( ! in_array( $role, $userRoles ) )
            return false;
    return true;
}

function mustHaveAnyOfTheseRoles( $roles )
{
    if( anyOfTheseRoles( $roles ) ) 
        return( 0 );
    else
    {
        echo printWarning( "You don't have permission to access this page" );
        goToPage( "index.php", 0 );
        exit( 0 );
    }
}

function mustHaveAllOfTheseRoles( $roles )
{
    if( allOfTheseRoles( $roles ) ) 
        return( 0 );
    else
    {
        echo printWarning( "You don't have permission to access this page" );
        goToPage( "index.php", 0 );
        exit( 0 );
    }
}


?>
