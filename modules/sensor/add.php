<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$node = ObjectHandlerServiceControlSensor::postContainerNode();
$class = ObjectHandlerServiceControlSensor::postContentClass();

eZSys::addAccessPath( array( 'layout', 'set', 'sensor_add' ), 'layout', false );

if ( $node instanceof eZContentObjectTreeNode && $class instanceof eZContentClass && $node->canCreate() )
{
    $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
    $object = eZContentObject::createWithNodeAssignment( $node,
        $class->attribute( 'id' ),
        $languageCode,
        false );
    if ( $object )
    {
        $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' ) . $queryString );
        return;
    }
    else
        return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );