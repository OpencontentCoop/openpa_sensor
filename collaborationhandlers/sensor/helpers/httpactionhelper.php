<?php

class SensorHttpActionHelper
{
    protected $httpActions = array();
    /**
     * @var SensorUserPostRoles
     */
    protected $postUserRoles;

    protected function __construct( SensorUserPostRoles $postUserRoles )
    {
        $this->postUserRoles = $postUserRoles;

        $this->httpActions = array(
            'Assign' => array(
                'http_parameters' => array(
                    'OpenPASensorItemAssignTo' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'assign',
            ),
            'Fix' => array(
                'action_name' => 'fix'
            ),
            'Close' => array(
                'action_name' => 'close'
            ),
            'MakePrivate' => array(
                'action_name' => 'make_private'
            ),
            'MakePublic' => array(
                'action_name' => 'make_public'
            ),
            'Moderate' => array(
                'http_parameters' => array(
                    'OpenPASensorItemModerationIdentifier' => array(
                        'required' => false,
                        'default' => 'accepted',
                        'action_parameter_name' => 'status'
                    )
                ),
                'action_name' => 'moderate',
            ),
            'AddObserver' => array(
                'http_parameters' => array(
                    'OpenPASensorItemAddObserver' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'add_observer',
            ),
            'AddCategory' => array(
                'http_parameters' => array(
                    'OpenPASensorItemCategory' => array(
                        'required' => true,
                        'action_parameter_name' => 'category_id'
                    ),
                    'OpenPASensorItemAssignToCategoryApprover' => array(
                        'required' => false,
                        'default' => false,
                        'action_parameter_name' => 'assign_to_approver'
                    )
                ),
                'action_name' => 'add_category',
            ),
            'AddArea' => array(
                'http_parameters' => array(
                    'OpenPASensorItemArea'  => array(
                        'required' => true,
                        'action_parameter_name' => 'area_id'
                    )
                ),
                'action_name' => 'add_area',
            ),
            'SetExpiry' => array(
                'http_parameters' => array(
                    'OpenPASensorItemExpiry'  => array(
                        'required' => true,
                        'action_parameter_name' => 'expiry_days'
                    )
                ),
                'action_name' => 'set_expiry',
            ),
            'Comment' => array(
                'http_parameters' => array(
                    'OpenPASensorItemComment'  => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    )
                ),
                'action_name' => 'add_comment',
            ),
            'PrivateMessage' => array(
                'http_parameters' => array(
                    'OpenPASensorItemPrivateMessage'  => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    ),
                    'OpenPASensorItemPrivateMessageReceiver' => array(
                        'required' => true,
                        'action_parameter_name' => 'participant_ids'
                    )
                ),
                'action_name' => 'add_message',
            ),
            'Respond' => array(
                'http_parameters' => array(
                    'OpenPASensorItemResponse' => array(
                        'required' => true,
                        'action_parameter_name' => 'text'
                    )
                ),
                'action_name' => 'add_response',
            ),
            'EditComment' => array(
                'http_parameters' => array(
                    'OpenPASensorEditComment' => array(
                        'required' => true,
                        'action_parameter_name' => 'id_text'
                    )
                ),
                'action_name' => 'edit_comment',
            ),
            'EditMessage' => array(
                'http_parameters' => array(
                    'OpenPASensorEditMessage' => array(
                        'required' => true,
                        'action_parameter_name' => 'id_text'
                    )
                ),
                'action_name' => 'edit_message',
            )
        );
    }

    final public static function instance( SensorUserPostRoles $postUserRoles )
    {
        //@todo customize handler
        return new SensorHttpActionHelper( $postUserRoles );
    }

    public function handleHttpAction()
    {
        $http = eZHTTPTool::instance();
        foreach( $this->httpActions as $action => $parameters )
        {
            $actionPostVariable = 'CollaborationAction_' . $action;
            if ( $http->hasPostVariable( $actionPostVariable ) )
            {
                $actionName = $parameters['action_name'];
                $actionParameters = array();
                $doAction = true;
                if ( !isset( $parameters['http_parameters'] ) )
                {
                    $parameters['http_parameters'] = array();
                }
                foreach( $parameters['http_parameters'] as $parameterName => $parameterOptions )
                {
                    $parameterPostVariable = 'Collaboration_' . $parameterName;
                    if ( $parameterOptions['required'] && $http->hasPostVariable( $parameterPostVariable ) )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $http->postVariable( $parameterPostVariable );
                    }
                    elseif ( isset( $parameterOptions['default'] ) )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $parameterOptions['default'];
                    }
                    else
                    {
                        $doAction = false;
                        eZDebug::writeError( "Parameter $parameterName is required", $actionName );
                        break;
                    }
                }
                if ( $doAction )
                {
                    eZDebugSetting::writeNotice( 'sensor', "Http call $actionName action with arguments " . var_export( $actionParameters, 1 ), __METHOD__ );
                    $this->postUserRoles->handleAction( $actionName, $actionParameters );
                }
            }
        }
    }

}