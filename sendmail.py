#!/usr/bin/env python

import os
import sys
import html2other
import smtplib
from email.mime.text import MIMEText
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email import Encoders
import unicodedata
import re
from logger import _logger

# Function to remove unprintable characters from email.

all_chars = (unichr(i) for i in range(0x110000))
# or equivalently and much more efficiently
control_chars = ''.join(map(unichr, range(0,32) + range(127,160)))
control_char_re = re.compile('[%s]' % re.escape(control_chars))

def remove_control_chars(s):
    return control_char_re.sub('', s)

def main( args ):
    _logger.debug( "Got command line params %s" % vars(args) )
    fromAddr = args.sender
    toAddr = args.to
    subject = args.subject

    if not (toAddr and subject ):
        _logger.warn( "To toadders or subject specified. Not sending out email")
        return -1

    body = '';
    try:
        with open( args.msgfile, 'r' )  as f:
            # Remove all non-printable characters from message.
            lines = f.read( ).split( '\n' )
            body = '\n'.join( lines )

    except Exception as e:
        _logger.error( "I could not read file %s. Error was %s" % (args.msgfile, e))
        return False

    msg = MIMEMultipart( 'alternative' )
    msg[ 'To' ] = ",".join( args.to )

    if args.cc:
        msg[ 'CC' ] = ','.join( args.cc )
        toAddr += args.cc

    msg[ 'Subject' ] = subject
    msg[ 'From' ] = fromAddr

    msg.attach( MIMEText( html2other.tomd( body ), 'plain' ) );
    msg.attach( MIMEText( body, 'html' ) );

    # Now attach files Only PDF are allowed.
    for attach in args.attach:
        print( '[INFO] Attaching file %s' % attach )
        if not os.path.exists( attach ):
            continue

        # if filesize is more than 1 MB, ignore it.
        filesize = os.path.getsize( attach ) >> 20
        if filesize > 1.0:
            continue

        with open( attach, 'rb' ) as f:
            data = MIMEBase( 'application', 'pdf' )
            data.set_payload( f.read( ) )
            Encoders.encode_base64( data )
            data.add_header(
                    'Content-Disposition', 'attachment'
                    , filename= os.path.basename( attach )
                    )
            msg.attach(data)

    s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
    # s.set_debuglevel( 1 )
    success = False
    try:
        _logger.info( 'Sending email to %s' % toAddr )
        _logger.info( '\t From %s' % fromAddr )
        s.sendmail( fromAddr, toAddr, msg.as_string( ) )
        success = True
        _logger.info( "Everything went OK. Mail sent sucessfully" )
    except Exception as e:
        _logger.info( "Could not send. Error was %s" % e )

    s.quit( )
    return success

if __name__ == '__main__':
    import argparse
    # Argument parser.
    description = '''Email client'''
    parser = argparse.ArgumentParser(description=description)
    parser.add_argument('--msgfile', '-i'
        , required = True
        , help = 'Input file containing message'
        )
    parser.add_argument('--as-html', '-H'
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
    parser.add_argument('--cc', '-c'
        , required = False
        , action = 'append'
        , help = 'CC List'
        )
    parser.add_argument('--subject', '-s'
        , required = True
        , help = 'Subject of message'
        )
    parser.add_argument( '--attach', '-a'
        , required = False
        , default = [ ]
        , action = 'append'
        , help = 'attach these files'
        )
    class Args: pass
    args = Args()
    parser.parse_args(namespace=args)
    main( args )
