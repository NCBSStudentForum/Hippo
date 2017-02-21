<?php

include_once( 'tohtml.php' );

echo userHTML( );

$info = getUserInfo( $_SESSION['user'] );

$picPath = $_SESSION[ 'conf' ]['data']['user_imagedir'] . 
    '/' . $_SESSION['user'] . '.png';


echo alertUser( 
    "<p>Make sure your <tt>TITLE</tt>, <tt>JOINED ON</tt> and picture are 
    correct. </p>"
    );

echo '<table class="">';
echo '<tr><td>';

if( file_exists( $picPath ) )
    echo showImage( $picPath );
else 
{
    echo printInfo( "I could not find your picture in my database.
        Please upload one."
    );
}
echo '</td></tr><tr><td>';

// Form to upload a picture
echo '<form action="user_upload_picture.php" 
    method="post" 
    enctype="multipart/form-data">';

echo '<p><small>
    This picture will be used in AWS notifications. It will be 
    rescaled to fit 5 cm x 5 cm space. I will not accept any picture bigger 
    than 1MB in size. Allowed formats (PNG/JPG/GIF/BMP). 
    </small></p>
    ';
echo '<input type="file" name="picture" id="picture" value="" />';
echo '<button name="Response" title="Upload your picture" value="upload">' 
        . $symbUpload . '</button>';
echo '</form>';
echo '</td></tr>';
echo '</table>';
echo '<br>';

// This is second table.
echo "<form method=\"post\" action=\"user_info_action.php\">";
echo dbTableToHTMLTable( 'logins', $info
    , $editables = Array( 'title', 'first_name', 'last_name'
        , 'alternative_email' , 'institute', 'valid_until', 'joined_on'
        )
    );
echo "</form>";

if( ! $info['eligible_for_aws'] )
    echo printWarning( "If you should be 'ELIGIBLE FOR AWS', let academic office know." );

echo goBackToPageLink( "user.php", "Go back" );

?>
