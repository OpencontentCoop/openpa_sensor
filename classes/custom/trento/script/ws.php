<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Test di Sensor Webservice Trento\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[id:][send_pendings][list_pendings]',
    '',
    array(
        'id' => 'Post id',
        'list_pendings' => 'List pending items',
        'send_pendings' => 'Send pending items'
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
    if ( is_numeric( $id ) )
    {
        $helper = SensorHelper::instanceFromContentObjectId( $id );
        $wsPost = new TrentoWsSensorPost( $helper->currentSensorPost, isset( $options['verbose'] ) );    
        if ( $options['debug'] )
        {
            print_r( $wsPost->getData() );
        }
        else
        {
            $wsPost->send();
        }
    }
    elseif ( $options['list_pendings'] )
    {
        $cli->output( "List pending items" );
        $entries = TrentoWsSensorPost::listPendingItems();
        print_r( $entries );
    }
    elseif ( $options['send_pendings'] )
    {        
        $cli->output( "Send pending items" );
        TrentoWsSensorPost::sendPendingItems( isset( $options['verbose'] ) );
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
