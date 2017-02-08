$(function() {
    var logins = <?php echo json_encode( $logins ); ?>;
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
});
