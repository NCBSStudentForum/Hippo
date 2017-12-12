<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

$awsSpeakers = getTableEntries( 'logins', 'login'
    , "eligible_for_aws='YES' AND status='ACTIVE'
    " );

$speakerPiMap = array( );
$logins = array( );
foreach( $awsSpeakers as $login )
{
    $piOrHost = getPIOrHost( $login[ 'login' ] );
    $logins[] = $login[ 'login' ];
    $speakerPiMap[ $piOrHost ][] = $login;
}
ksort( $speakerPiMap );

// Collect all faculty
$faculty = getFaculty( );
$facultyByEmail = array( );
foreach( $faculty as $fac )
    $facultyByEmail[ $fac[ 'email' ] ] = $fac;
$facEmails = array_keys( $facultyByEmail );

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete pi.
$( function() {
    // These emails must not be key value array.
    var emails = <?php echo json_encode( $facEmails ); ?>;
    var logins = <?php echo json_encode( $logins ); ?>;
    $( "#pi_or_host" ).autocomplete( { source : emails });
    $( "#pi_or_host" ).attr( "placeholder", "type email of supervisor" );
    $( "#login" ).autocomplete( { source : logins });
    $( "#login" ).attr( "placeholder", "Speaker login" );
});
</script>


<?php

/**
    * @name User interface.
    * @{ */
/**  @} */

echo '
    <form action="#" method="post" accept-charset="utf-8">
    <table border="0">
    <tr>
        <td><input type="text" name="login" id="login" placeholder="Speaker id"/></td>
        <td><input type="text" name="pi_or_host" id="pi_or_host" placeholder="supervisor email"/></td>
        <td><button type="submit" name="response" value="update_pi_or_host">Update Speaker PI/HOST</button></td>
    </tr>
    </table>
    </form>
    ';

if( __get__( $_POST, 'response', '' )  == 'update_pi_or_host' )
{
    // Show only this user.
    $login = $_POST[ 'login' ];
    $pi = $_POST[ 'pi_or_host' ];
    if( $login )
        updateTable( 'logins', 'login', 'pi_or_host', $_POST );


    // Now display the updated list.
    $speakers = $speakerPiMap[ $_POST[ 'pi_or_host' ] ];
    echo '<h2> Updated AWS list for ' . $_POST[ 'pi_or_host' ] . '</h2>';

    $table = '<table class="info">';
    $table .= '<tr>';
    foreach( $speakers as $i => $login )
    {
        $speaker = getUserInfo( $login[ 'login' ] );
        $table .= "<td>" . arrayToName( $speaker ) . '</td>';
        if( ($i + 1 ) % 10 == 0 )
            $table .= "</tr><tr>";
    }
    $table .= '</tr>';
    $table .= '</table>';
    echo $table;
}

echo ' <h2>Table of active speakers</h2> ';
foreach( $speakerPiMap as $pi => $speakers )
{
    echo "<h3>AWS Speaker list for " . $pi . "</h3>";

    $table = "<table class=\"info\">";
    $table .="<tr>";

    $i = 0;
    foreach( $speakers as $login )
    {
        if( ! $login )
            continue;

        $speaker = getLoginInfo( $login['login'] );

        $i ++;
        $table .= "<td> $i " . arrayToName( $speaker ) . "<br />
            <tt>(" .  $speaker[ 'email' ] . ")</tt></td>";
        if( $i % 4 == 0 )
            $table .= "<tr></tr>";
    }
    $table .= "</tr>";
    $table .= "</table>";
    echo $table;
}


echo goBackToPageLink( "admin_acad.php", "Go back" );


?>
