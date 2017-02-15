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


/**
    * @brief Generate a select list outof given values.
    *
    * @param $venues List of venues.
    * @param $ismultiple Do we want to select multiple entries.
    * @param $selected Pre-select these guys.
    *
    * @return 
 */
function venuesToHTMLSelect( $venues = null, $ismultiple = false
    , $selectName = 'venue', $preSelected = array() 
    )
{
    if( ! $venues )
        $venues = getVenues( );

    $multiple = '';
    $default = '-- select a venue --';
    if( $ismultiple )
    {
        $multiple = 'multiple size="5"';
        $default = '-- select multiple venues --';
        $selectName .= "[]";
    }

    $html = "<select $multiple name=\"$selectName\">";
    if( ! $ismultiple )
        $html .= "<option disabled selected value>$default</option>";

    foreach( $venues as $v )
    {
        $selected = '';
        if( in_array( $v['id'], $preSelected ) )
            $selected = 'selected';

        $text = venueToText( $v );
        if( $v['suitable_for_conference'] == 'YES' )
            $text .= '<font color=\"blue\"> +C </font>';
        if( $v['has_projector'] == 'YES' )
            $text .= '<font color=\"blue\"> +P </font>';

        $venueid = $v['id'];
        $html .= "<option value=\"$venueid\" $selected> $text </option>";
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
   return  'https://ncbs.res.in/hippo';
}

/* Go to a page relative to base dir. */
function goToPage($page="index.php", $delay = 3)
{
  echo printInfo( "Going back to page $page in $delay seconds" );
  $baseurl = appRootDir( );
  if( strpos( $page, "http" ) == 0 )
      $url = $page;
  else
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

/**
    * @brief Go back to referer page.
    *
    * @param $defaultPage
    *
    * @return 
 */
function goBack( $default = 'index.php', $delay = 0 )
{
    $url = __get__( $_SERVER, 'HTTP_REFERER', $default );
    goToPage( $url, $delay );
}

function __get__( $arr, $what, $default = NULL )
{
    if( ! $arr )
        return $default;

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

    $durationInMonths = $exploded[2];
    if( ! $durationInMonths )
        $durationInMonths = 6;

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
        return date( 'D, M d, Y', $date );

    return date( 'D, M d Y', strtotime($date) );
}


// Return a format date for mysql database.
function dbDate( $date )
{
    if( is_int( $date ) )
        return date( 'Y-m-d', $date );

    return date( 'Y-m-d', strtotime( $date ) );
}

function dbDateTime( $date )
{
    if( is_int( $date ) )
        return date( 'Y-m-d H:i:s', $date );

    return date( 'Y-m-d H:i:s', strtotime( $date ) );
}

function dbTime( $time )
{
    if( is_int( $time ) )
        return date( 'H:i', $time );
    return date( 'H:i', strtotime( $time ) );
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

/**
    * @brief Base function to compute diff in two strings.
    *
    * @param $from
    * @param $to
    *
    * @return 
 */
function computeDiff($from, $to)
{
    $diffValues = array();
    $diffMask = array();

    $dm = array();
    $n1 = count($from);
    $n2 = count($to);

    for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
    for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
    for ($i = 0; $i < $n1; $i++)
    {
        for ($j = 0; $j < $n2; $j++)
        {
            if ($from[$i] == $to[$j])
            {
                $ad = $dm[$i - 1][$j - 1];
                $dm[$i][$j] = $ad + 1;
            }
            else
            {
                $a1 = $dm[$i - 1][$j];
                $a2 = $dm[$i][$j - 1];
                $dm[$i][$j] = max($a1, $a2);
            }
        }
    }

    $i = $n1 - 1;
    $j = $n2 - 1;
    while (($i > -1) || ($j > -1))
    {
        if ($j > -1)
        {
            if ($dm[$i][$j - 1] == $dm[$i][$j])
            {
                $diffValues[] = $to[$j];
                $diffMask[] = 1;
                $j--;  
                continue;              
            }
        }
        if ($i > -1)
        {
            if ($dm[$i - 1][$j] == $dm[$i][$j])
            {
                $diffValues[] = $from[$i];
                $diffMask[] = -1;
                $i--;
                continue;              
            }
        }
        {
            $diffValues[] = $from[$i];
            $diffMask[] = 0;
            $i--;
            $j--;
        }
    }    

    $diffValues = array_reverse($diffValues);
    $diffMask = array_reverse($diffMask);

    return array('values' => $diffValues, 'mask' => $diffMask);
}

/**
    * @brief Compute diff of two lines.
    *
    * @param $line1
    * @param $line2
    *
    * @return 
 */
function diffline($line1, $line2)
{
    $diff = computeDiff(str_split($line1), str_split($line2));
    $diffval = $diff['values'];
    $diffmask = $diff['mask'];

    $n = count($diffval);
    $pmc = 0;
    $result = '';
    for ($i = 0; $i < $n; $i++)
    {
        $mc = $diffmask[$i];
        if ($mc != $pmc)
        {
            switch ($pmc)
            {
                case -1: $result .= '</del>'; break;
                case 1: $result .= '</ins>'; break;
            }
            switch ($mc)
            {
                case -1: $result .= '<del>'; break;
                case 1: $result .= '<ins>'; break;
            }
        }
        $result .= $diffval[$i];

        $pmc = $mc;
    }
    switch ($pmc)
    {
        case -1: $result .= '</del>'; break;
        case 1: $result .= '</ins>'; break;
    }

    return $result;
}

/**
    * @brief Check if given string a date.
    *  http://php.net/manual/en/function.checkdate.php#113205
    * @param $date
    *
    * @return True if string is a date.
 */
function isStringAValidDate( $date )
{
    $d = date_create_from_format( 'Y-m-d', $date );
    if( ! $d )
        return false;
    return (strcasecmp( $d->format( 'Y-m-d' ), $date ) == 0);
}

function isMobile() 
{
    return preg_match(
        "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
        , $_SERVER["HTTP_USER_AGENT"]
    );
}

function convertImage($originalImage, $ext, $outputImage, $quality = 9 )
{
    // jpg, png, gif or bmp?
    if (preg_match('/jpg|jpeg/i',$ext))
        $imageTmp=imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i',$ext))
        $imageTmp=imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i',$ext))
        $imageTmp=imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i',$ext))
        $imageTmp=imagecreatefrombmp($originalImage);
    else
        return 0;

    // quality is a value from 0 (worst) to 10 (best)
    imagepng($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);
    return 1;
}

/**
    * @brief Image of user,
    *
    * @param $user
    *
    * @return 
 */
function getUserPicture( $user )
{
    $picPath = $_SESSION[ 'conf' ]['user_imagedir'] . '/' . $user . '.png';
    if( ! file_exists( $picPath ) )
        $picPath = __DIR__ . "/data/no_image_available.png";
        
    $html ='<img class="login_picture" width="200px"
        height="auto" src="' . dataURI( $picPath, 'image/png' ) . '" >';
    return $html;
}
