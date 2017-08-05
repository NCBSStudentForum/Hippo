#NCBS Hippo

AWS scheduler cum even manager for NCBS. 

# Dependencies 

- Requires PHP >= 5.6 
- php5, php5-imap, php5-ldap, php5-imagick
- mysql 
- python-pypandoc, pandoc (>=1.12) or python-html2text
- sudo pip install mysql-connector-python-rf
- pandoc >= 1.19.2.1

# Apache behind proxy

To communicate to google-calendar, apache needs to know proxy server. Write
following in `httpd.conf` file

    SetEnv HTTP_PROXY 172.16.223.223:3128
    SetEnv HTTPS_PROXY 172.16.223.223:3128

