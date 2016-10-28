<?php 

// This is admin interface. We are here to manage the requests.

include_once( "header.php" );
include_once( "methods.php" );
include_once( "sqlite.php" );
include_once( "tables.php" );

?>

<h2> Pending requests </h2>

<?php 
$requests = getPendingRequests( ); 
?>

<form action="admin_request.php" method="post" accept-charset="utf-8">
<?php echo requestsToHTMLTable( $requests ); ?>
</form>

<button type="logout" value="Log out">Log out</button>
