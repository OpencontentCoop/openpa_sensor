<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$ini = eZINI::instance();
$db = eZDB::instance();
$Result = array();

$currentUser = eZUser::currentUser();

$invalidForm = false;
$errors = array();
$captchaIsValid = false;

$tpl->setVariable( 'name', false );
$tpl->setVariable( 'email', false );

if ( $http->hasPostVariable( 'RegisterButton' ) )
{
    $sensorUserRegister = new SensorUserRegister();
    try
    {
        $sensorUserRegister->setName( $http->postVariable( 'Name' ) );
        $tpl->setVariable( 'name', $http->postVariable( 'Name' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
        $tpl->setVariable( 'name', $sensorUserRegister->getName() );
    }
    try
    {
        $sensorUserRegister->setEmail( $http->postVariable( 'EmailAddress' ) );
        $tpl->setVariable( 'email', $sensorUserRegister->getEmail() );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
        $tpl->setVariable( 'email', $http->postVariable( 'EmailAddress' ) );
    }
    try
    {
        $sensorUserRegister->setPassword( $http->postVariable( 'Password' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
    }

    if ( !$invalidForm )
    {
        try
        {
            $sensorUserRegister->store();
            $captchaIsValid = SensorUserRegister::captchaIsValid();
        }
        catch ( InvalidArgumentException $e )
        {
            $errors[] = $e->getMessage();
            $invalidForm = true;
        }
        catch ( Exception $e )
        {
            return $Module->handleError(
                intval( $e->getMessage() ),
                false,
                array(),
                array( 'SensorErrorCode', 1 )
            );
        }
    }
}
elseif ( SensorUserRegister::hasSessionUser() )
{
    $captchaIsValid = SensorUserRegister::captchaIsValid();
    if ( $captchaIsValid )
    {
        try
        {
            SensorUserRegister::finish( $Module );
        }
        catch ( Exception $e )
        {
            return $Module->handleError(
                intval( $e->getMessage() ),
                false,
                array(),
                array( 'SensorErrorCode', 1 )
            );
        }
    }
}
else
{
    $Module->redirectTo( '/sensor/home' );
}

$tpl->setVariable( 'verify_mode',  SensorUserRegister::getVerifyMode() );
$tpl->setVariable( 'check_mail', ( SensorUserRegister::getVerifyMode() !== SensorUserRegister::MODE_ONLY_CAPTCHA ) );
$tpl->setVariable( 'sensor_signup', true );
$tpl->setVariable( 'show_captcha', !$captchaIsValid );
$tpl->setVariable( 'invalid_form', $invalidForm );
$tpl->setVariable( 'errors', $errors );
$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
$Result['content'] = $tpl->fetch( 'design:sensor/register.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/signup' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();