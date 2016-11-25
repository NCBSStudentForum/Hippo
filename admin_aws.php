<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'database.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

echo userHTML( );

echo "<h2>AWS Admin</h2>";

echo '<table class="admin">';
echo '<tr>
    <td>Manage pending requests</td>
    <td> <a href="admin_aws_manages_requests.php">Manage pending requests</a> </td>
    </tr>';
echo '</table>';

echo "<h3> Information</h3>";
echo '
  <table border="0" class="admin">
    <tr>
    <td>AWS summary
        <small>See the summary of all AWSs </small>
    </td>

      <td><a href="admin_aws_summary_user_wise.php">User wise</a>
      || <a href="admin_aws_summary_date_wise.php">Date
      wise</a></td>
    </tr>

  </table>';

echo "<h3>Scheduling</h3>";
echo '
  <table border="0" class="admin">
    <tr>
      <td>Manage upcoming AWS</td>
      <td><a href="admin_aws_manages_upcoming_aws.php">Manage
      upcoming AWSes</a></td>
    </tr>
  </table>';
?>
