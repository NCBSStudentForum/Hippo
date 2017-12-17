<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$myJCS = getMyJCs( );
$myJCIds = getValuesByKey( $myJCS, 'jc_id' );
$jcSelect = arrayToSelectList( 'jc_id', $myJCIds, array(), false, $myJCIds[0] );

$default = array(
    'presenter' => $_SESSION[ 'user' ]
    , 'jc_id' => $jcSelect
    // If there is no Id in $_POST, create one. This is most likely to be new 
    // entry.
    , 'id' => __get__( $_POST, 'id', getUniqueID( 'jc_requests' ) )
);


// On this page below, we let user edit the entry here only.
if( __get__( $_POST, 'response', '' ) == 'Edit' )
    $default = array_merge( $_POST, $default );


echo '<h1> Submit a presentation request </h1>';

echo printInfo( "Make sure to add 'Why paper is interesting to be presented to
    community?' in <tt>DESCRIPTION</tt> field. " );

// Make a form.
$editables = 'jc_id,title,date,description,url';
echo ' <form action="#" method="post" accept-charset="utf-8">';
echo dbTableToHTMLTable( 'jc_requests', $default, $editables );
echo '</form>';

if( __get__( $_POST, 'response', '' ) == 'submit' )
{
    $_POST[ 'status' ] = 'VALID';


    $res = insertOrUpdateTable( 'jc_requests'
        , 'id,jc_id,presenter,date,title,description,url'
        , 'title,description,date,status,url'
        , $_POST
    );

    if( $res )
    {
        echo printInfo( 'Successfully added your entry' );
    }
}
else if( __get__( $_POST, 'response', '' ) == 'delete' )
{
    $id = $_POST[ 'id' ];
    $data[ 'id' ] = $id;
    $data[ 'status' ] = 'CANCELLED';
    $res = updateTable( 'jc_requests', 'id', 'status', $data);
    if( $res )
        echo printInfo( "Your request has been cancelled/invalidated." );
}

echo '<h1>My presentation requests </h1>';
$me = whoAmI( );

$requests = getTableEntries( 'jc_requests', 'date'
    , "status='VALID' AND presenter='$me'"
);

echo '<table class="show_events">';
echo '<th>Request</th><th>Votes</th>';

foreach( $requests as $i => $req )
{
    echo '<tr>';
    echo '<td>';
    echo ' <form action="#" method="post" accept-charset="utf-8">';
    echo dbTableToHTMLTable( 'jc_requests', $req, '', 'Edit', 'status,presenter' );
    echo '</form>';

    // Another form to delete this request.
    echo ' <form action="#" method="post" accept-charset="utf-8">';

    echo "<button name='response' onclick='AreYouSure(this)'
            title='Cancel this request'>Cancel</button>";
    echo "<input type='hidden' name='id' value='" . $req['id'] . "' />";
    echo '</form>';
    echo "</td>";

    $votes = count( getVotes( "jc_requests." . $req['id'] ));
    echo "<td> $votes </td>";
    echo '</tr>';
}
echo '</table>';




echo goBackToPageLink( 'user.php', 'Go Back' );

?>
