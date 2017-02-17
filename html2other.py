"""html2markdown.py: 

"""
    
__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2016, Dilawar Singh"
__credits__          = ["NCBS Bangalore"]
__license__          = "GNU GPL"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import sys
import os
import re
import textwrap
from bs4 import BeautifulSoup
from logger import _logger

pandoc_ = True
try:
    if not os.path.isfile( '/usr/bin/pandoc' ):
        os.environ.setdefault( 'PYPANDOC_PANDOC', '/usr/local/bin/pandoc' )
    import pypandoc
except Exception as e:
    _logger.warn( 'Failed to convert to html using pandoc.  %s' % e )
    pandoc_ = False

def reformat( msg ):
    lines = msg.split( '\n' )
    head = lines[0].split( )
    s1 = len( head[0] )

def tomd( msg ):
    # First try with pandoc.
    #   Remove all <div> tags.
    # html = BeautifulSoup( msg, 'html.parser' )
    # for td in html.find_all( 'td' ):
        # if td:
            # td.string = "\n ".join( textwrap.wrap(td.text, 80 ))

    # msg = html.text
    msg = msg.replace( '</div>', '' )
    msg = re.sub( r'\<div\s+.+?\>', '', msg )

    if pandoc_:
        msg = pypandoc.convert_text( msg, 'md', format = 'html' 
                , extra_args = [ ]
                )
    else:
        _logger.info( 'Trying html2text ' )
        try:
            import html2text
            msg = html2text.html2text( msg )
        except Exception as e:
            _logger.warn( 'Failed to convert to html using html2text. %s' % e )
    msg = msg.replace( '&', '\&' )
    return msg

def toTex( infile ):
    with open( infile, 'r' ) as f:
        msg = f.read( )
        try:
            msg = pypandoc.convert_text( msg, 'tex', format = 'html'
                    , extra_args = [ '--parse-raw' ])
        except Exception as e:
            pass
    return msg

def htmlfile2md( filename ):
    with open( filename, 'r' ) as f:
        text = f.read( )
    return tomd( text )

def main( ):
    infile = sys.argv[1]
    outfmt = sys.argv[2]
    if outfmt == 'md':
        md = htmlfile2md( infile )
        print( md )
    elif outfmt == 'tex':
        print( toTex( infile ) )

if __name__ == '__main__':
    main()
