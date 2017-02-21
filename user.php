<?php 

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( 
    array( 'USER', 'ADMIN', 'BOOKMYVENUE_ADMIN' , 'AWS_ADMIN' ) 
    ); 
echo userHTML( );

$html = '<table class="tasks">';
$html .= '
   <tr>
      <td>
         See and edit (most of) your details 
      </td>
      <td> 
         <a href="user_info.php">Edit my profile</a> 
      </td>
   </tr>
   </table>';
echo $html;

// Only show this section if user is eligible for AWS.
$userInfo = getLoginInfo( $_SESSION[ 'user' ] );
if( $userInfo[ 'eligible_for_aws' ] == 'YES' )
{
    echo '<h3>Annual Work Seminars</h3>';
    $html = '<div class="tasks"><table class="tasks">';
    $html .= '
        <tr>
        <td>
            Add/update your TCM members and supervisors. <br/>
            <small>
            You should do it before doing anything else with your AWS entries. 
            Better you visit this link to see if everyone your AWS requires is in my 
            database.</small>
            </td>
          <td> 
             <a href="user_update_supervisors.php">Update TCM Members/Supervisors</a> 
          </td>
        </tr>
        <tr>
        <td>
            Annual Work Seminar <br>
            <small> You can see your previous AWS, and check the details about upcoming 
            AWS.  </small>
        </td>
          <td> 
             <a href="user_aws.php">My AWS</a> 
          </td>
        </tr>
        ';

    $html .= "</table></div>";
    echo $html;
}


echo '<h3>BookMyVenue</h3>';

$html = '<table class="tasks">';
$html .= '<tr>
      <td>Browse available venues and submit a booking request</td>
      <td> <a href="bookmyvenue_browse.php">Browse and book a venue</a> </td>
   </tr>
   <tr>
      <td>You can see your unapproved requests and modify their description and cancel
         them if neccessary.
      </td>
      <td> <a href="user_show_requests.php">Manage my booking REQUESTS</a> </td>
   </tr>
   <tr>
      <td>These booking requests have been approved (we call them events). You can 
         still edit their title and description (and also cancel them). </td>
      <td> <a href="user_show_events.php">Manage my BOOKED events</a></td>
   </tr>
   </table>';

echo $html;


echo "<h3>Talk manangement </h3>";
echo '<table class="tasks">
   <form action="user_register_talk.php" method="post">
    <tr>
        <td> 
            Register a new talk
        </td><td>
            <a href="user_register_talk.php">Register a new talk</a></td>
        </td>
    </tr><tr>
        <td> 
            Edit/update a previously registered talk and book a venue for it
        </td><td>
            <a href="user_manage_talk.php">Manage my talks</a></td>
        </td>
    </tr>
    </form>
    </table>';

if( anyOfTheseRoles( Array('ADMIN', 'BOOKMYVENUE_ADMIN'
, 'JOURNALCLUB_ADMIN', 'AWS_ADMIN' ) ) 
)
{
   echo "<h2>Admin</h2>";
   $roles =  getRoles( $_SESSION['user'] );

   $html = "<table class=\"tasks\">";
  if( in_array( "ADMIN", $roles ) )
      $html .= '<tr><td> All mighty ADMIN </td>
         <td><a href="admin.php">Admin</a></td> </td>
        </tr>';
   if( in_array( "BOOKMYVENUE_ADMIN", $roles ) )
       $html .= '<tr><td>Approve/reject booking request, modify or cancel them as well.</td>
      <td> <a href="bookmyvenue_admin.php">BookMyVenue Admin</a></td> </tr>';
   // if( in_array( "JOURNALCLUB_ADMIN", $roles ) )
   //      $html .= '<tr><td>TODO: Add journal club, invite people, select papers.</td>
   //   <td> <a href="journalclub_admin.php">JournalClub Admin</a></td> </tr>';
   if( in_array( "AWS_ADMIN", $roles ) )
       $html .= '<tr><td>Schedule AWS, bug students for abstract. Send emails 
         periodically. 
      </td>
      <td> <a href="admin_acad.php">Academic Admin</a></td> </tr>';
   $html .= "</table>";
   echo $html;
}

?>
