<?php

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

/* --------------------------------------------------------------------------*/
/**
    * @Synopsis  Assign user to a JC presentation and send an email.
    *
    * @Param $data (array of data. Usually as $_POST )
    *
    * @Returns
 */
/* ----------------------------------------------------------------------------*/
function fixJCSchedule( $login, $data )
{

    $newId = getUniqueID( 'jc_presentations' );
    $data[ 'title' ] = '';
    $data[ 'status' ] = 'VALID';
    $data[ 'id' ] = getUniqueID( 'jc_presentations' );

    $entry = insertOrUpdateTable( 'jc_presentations'
        , 'id,presenter,jc_id,date,title', 'status'
        , $data );

    echo printInfo( 'Assigned user ' . $data[ 'presenter' ] .
        ' to present a paper on ' . dbDate( $data['date' ] )
        );

    $macros = array(
        'PRESENTER' => arrayToName( getLoginInfo( $login ) )
        , 'THIS_JC' => $data[ 'jc_id' ]
        , 'JC_ADMIN' => arrayToName( getLoginInfo( whoAmI( ) ) ) or 'NCBS Hippo'
        , 'DATE' => humanReadableDate( $data[ 'date' ] )
    );

    // Now create a clickable link.
    $jcPresentation = getJCPresentation( $data['jc_id'],  $data[ 'presenter' ], $data[ 'date' ] );
    $id = $jcPresentation[ 'id' ];
    $clickableQ = "update jc_presentations SET acknowledged='YES' WHERE id='$id'";
    $qid = insertClickableQuery( $login, "jc_presentation.$id", $clickableQ );

    if( $qid )
    {
        echo printInfo( "Successfully inserted clickable query" );
    }

    $clickableURL = queryToClickableURL( $qid, 'Click Here To Acknowledge' );
    $mail = emailFromTemplate( 'NOTIFY_PRESENTER_JC_ASSIGNMENT', $macros );

    $to = getLoginEmail( $login );
    $cclist = $mail['cc'];
    $subject = $data[ 'jc_id' ] . ' | Your presentation date has been fixed';


    // Add clickableQuery to outgoing mail.
    $body = $mail[ 'email_body' ];
    $body = addClickabelURLToMail( $body, $clickableURL );
    $res = sendHTMLEmail( $body, $subject, $to, $cclist );
    return $res;
}

function assignJCPresentationToLogin( $login, $data )
{
    return fixJCSchedule( $login, $data );
}

?>
