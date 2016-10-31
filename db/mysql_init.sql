CREATE DATABASE IF NOT EXISTS bookmyvenue;
USE bookmyvenue;

DROP TABLE IF EXISTS requests;
CREATE TABLE IF NOT EXISTS requests (
    gid INT NOT NULL, rid INT NOT NULL
    , user VARCHAR(50) NOT NULL
    , title VARCHAR(100) NOT NULL
    , description TEXT 
    , venue VARCHAR(20) NOT NULL
    , date DATE NOT NULL
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , status ENUM ( 'PENDING', 'APPROVED', 'REJECTED' ) DEFAULT 'PENDING'
    , timestamp TIMESTAMP  DEFAULT CURRENT_TIMESTAMP 
    , PRIMARY KEY( gid, rid )
    );
    
DROP TABLE IF EXISTS events;
CREATE TABLE IF NOT EXISTS events (
    -- Sub even will be parent.children format.
    gid INT NOT NULL, eid INT NOT NULL
    , type ENUM( 'PUBLIC', 'PRIVATE' ) DEFAULT 'PRIVATE' 
    , class ENUM( 
        'LABMEET', 'LECTURE', 'MEETING'
        , 'CONFERENCE', 'UNKNOWN', 'CULTURAL'
        ) DEFAULT 'UNKNOWN' 
    , short_description VARCHAR(200) NOT NULL
    , description TEXT
    , date DATE NOT NULL
    , venue VARCHAR(80)
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , PRIMARY KEY( gid, eid )
    , FOREIGN KEY (venue) REFERENCES venues(id)
    );
    
DROP TABLE IF EXISTS venues;
CREATE TABLE IF NOT EXISTS venues (
    id VARCHAR(80) PRIMARY KEY
    , location VARCHAR(200) NOT NULL
    , strength INT NOT NULL
    , has_projector ENUM( 'YES', 'NO' ) NOT NULL
    , suitable_for_conference ENUM( 'YES', 'NO' ) NOT NULL
    );
    
# Insert venues.
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference ) 
    VALUES ( 'Safeda', 'SLC 2nd Floor', '40', 'YES', 'NO' );
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference )
    VALUES ( 'Synpase', 'SLC Ground Floor', '10', 'YES', 'YES' );
INSERT INTO venues 	(id, location, strength, has_projector, suitable_for_conference )
    VALUES ( 'Mitochondria', 'SLC 1st Floor', '10', 'YES', 'NO' );

