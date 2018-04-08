from pyramid.view import view_config
from pyramid.httpexceptions import HTTPFound
from pyramid.security import (remember, forget,)
from pyramid.view import (view_config, view_defaults) 
from .security import (authenticate, USERS, check_password)

@view_config(route_name='home', renderer='templates/home.jinja2')
def my_view(request):
    return {'project': 'Hippo'}

@view_config(route_name='events', renderer='templates/events.jinja2')
def events(request):
    print( 'rendering events' )
    return { }

@view_config(route_name='AWSs', renderer='templates/aws.jinja2')
def AWSs(request):
    return { 'project' : 'Hippo' }


# login logout view.
@view_config(route_name='login', renderer='templates/login.jinja2')
def login(request):
    login_url = request.route_url('login')
    referrer = request.url
    if referrer == login_url:
        referrer = '/'  # never use login form itself as came_from
    came_from = request.params.get('came_from', referrer)
    message = ''
    login = ''
    password = ''
    if 'form.submitted' in request.params:
        login = request.params['login']
        password = request.params['password']
        if authenticate( login, password ):
            headers = remember(request, login
            return HTTPFound(location=came_from, headers=headers)
        message = 'Failed login'

    return dict(
        name='Login',
        message=message,
        url=request.application_url + '/login',
        came_from=came_from,
        login=login,
        password=password,
    )

@view_config(route_name='logout')
def logout(request):
    headers = forget(request)
    url = request.route_url('home')
    return HTTPFound(location=url, headers=headers)

