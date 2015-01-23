<?php
/** @var eZModule $module */
$module = $Params['Module'];
$node = ObjectHandlerServiceControlSensor::forumContainerNode();
$module->redirectTo( $node->attribute( 'url_alias' ) );
