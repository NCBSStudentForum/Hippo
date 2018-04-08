"""security.py: 

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2018-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

from . import _globals
from . import hippo_ldap

def authenticate( login, password ):

    if _globals.get( 'AUTHENTICATED' ):
        logging.info( 'Alreading authenticated' )
        return True

    auth = False
    try:
        auth = hippo_ldap.authenticate_using_ldap( login, password )
    except Exception as e:
        pass

    if not auth:
        from . import hippo_imap
        try:
            auth = hippo_imap.authenticate_using_imap( login, password ) 
        except Exception as e:
            pass
    if auth:
        _globals.set( 'AUTHENTICATED', True )
        _globals.set( 'user',  login )
    return auth
