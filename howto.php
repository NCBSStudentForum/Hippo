<?php
include_once 'header.php';
include_once 'methods.php';

echo '<h2>Login</h2>';

echo '
    <p>
    Use your NCBS or InStem login id to login. If it is your first time,
    you will be taken to a page to review your profile. Kindly 
    <tt>review/edit</tt> your details.
    If you are suppose to give Annual Work Seminar (AWS), you 
    <strong>must</strong> double check all entries. In case of discrepency, 
    write email to Academic Office.
    </p>
    <p>
    You top-right corner, there is a box where shortcut links are provided. Any 
    time you feel lost, click on <tt>MyHome</tt> link to go to your home.
    Whatever you can do using Hippo is listed on this page.
    </p> ' ;

echo '<h2>How to create a booking request?</h2>';

echo "
    <ul>
        <li>
            Click <tt>QuickBook</tt> on the top-right corner to create your booking.
        </li>

        <li>
        You will be asked for date, start time, and end time.  And other optional
        information. click on 'Scan' <button disabled>" . $symbScan . "</button>
        (Lens icon) to see the 
        available venues for given date/time.
        </li>

        <li>
        Press <button disabled>" . $symbCheck . "</button> in front of your 
            preferred venue, you will be
            asked to fill details for your booking. Please make sure you fill it under
            the right <tt>CLASS</tt> (e.g. <tt>THESIS SEMINAR, LAB MEETING, TALK</tt>
            etc. ).
        </li>

        <li>
        Once a request is made, your slot/venue is blocked and an email has been 
        sent your way. If you are importing NCBS emails into other email accounts 
        such as google, do check your spam folder.
        </li>

        <li>
        Wait for someone from Hippo admins to confirm your request.
        You will also receive confirmation email after approval/disapproval.
        </li>

        <li>
            <strong>Recurrent bookings</strong> can be created by filling the repeat pattern in your
            request, which would be for a maximum of 6 months period. You will receive an
            email alert to renew your booking, 5 to 7 days in advance before your last
            event expires.
        </li>
    </ul> 
    ";

echo '<h2>How to cancel or edit booking request/event?</h2>';

echo '
    <ul>
        <li>
        To edit or cancel request, click on <tt>My Home</tt> on the top right 
        corner and follow <tt>My booking requests</tt>.
        </li>
        <li>
        To edit or cancel officially confirmed events, click on <tt>My Home</tt> 
        on the top right corner and follow <tt>My booked events</tt>.
        </li>
    </ul>';

echo "<p>All the booked events can be viewed 
    <a target=\"_blank\" 
    href=\"https://www.ncbs.res.in/hippo/allevents.php\">by clicking here</a>
    </p>" ;

echo "<p>TODO .. A lot here </p>";

?>

