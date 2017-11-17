"""query_wiki.py:

    Query wikipedia about a term.

"""

__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

import nltk
try:
    from nltk.corpus import stopwords
except Exception as e:
    nltk.download( 'stopwords' )

from nltk.corpus import stopwords
import sys
import os
from wikiapi import WikiApi

wiki_ = WikiApi( )
common_ = set( stopwords.words( 'english' ) )

def main( ):
    query = sys.argv[1]
    print( 'Searching for %s' % query )
    if query in common_:
        print( 'Very common word' )
        return ''

    res = wiki_.find( query )
    if len(res) > 1:
        art = wiki_.get_article( res[0] )
        print(art.url)


if __name__ == '__main__':
    main()
