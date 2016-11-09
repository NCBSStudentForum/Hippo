<?php

set_include_path( '..' );

include_once( 'methods.php' );


$pat = constructRepeatPattern( "Mon,Tue,Wed,Fri", "", "" );
echo "User pattern $pat \n";
echo " My construction ";
$pat = repeatPatToDays( $pat );
//var_dump( $pat );
print( "\nTest 2 </br> \n" );
$pat = constructRepeatPattern( "Tue Wed ", "", "2" );
echo( $pat );
print_r( repeatPatToDays( $pat ) );

?>
