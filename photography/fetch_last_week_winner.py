#!/usr/bin/env python

import re
import mechanize
import mimetypes
import urllib2
import random

br_ = mechanize.Browser()
base_url_ = 'http://intranet.ncbs.res.in/photography'

def is_url_image(url):    
    mimetype,encoding = mimetypes.guess_type(url)
    return (mimetype and mimetype.startswith('image'))

def check_url(url):
    """Returns True if the url returns a response code between 200-300,
       otherwise return False.
    """
    try:
        headers={
            "Range": "bytes=0-10",
            "User-Agent": "MyTestAgent",
            "Accept":"*/*"
        }

        req = urllib2.Request(url, headers=headers)
        response = urllib2.urlopen(req)
        return response.code in range(200, 209)
    except Exception, ex:
        return False

def is_image_and_ready(url):
    return is_url_image(url) and check_url(url)

def print_broser( ):
    global br_
    print( br_ )

def main( ):
    global br_, base_url_
    res = br_.open( base_url_ )
    urls = [ ]
    for l in br_.links( ):
        url = l.url 
        if is_image_and_ready( url ):
            urls.append(url)
    print( random.choice( urls ) )


if __name__ == '__main__':
    main()


