<?php
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$ini = eZINI::instance();
$http = eZHTTPTool::instance();
$test = 'post';

$siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $siteUrl );
        if ( count( $parts ) >= 2 )
        {
            $suffix = array_shift( $parts );
            $siteUrl = implode( '/', $siteUrl );
        }
        echo rtrim( $siteUrl, '/' );

if ( $test == 'notification' )
{
    $objectId = 172824;
    $contentObject = eZContentObject::fetch($objectId);
    $tpl->setVariable( 'object', $contentObject );
    $result = $tpl->fetch( 'design:notification/handler/ezsubtree/view/plain.tpl' );
    echo $result;
    eZDisplayDebug();
    eZExecution::cleanExit();
}
elseif ( $test == 'registration' )
{
    $user = eZUser::fetch( 14 );
    if ( $user === null )
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    
    $tpl->setVariable( 'user', $user );
    $body = $tpl->fetch( 'design:sensor/mail/registrationinfo.tpl' );
    
    $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
    if ( $tpl->hasVariable( 'email_sender' ) )
        $emailSender = $tpl->variable( 'email_sender' );
    else if ( !$emailSender )
        $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );
    
    if ( $tpl->hasVariable( 'subject' ) )
        $subject = $tpl->variable( 'subject' );
    else
        $subject = ezpI18n::tr( 'kernel/user/register', 'Registration info' );
    
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
}
elseif ( $test == 'post' )
{
    $objectId = 2199;
    $participantRole = eZCollaborationItemParticipantLink::ROLE_AUTHOR;
    
    $helper = SensorHelper::instanceFromContentObjectId( $objectId );
    $item = $helper->attribute( 'collaboration_item' );
    //$item->createNotificationEvent();    
    $object = eZContentObject::fetch( $item->attribute( "data_int1" ) );
    if ( !$object instanceof eZContentObject )
    {
        throw new Exception( 'object not found' );
    }
    $post = OpenPAObjectHandler::instanceFromContentObject( $object )->attribute( 'control_sensor' );
    
    $itemHandler = $item->attribute( 'handler' );
    
    $templateName = $itemHandler->notificationParticipantTemplate( $participantRole );
    $templatePath = 'design:sensor/mail/' . $templateName;

    $tpl->setVariable( 'collaboration_item', $item );
    $tpl->setVariable( 'collaboration_participant_role', $participantRole );
    $tpl->setVariable( 'collaboration_item_status', $item->attribute( SensorHelper::ITEM_STATUS ) );
    $tpl->setVariable( 'post', $post );
    $tpl->setVariable( 'object', $object );
    $tpl->setVariable( 'node', $object->attribute( 'main_node' ) );

    $result = $tpl->fetch( $templatePath );

    $body = $tpl->variable( 'body' );
    $subject = $tpl->variable( 'subject' );

        $tpl->setVariable( 'title', $subject );
        $tpl->setVariable( 'content', $body );
        $templateResult = $tpl->fetch( 'design:sensor/mail/mail_pagelayout.tpl' ); 
    
}

if ( $http->hasGetVariable( 'send' ) )
{
    //$mailResult = eZMailTransport::send( $mail );
    print_r($mailResult);
}
else
{
    echo $templateResult;
}


eZDisplayDebug();
eZExecution::cleanExit();
