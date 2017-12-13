<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// If current user does not have the privileges, send her back to  home 
// page.
if( ! isJCAdmin( $_SESSION[ 'user' ] ) )
{
    echo printWarning( "You don't have permission to access this page" );
    echo goToPage( "user.php", 2 );
    exit;
}

// Otherwise continue.
$jcs = getJCForWhichUserIsAdmin( $_SESSION['user'] );

$jcIds = array_map( function( $x ) { return $x['jc_id']; }, $jcs );

$currentJC = $jcIds[0];

echo "<h1>Manage subscriptions</h1>";

// Show table and task here.
$form = '<form method="post" action="user_jc_admin_submit.php">';
$form .= '<input type="text" name="logins" placeholder="ram,shyam,jack" />';
$form .= arrayToSelectList( 'jc_id', $jcIds, array(), false, $jcIds[0] );
$form .= ' <button name="response" value="Add">Add Subscription</button>';
$form .= '</form>';
echo $form;

foreach( $jcIds as $currentJC )
{
    $subs = getJCSubscriptions( $currentJC );
    $subTable = '<table class="info">';
    foreach( $subs as $i => $sub )
    {
        $subTable .= '<tr>';
        $login = $sub['login'];
        $info = getLoginInfo( $login );
        $name = arrayToName( $info );
        $email = mailto( $info[ 'email' ] );
        $subTable .= '<td>' . ($i+1) . "</td><td> $login </td>
            <td>$name ($email) </td>";

        $subTable .= '<form method="post" action="user_jc_admin_submit.php">';
        $subTable .= '<td>';
        $subTable .= '<input type="hidden" name="login" value="' . $login . '" />';
        $subTable .= '<input type="hidden" name="jc_id" value="' . $currentJC . '" />';
        $subTable .= '<button style="float:right;" onclick="AreYouSure(this)" 
                              name="response" >' . $symbDelete . '</button>';
        $subTable .= '</td>';
        $subTable .= '</form>';

        $subTable .= '</tr>';
    }

    $subTable .= '</table>';
    echo '<h2>Subscription list of ' . $currentJC . '</h2>';
    echo $subTable;
}


echo goBackToPageLink( "user.php", "Go back" );

?>
