<?php
include('html2text.php');

/**
 * Convert html to text and then send a plain text msg.
 */
function sendEmail($msg, $ldap) 
{
	$msg = html2text($msg);
	$to = $ldap."@iitb.ac.in";
	$cmd="echo '$msg' | mutt -s 'Your details have been edited/updated successfully' -e 'set copy=no'";
	$arg1=" -e \"set smtp_url=\"smtp://sandesh.ee.iitb.ac.in:25\"\"";
	$arg2=" -e \"set from=\"noreply@ee.iitb.ac.in\"\"";
	$arg3=" -e \"set realname=\"EE-Minion\"\"";
	$arg4=" -e \"set content_type=text/html\"";
	$arg5=" '$to' 2>&1";
	exec($cmd.$arg1.$arg2.$arg3.$arg5, $out);
	return $out;
}

function sendEmailWithSub($msg, $sub, $ldap) 
{
	$msg = html2text($msg);
	$to = $ldap."@iitb.ac.in";
	$cmd="echo '$msg' | mutt -s '$sub' -e 'set copy=no'";
	$arg1=" -e \"set smtp_url=\"smtp://sandesh.ee.iitb.ac.in:25\"\"";
	$arg2=" -e \"set from=\"noreply@ee.iitb.ac.in\"\"";
	$arg3=" -e \"set realname=\"EE-Minion\"\"";
	$arg4=" -e \"set content_type=text/html\"";
	$arg5=" '$to' 2>&1";
	exec($cmd.$arg1.$arg2.$arg3.$arg5, $out);
	return $out;
}

?>
