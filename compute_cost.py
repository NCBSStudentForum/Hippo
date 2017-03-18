"""compute_cost.py: 

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar Singh"
__credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import random
import datetime

__fmt__ = '%Y-%m-%d'

def computeCost( currentDate, lastDate, nAWS ):
    ndays = ( lastDate - currentDate ).days 
    maxAWS = 5
    nyears = ndays / 365.0
    if ndays < 365.0 or nAWS > maxAWS:
        return int( 100 * 20.0 )

    cost = 3 * nyears 
    if nAWS > 2:
        cost += 3 * (maxAWS - nAWS)  - (20.0/nAWS) * ( nyears - 1)

    return int( 100 * max( 0, cost ))

def random_date(start, end):
    """
    This function will return a random datetime between two datetime 
    objects.
    """
    delta = end - start
    int_delta = (delta.days * 24.0 * 60 * 60) + delta.seconds
    random_second = randrange(int_delta)
    return start + datetime.timedelta(seconds=random_second)

def test( ):
    import pylab
    # Generate random test data.
    start = datetime.datetime.strptime( '2017-03-18', __fmt__ )
    end = datetime.datetime.strptime( '2021-03-18', __fmt__  )
    for naws in range( 0, 5 ):
        xval, yval = [ ], [ ]
        for i in range( 5* 54 ):
            date = start + datetime.timedelta( days = i * 7 )
            xval.append( (date - start).days / 365.0 )
            yval.append( computeCost( start, date, naws ) )
        pylab.xlabel( 'Year' )
        pylab.ylabel( 'Cost' )
        pylab.plot( xval, yval, alpha = 0.7, label = '%s' % naws )
        pylab.legend( )

    pylab.savefig( "%s.png" % sys.argv[0] )


if __name__ == '__main__':
    test()
