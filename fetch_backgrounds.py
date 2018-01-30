#!/usr/bin/env python

import os
import re
import mimetypes
import urllib
import random
import json
import requests
import io
import PIL.Image
import PIL.ImageChops
from PIL import ImageDraw
from PIL import ImageFont
import numpy as np
import lxml.html

os.environ[ 'http_proxy' ] = 'http://proxy.ncbs.res.in:3128'
os.environ[ 'https_proxy' ] = 'http://proxy.ncbs.res.in:3128'

base_url_ = 'https://intranet.ncbs.res.in/photography'
json_url_ = 'https://www.dropbox.com/sh/juqqlthnu74qzp8/AAD43iy7JenaMDu7-8c_B9MOa/output.json?dl=1'

# check if JSON url is fine. It yes, use it else go to intranet.
useJSON_ = False

try:
    r = requests.get( json_url_ )
    if r.status_code == 200:
        useJSON_ = True
        print( '[INFO] Using JSON' )
except Exception as e:
    useJSON_ = False
    pass


background_dir_ = './data/_backgrounds'
if not os.path.exists( background_dir_ ):
    os.makedirs( background_dir_ )

def log( msg ):
    print( msg )
    return
    with open( '/tmp/a.txt', 'a' ) as f:
        f.write( msg + '\n' )

def is_url_image(url):
    mimetype,encoding = mimetypes.guess_type(url)
    return (mimetype and mimetype.startswith('image'))

def is_image_and_ready(url):
    return is_url_image(url)

def writeOnImage( img, caption, copyright = '(c) NCBS Photography Club' ):
    draw = ImageDraw.Draw(img)
    # font = ImageFont.truetype(<font-file>, <font-size>)
    font = ImageFont.truetype("./data/OpenSans-Regular.ttf", 12 )
    fontCaption = ImageFont.truetype("./data/OpenSans-Regular.ttf", 20 )
    # get mean color of box.
    nI = np.asarray( img )
    color = np.mean( nI[10:300,15:100, :] )
    print( 'mean color %s' % color )
    if color > 125:
        writeColor = (0,0,0)
    else:
        writeColor = (255,255,255)
    draw.text((10, 15) , caption[0:80], writeColor, font=fontCaption)
    draw.text((10, 50) , copyright, writeColor, font=font)
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

def download_url( url, caption, copyright = '(c) NCBS Photography Club' ):
    # Download URL.
    outfile = os.path.basename( url )
    outpath = os.path.join( background_dir_, outfile + '.jpg' )
    print( '[INFO] Downloading %s -> %s' % (url, outpath) )
    if not os.path.exists( outpath ):
        try:
            r = requests.get( url )
            img = io.BytesIO( r.content )
            img = PIL.Image.open( img )
            img = crop_surrounding_whitespace( img )
            width = 800
            height = int((float(img.size[1])*width/float(img.size[0])))
            img = img.resize( (width,height), PIL.Image.ANTIALIAS )
            writeOnImage( img, caption, copyright )
            img.save( outpath )
        except Exception as e:
            print( e )
            pass
    else:
        print( 'File already downloaded' )



def get_images_from_intranet( ):
    global base_url_
    html = None
    try:
        r = requests.get( base_url_ )
        html = r.text
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
            download_url( url, caption )

def get_images_from_dropbox( ):
    global useJSON_
    log( 'Fetching from dropbox' )
    data = None
    try:
        r = requests.get( json_url_ )
        data =  r.json( )
    except Exception as e:
        log( 'Failed to read JSON' )
        print( e )

    for k in data:
        img = data[k]
        author = img['author']
        url = img[ 'photo_url']
        caption = img[ 'title' ]
        if is_image_and_ready( url ):
            download_url( url, caption, '(c) %s' % author )

def main( ):
    global useJSON_
    if useJSON_:
        log( 'using json' )
        get_images_from_dropbox( )
    else:
        log( 'using intranet' )
        get_images_from_intranet( )

if __name__ == '__main__':
    log( 'running' )
    main()
    log( 'Finished' )
