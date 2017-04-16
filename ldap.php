<?php

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

function getUserInfoFromLdap( $ldap, $ldap_ip="ldap.ncbs.res.in" )
{
    $ports = array( 389, 18288 );
    $baseDNs = array( 389 => 'dc=ncbs,dc=res,dc=in'
                        , 18288 => "dc=instem,dc=res,dc=in"
                    );

    // Search on all ports.
    $info = array( 'count' => 0 );
    foreach( $baseDNs  as $port => $base_dn )
    {

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

        $sr = ldap_search($ds, $base_dn, "uid=$ldap");
        $info = ldap_get_entries($ds, $sr);

        if( $info[ 'count' ] > 0  )
            break;
    }

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

        array_push($result
            , array(
                "fname" => $i['givenname'][0]
                , "first_name" => $i['givenname'][0]
                , "lname" => $i['sn'][0]
                , "last_name" => $i['sn'][0]
                , "uid" => $profileidentification
                , "id" => $profileidentification
                , "email" => $i['mail'][0]
                , "laboffice" => $laboffice[0]
                , "joined_on" => $joinedOn[0]
            )
        );
    }

    if( count( $result ) > 0 )
        return $result[0];
    else
        return null;
}

?>
