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
      you are eligible to give AWS, please visit and check details.
      </small>
      </td>
      <td>
         <a href="user_info.php"><i class="fa fa-user fa-3x"></i> My Profile</a>
      </td>
   </tr>
   </table>';
echo $html;

echo "<h1> Manage courses </h1>";

$thisSem = getCurrentSemester( ) . ' ' . getCurrentYear( );
echo '<table class="tasks">';
echo  "<tr>
        <td>
            Manage courses for semester ( $thisSem )
            <small>Register/deregister courses for this semster. </small>
        </td>
        <td>
            <a href=\"user_manages_courses.php\"><i class=\"fa fa-book fa-3x\"></i>My Courses</a>
        </td>
        </tr>";
echo '</table>';

// Journal club entry.
echo '<h1>Journal Club </h1>';
$table = '<table class="tasks">
    <tr>
        <td>
            Subscribe/Unsubscribe from journal club. See upcoming presentation.
            Vote on presentation requests.
         </td>
        <td>
            <a href="user_manages_jc.php">My Journal Clubs</a>
        </td>
    </tr>
    <tr>
        <td>Create a JC presentation  request </td>
        <td><a href="user_manages_jc_presentation_requests.php">
                My Presentation Requests</a>
        </td>
    </tr>';

if( isJCAdmin( $_SESSION[ 'user' ] ) )
{
    $table .= '<tr>
        <td><strong>Journal club admin </strong></td>
        <td><a href="user_jc_admin.php">JC Admin</a>
        </td>
    </tr>';
}


$table .= '</table>';

echo $table;

// Only show this section if user is eligible for AWS.
$userInfo = getLoginInfo( $_SESSION[ 'user' ] );
if( $userInfo[ 'eligible_for_aws' ] == 'YES' )
{
    echo '<h1>Annual Work Seminars</h1>';
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
            <small> See your previous AWSs and update them. Check
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


echo '<h1>Book My Venue</h1>';

$html = '<table class="tasks">';
$html .= '<tr>
    <td> Create a booking request <br>
    <small>Do not use for registering Talks or Thesis Seminar </small>
    </td>
      <td> <a href="quickbook.php"><i class="fa fa-hand-pointer-o fa-3x"></i>Book my venue</a> </td>
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


echo "<h1>Talks and Thesis Seminar </h1>";
echo '<table class="tasks">
   <form action="user_register_talk.php" method="post">
    <tr>
        <td>
        Register a new talk, seminar, or a thesis seminar. <small>Keep the
        email and photograph of speaker handy, not neccessary but highly recommended.
        </small>
        </td><td>
            <a href="user_register_talk.php">
            <i class="fa fa-comments fa-3x"></i> Register talk/seminar</a></td>
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

//// Nilami store.
//echo "<h3>Community services</h3>";
//echo '<table class="tasks">
//   <form action="user_register_talk.php" method="post">
//   <tr>
//        <td>Put a notification for selling or create an offer. </td>
//        <td> <a href="user_sells.php">Sell</a> and
//             <a href="user_buys.php">Put an offer</a></td>
//    </tr><tr>
//        <td> You can browse available TO-LET entries.
//        </td>
//       <td> <a href="user_browse_tolet.php">Browse TO-LET list</a></td> </td>
//   </tr>
//    </tr><tr>
//        <td>Create email-alerts so that when a new entry is created you recieve
//        notification. Or create a TO-LET entry.
//        </td>
//       <td> <a href="user_tolet.php">My TO-LET</a></td> </td>
//   </tr>
//   </form>
//   </table>';


if( anyOfTheseRoles( 'ADMIN,BOOKMYVENUE_ADMIN,JOURNALCLUB_ADMIN,AWS_ADMIN' ) )
{
   echo "<h1> <i class=\"fa fa-cogs\"></i>   Admin</h1>";
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
