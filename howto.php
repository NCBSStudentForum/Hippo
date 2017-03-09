<?php
include_once 'header.php';
include_once 'methods.php';

echo '<h2>0. Login</h2>';
echo printInfo('
    You must login first, using your NCBS or InStem login id. If it is your first time,
    you will be taken to a page to review your profile. Kindly <tt>review/edit</tt> your details.
    If you are suppose to give Annual Work Seminar (AWS), you <strong>must</strong> double 
    check all entries. In case of discrepency, write email to Academic Office.
    <br>
    You top-right corner, there is a box where shortcut links are provided. Any 
    time you feel lost, click on <tt>MyHome</tt> link to go to your home.

    Whatever you can do is listed on this page.
    ' );

echo '<h2>How to create a booking request</h2>';

echo printInfo( "
    <ul>
        <li>
            Click <tt>QuickBook</tt> on the top-right corner to create your booking.
        </li>

        <li>
            You will be asked for date, time etc. click on 'Scan' (Lens icon) to 
            see the available venues for given date/time and specifications.
        </li>

        <li>
            Press select (Tick icon) in front of your preferred venue, you will be
            asked to fill-in details for your booking. Please make sure you fill it under
            the right category.
        </li>

        <li>
            Once a request is made, your slot/venue is blocked. You will receive an
            email from Hippo (check your spam folder if spam filter is ON).
        </li>

        <li>
            Your booking will ONLY be officially confirmed after approval 
            from the Hippo admin. 
            You will also receive confirmation email after approval/disapproval.
        </li>

        <li>
            Recurrent bookings can be created by filling the repeat pattern in your
            request, which would be for a maximum of 6 months period. You will receive an
            email alert to renew your booking.
        </li>
    </ul> 
    " );

echo '<h2>How to cancel or edit booking request/event?</h2>';

echo printInfo( '
    <ul>
        <li>
        To edit or cancel request, click on <tt>My Home</tt> on the top right 
        corner and follow <tt>Manage my booking requests</tt>.
        </li>
        <li>
        To edit or cancel officially confirmed events, click on <tt>My Home</tt> 
        on the top right corner and follow <tt>Manage my booked events</tt>.
        </li>
    </ul>' );

echo printInfo( " All the booked events can be viewed here: https://www.ncbs.res.in/hippo/allevents.php" );

echo "<p>
    PS: Any suggestion to improve Hippo is welcome. If for some reason, you are
    unable to book any event on Hippo, just send your booking request details to
    bookmyvenue@ncbs.res.in " ;

?>

