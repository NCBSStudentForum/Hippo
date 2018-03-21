#!/usr/bin/env python3
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar singh <dilawars@ncbs.res.in>"
__credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawra Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development/Production"

import sys
import os
import math
import datetime 
import copy
import tempfile 
from db_connect import db_
from global_data import *

def spec_short( spec ):
    return  ''.join( [ x.strip()[0] for x in spec.split( ) ] )

def getSpecialization( cur, piOrHost ):
    cur.execute( "SELECT specialization FROM faculty WHERE email='%s'" % piOrHost )
    a = cur.fetchone( )
    return a['specialization']

def init( cur ):
    """
    Create a temporaty table for scheduling AWS
    """

    global db_

    cur.execute( 'DROP TABLE IF EXISTS aws_temp_schedule' )
    cur.execute( 
            '''
            CREATE TABLE IF NOT EXISTS aws_temp_schedule 
            ( speaker VARCHAR(40) PRIMARY KEY, date DATE NOT NULL ) 
            ''' 
        )
    db_.commit( )
    cur.execute( 
        """
        SELECT * FROM logins WHERE eligible_for_aws='YES' AND status='ACTIVE'
        ORDER BY login 
        """
        )
    for a in cur.fetchall( ):
        speakers_[ a['login'].lower() ] = a
        spec = a['specialization']
        if spec is None:
            pi = a['pi_or_host']
            if pi is None:
                continue
            spec = getSpecialization( cur, pi )

        spec = spec or 'UNSPECIFIED'
        specialization_[ a['login'] ] = spec
    
    cur.execute( """SELECT * FROM holidays ORDER BY date""")
    for a in cur.fetchall( ):
        if a[ 'schedule_talk_or_aws' ] == 'NO':
            holidays_[ a['date'] ] = a


def get_data( ):
    global db_
    try:
        cur = db_.cursor( dictionary = True )
    except Exception as e:
        print( 
        '''If complain is about dictionary keyword. Install 
        https://pypi.python.org/pypi/mysql-connector-python-rf/2.2.2
        using easy_install'''
        )
        quit( )

    init( cur )

    # Entries in this table are usually in future.
    cur.execute( 'SELECT * FROM upcoming_aws' )
    for a in cur.fetchall( ):
        aws_[ a[ 'speaker' ] ].append( a )
        upcoming_aws_[ a['speaker'].lower( ) ] = a['date']
        # Keep the number of slots occupied at this day.
        upcoming_aws_slots_[ a['date'] ].append( a['speaker'] )

    # Now get all the previous AWSs happened so far.
    cur.execute( 'SELECT * FROM annual_work_seminars' )
    for a in cur.fetchall( ):
        aws_[ a[ 'speaker' ].lower() ].append( a )

    for a in aws_:
        # Sort a list in place.
        aws_[a].sort( key = lambda x : x['date'] )
        # print( a, [ x['date'] for x in aws_[a] ] )

    # Select all aws scheduling requests which have been approved.
    cur.execute( "SELECT * FROM aws_scheduling_request WHERE status='APPROVED'" )
    for a in cur.fetchall( ):
        aws_scheduling_requests_[ a[ 'speaker' ].lower( ) ] = a

    # Now pepare output file.
    text = [ ]
    text.append( 'login,pi_or_host,specialization,#aws,last_aws_on')
    for l in speakers_:
        if l in upcoming_aws_:
            print( '-> Name is in upcoming AWS. Ignoring', file = sys.stderr )
            continue

        piOrHost = speakers_[l].get('pi_or_host', 'UNKNOWN')
        line = [ ]
        line.append(l)
        line.append( '%s' % piOrHost )
        spec = spec_short( specialization_.get( l, 'UNKNOWN' ) )
        line.append( spec )

        nAws = len( aws_.get( l, [] ) )
        line.append( '%d' % nAws )

        line.append( '%s' % lastAwsDate( l ) )

        text.append( ','.join( line ) )

    text = '\n'.join( text )
    print( text, file = sys.stdout )
    
def lastAwsDate( speaker ):
    if speaker in aws_:
        awss = [ aws['date'] for aws in aws_[ speaker ] ]
        return sorted( awss )[-1]
    else:
        # joined date.
        return speakers_[speaker][ 'joined_on' ]

# From  http://stackoverflow.com/a/3425124/1805129
def monthdelta(date, delta):
    m, y = (date.month+delta) % 12, date.year + ((date.month)+delta-1) // 12
    if not m: m = 12
    d = min(date.day, [31,
        29 if y%4==0 and not y%400==0 else 28,31,30,31,30,31,31,30,31,30,31][m-1])
    dt = date.replace(day=d,month=m, year=y)
    return dt

def diffInDays( date1, date2, absolute = False ):
    ndays = ( date1 - date2 ).days
    if absolute:
        ndays = abs( ndays )
    return ndays

def main( outfile ):
    global db_
    get_data( )
    db_.close( )

if __name__ == '__main__':
    outfile = tempfile.NamedTemporaryFile( ).name
    if len( sys.argv ) > 1:
        outfile = sys.argv[1]
    main( outfile )
