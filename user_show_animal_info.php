<?php

include( 'tables.php' );

echo animalToHTMLTable( $_POST['animal_id'] );

?>

<div style="float: right">
    <?php echo goBackToPageLink( "user.php" ); ?>
</div>

