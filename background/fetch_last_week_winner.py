#!/usr/bin/env python

import os
import re
import mechanize
import mimetypes
import urllib2
import random

os.environ[ 'http_proxy' ] = 'http://proxy.ncbs.res.in:3128'
os.environ[ 'https_proxy' ] = 'http://proxy.ncbs.res.in:3128'

br_ = mechanize.Browser()
base_url_ = 'https://intranet.ncbs.res.in/photography'

background_dir_ = '_backgrounds'
if not os.path.exists( background_dir_ ):
    os.makedirs( background_dir_ )

def log( msg ):
    return 
    with open( '/tmp/a.txt', 'a' ) as f:
        f.write( msg + '\n' )

def is_url_image(url):    
    mimetype,encoding = mimetypes.guess_type(url)
    return (mimetype and mimetype.startswith('image'))

def is_image_and_ready(url):
    return is_url_image(url) 

def print_broser( ):
    global br_

def main( ):
    global br_, base_url_
    log( 'trying url open' )
    try:
        res = br_.open( base_url_ )
    except Exception as e:
        log( 'failed to open %s' % e )
    urls = [ ]
    for l in br_.links( ):
        url = l.url 
        if is_image_and_ready( url ):
            urls.append(url)
            outfile = os.path.basename( url )
            outpath = os.path.join( background_dir_, outfile )
            if not os.path.exists( outpath ):
                try:
                    with open( outpath, 'wb' ) as f:
                        f.write( urllib2.urlopen( url ).read( ) )
                except Exception as e:
                    pass

            log( url )

    # This we write to stdout.
    print( random.choice( urls ) )


if __name__ == '__main__':
    log( 'running' )
    main()
    log( 'Finished' )


