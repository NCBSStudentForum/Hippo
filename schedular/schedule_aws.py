"""schedule_aws.py: 

Query the database and schedule AWS.

"""
from __future__ import print_function 

    
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
import networkx as nx
import datetime 
import matplotlib as mpl
import matplotlib.pyplot as plt


g_ = nx.DiGraph( )

config = ConfigParser.ConfigParser( )
config.read( '../minionrc' )

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

    for speaker in aws:
        try:
            sorted( aws[ speaker ], key = lambda x: x['date'], reverse = True )
        except Exception as e:
            print( 'x', end='' )
            sys.stdout.flush( )
    return aws

def construct_flow_graph( aws ):
    global g_

    g_.add_node( 'source', pos = (0,0) )
    g_.add_node( 'sink', pos = (10, 10) )

    # Each speaker gets his node.
    speakers = aws.keys( )
    for i, speaker in enumerate( speakers ):
        g_.add_node( speaker, last_date = aws[speaker][0]['date'], pos = (1,3*i) )
        g_.add_edge( 'source', speaker, capacity = 1.0 )

    # Now add mondays for next 20 weeks.
    today = datetime.date.today()
    nextMonday = today + datetime.timedelta( days = -today.weekday(), weeks=1)
    slots = []
    for i in range(20):
        nDays = i * 7
        monday = nextMonday + datetime.timedelta( nDays )
        # For each Monday, we have 3 AWS
        for j in range( 3 ):
            dateSlot = '%s,%d' % (monday, j)
            g_.add_node( dateSlot, date = monday, pos = (5, 10*(3*i + j)) )
            g_.add_edge( dateSlot, 'sink', capacity = 1.0 )
            slots.append( dateSlot )
    
    # Now for each student, add potential edges.
    for n in speakers:
        prevAWSDate = g_.node[ speaker ][ 'last_date' ]
        for slot in slots:
            date = g_.node[ slot ][ 'date' ]
            # print( prevAWSDate, date )
            nDays = ( date - prevAWSDate ).days
            if nDays > 357:
                print( nDays )
                ratio = 357.0 / nDays 
                if ratio < 0.4:
                    print('Candidate %s has not given her aws in long time.' % n)
                    ratio = 1.0
                g_.add_edge( n, slot, capacity = ratio )

def schedule( ):
    global g_
    res = nx.max_flow(g_, 'source', 'sink' )
    print( res )

def main( ):
    aws = getAllAWS( )
    construct_flow_graph( aws )
    schedule( )
    pos = nx.get_node_attributes( g_, 'pos' )
    # nx.draw( g_, pos )
    plt.show( )

if __name__ == '__main__':
    main()
