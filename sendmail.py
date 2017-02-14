#!/usr/bin/env python

import os
import sys
import re
import smtplib 
from email.mime.text import MIMEText
from logger import _logger

if len( sys.argv ) < 4:
    _logger.error( "Usage: %s TO SUBJECT MSG_FILE" % sys.argv[0] )
    _logger.error( "|- Got params %s" % sys.argv )
    quit( )

def toText( msg ):
    # First try with pandoc.
    #   Remove all <div> tags.
    msg = msg.replace( '</div>', '' )
    msg = re.sub( r'\<div\s+.+?\>', '', msg )

    try:
        import pypandoc
        if not os.path.isfile( '/usr/bin/pandoc' ):
            os.environ.setdefault( 'PYPANDOC_PANDOC', '/usr/local/bin/pandoc' )

        msg = pypandoc.convert_text( msg, 'md', format = 'html' ) 
    except Exception as e:
        _logger.warn( 'Failed to convert to html using pandoc.  %s' % e )
        _logger.info( 'Trying html2text ' )
        try:
            import html2text
            msg = html2text.html2text( msg )
        except Exception as e:
            _logger.warn( 'Failed to convert to html using html2text. %s' % e )
    return msg

def sendMail( fromAddr, toAddr, subject, msg ):
    msg = toText( msg )
    msg = MIMEText( msg )
    msg[ 'subject' ] = subject
    msg[ 'From' ] = 'NCBS Hippo <noreply@ncbs.res.in>'

    s = smtplib.SMTP( 'ghevar.ncbs.res.in', 587 )
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
