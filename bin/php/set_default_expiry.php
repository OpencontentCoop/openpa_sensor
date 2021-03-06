<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Sensor Set default expiry for all ticket\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

try
{
    /** @var eZCollaborationItem[] $items */
    $items = eZPersistentObject::fetchObjectList(
        eZCollaborationItem::definition(),
        null,
        array( 'type_identifier' => SensorHelper::factory()->getSensorCollaborationHandlerTypeString() )
    );

    foreach( $items as $item )
    {
        if ( $item->attribute( SensorPost::COLLABORATION_FIELD_EXPIRY ) == '' )
        {
            $helper = SensorHelper::instanceFromCollaborationItem( $item );
            $helper->currentSensorPost->setExpiry(
                OpenPAINI::variable( 'SensorConfig', 'DefaultPostExpirationDaysInterval', 15 )
            );
        }
    }


    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}