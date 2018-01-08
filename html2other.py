#!/usr/bin/env python2.7

"""html2markdown.py:

"""

__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar Singh"
_credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys

PYMAJOR = sys.version_info[0]
if PYMAJOR == 2:
    reload( sys )
    sys.setdefaultencoding( 'utf-8' )

import os
import re
import html2text
import string
import tempfile
import base64
from logger import _logger

pandoc_ = True
try:
    if not os.path.isfile( '/usr/bin/pandoc' ):
        os.environ.setdefault( 'PYPANDOC_PANDOC', '/usr/local/bin/pandoc' )
    import pypandoc
except Exception as e:
    _logger.warn( 'Failed to convert to html using pandoc.  %s' % e )
    pandoc_ = False

# Wrap stdout so we can write to unicode
# sys.stdout = codecs.getwriter(locale.getpreferredencoding())(sys.stdout)

def fix( msg ):
    if PYMAJOR == 2:
        msg = msg.decode( 'ascii', 'ignore' )
    return msg

def tomd( msg ):
    msg = fix( msg )
    # remove <div class="strip_from_md"> </div>
    pat = re.compile( r'\<div\s+class\s*\=\s*"strip_from_md"\s*\>.+?\</div\>', re.DOTALL )

    # remove all 'style' too.
    stylePat = re.compile( r'style\s*=\s*".+?;"', re.DOTALL )

    # remove all 'class' too.
    classPat = re.compile( r'class\s*=\s*"\w+"', re.DOTALL )

    for pat in [ pat, stylePat, classPat ]:
        for s in pat.findall( msg ):
            msg = msg.replace( s, '' )


    if PYMAJOR == 2:
        msg = filter( lambda x: x in string.printable, msg )

    msg = msg.replace( '</div>', '' )
    msg = re.sub( r'\<div\s+.+?\>', '', msg )

    if pandoc_:
        md = pypandoc.convert_text( msg, 'md', format = 'html'
                , extra_args = [ '--atx-headers' ]
                )
        return md.encode( 'ascii', 'ignore' )
    else:
        _logger.info( 'Trying html2text ' )
        try:
            import html2text
            msg = html2text.html2text( msg )
        except Exception as e:
            _logger.warn( 'Failed to convert to html using html2text. %s' % e )

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
    with open( infile, 'r' ) as f:
        msg = fix( f.read( ) )
        try:
            msg = pypandoc.convert_text( msg, 'tex', format = 'html'
                    , extra_args = [ '--parse-raw' ])
            msg = fixInlineImage( msg )
        except Exception as e:
            msg = 'Failed to convert to TeX %s' % e
    return msg

def htmlfile2md( filename ):
    with open( filename, 'r' ) as f:
        text = f.read( )

    md = tomd( text )
    md = md.decode( 'utf-8' )

    # Style ect.
    pat = re.compile( r'{(style|lang=).+?}', re.DOTALL )
    md = pat.sub( '', md )
    md = md.replace( '\\', '' )
    return md

def main( infile, outfmt ):
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
