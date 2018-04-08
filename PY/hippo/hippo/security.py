import bcrypt

def hash_password(pw):
    pwhash = bcrypt.hashpw(pw.encode('utf8'), bcrypt.gensalt())
    return pwhash.decode('utf8')

def check_password(pw, hashed_pw):
    expected_hash = hashed_pw.encode('utf8')
    return bcrypt.checkpw(pw.encode('utf8'), expected_hash)


USERS = {'editor': hash_password('editor'), 'viewer': hash_password('viewer')}
GROUPS = {'editor': ['group:editors']}

def groupfinder(userid, request):
    if userid in USERS:
        return GROUPS.get(userid, [])




def authenticate( login, password ):
    import hippo_ldap
    auth = hippo_ldap.authenticate_using_ldap( login, password )
    if not auth:
        import hippo_imap
        auth = hippo_imap.authenticate_using_imap( login, password ) 
    return auth
