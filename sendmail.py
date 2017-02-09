#!/usr/bin/env python

import os
import sys
import smtplib 
import html2text
from email.mime.text import MIMEText
from logger import _logger

if len( sys.argv ) < 4:
    _logger.error( "Usage: %s TO SUBJECT MSG_FILE" % sys.argv[0] )
    _logger.error( "|- Got params %s" % sys.argv )
    quit( )

fromAddress = 'NCBS Hippo <noreply@ncbs.res.in>'
toAddr = sys.argv[1]
subject = sys.argv[2]

_logger.debug( "Got command line params %s" % sys.argv )

msg = '''Error: no text found'''

try:
    with open( sys.argv[3], 'r' )  as f:
        msg = f.read( )
except Exception as e:
    _logger.error( "I could not read file %s. Error was %s" % (sys.argv[3], e))
    quit( )

try:
    msg = html2text.html2text( msg )
except Exception as e:
    _logger.warn( 'Failed to convert to html. Error was %s' % e )

msg = MIMEText( msg )
msg[ 'subject' ] = subject
msg[ 'From' ] = 'NCBS Hippo <noreply@ncbs.res.in>'

s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
s.set_debuglevel( 2 )

try:
    _logger.info( 'Sending email to %s' % toAddr )
    s.sendmail( fromAddress, toAddr.split( ',' ), msg.as_string( ) )
except Exception as e:
    with open( '/var/log/hippo.log', 'a' ):
        _logger.error( 'Failed to send email. Error was %s' % e )

_logger.info( "Everything went OK. Mail sent sucessfully" )
s.quit( )
