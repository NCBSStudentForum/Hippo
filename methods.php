<?php 

include_once('display_content.php');
include_once( 'logger.php' );

date_default_timezone_set('Asia/Kolkata');

function venueToText( $venue )
{
    $txt = '';
    $txt .= $venue['id'] . ' ';
    $txt .= ' ' . $venue['strength'] . ' ';
    $txt .= '[' . $venue['type'] . ']' ;
    return $txt;
}

// Convert an integer to color.
function toColor($n) 
{
    $n = crc32($n % 1000);
    $n &= 0xffffffff;
    return("#".substr("000000".dechex($n),-6));
}

function venuesToHTMLSelect( $venues, $ismultiple = false )
{
    $multiple = '';
    $default = '-- select a venue --';
    $name = 'velue';
    if( $ismultiple )
    {
        $multiple = 'multiple size="5"';
        $default = '-- select multiple venues --';
        $name = 'venue[]';
    }

    $html = "<select $multiple name=\"$name\">";
    if( ! $ismultiple )
        $html .= "<option disabled selected value>$default</option>";

    foreach( $venues as $v )
    {
        $text = venueToText( $v );
        if( $v['suitable_for_conference'] == 'Yes' )
            $text .= '<font color=\"blue\"> +C </font>';
        if( $v['has_projector'] == 'Yes' )
            $text .= '<font color=\"blue\"> +P </font>';

        $venueid = $v['id'];
        $html .= "<option value=\"$venueid\"> $text </option>";
    }

    $html .= "</select>";
    return $html;
}

function generateRandomString($length = 10) 
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function appRootDir( )
{
   return  dirname( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] );
}

/* Go to a page relative to base dir. */
function goToPage($page="index.php", $delay = 3)
{
  echo printWarning("... Going to page $page in $delay seconds ...");
  $baseurl = appRootDir( );
  $url = "$baseurl/$page";
  header("Refresh: $delay, url=$url");
}

function goBackToPageLink( $url, $title = "Go back" )
{
    $html = "<br />";
    $html .= "<a style=\"float: left\" href=\"$url\">
            <font color=\"blue\" size=\"5\">$title</font>
        </a>";
    return $html;
}

function __get__( $arr, $what, $default = NULL )
{
    if( array_key_exists( $what, $arr ) )
        return $arr[$what];
    else
        return $default;
}

/**
    * @brief Convert a repeat pattern to dates.
    *
    * @param $pat This pattern is command separeted list of days,
    * weeks,durations. eg. 0/2/4,2/3,5 means that event will be scheduled on day
    * 0 (sun), day 2 (Tue), and day 4 (Thu), every 2nd and 3rd week for 5
    * months.
    *
    * @return List of dates generated from this pattern. 
 */
function repeatPatToDays( $pat )
{
    if( trim($pat) == '' )
        return;

    $exploded = explode( ",", $pat);
    $days = $exploded[0];
    // These are absolute indices of days.
    if( $days == "*" )
        $days = "0/1/2/3/4/5/6";

    $weeks = __get__( $exploded, 1, "*" );
    $durationInMonths = __get__( $exploded, 2, 6 );

    if( $weeks == "*" )
        $weeks = "0/1/2/3";

    $weeks = explode( "/", $weeks );
    $days = explode( "/", $days );

    $result = Array();

    // Get the base day which is Sunday. If today is not sunday then previous
    // Sunday must be taken into account.
    if( date('w', strtotime( 'today' ) ) == 0 )
        $baseDay = date( 'Y-m-d', strtotime( 'today' ) );
    else
        $baseDay = date( 'Y-m-d', strtotime( "previous Sunday" ));

    // Now fill the dates for given pattern.
    $dates = Array( );
    for( $i = 0; $i < 12;  $i ++ ) // Iterate of maximum duration.
        foreach( $weeks as $w )
            foreach( $days as $d )
            {
                $nday = (28 * $i) + (7 * intval($w)) + intval($d);
                $date = date( 'Y-m-d', strtotime( $baseDay . '+ ' . $nday . ' days ') );
                // Cool, if this day is more than $durationInMonths away, then
                // stop.
                $interval = date_diff( date_create($date), date_create($baseDay));
                $diffMonth = $interval->format( '%m' );
                if( $diffMonth < $durationInMonths )
                    array_push( $dates, $date );
                else
                    return $dates;
            }

    return $dates;
}

function daysToDate( $ndays, $baseDay = NULL )
{
    $bd = date("l", $baseDay);
    $result = Array( );
    $baseDay = date("Y-m-d", $baseDay);
    foreach( $ndays as $nd )
    {
        $date = date('Y-m-d', strtotime( $baseDay . ' + ' . $nd  . ' days'));
        array_push( $result, $date );
    }
    return $result;
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function humanReadableDate( $date )
{
    if( is_int( $date ) )
        return date( 'Y-M-d', $date );

    return date( 'Y-M-d', strtotime($date) );
}


// Return a format date for mysql database.
function dbDate( $date )
{
    if( is_int( $date ) )
        return date( 'Y-m-d', $date );

    return date( 'Y-m-d', strtotime( $date ) );
}

// Return the name of the day for given date.
function nameOfTheDay( $date )
{
    return date( 'l', strtotime( $date ) );
}

function getNumDaysInBetween( $startDate, $endDate )
{
    $start = new DateTime( $startDate );
    $end = new DateTime( $endDate );
    return intval($start->diff( $end )->format( "%R%a" ));
}

// Go back to calling page.
function goBack( )
{
    goToPage( $_SERVER['HTTP_REFERER'], 0 );
}

/**
    * @brief Construct a repeat pattern out of user queries.
    *
    * @param $daypat
    * @param $weekpat
    * @param $monthpat
    *
    * @return 
 */
function constructRepeatPattern( $daypat, $weekpat, $durationInMonths )
{
   $weekNum = Array( 
      "first" => 0, "second" => 1, "third" => 2, "fourth" => 3 
      , "1st" => 0, "2nd" => 1, "3rd" => 3, "4th" => 3
      , "fst" => 0, "snd" => 1, "thrd" => 3, "frth" => 3
   );

   if( ! trim( $daypat ) )
       return '';

   $repeatPat = '';
   $daypat = str_replace( ",", " ", $daypat );
   $weekpat = str_replace( ",", " ", $weekpat );

   $days = array_map( function( $day ) {
      return date('w', strtotime( $day ) ); }, explode( " ", $daypat )
   );
   $days = implode( "/", $days );

   $weeks = Array();
   if( $weekpat )
   {
      foreach( explode(" ", $weekpat ) as $w )
      {
         if( array_key_exists( $w, $weekNum ) )
            array_push( $weeks, $weekNum[$w] );
      }
   }

   $weeks = implode( "/", $weeks );
   if( ! $weeks )
       $weeks = '0/1/2/3';

   return "$days,$weeks,$durationInMonths";
}

?>
