<?php

include_once( 'tohtml.php' );

echo userHTML( );

$info = getUserInfo( $_SESSION['user'] );

$picPath = $_SESSION[ 'conf' ]['data']['user_imagedir'] . 
    '/' . $_SESSION['user'] . '.png';

echo "<h3>Your details from LDAP</h3>";

echo arrayToVerticalTableHTML( $info, "user", ''
   , Array( 'roles', 'status', 'institute', 'created_on', 'last_login',
   'valid_until', 'alternative_email' )
);

echo "<h3>Edit details </h3>";

echo printInfo( 
    "<p>&#x26a0 Atleast, select your TITLE and JOINED ON date and 
    update your picture. </p>" 
);

echo '<table class="editable_user_picture">';
echo '<tr><td>';

if( file_exists( $picPath ) )
{
    echo '<img class="login_picture" width="200px"
        height="auto" src="' . dataURI( $picPath, 'image/png' ) . '" >';
}
else 
{
    echo printInfo( "I could not find your picture in my database.
        Please upload one."
    );
}
echo '</td><td>';

// Form to upload a picture
echo '<form action="user_upload_picture.php" 
    method="post" 
    enctype="multipart/form-data">';

echo '<p>
    This picture will be used in AWS notifications. It will be 
    rescaled to fit 5 cm x 5 cm space. I will not accept any picture bigger 
    than 1MB in size. Allowed formats (PNG/JPG/GIF/BMP).
    </p>
    <br />
    ';
echo '<input type="file" name="picture" id="picture" value="" />';
echo '<button name="Response" title="Upload your picture" value="upload">' 
        . $symbUpload . '</button>';
echo '</form>';
echo '</td></tr>';
echo '</table>';

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
