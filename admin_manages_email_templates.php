<?php

include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );

echo userHTML( );

$templates = getEmailTemplates( );

$html = '<form method="post" action="">';
$html .= '<select name="id"> 
    <option disabled selected>Add a new template</option>';

$templateDict = array( );
foreach( $templates as $template )
{
    $templateDict[ $template[ 'id' ] ] = $template;
    $html .= '<option name="id" value="' . $template[ 'id' ] .  '">' 
        . $template[ 'when_to_send' ] . '</option>';
}

$html .= "</select>";
$html .= '<button type="submit">Submit</button>';
$html .= "</form>";
echo $html;

$id = __get__( $_POST, 'id', '' );

echo "<h3>Add/Edit a new template</h3>";

// If existing id is selected then edit, else add.
$todo = 'add';
$defaults = array( "id" => "" );
if( $id )
{
    $defaults = $templateDict[ $id ];
    $todo = 'edit';
}

echo '<form method="post" action="admin_manages_email_templates_submit.php">';
echo dbTableToHTMLTable( 'email_templates', $defaults
    , array( 'id', 'when_to_send', 'description' ), $todo
    );
echo "</form>";

echo goBackToPageLink( "admin.php", "Go back" );

?>
