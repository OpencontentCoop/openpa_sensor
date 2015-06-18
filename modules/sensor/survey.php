<?php
/** @var eZModule $module */
$module = $Params['Module'];
$nodeId = $Params['NodeID'];
if ( !is_numeric( $nodeId ) )
{
    $node = ObjectHandlerServiceControlSensor::surveyContainerNode();
    if ( $node->attribute( 'children_count' ) > 1 )
        //$module->redirectTo( $node->attribute( 'url_alias' ) );
    {
        $nodeId = $node->attribute( 'node_id' );
    }
    else
    {
        /** @var eZContentObjectTreeNode[] $children */
        $children = $node->attribute( 'children' );
        //$module->redirectTo( $children[0]->attribute( 'url_alias' ) . '#partecipa' );
        $nodeId = $children[0]->attribute( 'node_id' );
    }
}
$contentModule = eZModule::exists( 'content' );
return $contentModule->run(
    'view',
    array( 'full', $nodeId )
);
