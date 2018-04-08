"""hippo_ldap.py: 

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2018-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import logging

ldap_ports_ = dict( ncbs=389, instem=18288, ccamp=19554 ) #, query = 8862 )

def authenticate_using_ldap( user:str, password:str) -> bool:
    import ldap3
    logging.info( 'Loging %s' % user )
    authenticated = False
    for group in ldap_ports_:
        url = 'ldap.ncbs.res.in:%d' % ldap_ports_[group]
        server = ldap3.Server( url, get_info=ldap3.ALL )
        user_dn="uid=%s,ou=People,dc=%s,dc=res,dc=in" % (user,group)
        logging.debug( ' | USER_DN: %s' % user_dn )
        c = ldap3.Connection( server
                , user=user_dn, password=password
                , raise_exceptions = False
                , lazy = False
                )
        c.open()
        c.bind()
        if c.result[ 'result' ] == 0:
            authenticated = True
            break

    return authenticated

def test( ):
    import getpass
    password = getpass.getpass( prompt = 'Passowrd?' )
    res = authenticate_using_ldap( 'dilawars', password )
    print( res )

if __name__ == '__main__':
    test()
