# global variables.


session_ = dict( 
        calendar_url = "https://calendar.google.com/calendar/embed?src=d2jud2r7bsj0i820k0f6j702qo%40group.calendar.google.com&ctz=Asia/Calcutta"
        , AUTHENTICATED = False 
        )

def set( key, val ):
    session_[key] = val

def get( key ):
    return session_.get( key, None )

def keys( ):
    return session_.keys()
