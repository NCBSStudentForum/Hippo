#!/usr/bin/env python

import os
import sys
import smtplib 
import html2text
from email.mime.text import MIMEText
import logging

logging.basicConfig(
        level=logging.DEBUG,
        format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
        datefmt='%m-%d %H:%M',
        filename='/var/log/hippo.log',
        filemode='a'
        )

_logger = logging.getLogger('hippo.sendmail')

if len( sys.argv ) < 4:
    _logger.warn( "Usage: %s TO SUBJECT MSG_FILE" % sys.argv[0] )
    quit( )

fromAddress = 'NCBS Hippo <noreply@ncbs.res.in>'
toAddr = sys.argv[1]
subject = sys.argv[2]

_logger.debug( "Got command line params %s" % sys.argv )

msg = '''Error: no text found'''
with open( sys.argv[3], 'r' )  as f:
    msg = html2text.html2text( f.read( ) )

msg = MIMEText( msg )
msg[ 'subject' ] = subject
msg[ 'From' ] = 'NCBS Hippo <noreply@ncbs.res.in>'

s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
s.set_debuglevel( 2 )

try:
    _logger.info( 'Sending email to %s' % toAddr )
    s.sendmail( fromAddress, toAddr.split( ',' ), msg.as_string( ) )
    # remove the mail
    os.remove( sys.argv[3] )
except Exception as e:
    with open( '/var/log/hippo.log', 'a' ):
        _logger.error( 'Failed to send email. Error was %s' % e )

_logger.info( "Everything went OK. Mail sent sucessfully" )
s.quit( )
