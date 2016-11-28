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
import tempfile 
import logging
import random
import getpass

logFile = '/tmp/__minion_sch_%s.log' % getpass.getuser( )
logging.basicConfig( 
        filename = logFile
        , level=logging.DEBUG
        , format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s'
        , filemode = 'a'
        , datefmt='%m-%d %H:%M'
        )

print( 'Writing to %s' % logFile )
logging.info( 'Started on %s' % datetime.date.today( ) )

g_ = nx.DiGraph( )

# All AWS entries in the past.
aws_ = defaultdict( list )

# Upcoming AWS
upcoming_aws_ = { }

# These speakers must give AWS.
speakers_ = { }

config = ConfigParser.ConfigParser( )
thisdir = os.path.dirname( os.path.realpath( __file__ ) )
config.read( os.path.join( thisdir, 'minionrc' ) )
logging.debug( 'Read config file %s' % str( config ) )

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
    global db_, speakers_
    cur.execute( 'DROP TABLE IF EXISTS aws_temp_schedule' )
    cur.execute( 
            '''
            CREATE TABLE IF NOT EXISTS aws_temp_schedule 
            ( speaker VARCHAR(40) PRIMARY KEY, date DATE NOT NULL ) 
            ''' 
        )
    db_.commit( )
    cur.execute( """
        SELECT * FROM logins WHERE eligible_for_aws='YES' AND status='ACTIVE'
        ORDER BY login 
        """
        )
    for a in cur.fetchall( ):
        speakers_[ a['login'] ] = a

    logging.info( 'Total speakers %d' % len( speakers_ ) )


def getAllAWSPlusUpcoming( ):
    global aws_, db_
    global upcoming_aws_
    cur = db_.cursor( cursor_class = MySQLCursorDict )
    init( cur )

    # Entries in this table are usually in future.
    cur.execute( 'SELECT * FROM upcoming_aws' )
    for a in cur.fetchall( ):
        aws_[ a[ 'speaker' ] ].append( a )
        upcoming_aws_[ a['speaker'] ] = a['date']

    # Now get all the previous AWSs happened so far.
    cur.execute( 'SELECT * FROM annual_work_seminars' )
    for a in cur.fetchall( ):
        aws_[ a[ 'speaker' ] ].append( a )

    for a in aws_:
        # Sort a list in place.
        aws_[a].sort( key = lambda x : x['date'] )
        # print( a, [ x['date'] for x in aws_[a] ] )



def computeCost( speaker, slot_date, last_aws ):
    """ Here we are working with integers. With float the solution takes
    incredible large amount of time.
    """
    global g_, aws_
    idealGap = 357
    nDays = ( slot_date - last_aws ).days
    nAws = len( aws_[speaker] )

    # If nDays is less than idealGap than cost function grows very fast. Use
    # weeks instead of months as time-unit.
    if( nDays <= idealGap ):
        # THere is no way, anyone should get AWS before idealGap. Cost should be
        # high. Let's measure it in weeks.
        cost = ( idealGap - nDays ) / 7
    else:
        # Here we have two possibilities. Some speaker have not got their AWS
        # yet for quite a long time. Give preference to them. Reduce the cost to
        # very low but the cost should be larger for later AWS. if the
        # difference is 1.5 times the idealGap. If gap is more than 2.5 years,
        # than something is wrong with user. Ignore this profile and emit a
        # warning.
        fromToday = (datetime.date.today( ) - last_aws).days
        if fromToday > 2.5 * idealGap:
            # logging.warn( '%s has not given AWS for %d days' % ( speaker, fromToday) )
            # logging.info( "I am not scheduling AWS for this user." )
            cost = 100
        elif fromToday >  1.5 * idealGap:
            cost = 0.0 + nAws / 10.0
        else:
            cost = float( nDays - idealGap ) / idealGap 

    # We multiply the weight by AWS given by this user in a way that first 2 aws
    # does not effect this weight. But later AWS has significant cost. This is
    # make sure that first 2 AWS are given preferences over the third or more
    # AWS users.
    cost =  cost + max(0, nAws - 2 )

    # Add some random noise to make sure that we don't have same coupling of
    # speakers as before 
    cost += random.random()

    # This does not work well with float.
    return int( 100 * cost )

