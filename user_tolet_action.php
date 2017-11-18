<?php 

include_once "header.php";
include_once "methods.php";
include_once "database.php";
include_once 'tohtml.php';
include_once 'mail.php';
include_once 'check_access_permissions.php';
 ?>


<?php

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

// Create a bid.
if( $_POST[ 'response' ] == 'New Alert' )
{
    echo printInfo( "Creating a new alert .. " );
    $res = insertIntoTable( 'alerts', 'login,on_table,on_field,value', $_POST );
    if( $res )
    {
        echo printInfo( "Successfully created a new alert. " );
        goBack( "user_tolet.php", 1 );
        exit;
    }
    else
        echo minionEmbarrassed( "Failed to create an alert" );


}
else if( $_POST[ 'response' ] == 'Add new listing' ) // Add new apartment
{
    echo printInfo( "Creating a new apartment listing" );
    $aptId = getNumberOfEntries( 'apartments', 'id' );
    $_POST[ 'id' ] = intval( $aptId[ 'id' ] ) + 1;
    $res = insertIntoTable( 'apartments'
            , 'id,open_vacancies,type,created_by,created_on,address,description' 
                . ',owner_contact,rent,advance' 
            , $_POST 
            );
    if( $res )
    {
        echo printInfo( 'A new apartment listing has been added' );

        //Now send alerts.
        $aptType = $_POST[ 'type' ];
        $alerts = getTableEntries( 'alerts', 'value'
                , "value='$aptType' AND on_table='apartments' AND on_field='type'"
            );

        echo printInfo( "Sending alerts to subcriber. Total " . count( $alerts ));
        foreach( $alerts as $alt )
        {
            $subject = 'A new apartment listing has been created';
            $msg = initUserMsg( $alt['login'] );
            $to = getLoginEmail( $alt[ 'login' ] );

            $apt = getTableEntry( 'apartments', 'id', $_POST );
            $msg .= arrayToVerticalTableHTML( $apt, 'info' );
            $msg .= "<p> You recieved this message because it matches one of the 
                alert you have created on TO-LET services </p>";

            echo printInfo( "Sending apartment alert to $to " );
            sendHTMLEmail( $msg, $subject, $to );
        }

        //echo goBack( 'user_tolet.php', 1 );
        //exit;
    }
    else
        echo minionEmbarrassed( 'Failed to insert apartment entry' );
}
else if( $_POST[ 'response' ] == 'Update listing' ) // Update apartment entry.
{
    echo printInfo( "Updatng apartment listing" );
    $res = updateTable( 'apartments'
                , 'id' 
                , 'type,open_vacancies,address,description' 
                . ',owner_contact,rent,advance,status' 
            , $_POST 
            );
    if( $res )
    {
        echo printInfo( 'Successfully updated  apartment listing. ' );
        echo goBack( 'user_tolet.php', 1 );
        exit;
    }
    else
        echo minionEmbarrassed( 'Failed to update apartment entry' );
}

echo goBackToPageLink( "user_tolet.php", "Go back" );

?>
