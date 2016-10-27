<?php

function getUserInfoFromLdap($ldap_ip="ldap.ncbs.res.in", $ldap)
{
    $base_dn = 'dc=ncbs,dc=res,dc=in';
    $ds = ldap_connect($ldap_ip) or die( "Could not connect to $ldap_ip" );
    $r = ldap_bind($ds);
    $sr = ldap_search($ds, $base_dn, "uid=$ldap");
    $info = ldap_get_entries($ds, $sr);

    $result = array();
    for( $i=0; $i < $info['count']; $i++)
    {
        $name = $info[$i]["givenname"][0];
        $lname = $info[$i]["sn"][0];
        $uid = $info[$i]["uid"][0];
        array_push($result
            , array("fname" => $name , "lname" => $lname, "uid" => $uid )
        );
    }
    return $result;
}

?>
