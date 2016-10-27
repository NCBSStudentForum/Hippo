#!/usr/bin/env python

"""init_db.py: 


"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar Singh"
__credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sqlite3 as sql

# Two databases, one stores non-mutable information and other daily records.
dbnames_ = [ 'bmv.sqlite' ]

def add_tables_to_db( db ):
    c = db.cursor( )
    # These values never changes.
    c.execute(
            "CREATE TABLE IF NOT EXISTS venues ("
            "id NCHAR PRIMARY KEY NOT NULL"      
            ",name TEXT NOT NULL"
            ",location TEXT NOT NULL"
            ",strength INT NOT NULL"
            ",hasConference INT DEFAULT 0"
            ",hasProjector INT DEFAULT 0"
            ",comment TEXT"
            ")"
            )
    db.commit()
    c.execute( 'CREATE TABLE IF NOT EXISTS events ('
            'id NCHAR PRIMARY KEY NOT NULL'
            ',cloneof NCHAR'
            ',type NCHAR NOT NULL'
            ',venue NCHAR'
            ',description TEXT NOT NULL'
            ',startOn DATETIME NOT NULL' 
            ',endOn DATETIME NOT NULL'
            ',doesRepeat INT default 0'
            ',booked INT default 0'
            ',COMMENT TEXT'
            ',FOREIGN KEY(venue) REFERENCE venues(id)'
            ')'
            )
    db.commit( )
    c.execute( "CREATE TABLE IF NOT EXISTS requests ("
            " id NCHAR PRIMARY KEY NOT NULL"
            ", requestBy NCHAR NOT NULL"
            ", eventId NCHAR"
            ", venue NCHAR "
            ", description NOT NULL"
            ", startOn DATETIME NOT NULL"
            ", endOn DATETIME NOT NULL"
            ", comment TEXT"
            ", status NCHAR DEFAULT 'pending'"
            ", timestamp DATETIME DEFAULT (datetime('now', 'localtime'))"
            ")"
            )

    db.commit()
    c.execute( "CREATE TABLE IF NOT EXISTS log ("
            " timestamp DATETIME DEFAULT (datetime('now', 'localtime'))"
            ", requestId NCHAR "
            ", prevStatus NCHAR "
            ", currStatus NCHAR "
            ", changedBy NCHAR "
            ", comment TEST "
            ")"
            )
    db.commit( )
    db.close( )

def add_tables( db ):
    print( 'Adding table to %s' % db )
    if db == 'bmv.sqlite':
        db_ = sql.connect( db )
        add_tables_to_db( db_ )
    else:
        print( '[WARN] Unknown database %s' % db )


def init_db( ):
    global gbdnames_
    print( 'Initializing database' )
    for db in dbnames_:
        add_tables( db )
        print( 'Created databases: %s' % db )


def main( ):
    init_db( )

if __name__ == '__main__':
    main()
