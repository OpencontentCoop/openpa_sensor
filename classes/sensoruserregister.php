<?php

class SensorUserRegister
{
    const MODE_ONLY_CAPTCHA = 1;

    const MODE_MAIL_WITH_MODERATION = 2;

    const MODE_MAIL_BLOCK = 3;

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

    public static function getVerifyMode()
    {
        $mode = OpenPAINI::variable( 'SensorConfig', 'RegistrationMode', self::MODE_MAIL_WITH_MODERATION );
        if ( in_array( $mode, array( self::MODE_MAIL_WITH_MODERATION, self::MODE_MAIL_BLOCK, self::MODE_ONLY_CAPTCHA ) ) )
        {
            return $mode;
        }
        else
        {
            return self::MODE_MAIL_WITH_MODERATION;
        }
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
            $forgotUrl = 'user/forgotpassword';
            eZURI::transformURI( $forgotUrl );
            throw new InvalidArgumentException( ezpI18n::tr(
                'openpa_sensor/signup',
                'Email già in uso. Hai dimenticato la password? <a href="%forgot_url">Clicca qui</a>',
                null,
                array( '%forgot_url' => $forgotUrl )
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
                if ( $handler->hasAttribute( 'first_name' ) && $handler->hasAttribute( 'user_account' ) )
                {
                    $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->fromString( $this->name );
                    $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->store();

                    $user = eZUser::create( $objectID );
                    $login = $this->email;
                    eZDebugSetting::writeDebug( 'kernel-user', $this->password, "password" );
                    eZDebugSetting::writeDebug( 'kernel-user', $login, "login" );
                    eZDebugSetting::writeDebug( 'kernel-user', $this->email, "email" );
                    eZDebugSetting::writeDebug( 'kernel-user', $objectID, "contentObjectID" );

                    $user->setInformation( $objectID, $login, $this->email, $this->password, $this->password );
                    $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->setContent( $user );
                    $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->store();
                }
                else
                {
                    throw new Exception( eZError::KERNEL_ACCESS_DENIED );
                }
            }
            eZUserOperationCollection::setSettings( $objectID, self::getVerifyMode() !== self::MODE_MAIL_BLOCK, 0 );
            self::setSessionUser( $objectID );
        }
        $db->commit();
    }

    public static function captchaIsValid()
    {
        if ( self::getVerifyMode() == self::MODE_MAIL_BLOCK )
        {
            return true;
        }
        else
        {
            require_once 'extension/openpa_sensor/classes/recaptchalib.php';
            $http = eZHTTPTool::instance();
            $commentsIni = eZINI::instance( 'ezcomments.ini' );
            $privateKey = $commentsIni->variable( 'RecaptchaSetting', 'PrivateKey' );
            if ( $http->hasPostVariable( 'recaptcha_challenge_field' )
                 && $http->hasPostVariable( 'recaptcha_response_field' )
            )
            {
                $ip = $_SERVER["REMOTE_ADDR"];
                $challengeField = $http->postVariable( 'recaptcha_challenge_field' );
                $responseField = $http->postVariable( 'recaptcha_response_field' );
                $captchaResponse = recaptcha_check_answer(
                    $privateKey,
                    $ip,
                    $challengeField,
                    $responseField
                );
                return $captchaResponse->is_valid;
            }

        }
        return false;
    }

    public static function finish( eZModule $Module )
    {
        $object = self::getSessionUserObject();
        if ( $object instanceof eZContentObject )
        {
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $object->attribute( 'id' ), 'version' => 1 ) );

            eZUserOperationCollection::setSettings( $object->attribute( 'id' ), self::getVerifyMode() !== self::MODE_MAIL_BLOCK, 0 );

            if ( ( array_key_exists( 'status', $operationResult ) && $operationResult['status'] != eZModuleOperationInfo::STATUS_CONTINUE ) )
            {
                eZDebug::writeDebug( $operationResult, __FILE__ );
                throw new Exception( eZError::KERNEL_NOT_AVAILABLE );
            }
            else
            {
                self::removeSessionUser();
                
                /** @var eZUser $user */
                $user = eZUser::fetch( $object->attribute( 'id' ) );

                if ( !$user instanceof eZUser )
                {
                    throw new Exception( eZError::KERNEL_NOT_FOUND );
                }

                $userSetting = eZUserSetting::fetch( $object->attribute( 'id' ) );
                if ( $userSetting instanceof eZUserSetting )
                {
                    $hash = md5( mt_rand() . time() . $user->id() );
                    $accountKey = eZUserAccountKey::createNew( $user->id(), $hash, time() );
                    $accountKey->store();
                }
                else
                {
                    eZDebug::writeError( "UserSettings not found for user #" . $user->id(), __METHOD__ );
                    throw new Exception( eZError::KERNEL_NOT_FOUND );
                }

                if ( self::getVerifyMode() == self::MODE_MAIL_BLOCK )
                {
                    self::sendMail( $user, $hash );
                }
                elseif( self::getVerifyMode() == self::MODE_MAIL_WITH_MODERATION )
                {
                    $sensorUserInfo = SensorUserInfo::instance( $user );
                    $sensorUserInfo->setModerationMode( true );
                    self::sendMail( $user, $hash );
                    $user->loginCurrent();
                }
                elseif( self::getVerifyMode() == self::MODE_ONLY_CAPTCHA )
                {
                    self::sendMail( $user );
                    $user->loginCurrent();
                    $Module->redirectTo( '/sensor/home' );
                }

                $rule = eZCollaborationNotificationRule::create( OpenPASensorCollaborationHandler::TYPE_STRING, $user->id() );
                $rule->store();
            }
        }
        else
        {
            eZDebug::writeError( "Session user not found" );
            throw new Exception( eZError::KERNEL_NOT_FOUND );
        }
    }

    protected static function sendMail( eZUser $user, $hash = null )
    {
        $tpl = eZTemplate::factory();
        if ( $hash !== null )
        {
            $tpl->setVariable( 'hash', $hash );
        }
        $tpl->setVariable( 'user', $user );
        $body = $tpl->fetch( 'design:sensor/mail/registrationinfo.tpl' );

        $ini = eZINI::instance();
        $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
        if ( $tpl->hasVariable( 'email_sender' ) )
            $emailSender = $tpl->variable( 'email_sender' );
        else if ( !$emailSender )
            $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );

        if ( $tpl->hasVariable( 'subject' ) )
            $subject = $tpl->variable( 'subject' );
        else
            $subject = ezpI18n::tr( 'kernel/user/register', 'Informazioni di registrazione' );

        $tpl->setVariable( 'title', $subject );
        $tpl->setVariable( 'content', $body );
        $templateResult = $tpl->fetch( 'design:sensor/mail/mail_pagelayout.tpl' );

        $mail = new eZMail();
        $mail->setSender( $emailSender );
        $receiver = $user->attribute( 'email' );
        $mail->setReceiver( $receiver );
        $mail->setSubject( $subject );
        $mail->setBody( $templateResult );
        $mail->setContentType( 'text/html' );
        return eZMailTransport::send( $mail );
    }

}