# From  http://stackoverflow.com/a/3425124/1805129
def monthdelta(date, delta):
    m, y = (date.month+delta) % 12, date.year + ((date.month)+delta-1) // 12
    if not m: m = 12
    d = min(date.day, [31,
        29 if y%4==0 and not y%400==0 else 28,31,30,31,30,31,31,30,31,30,31][m-1])
    dt = date.replace(day=d,month=m, year=y)
    return dt.date( )

def construct_flow_graph(  ):
    global g_
    global aws_
    global speakers_

    g_.add_node( 'source', pos = (0,0) )
    g_.add_node( 'sink', pos = (10, 10) )

    lastDate = None
    for i, speaker in enumerate( speakers_ ):
        # Last entry is most recent
        if speaker not in aws_.keys( ):
            # We are here because this speaker has not given any AWS yet. If
            # this user has PHD/POSTDOC, or INTPHD title. We create  a dummy
            # last date to bring her into potential speakers.

            # First make sure, I have their date of joining. Otherwise I
            # can't continue. For INTPHD assign their first AWS after 18
            # months. For PHD and POSTDOC, it should be after 12 months.
            if speakers_[ speaker ]['title'] == 'INTPHD':
                # InPhd should get their first AWS after 15 months of
                # joining.
                logging.info( '%s = INTPHD with 0 AWS so far' % speaker )
                joinDate = speakers_[ speaker ]['joined_on']
                if not joinDate:
                    logging.warn( "Could not find joining date" )
                else:
                    lastDate = monthdelta( joinDate, -6 )

            elif speakers_[ speaker ]['title'] in [ 'PHD', 'POSTDOC' ]:
                joinDate = speakers_[ speaker ]['joined_on']
                logging.info( '%s PHD/POSTDOC with 0 AWS so far' % speaker )
                if not joinDate:
                    logging.warn( "Could not find joining date" )
                else:
                    lastDate = joinDate.date( )
        else: 
            # We are here because this speaker has given AWS before
            # If this speaker is already on upcoming AWS list, ignore it.
            if speaker in upcoming_aws_:
                logging.info( 
                        'Speaker %s is already scheduled on %s' % ( 
                            speaker, upcoming_aws_[ speaker ] 
                            )
                        )

                # logging.info( 'Warning: Could not find last AWS date for %s' % speaker )
                # logging.info( '\t I am ignoring him' )
                # assert lastDate, "No last date found for speaker %s" % speaker 
                continue
            else:
                lastDate = aws_[speaker][-1]['date']

        # If a speaker has a lastDate either because he has given AWS in the
        # past or becuase she is fresher. Create an edge.
        if lastDate:
            g_.add_node( speaker, last_date = lastDate, pos = (1, 3*i) )
            g_.add_edge( 'source', speaker, capacity = 1, weight = 0 )

    # Now add mondays for next 20 weeks.
    today = datetime.date.today()
    nextMonday = today + datetime.timedelta( days = -today.weekday(), weeks=1)
    slots = []

    totalWeeks = 53
    logging.info( "Computing for total %d weeks" % totalWeeks )
    for i in range( totalWeeks ):
        nDays = i * 7
        monday = nextMonday + datetime.timedelta( nDays )
        if monday in upcoming_aws_.values( ):
            logging.info( 'Date %s is taken ' % monday )
            continue 

        # For each Monday, we have 3 AWS
        for j in range( 3 ):
            dateSlot = '%s,%d' % (monday, j)
            g_.add_node( dateSlot, date = monday, pos = (5, 10*(3*i + j)) )
            g_.add_edge( dateSlot, 'sink', capacity = 1, weight = 0 )
            slots.append( dateSlot )
    
    # Now for each student, add potential edges.
    idealGap = 357
    for speaker in speakers_:
        if speaker not in g_.nodes( ):
            logging.info( 'Nothing for user %s' % speaker )
            continue
        prevAWSDate = g_.node[ speaker ][ 'last_date' ]
        for slot in slots:
            date = g_.node[ slot ][ 'date' ]
            weight = computeCost( speaker, date, prevAWSDate )
            g_.add_edge( speaker, slot, capacity = 1, weight = weight ) 
    logging.info( 'Constructed flow graph' )


