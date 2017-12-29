all : deploy 


deploy : build 
	firefox http://127.0.0.1:8000/
	python3 ./manage.py runserver 


build : 
	python3 ./manage.py migrate
