"""insert_venues.py:

Insert venues.

"""
    
__author__           = "Me"
__copyright__        = "Copyright 2016, Me"
__credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Me"
__email__            = ""
__status__           = "Development"

import sys
import os
import sqlite3 as sql

# Venues are in the following format.
# id,name,strength,hasConference,hasProjector,type
venues = [ 
        "safeda,Safeda - SLC, 2nd Floor,40,0,1,lectureroom"
        , "malgova,Malgova - SLC, 2nd Floor,50,0,1,lectureroom"
        , "synapse,Synapse - SLC, 1st Floor,10,1,1,conferenceroom"
        ]


def main( ):
    """Insert venues into sqlite database """
    db = sql.connect( './bmv.sqlite' )
    c = db.cursor( )
    for v in venues:
        d = v.split( ',' )
        query = 'REPLACE INTO venues '
        ' (id,name,strength,hasConference,hasProjector,type)' 
        print query


if __name__ == '__main__':
    main()
