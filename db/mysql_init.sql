CREATE DATABASE IF NOT EXISTS bookmyvenue;
USE bookmyvenue;

DROP TABLE IF EXISTS requests;
CREATE TABLE IF NOT EXISTS requests (
    id INT NOT NULL AUTO_INCREMENT
    , user VARCHAR(50) NOT NULL
    , title VARCHAR(100) NOT NULL
    , description TEXT 
    , venue VARCHAR(20) NOT NULL
    , date DATE NOT NULL
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , status ENUM ( 'pending', 'approved', 'rejected' ) DEFAULT 'pending'
    , does_repeat ENUM( 'Yes', 'No' ) DEFAULT 'No'
    , repeat_pat VARCHAR(100) DEFAULT '-1'
    , timestamp TIMESTAMP  DEFAULT CURRENT_TIMESTAMP 
    , PRIMARY KEY( id )
    );
    
DROP TABLE IF EXISTS events;
CREATE TABLE IF NOT EXISTS events (
    -- Sub even will be parent.children format.
    id DECIMAL(6,4) NOT NULL
    , short_description VARCHAR(200) NOT NULL
    , description TEXT
    , date DATE NOT NULL
    , venue VARCHAR(80)
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , PRIMARY KEY( id )
    );
    
DROP TABLE IF EXISTS venues;
CREATE TABLE IF NOT EXISTS venues (
    id VARCHAR(80) PRIMARY KEY
    , location VARCHAR(200) NOT NULL
    , strength INT NOT NULL
    , has_projector ENUM( 'Yes', 'No' ) NOT NULL
    , suitable_for_conference ENUM( 'Yes', 'No' ) NOT NULL
    );
    
# Insert venues.
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference ) 
    VALUES ( 'Safeda', 'SLC 2nd Floor', '40', 'Yes', 'No' );
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference )
    VALUES ( 'Synpase', 'SLC Ground Floor', '10', 'Yes', 'Yes' );
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference )
    VALUES ( 'Mitochondria', 'SLC 1st Floor', '`0', 'Yes', 'No' );

