<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Sensor Disable all operators\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );
/** @var eZUser $user */
$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );


try
{
    $operators = ObjectHandlerServiceControlSensor::operators();
    foreach( $operators as $operator )
    {
        $cli->warning( $operator->attribute( 'name' ) . ' ', false );
        $user = eZUser::fetch( $operator->attribute( 'contentobject_id' ) );
        if ( $user instanceof eZUser )
        {
            $sensorUser = SensorUserInfo::instance( $user );
            $sensorUser->setBlockMode();
            $cli->warning( 'blocked' );
        }
        else
        {
            $cli->error( 'not found!' );
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