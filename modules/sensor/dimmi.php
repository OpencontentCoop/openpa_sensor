<?php
/** @var eZModule $module */
$module = $Params['Module'];
$node = ObjectHandlerServiceControlSensor::forumContainerNode();
if ( $node->attribute( 'children_count' ) > 1 )
    $module->redirectTo( $node->attribute( 'url_alias' ) );
else
{
    $children = $node->attribute( 'children' );
    $module->redirectTo( $children[0]->attribute( 'url_alias' ) );
}
