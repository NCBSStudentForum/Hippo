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
from logger import _logger
from db_connect import db_

fmt_ = '%Y-%m-%d'

cwd = os.path.dirname( os.path.realpath( __file__ ) )

def init( cur ):
    """
    Create a temporaty table for scheduling AWS
    """
    pass

def addOrUpdateEvent( e ):
    st, et = e[ 'start_time'], e['end_time']
    date = e['date']
    title = e['title'] 
    print( date, st, et, title )

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

    print( 'Getting events between %s and %s' % (todayStr, endDayStr ))
    cur.execute( """
        SELECT * FROM events WHERE is_public_event='YES' AND date >= '%s'
        AND date <= '%s'""" % (todayStr, endDayStr)
        )
    es = cur.fetchall( )
    for e in es:
        addOrUpdateEvent( e )


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
