"""schedule_aws.py: 

Query the database and schedule AWS.

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
import mysql.connector
import mysql
import ConfigParser
from collections import defaultdict

config = ConfigParser.ConfigParser( )
config.read( './minionrc' )

class MySQLCursorDict(mysql.connector.cursor.MySQLCursor):
    def _row_to_python(self, rowdata, desc=None):
        row = super(MySQLCursorDict, self)._row_to_python(rowdata, desc)
        if row:
            return dict(zip(self.column_names, row))
        return None

db_ = mysql.connector.connect( 
        host = config.get( 'mysql', 'host' )
        , user = config.get( 'mysql', 'user' )
        , passwd = config.get( 'mysql', 'password' )
        , db = 'minion'
        )

def getAllAWS( ):
    aws = defaultdict( list )
    cur = db_.cursor( cursor_class = MySQLCursorDict )
    cur.execute( 'SELECT * FROM annual_work_seminars' )
    for a in cur.fetchall( ):
        aws[ a[ 'speaker' ] ].append( a )
    db_.close( )
    return aws

def main( ):
    aws = getAllAWS( )
    print aws 
        
    pass

if __name__ == '__main__':
    main()
