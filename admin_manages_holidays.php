<?php

include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );

echo userHTML( );

echo '<h3>Add a holiday </h3>';
echo '
    <form method="get" action="">
    <table border="0">
    <tr>
        <th>Select date</th>
        <th>Description</th>
    </tr>
    <tr>
        <td> <input type="text" name="date" class="datepicker" value="" > </td>
        <td> <input type="text" name="description" value="" > </td>
        <td>
            <button type="submit" name="response" value="Add">Add</button>
        </td>
    </tr>
    </table>
    </form>
    ';

// Add or delete an entry.
if( isset( $_GET[ 'response' ] ) )
{
    if( $_GET[ 'response' ] == 'Add' )
    {
        if( $_GET['date'] && $_GET['description'] )
        {
            $res = insertIntoTable( "holidays", "date,description", $_GET );
            if( $res )
            {
                echo printInfo( "Added holiday successfully" );
                goToPage( "admin_manages_holidays.php", 1);
                exit;
            }
            else
                echo minionEmbarrassed( "Could not add holiday to database" );
        }
        else
        {
            echo printWarning( 
                "Either 'date' or 'description' of holiday was incomplete" 
            );
            goToPage( "admin_manages_holidays.php", 1);
            exit;
        }
    }
    elseif( $_GET[ 'response' ] == 'Delete' )
    {
        $res = deleteFromTable( 'holidays', 'date,description', $_GET );
        if( $res )
        {
            echo printInfo( "Successfully deleted entry from holiday list" );
            goToPage( "admin_manages_holidays.php", 1);
            exit;
        }
        else
        {
            echo minionEmbarrassed( 
                "Could not delete holiday from the list"
            );
        }
    }
}

echo '<h3>List of holidays in my database</h3>';

$holidays = getHolidays( );
foreach( $holidays as $index => $holiday )
{
    echo '<form method="get" action="">';
    echo '<table>';
    echo '<tr>';
    echo '<td>' . ($index + 1) . '</td><td>' . arrayToTableHTML( $holiday, 'show_info' ) . '</td>';
    echo '<td> 
        <input type="hidden" name="date" value="' . $holiday['date'] . '" >
        <input type="hidden" name="description" value="' . $holiday['description'] . '"/>
        <button name="response" value="Delete">Delete</button> 
        </td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

}



echo goBackToPageLink( "admin.php", "Go back" );

?>
