<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$nodeId = $Params['NodeID'];
$node = eZContentObjectTreeNode::fetch( $nodeId );

if ( $node instanceof eZContentObjectTreeNode )
{
    $module->redirectTo( $node->attribute( 'url_alias' ) );
}
else
{
    $node = ObjectHandlerServiceControlSensor::surveyContainerNode();
    $module->redirectTo( $node->attribute( 'url_alias' ) );
}