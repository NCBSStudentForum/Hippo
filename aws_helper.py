"""aws_helper.py: 

    Helper functions are here.
"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import datetime
from logger import _logger
from global_data import *

def toDate( datestr ):
    return datetime.datetime.strptime( datestr, fmt_ )

def findReplacement( speaker, date, specialization, piH, schedule ):
    for dateA in sorted( schedule ):
        if dateA <= date:
            continue
        for i, speakerA in enumerate( schedule[dateA] ):
            if speakerA == speaker:
                continue
            slot = '%s,%d' % (dateA,i)
            spec = g_.node[slot]['specialization']
            if spec != specialization:
                continue

            thisPI = speakers_[ speakerA ]['pi_or_host']

            if thisPI == piH:
                continue

            # final check. Make sure the AWS does not come too early.
            nDays = ( toDate(dateA) - toDate(date) ).days
            prevAWS = g_.node[ speakerA ][ 'last_date' ]
            newDate = prevAWS - datetime.timedelta( days = nDays )
            if (newDate - prevAWS).days < 400:
                _logger.warn( 'Costly swapping. ignoring...' )
                continue
            return speakerA, dateA
    _logger.warn( 'Could not find replacement for %s.%s' % (speaker, date) )
    return None


def no_common_labs( schedule, nweeks = 2, ncalls = 0 ):
    # Make sure that first 2 week entries have different PIs.
    if ncalls > 100:
        _logger.warn( "Terminated after 100 calls" )
        return schedule
    failedDates = [ ]
    sortedDates = sorted( schedule )
    for ix, date in enumerate(sortedDates[:nweeks]):
        labs = []
        for i, speaker in enumerate(schedule[ date ]):
            spec = g_.node['%s,%d'%(date,i)]['specialization']
            piH = speakers_[speaker]['pi_or_host']
            if piH in labs:
                spec = specialization_.get( speaker, 'UNSPECIFIED' )
                replaceWith = findReplacement( speaker, date, spec, piH, schedule )
                if replaceWith is not None:
                    speakerB, dateB = replaceWith
                    speakerA, dateA = speaker, date
                    schedule[dateA].append( speakerB )
                    schedule[dateA].remove( speakerA )
                    schedule[dateB].append( speakerA )
                    schedule[dateB].remove( speakerB )
                    _logger.info( 'Swapping %s and %s' % (speakerA, speakerB))
                else:
                    # swap this row by next and try again.
                    failedDates.append( date )
                    _logger.info( "Failed to find alternative for %s" % date )
                    # swap this date with someone else.
                    for iy, datey in enumerate( sortedDates[nweeks:] ):
                        if len( schedule[ datey ] ) == len( schedule[ date ] ):
                            # we can swap with entry.
                            temp = schedule[ datey ]
                            schedule[ datey ] = schedule[ date ]
                            schedule[ date ] = temp
                            return no_common_labs( schedule, nweeks, ncalls + 1)
            else:
                labs.append( piH )

    for fd in failedDates:
        _logger.warn( 'Entry for date %s has multiple speakers from same lab' % fd )
        _logger.warn( 'Moving whole row down to more than %d positions' % nweeks)

    return schedule
