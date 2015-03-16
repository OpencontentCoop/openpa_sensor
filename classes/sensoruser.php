<?php

class SensorUser
{
    protected $name;

    protected $email;

    protected $password;

    public static function getSessionUserObject()
    {
        $object = eZContentObject::fetch( eZHTTPTool::instance()->sessionVariable( "RegisterUserID" ) );
        return ( $object instanceof eZContentObject ) ? $object : null;
    }

    public static function hasSessionUser()
    {
        return eZHTTPTool::instance()->hasSessionVariable( "RegisterUserID" );
    }

    public static function setSessionUser( $userID )
    {
        eZHTTPTool::instance()->setSessionVariable( "RegisterUserID", $userID );
    }

    public static function removeSessionUser()
    {
        eZHTTPTool::instance()->removeSessionVariable( 'RegisterUserID' );
    }

    public function __construct()
    {
    }

    public function setName( $name )
    {
        $name = trim( $name );
        if ( empty( $name ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEmail( $email )
    {
        $email = trim( $email );
        if ( empty( $email ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        if ( !eZMail::validate( $email ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Indirizzo email non valido'
            ) );
        }
        if ( eZUser::fetchByEmail( $email ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Email già in uso'
            ) );
        }
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword( $password )
    {
        if ( empty( $password ) )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Inserire tutti i dati richiesti'
            ) );
        }
        if ( !eZUser::validatePassword( $password ) )
        {
            $minPasswordLength = eZINI::instance()->variable( 'UserSettings', 'MinPasswordLength' );
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'La password deve essere lunga almeno %1 caratteri',
                null,
                array( $minPasswordLength )
            ) );
        }
        if ( strtolower( $password ) == 'password' )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'La password non può essere "password".'
            ) );
        }
    }

    public function store()
    {
        $db = eZDB::instance();
        $ini = eZINI::instance();
        $db->begin();
        $defaultUserPlacement = (int)$ini->variable( "UserSettings", "DefaultUserPlacement" );
        $sql = "SELECT count(*) as count FROM ezcontentobject_tree WHERE node_id = $defaultUserPlacement";
        $rows = $db->arrayQuery( $sql );
        $count = $rows[0]['count'];
        if ( $count < 1 )
        {
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Il nodo (%1) specificato in [UserSettings].DefaultUserPlacement setting in site.ini non esiste!',
                null,
                array( $defaultUserPlacement )
            ) );
        }
        else
        {
            $userClassID = $ini->variable( "UserSettings", "UserClassID" );
            $class = eZContentClass::fetch( $userClassID );
            $userCreatorID = $ini->variable( "UserSettings", "UserCreatorID" );
            $defaultSectionID = $ini->variable( "UserSettings", "DefaultSectionID" );
            $contentObject = $class->instantiate( $userCreatorID, $defaultSectionID );
            $objectID = $contentObject->attribute( 'id' );

            $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $objectID,
                                                               'contentobject_version' => 1,
                                                               'parent_node' => $defaultUserPlacement,
                                                               'is_main' => 1 ) );
            $nodeAssignment->store();

            $handler = OpenPAObjectHandler::instanceFromContentObject( $contentObject );
            if ( $handler->hasAttribute( 'control_sensor' ) )
            {
                $service = $handler->service( 'control_sensor' );
                if ( $handler->hasAttribute( 'first_name' ) && $handler->hasAttribute( 'user_account' ) )
                {
                    $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->fromString( $name );
                    $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->store();

                    $user = eZUser::create( $objectID );
                    $login = $email;
                    eZDebugSetting::writeDebug( 'kernel-user', $password, "password" );
                    eZDebugSetting::writeDebug( 'kernel-user', $login, "login" );
                    eZDebugSetting::writeDebug( 'kernel-user', $email, "email" );
                    eZDebugSetting::writeDebug( 'kernel-user', $objectID, "contentObjectID" );

                    $user->setInformation( $objectID, $login, $email, $password, $password );
                    $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->setContent( $user );
                    $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->store();
                }
                else
                {
                    throw new Exception( eZError::KERNEL_ACCESS_DENIED );
                }
            }
            eZUserOperationCollection::setSettings( $objectID, 0, 0 ); //@todo
            self::setSessionUser( $objectID );
        }
        $db->commit();
    }

    public function 

}