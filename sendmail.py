#!/usr/bin/env python

import os
import sys
import html2other
import smtplib 
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from logger import _logger

if len( sys.argv ) < 4:
    _logger.error( "Usage: %s TO SUBJECT MSG_FILE" % sys.argv[0] )
    _logger.error( "|- Got params %s" % sys.argv )
    quit( )


def sendMail( fromAddr, toAddr, subject, msghtml ):
    """Send html email """
    # msg = html2other.tomd( msg )
    msg = MIMEMultipart( 'alernative' )
    msg[ 'subject' ] = subject
    msg[ 'From' ] = 'NCBS Hippo <noreply@ncbs.res.in>'

    msg.attach( MIMEText( msghtml, 'html' ) );
    s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
    s.set_debuglevel( 2 )

    success = False
    try:
        _logger.info( 'Sending email to %s' % toAddr )
        s.sendmail( fromAddr, toAddr.split( ',' ), msg.as_string( ) )
        success = True
    except Exception as e:
        with open( '/var/log/hippo.log', 'a' ):
            _logger.error( 'Failed to send email. Error was %s' % e )

        _logger.info( "Everything went OK. Mail sent sucessfully" )
    s.quit( )
    return success

def main( args ):
    _logger.debug( "Got command line params %s" % args )
    fromAddr = 'NCBS Hippo <noreply@ncbs.res.in>'
    toAddr = args[1]
    subject = args[2]

    msg = '''Error: no text found'''
    try:
        with open( args[3], 'r' )  as f:
            msg = f.read( )
    except Exception as e:
        _logger.error( "I could not read file %s. Error was %s" % (args[3], e))
        return False

    return sendMail( fromAddr, toAddr, subject, msg )

if __name__ == '__main__':
    main( sys.argv )
