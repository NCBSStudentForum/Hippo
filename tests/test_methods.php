<?php

include_once( 'methods.php' );


$pat = constructRepeatPattern( "Mon", "", "" );
echo "User pattern $pat \n";
echo " My construction ";
$pat = repeatPatToDays( $pat );
var_dump( $pat );

?>
