<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Test di Sensor Webservice Trento\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[id:]',
    '',
    array(
        'id' => 'Post id'        
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

/** @var eZUser $user */
$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

try
{
    $id = $options['id'];
    $helper = SensorHelper::instanceFromContentObjectId( $id );
    $wsPost = new TrentoWsSensorPost( $helper->currentSensorPost, $options['verbose'] );    
    if ( $options['debug'] )
    {
        print_r( $wsPost->getData() );
    }
    else
    {
        $wsPost->send();
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}