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
import ldap3

ldap_ports_      = dict( ncbs=389, instem=18288, ccamp=19554 )
ldap_query_port_ = 8862
default_attribs_ = [ 
        'sn', 'mail'
        , 'profilefirstname', 'profilelastname', 'profiledateofjoin'
        , 'profileidentification', 'profilelaboffice', 'profiledesignation'
        , 'profileactive'
        ]

def readable( entry ):
    ldap = { }
    for k in default_attribs_:
        v = getattr( entry, k )
        ldap[ k.replace( 'profile', '' ) ] = v.value
    return ldap

def query( user: str, attribs = [] ):
    attribs = attribs or default_attribs_
    url = 'ldap.ncbs.res.in:%d' % ldap_query_port_
    basedn  = 'dc=ncbs,dc=res,dc=in'
    server = ldap3.Server( url, get_info=ldap3.ALL )
    conn = ldap3.Connection( server, user='', password='', auto_bind = True )
    if not conn.bind():
        logging.warn( "Failed to bind to ldap server. %s" % conn.result)
        return { }

    conn.search( basedn, "(uid=%s)" % user 
            , attributes = attribs 
            )
    e = conn.entries[0]
    return readable( e )


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

def test_authenticate( ):
    import getpass
    password = getpass.getpass( prompt = 'Passowrd?' )
    res = authenticate_using_ldap( 'dilawars', password )

def test_query( ):
    for x in 'dilawars,anushreen,bhalla,farris'.split(','):
        r = query(x)
        print( r )

if __name__ == '__main__':
    #  test_authenticate()
    test_query( )
