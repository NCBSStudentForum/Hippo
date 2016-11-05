<?php

function getUserInfoFromLdap( $ldap, $ldap_ip="ldap.ncbs.res.in" )
{
    $base_dn = 'dc=ncbs,dc=res,dc=in';
    $ds = ldap_connect($ldap_ip) or die( "Could not connect to $ldap_ip" );
    $r = ldap_bind($ds);
    $sr = ldap_search($ds, $base_dn, "uid=$ldap");
    $info = ldap_get_entries($ds, $sr);

    $result = array();
    for( $s=0; $s < $info['count']; $s++)
    {
        $i = $info[$s];
        array_push($result
            , array(
                "fname" => $i['givenname'][0]
                , "lname" => $i['sn'][0]
                , "uid" => $i['profileidentification'][0]
                , "email" => $i['mail'][0]
                , "laboffice" => $i['profilelaboffice'][0]
                , "joined_on" => $i['profiletenureend'][0]
            )
        );
    }
    return $result[0];
}

?>