def write_graph( outfile  = 'network.dot' ):
    # Convert datetime to string before writing to file.
    # This operation should be done at the very end.
    # This operation should be done at the very end.
    dotText = [ "digraph G { " ]
    for n in g_.nodes():
        nodeText = '\t"%s" [' % n
        for attr in g_.node[ n ]:
            nodeText += '%s="%s", ' % (attr, g_.node[n][attr] ) 
        nodeText += ']'
        dotText.append( nodeText )

    for s, t in g_.edges( ):
        edgeText = ( '\t "%s" -> "%s" [' % (s, t) )
        for attr in g_[s][t]:
            edgeText += '%s="%s",' % (attr, g_[s][t][attr] )
        edgeText += ']'
        dotText.append( edgeText )

    dotText.append( "}" )
    with open( outfile, "w" ) as f:
        f.write( "\n".join( dotText ) )

def test_graph( graph ):
    """Test that this graph is valid """
    # Each edge must have a capcity and weight 
    for u, v in graph.edges():
        if 'capacity' not in  graph[u][v]:
            logging.info( 'Error: %s -> %s no capacity assigned' % (u, v) )
        if 'weight' not in  graph[u][v]:
            logging.info( 'Error: %s -> %s no weight assigned' % (u, v) )
    logging.info( '\tDone testing graph' )

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
    logging.info( 'Scheduling AWS now' )
    test_graph( g_ )
    logging.info( 'Computing max-flow, min-cost' )
    res = nx.max_flow_min_cost( g_, 'source', 'sink' )
    logging.info( '\t Computed. Getting schedules now ...' )
    schedule = getMatches( res )
    logging.info( '\t ... Computed schedules.' )
    return schedule

def print_schedule( schedule, outfile ):
    global g_, aws_
    with open( outfile, 'w' ) as f:
        f.write( "This is what is got \n" )
    for date in  sorted(schedule):
        line = "%s :" % date
        for speaker in schedule[ date ]:
            line += '%13s (%10s, %1d)' % (speaker
                , g_.node[speaker]['last_date'].strftime('%Y-%m-%d') 
                , len( aws_[ speaker ] )
                )
        with open( outfile, 'a' ) as f:
            f.write( '%s\n' % line )
            print( line )

def commit_schedule( schedule ):
    global db_
    cur = db_.cursor( )
    logging.info( 'Committing computed schedules ' )
    for date in sorted(schedule):
        for speaker in schedule[date]:
            query = """
                INSERT INTO aws_temp_schedule (speaker, date) VALUES ('{0}', '{1}') 
                ON DUPLICATE KEY UPDATE date='{1}'
                """.format( speaker, date ) 
            logging.debug( query )
            cur.execute( query )
    db_.commit( )
    logging.info( "Committed to database" )

def main( outfile ):
    global aws_
    global db_
    logging.info( 'Scheduling AWS' )
    getAllAWSPlusUpcoming( )
    try:
        construct_flow_graph( )
        ans = schedule( )
    except Exception as e:
        logging.warn( "Failed to schedule. %s" % e )
    try:
        print_schedule( ans, outfile )
    except Exception as e:
        logging.error( "Could not print schedule. %s" % e )

    commit_schedule( ans )
    try:
        write_graph( )
    except Exception as e:
        logging.error( "Could not write graph to file" )
        logging.error( "\tError was %s" % e )
    db_.close( )

if __name__ == '__main__':
    outfile = tempfile.NamedTemporaryFile( ).name
    if len( sys.argv ) > 1:
        outfile = sys.argv[1]
    main( outfile )
