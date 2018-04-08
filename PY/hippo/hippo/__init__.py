from pyramid.authentication import AuthTktAuthenticationPolicy
from pyramid.authorization import ACLAuthorizationPolicy
from pyramid.config import Configurator

from .security import groupfinder


def main(global_config, **settings):
    config = Configurator(settings=settings)
    config.include('pyramid_jinja2')
    config.add_static_view('static', 'static', cache_max_age=3600)

    # security policies
    authn_policy = AuthTktAuthenticationPolicy(
            settings['hippo.secret'], callback=groupfinder,
            hashalg='sha512'
            )
    authz_policy = ACLAuthorizationPolicy()
    config.set_authentication_policy(authn_policy)
    config.set_authorization_policy(authz_policy)


    config.add_route('home', '/')
    config.add_route('login', '/login')
    config.add_route('logout', '/logout')
    config.add_route('events', '/events')
    config.add_route('AWSs', '/AWSs')
    config.add_route('aws', '/AWSs')
    config.add_route('talks', '/talks')
    config.add_route('jcs', '/JCs')
    config.add_route('JCs', '/JCs')
    config.add_route('statistics', '/statistics')
    config.add_route('courses', '/courses')
    config.add_route('map', '/map')
    config.add_route('docs', '/docs')
    config.scan()
    return config.make_wsgi_app()
