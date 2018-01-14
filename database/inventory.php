<?php

include_once 'database/base.php';
include_once 'methods.php';

function getUserInventory( $user ) : array
{
    return array( );

}


function getMyInvetory(  )
{
    $user = whoAmI( );
    return getUserInventory( $user );
}

?>
