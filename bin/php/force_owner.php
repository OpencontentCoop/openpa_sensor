<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Sensor Force Owner for a ticket\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[id:][user_id:]',
    '',
    array(
        'id' => 'Object id',
        'user_id' => 'User id'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

try
{
    if ( isset( $options['id'] ) )
    {
        $helper = SensorHelper::instanceFromContentObjectId( $options['id'] );
        if ( isset( $options['user_id'] ) )
        {
            $newOwnerId = $options['user_id'];
            $helper->forceAssignTo( $newOwnerId );
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