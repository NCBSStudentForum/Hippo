<?php 

// This is admin interface. We are here to manage the requests.

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

?>

<h2> Pending requests </h2>

<?php 
$requests = getPendingRequestsGroupedByGID( ); 
?>

<form action="admin_request_review.php" method="post" accept-charset="utf-8">
<?php echo requestsToHTMLReviewForm( $requests ); ?>
</form>

<form method="POST" action="logout.php" >
<button type="logout" value="Log out">Log out</button>
</form>
