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
    $sensorUserInfo = SensorUserInfo::instance( $user );
    if ( $http->hasPostVariable( "UpdateSettingButton" ) && $currentUser->attribute( 'login' ) !== $user->attribute( 'login' ) )
    {
        $sensorUserInfo->setBlockMode( $http->hasPostVariable( 'is_enabled' ) );
        $sensorUserInfo->setDenyCommentMode( $http->hasPostVariable( 'sensor_deny_comment' ) );
        $sensorUserInfo->setCanBehalfOfMode( $http->hasPostVariable( 'sensor_can_behalf_of' ) );
        $sensorUserInfo->setModerationMode( $http->hasPostVariable( 'moderate' ) );
    }
    
    if ( $http->hasPostVariable( "CancelSettingButton" ) )
    {
        $Module->redirectTo( 'sensor/config' );
        return;
    }
    
    $result = $user->hasAccessTo( 'sensor', 'behalf' );
    $canBehalfOf = $result['accessWord'] != 'no';
    
    $tpl->setVariable( 'user', $user );
    $tpl->setVariable( 'userID', $UserID );
    $tpl->setVariable( 'sensor_user_info', $sensorUserInfo );
    
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