<?php

$Module = $Params['Module'];
$UserID = $Params['ID'];
$user = eZUser::fetch( $UserID );
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$currentUser = eZUser::currentUser();

if ( !$user instanceof eZUser )
{
    $Module->redirectTo( 'sensor/config' );
    return;
}
else
{
    $userObject = $user->attribute( 'contentobject' );
    $userSetting = eZUserSetting::fetch( $UserID );
    $denyComment = eZPreferences::value( 'sensor_deny_comment', $user );
    
    if ( !$userObject )
    {
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
    
    if ( $http->hasPostVariable( "UpdateSettingButton" ) && $currentUser->attribute( 'login' ) !== $user->attribute( 'login' ) )
    {
        $isEnabled = 1;
        if ( $http->hasPostVariable( 'max_login' ) )
        {
            $maxLogin = $http->postVariable( 'max_login' );
        }
        else
        {
            $maxLogin = $userSetting->attribute( 'max_login' );
        }
        if ( $http->hasPostVariable( 'is_enabled' ) )
        {
            $isEnabled = 0;
        }
    
        if ( eZOperationHandler::operationIsAvailable( 'user_setsettings' ) )
        {
               $operationResult = eZOperationHandler::execute( 'user',
                                                               'setsettings', array( 'user_id'    => $UserID,
                                                                                     'is_enabled' => $isEnabled,
                                                                                     'max_login'  => $maxLogin ) );
        }
        else
        {
            eZUserOperationCollection::setSettings( $UserID, $isEnabled, $maxLogin );
        }
        
        if ( $http->hasPostVariable( 'sensor_deny_comment' ) )
        {
            eZPreferences::setValue( 'sensor_deny_comment', 1, $userObject->attribute( 'id' ) );
        }
        else
        {
            $db = eZDB::instance();
            $db->query( "DELETE FROM ezpreferences WHERE user_id = {$userObject->attribute( 'id' )} AND name = 'sensor_deny_comment'" );
        }
        
        $userSetting = eZUserSetting::fetch( $UserID );
        $denyComment = eZPreferences::value( 'sensor_deny_comment', $user );
        
        $role = eZRole::fetchByName( 'Sensor Assistant' );        
        if ( $http->hasPostVariable( 'sensor_can_behalf_of' ) )
        {
            if ( $role instanceof eZRole )
            {
                $role->assignToUser( $UserID );
            }
        }
        else
        {
            if ( $role instanceof eZRole )
            {
                $role->removeUserAssignment( $UserID );
            }
        }
        eZUser::purgeUserCacheByUserId( $UserID );
        
    }
    
    if ( $http->hasPostVariable( "CancelSettingButton" ) )
    {
        $Module->redirectTo( 'sensor/config' );
        return;
    }
    
    $result = $user->hasAccessTo( 'sensor', 'behalf' );
    $canBehalfOf = $result['accessWord'] != 'no';
    
    $tpl->setVariable( 'user_can_behalf_of', $canBehalfOf );
    $tpl->setVariable( 'user_deny_comment', $denyComment );
    $tpl->setVariable( 'user', $user );
    $tpl->setVariable( 'userID', $UserID );
    $tpl->setVariable( 'userObject', $userObject );
    $tpl->setVariable( 'userSetting', $userSetting );
    
    $tpl->setVariable( 'persistent_variable', array() );
    
    $Result = array();
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
    $Result['content'] = $tpl->fetch( 'design:sensor/user.tpl' );
    $Result['node_id'] = 0;
    
    $contentInfoArray = array( 'url_alias' => 'sensor/user' );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}