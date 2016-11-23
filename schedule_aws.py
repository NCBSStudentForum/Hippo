#!/usr/bin/env python 

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
import math
import mysql.connector
import mysql
import ConfigParser
from collections import defaultdict
import networkx as nx
import datetime 

import logging
logging.basicConfig(
        level=logging.DEBUG
        , format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
        datefmt='%m-%d %H:%M'
        )
logging.info( 'Started on %s' % datetime.datetime.today( ) )

g_ = nx.DiGraph( )

# All AWS entries.
aws_ = defaultdict( list )

config = ConfigParser.ConfigParser( )
thisdir = os.path.dirname( os.path.realpath( __file__ ) )
config.read( os.path.join( thisdir, 'minionrc' ) )

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

def init( cur ):
    """Create a temporaty table for scheduling AWS"""
    global gb_
    cur.execute( 
            '''
            CREATE TABLE IF NOT EXISTS aws_schedule 
            ( speaker VARCHAR(40) PRIMARY KEY, date DATE NOT NULL ) 
            ''' 
        )
    db_.commit( )


def getAllAWS( ):
    global aws_, db_
    cur = db_.cursor( cursor_class = MySQLCursorDict )
    init( cur )
    cur.execute( 'SELECT * FROM annual_work_seminars ORDER BY date DESC' )
    for a in cur.fetchall( ):
        aws_[ a[ 'speaker' ] ].append( a )

def getWeight( speaker, slot_date, last_aws ):
    """ Here we are working with integers. With float the solution takes
    incredible large amount of time.
    """
    global g_, aws_
    idealGap = 357
    nDays = ( slot_date - last_aws ).days
    # Divide by month to get an interger.
    weight = abs( nDays - idealGap ) / 30
    nAws = len( aws_[speaker] )
    # We multiply the weight by AWS given by this user in a way that first 2 aws
    # does not effect this weight. But later AWS has significant cost. This is
    # make sure that first 2 AWS are given preferences over the third or more
    # AWS users.
    weight =  weight * max( 1, nAws - 2 )
    return weight 


def construct_flow_graph(  ):
    global g_
    global aws_

    g_.add_node( 'source', pos = (0,0) )
    g_.add_node( 'sink', pos = (10, 10) )

    # Each speaker gets his node.
    speakers = []
    for i, speaker in enumerate( aws_.keys() ):
        # first entry is most recent
        lastDate = aws_[speaker][0]['date']
        if lastDate:
            # assert lastDate, "No last date found for speaker %s" % speaker 
            g_.add_node( speaker, last_date = aws_[speaker][0]['date'], pos = (1,3*i) )
            g_.add_edge( 'source', speaker, capacity = 1, weight = 0 )
            speakers.append( speaker )
        else:
            print( 'Warning: Could not find last AWS date for %s' % speaker )
            print( '\t I am ignoring him' )

    # Now add mondays for next 20 weeks.
    today = datetime.date.today()
    nextMonday = today + datetime.timedelta( days = -today.weekday(), weeks=1)
    slots = []
    for i in range(40):
        nDays = i * 7
        monday = nextMonday + datetime.timedelta( nDays )
        # For each Monday, we have 3 AWS
        for j in range( 3 ):
            dateSlot = '%s,%d' % (monday, j)
            g_.add_node( dateSlot, date = monday, pos = (5, 10*(3*i + j)) )
            g_.add_edge( dateSlot, 'sink', capacity = 1, weight = 0 )
            slots.append( dateSlot )
    
    # Now for each student, add potential edges.
    idealGap = 357
    for speaker in speakers:
        prevAWSDate = g_.node[ speaker ][ 'last_date' ]
        for slot in slots:
            date = g_.node[ slot ][ 'date' ]
            weight = getWeight( speaker, date, prevAWSDate )
            g_.add_edge( speaker, slot, capacity = 1, weight = weight ) 

def test_graph( graph ):
    """Test that this graph is valid """
    # Each edge must have a capcity and weight 
    for u, v in graph.edges():
        if 'capacity' not in  graph[u][v]:
            print( 'Error: %s -> %s no capacity assigned' % (u, v) )
        if 'weight' not in  graph[u][v]:
            print( 'Error: %s -> %s no weight assigned' % (u, v) )

def getMatches( res ):
    result = defaultdict( list )
    for u in res:
        if 'source' == u:
            continue
        for v in res[u]:
            if 'sink' == v:
                continue
            f = res[u][v]
            if f > 0:
                date, slow = v.split(',')
                result[date].append( u )
    return result

def schedule( ):
    global g_
    test_graph( g_ )
    res = nx.max_flow_min_cost( g_, 'source', 'sink' )
    schedule = getMatches( res )
    return schedule

def print_schedule( schedule ):
    global g_, aws_
    for date in  sorted(schedule):
        line = "%s :" % date
        for speaker in schedule[ date ]:
            line += '%13s (%10s, %1d)' % (speaker
                , g_.node[speaker]['last_date'].strftime('%Y-%m-%d') 
                , len( aws_[ speaker ] )
                )
        print( line )

def commit_schedule( schedule ):
    global db_
    cur = db_.cursor( )
    for date in sorted(schedule):
        for speaker in schedule[date]:
            query = """
                INSERT INTO aws_schedule (speaker, date) VALUES ('{0}', '{1}') 
                ON DUPLICATE KEY UPDATE date='{1}'
                """.format( speaker, date ) 
            print( query )
            cur.execute( query )
    db_.commit( )
    print( "Committed to database" )

def draw_graph( ):
    global g_
    pos = nx.get_node_attributes( g_, 'pos' )
    nx.draw( g_, pos )
    plt.show( )


def main( ):
    global aws_
    global db_
    _logger.info( 'Scheduling AWS' )
    getAllAWS( )
    construct_flow_graph( )
    ans = schedule( )
    print_schedule( ans )
    commit_schedule( ans )
    db_.close( )

if __name__ == '__main__':
    main()
