# global variables.

session_ = { }

def set_global( key, val ):
    session_[key] = val


def get_global( key ):
    return session_.get( key, None )
