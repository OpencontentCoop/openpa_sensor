<?php

class SensorHelper
{
    protected $httpActions = array(
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
        'Message' => array(
            'http_parameters' => array(
                'OpenPASensorItemMessage'  => array(
                    'required' => true,
                    'action_parameter_name' => 'text'
                ),
                'OpenPASensorItemMessagePrivateReceiver' => array(
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
        )
    );

    /**
     * @var eZCollaborationItem
     */
    public $collaborationItem;

    /**
     * @var SensorPost
     */
    public $currentSensorPost;

    /**
     * @var array
     */
    public $sensorConfigParams;

    /**
     * @var SensorUserInfo
     */
    public $currentSensorUser;

    /**
     * @var SensorUserPostRoles
     */
    public $currentSensorUserRoles;

    protected function __construct( eZCollaborationItem $collaborationItem )
    {
        $this->sensorConfigParams = self::getSensorConfigParams();

        $this->collaborationItem = $collaborationItem;
        $this->currentSensorPost = SensorPost::instance(
            $this->collaborationItem,
            $this->sensorConfigParams
        );
        $this->currentSensorUser = SensorUserInfo::current();
        $this->currentSensorUserRoles = SensorUserPostRoles::instance(
            $this->currentSensorPost,
            $this->currentSensorUser
        );
    }

    public static function getSensorCollaborationHandlerTypeString()
    {
        return ezpEvent::getInstance()->filter( 'sensor/collaboration_type_string', 'openpasensor' );
    }

    public static function getSensorConfigParams()
    {
        $params = array();
        $params = ezpEvent::getInstance()->filter( 'sensor/config_param', $params );
        return $params;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return SensorHelper
     */
    public static function instanceFromCollaborationItem( eZCollaborationItem $collaborationItem )
    {
        return new SensorHelper( $collaborationItem );
    }

    /**
     * @param int $objectId
     *
     * @return SensorHelper
     * @throws Exception
     */
    public static function instanceFromContentObjectId( $objectId )
    {
        $type = SensorHelper::getSensorCollaborationHandlerTypeString();
        $collaborationItem = eZPersistentObject::fetchObject(
            eZCollaborationItem::definition(),
            null,
            array(
                'type_identifier' => $type,
                'data_int1' => intval( $objectId )
            ) );
        if ( $collaborationItem instanceof eZCollaborationItem )
        {
            return new SensorHelper( $collaborationItem );;
    }
        throw new Exception( "$type eZCollaborationItem not found for $objectId" );
    }

    public static function createSensorPost( SensorPostCreateStruct $struct )
    {
        $object = eZContentObject::fetch( $struct->contentObjectId );
        if ( !$object instanceof eZContentObject )
        {
            throw new Exception( "Object {$struct->contentObjectId} not found" );
        }

        $db = eZDB::instance();
        $res = (array) $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE data_int1 = " . $struct->contentObjectId );
        if ( count( $res ) > 0 )
        {
            $collaborationID = $res[0]['id'];
            $collaborationItem = eZCollaborationItem::fetch( $collaborationID );
            $post = SensorPost::instance( $collaborationItem, $struct->configParams );
            $post->restoreFormTrash();
            $post->eventHelper->handleEvent( 'on_restore' );
            return $post;
        }

        $struct = ezpEvent::getInstance()->filter( 'sensor/create_struct', $struct );

        $collaborationItem = eZCollaborationItem::create(
            SensorHelper::getSensorCollaborationHandlerTypeString(),
            $struct->authorUserId
        );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_OBJECT_ID, $struct->contentObjectId );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_HANDLER, 'SensorHelper' );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_STATUS, false );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_LAST_CHANGE, 0 );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_EXPIRY, self::expiryTimestamp( $collaborationItem->attribute( 'created' ) ) );
        $collaborationItem->store();

        $post = SensorPost::instance( $collaborationItem, $struct->configParams );

        $participantList = array(
            array(
                'id' => array( $struct->authorUserId ),
                'role' => SensorUserPostRoles::ROLE_AUTHOR
            ),
            array(
                'id' => $struct->approverUserIdArray,
                'role' => SensorUserPostRoles::ROLE_APPROVER
            )
        );
        foreach ( $participantList as $participantItem )
        {
            foreach( $participantItem['id'] as $participantID )
            {
                $participantRole = $participantItem['role'];
                $post->addParticipant( $participantID, $participantRole );
            }
        }

        $helper = self::instanceFromCollaborationItem( $collaborationItem );
        if ( $struct->privacy == 'private' )
        {
            $helper->currentSensorUserRoles->actionHandler->makePrivate();
        }

        if ( $struct->moderation !== null )
        {
            $helper->currentSensorUserRoles->actionHandler->moderate( $struct->moderation );
        }

        $post->setStatus( SensorPost::STATUS_WAITING );
        $post->eventHelper->handleEvent( 'on_create' );

        return $post;
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
                foreach( $parameters['http_parameters'] as $parameterName => $parameterOptions )
                {
                    $parameterPostVariable = 'Collaboration_' . $parameterName;
                    if ( $parameterOptions['required'] && $http->hasPostVariable( $parameterPostVariable ) )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $http->postVariable( $parameterPostVariable );
                    }
                    elseif ( isset( $parameterOptions['default'] ) )
                    {
                        $actionParameters[$parameterOptions['action_parameter_name']] = $http->postVariable( $parameterPostVariable );
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
                    $this->currentSensorUserRoles->handleAction( $actionName, $actionParameters );
                }
            }
        }
    }

    public function onRead()
    {
        $this->currentSensorUserRoles->handleAction( 'read' );
    }

    public static function roleName( $collaborationID, $roleID )
    {
        if ( $roleID < eZCollaborationItemParticipantLink::TYPE_CUSTOM )
        {
            if ( empty( $GLOBALS['SensorParticipantRoleNameMap'] ) )
            {

                $GLOBALS['SensorParticipantRoleNameMap'] =
                    array( eZCollaborationItemParticipantLink::ROLE_STANDARD => ezpI18n::tr( 'openpa_sensor/role_name', 'Standard' ),
                           eZCollaborationItemParticipantLink::ROLE_OBSERVER => ezpI18n::tr( 'openpa_sensor/role_name', 'Osservatore' ),
                           eZCollaborationItemParticipantLink::ROLE_OWNER => ezpI18n::tr( 'openpa_sensor/role_name', 'In carico a' ),
                           eZCollaborationItemParticipantLink::ROLE_APPROVER => ezpI18n::tr( 'openpa_sensor/role_name', 'Riferimento per il cittadino' ),
                           eZCollaborationItemParticipantLink::ROLE_AUTHOR => ezpI18n::tr( 'openpa_sensor/role_name', 'Autore' ) );
            }
            $roleNameMap = $GLOBALS['SensorParticipantRoleNameMap'];
            if ( isset( $roleNameMap[$roleID] ) )
            {
                return $roleNameMap[$roleID];
            }
            return null;
        }

        $item = eZCollaborationItem::fetch( $collaborationID );
        return $item->handler()->roleName( $collaborationID, $roleID );
    }
    
    private function participantRoleSortKey( $roleID )
    {
        $sorter = array(
            eZCollaborationItemParticipantLink::ROLE_STANDARD => 1000,
            eZCollaborationItemParticipantLink::ROLE_OBSERVER => 4,
            eZCollaborationItemParticipantLink::ROLE_OWNER => 3,
            eZCollaborationItemParticipantLink::ROLE_APPROVER => 2,
            eZCollaborationItemParticipantLink::ROLE_AUTHOR => 1
        );
        return isset( $sorter[$roleID] ) ? $sorter[$roleID] : 1000;
    }
    
    public function fetchParticipantMap()
    {        
        $itemID = $this->collaborationItem->attribute( 'id' );        
        $list = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $this->collaborationItem->attribute( 'id' ), 'limit' => 100  ) );
        if ( $list === null )
        {            
            return null;
        }
        $listMap = array();
        foreach ( $list as $listItem )
        {
            $sortKey = $this->participantRoleSortKey( $listItem->attribute( 'participant_role' ) );
            if ( !isset( $listMap[$sortKey] ) )
            {
                $sortName = self::roleName( $itemID, $listItem->attribute( 'participant_role' ) );
                $listMap[$sortKey] = array( 'name' => $sortName,
                                            'role_id' => $listItem->attribute( 'participant_role' ),
                                            'items' => array() );
            }
            $listMap[$sortKey]['items'][] = $listItem;
        }
        ksort( $listMap );
        return $listMap;
    }

    public static function instantiateExporter( $exportType, array $filters, eZCollaborationGroup $group, $selectedList )
    {
        //@todo
        if ( $exportType == 'csv' )
        {
            return new SensorPostCsvExporter( $filters, $group, $selectedList );
        }
        throw new Exception( "$export format not handled" );
    }
    
    public static function availableListTypes()
    {
        $listTypes = array(
            array(
                'identifier' => 'unread',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "Da leggere" ),
                'count_function' => array( 'SensorHelper', 'fetchUnreadItemsCount' ),
                'list_function' => array( 'SensorHelper', 'fetchUnreadItems' )
            ),
            array(
                'identifier' => 'active',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "In corso" ),
                'count_function' => array( 'SensorHelper', 'fetchActiveItemsCount' ),
                'list_function' => array( 'SensorHelper', 'fetchActiveItems' )
            ),
            array(
                'identifier' => 'unactive',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "Chiuse" ),
                'count_function' => array( 'SensorHelper', 'fetchUnactiveItemsCount' ),
                'list_function' => array( 'SensorHelper', 'fetchUnactiveItems' )
            )
        );
        return $listTypes;
    }

}
