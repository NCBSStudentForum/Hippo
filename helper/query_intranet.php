<?php

function sendQuery( $query )
{
    $url =  "https://intranet.ncbs.res.in/people?$query&page=10";
    $content = file_get_contents( $url );
    return $content;
}

echo sendQuery( "field_personal_group_tid=111" );
/*
https://intranet.ncbs.res.in/people/Prof.%20Upinder%20S%20Bhalla?combine=aditya&mail=adityaa%40ncbs.res.in&field_personal_institute_value=All&field_personal_group_tid=All&field_personal_group_tid_1=All&field_personal_group_tid_2=All&field_personal_group_tid_3=All&field_personal_designation_value=

https://intranet.ncbs.res.in/people?name=adityaa%40ncbs.res.in&field_personal_institute_value=All&field_personal_group_tid=All&field_personal_group_tid_1=All&field_personal_group_tid_2=All&field_personal_group_tid_3=All&field_personal_designation_value=&field_personal_telephone_value=

 */

?>
