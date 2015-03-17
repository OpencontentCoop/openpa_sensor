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
$showCaptcha = false;

$tpl->setVariable( 'name', false );
$tpl->setVariable( 'email', false );

if ( $http->hasPostVariable( 'RegisterButton' ) )
{
    $sensorUser = new SensorUser();
    try
    {
        $sensorUser->setName( $http->postVariable( 'Name' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
    }
    try
    {
        $sensorUser->setEmail( $http->postVariable( 'EmailAddress' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
    }
    try
    {
        $sensorUser->setPassword( $http->postVariable( 'Password' ) );
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
        $invalidForm = true;
    }

    $tpl->setVariable( 'name', $sensorUser->getName() );
    $tpl->setVariable( 'email', $sensorUser->getEmail() );

    if ( !$invalidForm )
    {
        try
        {
            $sensorUser->store();
            $showCaptcha = !SensorUser::checkCaptcha();
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
elseif ( SensorUser::hasSessionUser() )
{
    try
    {
        $showCaptcha = !SensorUser::checkCaptcha();
        SensorUser::finish();
    }
    catch( InvalidArgumentException $e )
    {
        $errors[] = $e->getMessage();
    }
    catch( RuntimeException $e )
    {
        $Module->redirectTo( '/sensor/signup' );
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
else
{
    $Module->redirectTo( '/sensor/home' );
}

$tpl->setVariable( 'check_mail', SensorUser::getVerifyMode() !== SensorUser::MODE_ONLY_CAPTCHA );
$tpl->setVariable( 'sensor_signup', true );
$tpl->setVariable( 'show_captcha', $showCaptcha );
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