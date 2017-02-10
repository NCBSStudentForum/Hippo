#!/usr/bin/env python

"""hippo_cron.py: 

This script is run by cron. To run it, use the following command

    $ sudo crontab -u apache -e 

-u apache could be -u www-data 

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
from logger import _logger


def sendEmails( ):



def main( ):
    _logger.info( 'Running cronjob' )
    sendEmails( )

if __name__ == '__main__':
    main()
