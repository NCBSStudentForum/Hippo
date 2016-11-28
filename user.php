<?php 

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

echo "<h3>Welcome " . $_SESSION['user'] . '</h3>';



$html = '<table class="show_user">';
   $html .= '
   <tr>
      <td>
         See and edit (most of) your details 
      </td>
      <td> 
         <a href="user_info.php">Show/Edit my details</a> 
      </td>
   </tr>
   <tr>
      <td>Browse available venues and submit a booking request</td>
      <td> <a href="bookmyvenue_browse.php">Book my venue</a> </td>
   </tr>
   <tr>
      <td>You can see your unapproved requests and modify their description and cancel
         them if neccessary.
      </td>
      <td> <a href="user_show_requests.php">Show/Edit BookMyVenue</a> </td>
   </tr>
   <tr>
      <td>These booking requests have been approved (we call them events). You can 
         still edit their description (and also cancel them). </td>
      <td> <a href="user_show_events.php">Show/Edit (my) upcoming events</a></td>
   </tr>
   <tr>
      <td>TODO: Journal clubs.</td>
      <td> <a href="user_jc.php">My Journal Club</a> </td>
   </tr>
   <tr>
      <td>Annual Work Seminar.</td>
      <td> 
         <a href="user_aws.php">My AWS</a> 
      </td>
   </tr>';

   $html .= "</table>";
echo $html;

if( anyOfTheseRoles( Array('ADMIN', 'BOOKMYVENUE_ADMIN'
, 'JOURNALCLUB_ADMIN', 'AWS_ADMIN' ) ) 
)
{
   echo "<h3>You have following roles as admin</h3>";
   $roles =  getRoles( $_SESSION['user'] );

   $html = "<table class=\"show_user\">";
      if( in_array( "ADMIN", $roles ) )
      $html .= '<tr><td> All migthy ADMIN </td>
         <td><a href="admin.php">Admin</a></td> </td>
   </tr>';
   if( in_array( "BOOKMYVENUE_ADMIN", $roles ) )
   $html .= '<tr><td>Approve/reject booking request, modify or cancel them as well.</td>
      <td> <a href="bookmyvenue_admin.php">BookMyVenue Admin</a></td> </tr>';
   if( in_array( "JOURNALCLUB_ADMIN", $roles ) )
   $html .= '<tr><td>TODO: Add journal club, invite people, select papers.</td>
      <td> <a href="journalclub_admin.php">JournalClub Admin</a></td> </tr>';
   if( in_array( "AWS_ADMIN", $roles ) )
   $html .= '<tr><td>Alpha: Schedule AWS, bug students for abstract. Send emails 
         periodically. 
      </td>
      <td> <a href="admin_aws.php">AWS Admin</a></td> </tr>';
   $html .= "</table>";
echo $html;
}

?>
