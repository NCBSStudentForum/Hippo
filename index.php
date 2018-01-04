<?php

include_once 'header.php';
include_once 'tohtml.php' ;
include_once 'methods.php' ;
include_once 'calendar/calendar.php' ;

// If user is already authenticated, redirect him to user.php
// NOTE: DO NOT put this block before loading configuration files.
if( array_key_exists( 'AUTHENTICATED', $_SESSION) && $_SESSION[ 'AUTHENTICATED' ] )
{
    if( $_SESSION[ 'user' ] != 'anonymous' )
    {
        echo printInfo( "Already logged-in" );
        goToPage( 'user.php', 0 );
        exit;
    }
}

$_SESSION['user'] = 'anonymous';
$_SESSION[ 'timezone' ] = getConfigFromDB(  'TIMEZONE' ) or 'Asia/Kolkata';

// Now create a login form.
echo "<table class=\"index\">";
echo '</tr>';
echo loginForm();
echo '</tr>';
echo "</table>";

// Show background image only on index.php page.
$thisPage = basename( $_SERVER[ 'PHP_SELF' ] );
if( strpos( $thisPage, 'index.php' ) !== false )
{
    // Select one background picture.
    $command = 'nohup python '
        . __DIR__ . '/fetch_backgrounds.py > /dev/null 2>&1 &'
        ;
    // Run command.
    shell_exec( $command );

    // Select one image from directory _backgrounds.
    $background = random_jpeg( "data/_backgrounds" );
    if( $background )
    {
        echo "<body style=\" background-image:url($background);
        filter:alpha(Opactity=30);opacity=0.3;
        width:800px;
        \">";
    }
}

echo '<br>';
echo '<div class="public_calendar">';
echo calendarIFrame( );
echo '</div>';

include_once 'footer.php';

?>
