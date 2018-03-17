#!/usr/bin/env python3

__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar Singh"
_credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import subprocess
import re
import html2text
import string
import tempfile
import base64
from logger import _logger

pandoc_ = True

def _cmd( cmd ):
    output = subprocess.check_output( cmd.split( ), shell = False )
    return output.decode( 'utf-8' )

def fix( msg ):
    return msg

def tomd( msg ):
    msg = fix( msg )
    # remove <div class="strip_from_md"> </div>
    pat = re.compile( r'\<div\s+class\s*\=\s*"strip_from_md"\s*\>.+?\</div\>', re.DOTALL )
    for s in pat.findall( msg ):
        msg = msg.replace( s, '' )
    msg = msg.replace( '</div>', '' )
    msg = re.sub( r'\<div\s+.+?\>', '', msg )

    htmlfile = '/tmp/_from_.html' 
    txtfile = '/tmp/_msg.txt'
    with open( htmlfile, 'w' ) as f:
        f.write( msg )

    _cmd( 'pandoc --atx-headers -t plain -o %s %s' % (txtfile, htmlfile) )
    with open( txtfile ) as f:
        return f.read( )

    # else return html.
    msg = re.sub( r'\\+\n', '\n', msg )
    return msg.decode( 'utf-8' )

def fixInlineImage( msg ):
    """Convert inline images to given format and change the includegraphics text
    accordingly.

    Surround each image with \includewrapfig environment.
    """

    # Sometime we loose = in the end.
    pat = re.compile( r'\{data:image/(.+?);base64,(.+?\=?\})', re.DOTALL )
    for m in pat.finditer( msg ):
        outfmt = m.group( 1 )
        data = m.group( 2 )
        fp = tempfile.NamedTemporaryFile( delete = False, suffix='.'+outfmt )
        fp.write( base64.b64decode( data ) )
        fp.close( )
        # Replace the inline image with file name.
        msg = msg.replace( m.group(0), "{%s}" % fp.name )

    # And wrap all includegraphics around by wrapfig
    msg = re.sub( r'(\\includegraphics.+?width\=(.+?)([\],]).+?})'
            , r'\n\\begin{wrapfigure}{R}{\2}\n \1 \n \\end{wrapfigure}'
            , msg, flags = re.DOTALL
            )

    return msg

def toTex( infile ):
    outfile = '/tmp/_out.tex' 
    try:
        _cmd( 'pandoc -f html -t ltex --parse-raw -o %s %s' % (outfile, infile ))
    except Exception as e:
        msg = 'Failed to convert to TeX due to "%s"' % e
        return msg

    with open( outfile ) as f:
        msg = fixInlineImage( f.read( ) )
    return msg

def htmlfile2md( filename ):
    with open( filename, 'r' ) as f:
        text = f.read( )

    md = tomd( text )

    # Style ect.
    pat = re.compile( r'{(style|lang=).+?}', re.DOTALL )
    md = pat.sub( '', md )
    md = md.replace( '\\', '' )
    return md

def main( infile, outfmt ):
    # Print is neccessary since we are reading stdout in PHP.
    if outfmt in [ 'md', 'markdown', 'text', 'txt' ]:
        md = htmlfile2md( infile )
        md = md.replace( '\\', '' )
        print( md )
        return md
    elif outfmt == 'tex':
        print( toTex( infile ) )
        return toTex( infile )
    elif outfmt == "html2text":
        with open( infile, 'r' ) as f:
            res = html2text.html2text( fix( f.read( ) ) )
            print( res )
            return res

if __name__ == '__main__':
    infile = sys.argv[1]
    outfmt = sys.argv[2]
    main( infile, outfmt )
