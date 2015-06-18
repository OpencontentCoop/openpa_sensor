<?php

class OpenPASensorOperator
{
    function operatorList()
    {
        return array(
            'sensor_root_handler',
            'is_current_sensor_page',
            'sensor_postcontainer',
            'sensor_categorycontainer',
            'user_settings',
            'objectstate_by_id',
            'bracket_to_strong',
            'current_sensor_userinfo',
            'sensor_post'
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
                'context' => array( 'type' => 'string', 'required' => false, 'default' => null )
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
            case 'sensor_post':
            {
                if ( $operatorValue instanceof eZContentObject )
                {
                    try
                    {
                        $operatorValue = SensorHelper::instanceFromContentObjectId(
                            $operatorValue->attribute( 'id' )
                        );
                    }
                    catch( Exception $e )
                    {
                        eZDebug::writeError( $e->getMessage(), __METHOD__ );
                        $operatorValue = null;
                    }
                }
            } break;

            case 'current_sensor_userinfo':
            {
                $operatorValue = SensorUserInfo::current();;
            } break;

            case 'bracket_to_strong':
            {
                $operatorValue = ObjectHandlerServiceControlSensor::replaceBracket( $operatorValue );
            } break;

            case 'objectstate_by_id';
            {
                $id = $operatorValue;
                $state = eZContentObjectState::fetchById( $id );
                if ( $state instanceof eZContentObjectState )
                {
                    $operatorValue = $state;
                }
            } break;
            
            case 'user_settings':
            {
                $object = $operatorValue;
                $userId = false;
                $settings = false;
                if ( $object instanceof eZContentObject )
                {
                    $userId = $object->attribute( 'id' );
                }
                elseif ( $object instanceof eZContentObjectTreeNode )
                {
                    $userId = $object->attribute( 'contentobject_id' );
                }
                if ( $userId )
                {
                   $settings = eZUserSetting::fetch( $userId );
                }
                return $operatorValue = $settings;
            } break;
            
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
                return $operatorValue = ObjectHandlerServiceControlSensor::rootHandler( $namedParameters['context'] );
            } break;
                        
            case 'sensor_postcontainer':
                {
                    return $operatorValue = ObjectHandlerServiceControlSensor::postContainerNode();
                } break;
            
            case 'sensor_categorycontainer':
                {
                    return $operatorValue = ObjectHandlerServiceControlSensor::postCategoriesNode();
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
                    $name = $parts[1];
                    if ( is_numeric( $name ) )
                    {
                        $user = eZUser::fetch( $name );
                        if ( $user instanceof eZUser )
                        {                            
                            $tpl = eZTemplate::factory();                            
                            $tpl->setVariable( 'sensor_person', $user->attribute( 'contentobject' ) );
                            $name = $tpl->fetch( 'design:content/view/sensor_person.tpl' );
                        }
                    }
                    switch( $parts[0] )
                    {
                        case '_fixed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Completata da %name', false, array( '%name' => $name ) );
                            break;
                        
                        case '_read':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Letta da %name', false, array( '%name' => $name ) );
                            break;
                        
                        case '_closed':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Chiusa da %name', false, array( '%name' => $name ) );
                            break;
                        
                        case '_assigned':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Assegnata a %name', false, array( '%name' => $name ) );
                            break;
                        
                        case '_reopened':
                            $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Riaperta da %name', false, array( '%name' => $name ) );
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
            } break;
        }
    }
} 