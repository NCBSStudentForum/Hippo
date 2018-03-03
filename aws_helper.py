"""aws_helper.py: 

    Helper functions are here.
"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

from logger import _logger
from global_data import *

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

            return speakerA, dateA
    return None


def no_common_labs( schedule ):

    ## if the first two weeks have less that 3 entries, sweep from other places.
    #for date in sorted(schedule)[:4]:
    #    speakers = schedule[date]
    #    spec = g_.node[ '%s,0' % date ]['specialization']
    #    piHS = [ speakers_[speaker]['pi_or_host'] for speaker in speakers ]
    #    for i in range( 3, len( speakers ), -1):
    #        addThisOne = findSpeakerWithSpecialization( spec, date, piHS, schedule )
    #        if addThisOne is not None:
    #            speakerB, dateB = addThisOne
    #            schedule[ date ].append( speakerB )
    #            schedule[ dateB ].remove( speakerB )

    # Make sure that first 2 week entries have different PIs.
    for date in sorted(schedule)[:2]:
        labs = []
        for i, speaker in enumerate(schedule[ date ]):
            spec = g_.node['%s,%d'%(date,i)]['specialization']
            piH = speakers_[speaker]['pi_or_host']
            if piH in labs:
                spec = speakersSpecialization_[ speaker ]
                replaceWith = findReplacement( speaker, date, spec, piH, schedule )
                if replaceWith is not None:
                    speakerB, dateB = replaceWith
                    speakerA, dateA = speaker, date
                    schedule[dateA].append( speakerB )
                    schedule[dateA].remove( speakerA )
                    schedule[dateB].append( speakerA )
                    schedule[dateB].remove( speakerB )
                    _logger.warn( 'Swapping %s and %s' % (speakerA, speakerB))
            else:
                labs.append( piH )
    return schedule
