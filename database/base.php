<?php

include_once 'database/init.php';

class BMVPDO extends PDO
{
    function __construct( $host = 'localhost'  )
    {
        $conf = parse_ini_file( '/etc/hipporc', $process_section = TRUE );
        $options = array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
        $host = $conf['mysql']['host'];
        $port = $conf['mysql']['port'];

        if( $port == -1 )
            $port = 3306;

        $user = $conf['mysql']['user'];
        $password = $conf['mysql']['password'];
        $dbname = $conf['mysql']['database'];

        if( $port == -1 )
            $port = 3306;

        try {
            parent::__construct( 'mysql:host=' . $host . ";dbname=$dbname"
                , $user, $password, $options
            );
        } catch( PDOException $e) {
            echo minionEmbarrassed(
                "failed to connect to database: ".  $e->getMessage()
            );
            $this->error = $e->getMessage( );
            echo goBackToPageLink( 'index.php', 0 );
            exit;
        }

    }
}

// Construct the PDO
$db = new BMVPDO( "localhost" );

// And initiaze the database.
initialize( $db  );

?>
