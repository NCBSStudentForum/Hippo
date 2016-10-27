<?php

include_once('ldap.php');
function sqlite_open($location)
{
  $db = new SQLite3($location);
  return $db;
}

function getAnimalWithId( $id )
{
    if( ! $id )
        return NULL;

    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );

    $stmt = $conn->prepare( 'SELECT * FROM animals WHERE id=:id');
    $stmt->bindValue( ':id', $id, SQLITE3_TEXT );
    $res = $stmt->execute( );
    $arr = $res->fetchArray( SQLITE3_ASSOC );

    $stmt = $conn->prepare( 'SELECT * FROM current_status WHERE id=:id');
    $stmt->bindValue( ':id', $id, SQLITE3_TEXT );
    $res = $stmt->execute( );
    $arr1 = $res->fetchArray( SQLITE3_ASSOC );

    if( $arr1 )
        $arr = array_merge( $arr, $arr1 );

    $stmt = $conn->prepare( 'SELECT * FROM health WHERE id=:id');
    $stmt->bindValue( ':id', $id, SQLITE3_TEXT );
    $res = $stmt->execute( );
    $arr2 = $res->fetchArray( SQLITE3_ASSOC );

    $conn->close();

    if( $arr2 )
        $arr = array_merge( $arr, $arr2 );
    return $arr;
}

function getInfoForDisplay()
{
  $conn = sqlite_open("db/jinawar.sqlite");
  $info = array();
  if($conn) 
  {
      $res = $conn->query("SELECT Count(*) FROM mice");
      if($res)
      {
          $arr = $res->fetchArray();
          $info['total_mice'] = $arr[0];
      }
      $conn->close();
  }
  return $info;
}

function getAnimalList( )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );
    $response = $conn->query( "SELECT id, name FROM animals WHERE status='alive'" );
    $result = Array();
    $i = 0;
    while( $arr = $response->fetchArray( SQLITE3_BOTH ) )
    {
        $result[$i] = $arr;
        $i ++;
    }

    $conn->close();
    return $result;
}

/**
    * @brief Get the ids for all cages with given type.
    *
    * @param $type
    *
    * @return 
 */
function getListOfCages( $type = NULL )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "failed to open $dbname" );
    $array = Array();
    if( $conn )
    {
        if( $type )
            $res = $conn->query( 'SELECT * FROM cages WHERE type="'. $type . '"' );
        else
            $res = $conn->query( 'SELECT * FROM cages' );

        $i = 0;
        while( $row = $res->fetchArray( SQLITE3_ASSOC ) )
        {
            $array[$i] = $row;
            $i++;
        }
        $conn->close();
    }
    return $array;
}

function summaryTable( )
{
    $html = "<table class=\"summary\" style=\"float: left\">";
    $animals = getAnimalList( );
    $breederCages = getListOfCages( "breeder" );
    $cages = getListOfCages( );
    $numAnimals = sizeof( $breederCages );
    $numCages = sizeof( $cages );
    $numBreederCages = sizeof( $breederCages );
    $html .= "<tr> <td>Alive animals </td><td> $numAnimals </td> </tr>";
    $html .= "<tr> <td>Total cages </td><td> $numCages </td> </tr>";
    $html .= "<tr> <td>Breeder cages </td><td> $numBreederCages </td> </tr>";
    $html .= "</table>";
    return $html;
}

function getHealth( $animal_id )
{
    $conn = connetJinawar( );

    $stmt = $conn->prepare( 'SELECT * FROM health WHERE id=:id' );
    $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );

    $res = $stmt->execute( );

    if( $res )
        $result = $res->fetchArray( );
    else
        $result = Array();
    $conn->close( );
    return $result;
}

function connetJinawar( )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die ("Could not connect to $dbname " );
    return $conn;
}

?>

