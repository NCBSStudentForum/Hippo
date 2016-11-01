CREATE DATABASE IF NOT EXISTS bookmyvenue_test;
USE bookmyvenue_test;

DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS venues;

CREATE TABLE IF NOT EXISTS requests (
    gid INT NOT NULL
    , rid INT NOT NULL
    , user VARCHAR(50) NOT NULL
    , title VARCHAR(100) NOT NULL
    , description TEXT 
    , venue VARCHAR(20) NOT NULL
    , date DATE NOT NULL
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , status ENUM ( 'PENDING', 'APPROVED', 'REJECTED', 'CANCELLED' ) DEFAULT 'PENDING'
    , modified_by VARCHAR(50) -- Who modified the request last time.
    , timestamp TIMESTAMP  DEFAULT CURRENT_TIMESTAMP 
    , PRIMARY KEY( gid, rid )
    );

-- venues must created before events because events refer to venues key as
-- foreign key.
CREATE TABLE IF NOT EXISTS venues (
    id VARCHAR(80) NOT NULL
    , name VARCHAR(300) NOT NULL
    , institute VARCHAR(100) NOT NULL
    , building_name VARCHAR(100) NOT NULL
    , floor INT NOT NULL
    , location VARCHAR(500) 
    , type VARCHAR(30) NOT NULL
    , strength INT NOT NULL
    , distance_from_ncbs DECIMAL(3,3) DEFAULT 0.0 
    , has_projector ENUM( 'YES', 'NO' ) NOT NULL
    , suitable_for_conference ENUM( 'YES', 'NO' ) NOT NULL
    , has_skype ENUM( 'YES', 'NO' ) DEFAULT 'NO'
    , PRIMARY KEY (id)
    );
    
    
DROP TABLE IF EXISTS events;
CREATE TABLE IF NOT EXISTS events (
    -- Sub even will be parent.children format.
    gid INT NOT NULL
    , eid INT NOT NULL
    -- If yes, this entry will be put on google calendar.
    , is_public_event ENUM( 'YES', 'NO' ) DEFAULT 'NO' 
    , class ENUM( 'LABMEET', 'LECTURE', 'MEETING', 'SEMINAR', 'TALK'
        , 'CONFERENCE', 'CULTURAL', 'AWS'
        , 'UNKNOWN'
        ) DEFAULT 'UNKNOWN' 
    , short_description VARCHAR(200) NOT NULL
    , description TEXT
    , date DATE NOT NULL
    , venue VARCHAR(80) NOT NULL
    , user VARCHAR( 50 ) NOT NULL
    , start_time TIME NOT NULL
    , end_time TIME NOT NULL
    , PRIMARY KEY( gid, eid )
    , FOREIGN KEY (venue) REFERENCES venues(id)
    );
    
