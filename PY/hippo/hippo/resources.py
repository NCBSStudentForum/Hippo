from pyramid.security import Allow, Everyone

class Root( object ):

    __acl__ = [ (Allow, Everyone, 'view' )
            , (Allow, 'group:users', 'view' )
            , (Allow, 'group:deansoffice', 'edit' )
            , (Allow, 'group:acadoffice', 'edit' )
            ]

    def __init__( self, request ):
        pass
