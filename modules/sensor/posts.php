<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$postId = $Params['ID'];
$Offset = $Params['Offset'];
if ( !is_numeric( $Offset ) )
    $Offset = 0;


if ( !is_numeric( $postId ) )
{
    $node = ObjectHandlerServiceControlSensor::postContainerNode();
    $module->redirectTo( $node->attribute( 'url_alias' ) );
}
else
{
    try
    {
        $object = eZContentObject::fetch( $postId );
        //SensorHelper::createCollaborationItem($postId);die();
        if ( !$object instanceof eZContentObject )
        {
            return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }

        if ( !$object->attribute( 'can_read' ) )
        {
            return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
        }

        /** @var ObjectHandlerServiceControlSensor $sensor */
        $sensor = OpenPAObjectHandler::instanceFromContentObject( $object )->attribute( 'control_sensor' );

        /** @var SensorHelper $helper */
        $helper = $sensor->attribute( 'collaboration_item' );

        /** @var eZCollaborationItem $collaborationItem */
        $collaborationItem = $helper->attribute( 'collaboration_item' );

        /** @var OpenPASensorCollaborationHandler $collaborationHandler */
        $collaborationHandler = $collaborationItem->handler();

        $collaborationItem->handleView( 'full' );

        $viewParameters = array( 'offset' => $Offset );

        $tpl->setVariable( 'view_parameters', $viewParameters );
        $tpl->setVariable( 'post', $sensor );
        $tpl->setVariable( 'helper', $helper );
        $tpl->setVariable( 'object', $object );
        $tpl->setVariable( 'collaboration_item', $collaborationItem );

        $currentParticipant = eZFunctionHandler::execute(
            "collaboration",
            "participant",
            array(  "item_id" => $collaborationItem->attribute( 'id' ) )
        );

        $participantList = eZFunctionHandler::execute(
            "collaboration",
            "participant_map",
            array( "item_id" => $collaborationItem->attribute( 'id' ) )
        );

        $tpl->setVariable( 'current_participant', $currentParticipant );
        $tpl->setVariable( 'participant_list', $participantList );

        $tpl->setVariable( 'sensor_post', true );
        $tpl->setVariable( 'post_geo_array_js', $sensor->attribute( 'geo_js_array' ) );

        $collaborationHandler->readItem( $collaborationItem );
    }
    catch( Exception $e )
    {
        $tpl->setVariable( 'error', $e->getMessage() );
    }

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
    $Result['content'] = $tpl->fetch( 'design:sensor/post.tpl' );
    $Result['node_id'] = 0;

    $contentInfoArray = array( 'url_alias' => 'sensor/post/' . $postId );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();

}
