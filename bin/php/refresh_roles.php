#!/usr/bin/env php
<?php

require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Refresh sensor role policies" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$roles = OpenPASensorInstaller::roleDefinitions();

foreach($roles as $roleName => $policies){
    $role = eZRole::fetchByName( $roleName );
    if ($role instanceof eZRole){
        $cli->warning("Refresh $roleName policies");
        $role->removePolicies();
        foreach( $policies as $policy )
        {
            $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], isset( $policy['Limitation'] ) ? $policy['Limitation'] : array() );
        }
    }
}


$script->shutdown();
