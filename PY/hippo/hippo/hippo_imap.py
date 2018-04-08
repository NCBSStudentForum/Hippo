"""hippo_imap.py: 

IMAP related functions.

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import imaplib
import logging

def get_server( email ):
    if 'ncbs' in email:
        return 'imap.ncbs.res.in'
    elif 'instem' in email:
        return 'imap.instem.res.in'
    elif 'ccamp'  in email:
        return 'imap.ccamp.res.in'
    return 'imap.ncbs.res.in'

def authenticate_using_imap( email, password ):
    login = email.split( '@' )[0]
    server = get_server( email )
    url = server
    M = imaplib.IMAP4_SSL( url )
    try:
        res = M.login( login, password )
        if res:
            return True
        return False
    except Exception as e:
        logging.warn( "Failed to open mailbox %s" % e )
        return False
    return False


def test( ):
    import getpass
    password = getpass.getpass( 'Password? ' )
    print( authenticate_using_imap( 'dilawars', password ) )

if __name__ == '__main__':
    test( )



