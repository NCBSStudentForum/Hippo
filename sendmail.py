#!/usr/bin/env python

import os
import sys
import html2other
import smtplib 
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from logger import _logger

def main( args ):
    _logger.debug( "Got command line params %s" % args )
    fromAddr = args.sender
    toAddr = args.to
    subject = args.subject

    msg = '''Error: no text found'''
    try:
        with open( args.msgfile, 'r' )  as f:
            msg = f.read( )
    except Exception as e:
        _logger.error( "I could not read file %s. Error was %s" % (args.msgfile, e))
        return False

    # msg = html2other.tomd( msg )
    msg = MIMEMultipart( 'alernative' )
    msg[ 'subject' ] = subject
    msg[ 'From' ] = fromAddr

    if args.as_html:
        msg.attach( MIMEText( msg, 'html' ) );
    else:
        msg.attach( MIMEText( msg, 'plain' ) );

    # Now attach files.
    for attach in args.attach:
        print( '[INFO] Attaching file %s' % attach )
        with open( attach, 'r' ) as f:
            msg.attach( MIMEText( f.read() ) )

    s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
    s.set_debuglevel( 1 )

    success = False
    try:
        _logger.info( 'Sending email to %s' % toAddr )
        s.sendmail( fromAddr, toAddr, msg.as_string( ) )
        success = True
    except Exception as e:
        with open( '/var/log/hippo.log', 'a' ):
            _logger.error( 'Failed to send email. Error was %s' % e )

        _logger.info( "Everything went OK. Mail sent sucessfully" )

    s.quit( )
    return success

if __name__ == '__main__':
    import argparse
    # Argument parser.
    description = '''Email client'''
    parser = argparse.ArgumentParser(description=description)
    parser.add_argument('--msgfile', '-f'
        , required = True
        , help = 'Input file containing message'
        )
    parser.add_argument('--as-html', '-h'
        , required = False
        , action = 'store_true' 
        , default = False
        , help = 'Send it as html. Default (false)'
        )
    parser.add_argument('--sender', '-f'
        , required = False
        , default = 'NCBS Hippo <noreply@ncbs.res.in>'
        , help = 'From whom?'
        )
    parser.add_argument('--to', '-t'
        , required = True
        , action = 'append'
        , help = 'Recipients'
        )
    parser.add_argument('--subject', '-s'
        , required = True
        , help = 'Subject of message'
        )
    parser.add_argument( '--attach', '-a'
        , required = False
        , default = ''
        , action = 'append'
        , help = 'attach these files'
        )
    class Args: pass 
    args = Args()
    parser.parse_args(namespace=args)
    main( args )
