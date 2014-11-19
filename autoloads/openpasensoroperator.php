<?php

class OpenPASensorOperator
{
    function operatorList()
    {
        return array(
            'sensor_root_handler',
            'is_current_sensor_page',
            'sensor_robot_message'
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'sensor_root_handler' => array(
                'params' => array( 'type' => 'array', 'required' => false, 'default' => array() )
            ),
            'is_current_sensor_page' => array(
                'function' => array( 'type' => 'string', 'required' => true )
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'is_current_sensor_page':
            {
                $currentModuleParams = $GLOBALS['eZRequestedModuleParams'];
                $module = $currentModuleParams['module_name'];
                $function = $currentModuleParams['function_name'];
                $parameters = $currentModuleParams['parameters'];

                $operatorValue = ( $module == 'sensor' && $function == $namedParameters['function'] );
            } break;

            case 'sensor_root_handler':
            {
                $root = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() );
                $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
                return $operatorValue = $rootHandler->attribute( 'control_sensor' );
            } break;
            
            case 'sensor_robot_message':
            {
                $message = $operatorValue;
                $parts = explode( ' by ', $message );
                if ( !isset( $parts[1] ) )
                {
                    $parts = explode( ' to ', $message );
                }
                if ( isset( $parts[1] ) )
                {
                    switch( $parts[0] )
                    {
                        case '_fixed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Completata da %name', false, array( '%name' => $parts[1] ) );
                            break;
                        
                        case '_read':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Letta da %name', false, array( '%name' => $parts[1] ) );
                            break;
                        
                        case '_closed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Chiusa da %name', false, array( '%name' => $parts[1] ) );
                            break;
                        
                        case '_assigned':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Assegnata a %name', false, array( '%name' => $parts[1] ) );
                            break;
                        
                        case '_reopened':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Riaperta da %name', false, array( '%name' => $parts[1] ) );
                            break;
                    }
                }
                else
                {
                    switch( $parts[0] )
                    {
                        case '_fixed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Completata' );
                            break;
                        
                        case '_read':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Letta' );
                            break;
                        
                        case '_closed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Chiusa' );
                            break;
                        
                        case '_assigned':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Assegnata' );
                            break;
                        
                        case '_reopened':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Riaperta' );
                            break;
                    }
                }
                $operatorValue = $result;
            }
        }
    }
} 