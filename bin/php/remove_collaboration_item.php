<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Sensor Remove Collaboration Item \n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[id:]',
    '',
    array(
        'id' => 'Collaboration id to remove'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

    if ( isset( $options['id'] ) )
    {
        SensorHelper::deleteCollaborationStuff( $options['id'] );
        $cli->warning( "Removed {$options['id']}" );
    }


    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
