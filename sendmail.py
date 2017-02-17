#!/usr/bin/env python

import sys
import smtplib 
import html2text
from email.mime.text import MIMEText

if len( sys.argv ) < 4:
    print( "Usage: %s TO SUBJECT MSG_FILE" % sys.argv[0] )
    quit( )

fromAddress = 'NCBS Hippo <noreply@ncbs.res.in>'
toAddr = sys.argv[1]
subject = sys.argv[2]

msg = '''Error: no text found'''
with open( sys.argv[3], 'r' )  as f:
    msg = html2text.html2text( f.read( ) )

msg = MIMEText( msg )
msg[ 'subject' ] = subject
msg[ 'From' ] = 'NCBS Hippo <noreply@ncbs.res.in>'

s = smtplib.SMTP( 'mail.ncbs.res.in', 587 )
s.set_debuglevel( 2 )

s.sendmail( fromAddress, toAddr.split( ',' ), msg.as_string( ) )
