#!/usr/bin/env python2.7

"""schedule_aws.py: 

Synchronize google calendar.


"""

from __future__ import print_function 
    
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
from collections import defaultdict, OrderedDict
import datetime 
import tempfile 
import base64
import subprocess
from logger import _logger
from db_connect import db_

fmt_ = '%Y-%m-%d'

cwd_ = os.path.dirname(os.path.realpath(__file__))

# gcalcli command.
cmd_ = [ os.path.join( cwd_, "gcalcli" ) ] 
options_ = [ "--calendar", 'NCBS Public Calendar', "--tsv", '--details', 'all' ]

def init( cur ):
    """
    Create a temporaty table for scheduling AWS
    """
    pass

def b64decode( b64_string ):
    b64_string += "=" * ((4 - len(b64_string) % 4) % 4)
    return base64.b64decode( b64_string ).strip( )

def getEid( eid ):
    eid = eid.split( 'eid=', 1)[-1].split('&')[0]
    eid = b64decode( eid )
    return eid.split( ' ' )[0]

def listToEventDict( event ):
    assert type( event ) == list
    e = { }
    if not event:
        return e
    e[ 'start_date' ] = event[0]
    e[ 'date' ] = event[0]
    e[ 'start_time' ] = event[1]
    e[ 'end_date' ] = event[2]
    e[ 'end_time' ] = event[3]
    e[ 'url' ] = event[4]
    e[ 'calendar_event_id' ] = getEid( event[4] )
    e[ 'title'] = event[6]
    e[ 'location'] = event[7]
    e[ 'description'] = event[8]
    return e

def eventToStr( event ):
    return "%s (%s), %s to %s" % ( event[ 'title'], event[ 'location' ]
            , event[ 'start_date' ], event[ 'end_date' ] 
            )

def execute( cmd ):
    print( '[INFO] Executing %s' % ' '.join( cmd ) )
    o = subprocess.check_output( cmd, shell = False )
    return filter( lambda x: len( x.strip( )) > 10, o.split( '\n' ) )

def get_agenda( start, end ):
    global cmd_, options_
    global fmt_
    start_date = start.strftime( fmt_ )
    end_date = end.strftime( fmt_ )
    print( '[INFO] Getting events between %s and %s' % (start_date, end_date))
    cmd = cmd_ + [ 'agenda' , '%s' % start_date, '%s' % end_date ] + options_
    events = execute( cmd )
    return map( lambda x: listToEventDict( x.split( '\t' ) ), events )

def addOrUpdateEvent( e ):
    st, et = e[ 'start_time'], e['end_time']
    date = e['date']
    title = e['title'] 

def is_event_in_google_calendar( event, dbEvents ):
    for i, eventDict in enumerate( dbEvents ):
        if eventDict[ 'calendar_event_id' ] == e[ 'calendar_event_id' ]:
            print( '[INFO] Event already exists' )
            return True
    return True

def areSameEvents( local, google ):
    res = True
    for i in [ 'title', 'description' ]:
        res = res and ( local[i] == google[i] )
    res = res and ( local[ 'venue' ] in google[ 'location' ] )
    res = res and ( '%s' % local[ 'start_time' ] == '%s:00' % google[ 'start_time' ] )
    res = res and ( '%s' % local[ 'end_time' ] == '%s:00' % google[ 'end_time' ] )
    return res

def deleteEvent( event ):
    global cmd_
    title = event[ 'title' ]
    start, end = event[ 'start_date' ], event[ 'end_date' ]
    print( "Deleting event %s" % eventToStr( event ) )
    cmd = cmd_ + [ 'delete', title, start, end, '--imaexpert' ]

def updateEvent( googleEvent, localEvent ):
    global cmd_ 
    options = [ ]
    cmd = cmd_ + [ 'update', googleEvent[ 'title' ] ] + options
    print( cmd )


def deleteOrUpdate( googleEvent, localEvents ):
    foundLocalEvent = None
    calEventId = googleEvent[ 'calendar_event_id' ]
    for le in localEvents:
        if areSameEvents( le, googleEvent ):
            foundLocalEvent = le
            break

    if foundLocalEvent is None:
        # delete this event.
        deleteEvent( googleEvent )
    else:
        updateEvent( googleEvent, foundLocalEvent )

def synchronize( localEvents, googleEvents ):
    global cmd_ 
    # First delete/update any google event.
    for i, ge in enumerate( googleEvents ):
        deleteOrUpdate( ge,  localEvents )


def process( ):
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

    assert( cur )

    today = datetime.datetime.today()
    endDay = today + datetime.timedelta( days = 14 )
    todayStr = today.strftime( fmt_ )
    endDayStr = endDay.strftime( fmt_ )

    # Events in google calendar.
    googleEvents = get_agenda( today, endDay )

    print( 'Getting events between %s and %s' % (todayStr, endDayStr ))
    cur.execute( """
        SELECT * FROM events WHERE is_public_event='YES' AND date >= '%s'
        AND date <= '%s'""" % (todayStr, endDayStr)
        )
    es = cur.fetchall( )
    synchronize( es, googleEvents )


def main( outfile ):
    global db_
    _logger.info( 'Synchronuzing calendar...' )
    process( )
    db_.close( )

if __name__ == '__main__':
    outfile = tempfile.NamedTemporaryFile( ).name
    if len( sys.argv ) > 1:
        outfile = sys.argv[1]
    main( outfile )
