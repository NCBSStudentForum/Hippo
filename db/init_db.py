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
dbnames_ = [ 'jinawar.sqlite' ]

def add_tables_to_jinawar( db ):
    c = db.cursor( )
    # These values never changes.
    c.execute(
            "CREATE TABLE IF NOT EXISTS animals ("
            "id NCHAR PRIMARY KEY NOT NULL"      
            ",name TEXT NOT NULL"
            ",dob DATE NOT NULL"
            ",dod DATE"
            ",gender NCHAR NOT NULL"
            ",status NCHAR DEFAULT alive"
            ",parent_cage_id NCHAR NOT NULL"
            ",strain NCHAR NOT NULL"
            ",comment TEXT"
            ")"
            )
    db.commit()
    c.execute( 'CREATE TABLE IF NOT EXISTS cages ('
            'id NCHAR PRIMARY KEY NOT NULL'
            ',type NCHAR NOT NULL'
            ',location TEXT'
            ',BARCODE BLOB'
            ',COMMENT TEXT'
            ')'
            )
    db.commit( )
    c.execute( "CREATE TABLE IF NOT EXISTS current_status ("
            " id NCHAR PRIMARY KEY NOT NULL"
            ", current_cage_id NCHAR NOT NULL"
            ", ear_pattern NCHAR NOT NULL"
            ", genotype_id NCHAR "
            ", genotype_done_on DATETIME"
            ", genotype_done_by NCHAR"
            ", comment TEXT"
            ", image BLOB"
            ", timestamp DATETIME DEFAULT (datetime('now', 'localtime'))"
            ")"
            )

    db.commit()
    c.execute( "CREATE TABLE IF NOT EXISTS log ("
            " timestamp DATETIME DEFAULT (datetime('now', 'localtime'))"
            ", prev TEXT "
            ", curr TEXT "
            ")"
            )
    db.commit( )
    db.close( )

def add_tables( db ):
    print( 'Adding table to %s' % db )
    if db == 'jinawar.sqlite':
        db_ = sql.connect( db )
        add_tables_to_jinawar( db_ )
    elif db == 'record.sqlite':
        db_ = sql.connect( db )
        add_tables_to_record( db_ )
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
