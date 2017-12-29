from django.shortcuts import render
from django.http import HttpRequest, HttpResponse

# Create your views here.
def index( req = None ):
    return HttpResponse( "<p>Hellow human</p>" )
