<?php

include_once 'header.php';
include_once( 'tohtml.php' );

echo userHTML( );

$info = getUserInfo( $_SESSION['user'] );

// Collect all faculty
$faculty = getFaculty( );
$facultyByEmail = array( );
foreach( $faculty as $fac )
    $facultyByEmail[ $fac[ 'email' ] ] = $fac;

$facEmails = array_keys( $facultyByEmail );

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete pi.
$( function() {
    // These emails must not be key value array.
    var emails = <?php echo json_encode( $facEmails ); ?>;
    $( "#logins_pi_or_host" ).autocomplete( { source : emails }); 
    $( "#logins_pi_or_host" ).attr( "placeholder", "type email of your supervisor" );
});
</script>


<?php
$conf = getConf( );
$picPath = $conf['data']['user_imagedir'] . '/' . $_SESSION['user'] . '.jpg';

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
echo '<button name="Response" title="Upload your picture" value="upload">Upload</button>';
echo '</form>';
echo '</td></tr>';
echo '</table>';
echo '<br>';

$editables = Array( 'title', 'first_name', 'last_name', 'alternative_email'
    , 'institute', 'valid_until', 'joined_on', 'pi_or_host' 
    );

echo "<form method=\"post\" action=\"user_info_action.php\">";
echo dbTableToHTMLTable( 'logins', $info, $editables );
echo "</form>";

if( strtoupper( $info['eligible_for_aws'] ) == "NO" )
    echo alertUser( 
        "If you are 'ELIGIBLE FOR AWS', please write to academic office." 
    );

//echo '<h3>Submit request to academic office</h3>';
//$form = ' <form method="post" action="user_aws_request.php">';
//if( strtoupper( $info['eligible_for_aws'] ) == "YES" )
//    $form .= ' <button type="submit" name="request_to_academic_office" 
//        value="remove_me_from_aws_list">Remove me from AWS list</button> ';
//else
//    $form .= ' <button type="submit" name="request_to_academic_office"
//        value="add_me_to_aws_list">Add me to AWS list</button> ';
//
//$form .= '</form>';
//echo $form;

echo goBackToPageLink( "user.php", "Go back" );

?>
