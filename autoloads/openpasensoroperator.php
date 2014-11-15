<?php

class OpenPASensorOperator
{
    function operatorList()
    {
        return array(
            'sensor_root_handler',
            'is_current_sensor_page'
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
            }
        }
    }
} 