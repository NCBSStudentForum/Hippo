"""resources.py: 

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import logging
from pyramid.httpexceptions import HTTPFound 

from . import _globals

def assertAuthentication(  ):
    if _globals.get( "AUTHENTICATED" ):
        return True

    logging.warn( "Is not authenticated" )
    raise HTTPFound( location = "login" )

