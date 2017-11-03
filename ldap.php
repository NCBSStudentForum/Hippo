<?php

include_once 'methods.php';

function findGroup( $laboffice )
{
    if( strcasecmp( $laboffice, "faculty" ) == 0 )
        return "FACULTY";
    if( strcasecmp( $laboffice, "instem" ) == 0 )
        return "FACULTY";
    return $laboffice;
}

function serviceping($host, $port=389, $timeout=1)
{
    $op = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$op) return 0; //DC is N/A
    else {
        fclose($op); //explicitly close open socket connection
        return 1; //DC is up & running, we can safely connect with ldap_connect
    }
}

function getUserInfoFromLdap( $query, $ldap_ip="ldap.ncbs.res.in" )
{
    $port = 8862;

    $ldapArr = explode( "@", $query );
    $ldap = $ldapArr[0];

    // Search on all ports.
    $info = array( 'count' => 0 );

    if( 0 == serviceping( $ldap_ip, $port, 2 ) )
    {
        echo alertUser( "Could not connect to $ldap_ip : $port . Timeout ... " );
        return NULL;
    }

    $ds = ldap_connect($ldap_ip, $port );
    $r = ldap_bind($ds); 

    if( ! $r )
    {
        echo "LDAP binding failed. TODO: Ask user to edit details ";
        continue;
    }

    $base_dn = "dc=ncbs,dc=res,dc=in";
    $sr = ldap_search($ds, $base_dn, "uid=$ldap");
    $info = ldap_get_entries($ds, $sr);

    $result = array();
    for( $s=0; $s < $info['count']; $s++)
    {
        $i = $info[$s];

        //var_dump( $i );
        $laboffice = __get__( $i, 'profilelaboffice', array( 'NA') );
        $joinedOn = __get__( $i, 'profiledateofjoin', array( 'NA' ) );

        // We construct an array with ldap entries. Some are dumplicated with 
        // different keys to make it suitable to pass to other functions as 
        // well.
        if( trim( $i['sn'][0] ) == 'NA' )
            $i['sn'][0] = '';

        $profileId = __get__( $i, 'profileidentification', array( -1 ) );
        $profileidentification = $profileId[0];
        $title = $i[ 'profilecontracttype'][0];
        $designation = $i[ 'profiledesignation'][0];
        $active = $i[ 'profileactive' ][0];
        $result[ ] =  array( "fname" => $i['profilefirstname'][0]
                , "first_name" => $i['profilefirstname'][0]
                , "lname" => $i['profilelastname'][0]
                , "last_name" => $i['profilelastname'][0]
                , "uid" => $profileidentification
                , "id" => $profileidentification
                , "email" => $i['mail'][0]
                , "laboffice" => $laboffice[0]
                , "joined_on" => $joinedOn[0]
                , "title" => $title
                , "designation" => $designation
                , 'is_active' => $active
            );
    }

    if( count( $result ) > 0 )
        return $result[0];
    else
        return null;
}


/* --------------------------------------------------------------------------*/
/**
    * @Synopsis  Use LDAP to authenticate user.
    *
    * @Param $user
    * @Param $pass
    *
    * @Returns   
 */
/* ----------------------------------------------------------------------------*/
function authenticateUsingLDAP( $user, $pass )
{
    if( strlen( trim($user) ) < 1 )
        return false;

    $auth = false;
    $ports = array( 389, 18288, 19554 );
    foreach(  $ports as $port )
    {
        echo printInfo( "Trying to connect to port $port" );
        $res = ldap_connect( "ldaps://ldap.ncbs.res.in:$port" ) or 
            die( "Could not connect to ldap" );

        if( $res )
        {
            $bind = ldap_bind( $res, $user, $pass ) or die( "Could not bind to ldap" );
            if( $bind )
            {
                $auth = true;
                ldap_unbind( $res );
                ldap_close( $res );
                break;
            }
            ldap_close( $res );
        }
    }

    return $auth;
}


?>
