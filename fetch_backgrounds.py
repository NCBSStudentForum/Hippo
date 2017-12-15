#!/usr/bin/env python

import os
import re
import mimetypes
import urllib2
import random

import PIL.Image
import PIL.ImageChops
from PIL import ImageDraw
from PIL import ImageFont

import cStringIO
import lxml.html

os.environ[ 'http_proxy' ] = 'http://proxy.ncbs.res.in:3128'
os.environ[ 'https_proxy' ] = 'http://proxy.ncbs.res.in:3128'

base_url_ = 'https://intranet.ncbs.res.in/photography'

background_dir_ = './data/_backgrounds'
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

def writeOnImage( img, caption ):
    draw = ImageDraw.Draw(img)
    # font = ImageFont.truetype(<font-file>, <font-size>)
    font = ImageFont.truetype("./data/OpenSans-Regular.ttf", 12 )
    fontCaption = ImageFont.truetype("./data/OpenSans-Regular.ttf", 20 )
    draw.text((10, 15) , caption[0:80]
            , (255,255,255) , font=fontCaption
            )
    draw.text((10, 50) , '(c) NCBS Photography Club'
            , (255,255,255)
            , font=font
            )
    return img 

def crop_surrounding_whitespace(image):
    """Remove surrounding empty space around an image.

    This implemenation assumes that the surrounding space has the same colour
    as the top leftmost pixel.

    :param image: PIL image
    :rtype: PIL image
    """
    bg = PIL.Image.new(image.mode, image.size, image.getpixel((0, 0)))
    diff = PIL.ImageChops.difference(image, bg)
    bbox = diff.getbbox()
    if not bbox:
        return image
    return image.crop(bbox)

def main( ):
    global base_url_
    html = None
    try:
        html = urllib2.urlopen( base_url_ ).read( )
        assert html
    except Exception as e:
        log( 'failed to open %s' % e )
        return 1

    doc = lxml.html.fromstring( html )
    tables = doc.xpath( '//table' ) 

    images = [ ]
    for table in tables:
        trs = table.xpath( './/tr' )
        for tr in trs:
            image = {} 
            tds = tr.xpath( './/td' )
            for td in tds:
                links = td.xpath( './/a' )
                if links:
                    for l in links:
                        if l.text:
                            image[ 'caption' ] = l.text
                        if is_url_image( l.attrib[ 'href' ] ):
                            image[ 'url' ] = l.attrib[ 'href' ]
            images.append( image )

    for im in images:
        if not im:
            continue
        url = im[ 'url' ]
        caption = im.get( 'caption', '' )
        if is_image_and_ready( url ):
            outfile = os.path.basename( url )
            outpath = os.path.join( background_dir_, outfile + '.jpg' )
            if not os.path.exists( outpath ):
                try:
                    img = cStringIO.StringIO( urllib2.urlopen( url ).read( ) )
                    img = PIL.Image.open( img )
                    img = crop_surrounding_whitespace( img )
                    width = 800
                    height = int((float(img.size[1])*width/float(img.size[0])))
                    img = img.resize( (width,height), PIL.Image.ANTIALIAS )
                    writeOnImage( img, caption )
                    img.save( outpath )
                except Exception as e:
                    print( e )
                    pass


if __name__ == '__main__':
    log( 'running' )
    main()
    log( 'Finished' )


