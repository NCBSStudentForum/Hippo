__author__           = "Dilawar Singh"
__copyright__        = "Copyright 2017-, Dilawar Singh"
__version__          = "1.0.0"
__maintainer__       = "Dilawar Singh"
__email__            = "dilawars@ncbs.res.in"
__status__           = "Development"

from wsgiref.simple_server import make_server 
from pyramid.config import Configurator 
from pyramid.response import Response


def hippo(request):
    return Response('Hello %(name)s!' % request.matchdict)

if __name__ == '__main__':
    with Configurator() as config:
        config.add_route('hippo', '/{name}')
        config.add_view(hippo, route_name='hippo')
        app = config.make_wsgi_app()
    server = make_server('0.0.0.0', 8081, app)
    server.serve_forever()
