<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'ADMIN' ) );

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

// We are here to send an email. 
$email = array( );
if( $_POST )
{
    $templ = json_decode( $_POST[ 'template' ], $assoc = true );

    $email[ 'email_body' ] = html2Markdown( $templ[ 'email_body'], true );
    $email[ 'recipients' ] = $templ[ 'recipients'] ;
    $email[ 'cc' ] = $templ[ 'cc'] ;
    $email[ 'subject'] = $_POST[ 'subject' ];

    echo '
        <form method="post" action="admin_acad_send_email_action.php">
        <table class="tasks">
        <tr>
            <td>Recipients</td>
            <td><input class="long" name="recipients" type="text" value="' . $email[ 'recipients' ] . '"></td>
        </tr>
        <tr>
            <td>CC</td>
            <td><input class="long" name="cc" type="text" value="' . $email[ 'cc' ] . '"></td>
        </tr>
        <tr>
            <td>Subject</td>
            <td><input class="long" name="subject" type="text" value="' . $email[ 'subject' ] . '"></td>
        </tr>
        <tr>
            <td>Email body</td>
            <td><textarea name="email_body" rows="20" cols="80">'
                . $email[ 'email_body' ] . '</textarea></td>
        </tr>
        <tr><td></td>
            <td> 
                <button style="float:right" name="response" value="send">Send</button> 
            </td>
        </tr>
        </table>
        </form>'
        ;
}


echo goBackToPageLink( 'admin_acad_email_and_docs.php', 'Go back' );


?>
