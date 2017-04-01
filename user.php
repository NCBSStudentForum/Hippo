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
      See and edit (most of) your details. <small>This is one time task. If 
      you suppose to give AWS, please visit and check all details.
      </small>
      </td>
      <td> 
         <a href="user_info.php">Show/edit my profile</a> 
      </td>
   </tr>
   </table>';
echo $html;

echo "<h3> Manage course </h3>";

$thisSem = getCurrentSemester( ) . ' ' . getCurrentYear( );
echo '<table class="tasks">';

echo  '<tr>
        <td>
            Manage course for semester (' . $thisSem . ')
                <small> You can register/deregister for current courses.
        </td>
        <td>
            <a href="user_manages_courses.php">My current courses</a> 
        </td>
        </tr>';

echo '</table>';

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
            This is important for AWS speakers. You should do it before updating your 
            AWS entries.</small>
            </td>
          <td> 
             <a href="user_update_supervisors.php">Update TCM Members/Supervisors</a> 
          </td>
        </tr>
        <tr>
        <td>
            List of your Annual Work Seminar <br>
            <small> You can see your previous AWSs and update them. You can check 
            the details about upcoming AWS and provide preferred dates.
           </small>
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
    <td>Browse available venues to submit a booking request
    <small>On top-right corner <strong><tt>QuickBook</tt></strong> is a
    simpler interface to do booking. You can use either. 
    This interface shows more details. </small>
    </td>
      <td> <a href="bookmyvenue_browse.php">Browse and book a venue</a> </td>
   </tr>
   <tr>
   <td>You can see your unapproved requests. You can modify their description, and 
        cancel them if neccessary.
      </td>
      <td> <a href="user_show_requests.php">My booking requests</a> </td>
   </tr>
   <tr>
      <td>These booking requests have been approved (we call them events). You can 
         still edit their title and description (and also cancel them). </td>
      <td> <a href="user_show_events.php">My approved events</a></td>
   </tr>
   </table>';

echo $html;


echo "<h3>Talk manangement </h3>";
echo '<table class="tasks">
   <form action="user_register_talk.php" method="post">
    <tr>
        <td> 
        Register a new talk, seminar, or a thesis seminar. <small>Keep the 
        email and photograph of speaker handy, not neccessary but highly recommended.
        </small>
        </td><td>
            <a href="user_register_talk.php">Register talk/seminar</a></td>
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

// Nilami store.
echo "<h3>Community services</h3>";
echo '<table class="tasks">
   <form action="user_register_talk.php" method="post">
   <tr>
        <td> You can put an item of sell or put bids on items available. </td>
        <td> <a href="user_sells.php">Sell</a> and <a href="user_buys.php">Buy</a></td>
    </tr><tr>
        <td>You can create alert so when a new entry is created you recieve an 
            email alert. You can also create an entry avaible for renting.
        </td>
       <td> <a href="user_tolet.php">TO-LET List</a></td> </td>
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
       $html .= '<tr><td>Approve/reject, modify or cancel booking requests.</td>
      <td> <a href="bookmyvenue_admin.php">BookMyVenue Admin</a></td> </tr>';
   if( in_array( "AWS_ADMIN", $roles ) )
       $html .= '<tr><td>Schedule AWS. Update AWS speaker list. 
       Manage running courses and more ...
      </td>
      <td> <a href="admin_acad.php">Academic Admin</a></td> </tr>';
   $html .= "</table>";
   echo $html;
}

?>
