<?php
include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );

// verify the request.
function verifyRequest( $request )
{
    // Check the end_time must be later than start_time .
    // At least 15 minutes event
    if( strtotime( $request['end_time'] ) - strtotime( $request['start_time'] ) < 900 )
    {
        $msg = "The event must be at least 15 minute long";
        $msg .= " Start time " . $request[ 'start_time' ] . " to end time " .
            $request[ 'end_time' ];
        return $msg;
    }
    if( ! isset( $request['venue'] ) )
    {
        return "No venue found in your request. May be a bug " ;
    }
    return "OK";

}


$msg = verifyRequest( $_POST );


if( $msg == "OK" )
{
   // Generate repeat pattern from days, week and month repeat patter.
   $repeatPat = constructRepeatPattern( 
      $_POST['day_pattern'], $_POST['week_pattern'] , $_POST['month_pattern']
   );

   $_POST['repeat_pat']  = $repeatPat;

   $res = submitRequest( $_POST );
   if( $res )
   {
      echo printInfo( 
         "Your request has been submitted and an email has been sent to you 
         with details.
         " );
         goToPage( "user.php", 1 );
      }
      else
      {
         echo printWarning( 
            "Your request could not be submitted. Please notify the admin." 
         );
         echo goBackToPageLink( "user.php", "Go back" );
         exit;
      }
   }
   else
   {
      echo printWarning( "There was an error in request" );
      echo printWarning( $msg );
      echo goBackToPageLink( "user.php", "Go back" );
      exit;
   }

?>
