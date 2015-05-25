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
    protected $collaborationItem;

    /**
     * @var SensorPost
     */
    protected $currentSensorPost;

    /**
     * @var array
     */
    protected $sensorConfigParams;

    /**
     * @var SensorUserInfo
     */
    protected $currentSensorUser;

    /**
     * @var SensorUserPostRoles
     */
    protected $currentSensorUserRoles;

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
        $string = 'openpasensor';
        return ezpEvent::getInstance()->filter( 'sensor/collaboration_type_string', $string );
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
        return new SensorHelper( $collaborationItem );;
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

    public static function getDateDiff( $start, $end = null )
    {
        if ( !( $start instanceof DateTime ) )
        {
            $start = new DateTime( $start );
        }

        if ( $end === null )
        {
            $end = new DateTime();
        }

        if ( !( $end instanceof DateTime ) )
        {
            $end = new DateTime( $start );
        }

        $interval = $end->diff( $start );
        $translate = function ( $nb, $str )
        {
            $string = $nb > 1 ? $str . 's' : $str;
            switch ( $string )
            {
                case 'year';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'anno' );
                    break;
                case 'years';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'anni' );
                    break;
                case 'month';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'mese' );
                    break;
                case 'months';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'mesi' );
                    break;
                case 'day';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'giorno' );
                    break;
                case 'days';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'giorni' );
                    break;
                case 'hour';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'ora' );
                    break;
                case 'hours';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'ore' );
                    break;
                case 'minute';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'minuto' );
                    break;
                case 'minutes';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'minuti' );
                    break;
                case 'second';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'secondo' );
                    break;
                case 'seconds';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'secondi' );
                    break;
            }
            return $string;
        };

        $format = array();
        if ( $interval->y !== 0 )
        {
            $format[] = "%y " . $translate( $interval->y, "year" );
        }
        if ( $interval->m !== 0 )
        {
            $format[] = "%m " . $translate( $interval->m, "month" );
        }
        if ( $interval->d !== 0 )
        {
            $format[] = "%d " . $translate( $interval->d, "day" );
        }
        if ( $interval->h !== 0 )
        {
            $format[] = "%h " . $translate( $interval->h, "hour" );
        }
        if ( $interval->i !== 0 )
        {
            $format[] = "%i " . $translate( $interval->i, "minute" );
        }
        if ( $interval->s !== 0 )
        {
            if ( !count( $format ) )
            {
                return ezpI18n::tr( 'openpa_sensor/expiring', 'meno di un minuto' );
            }
            else
            {
                $format[] = "%s " . $translate( $interval->s, "second" );
            }
        }

        // We use the two biggest parts
        if ( count( $format ) > 1 )
        {
            $format = array_shift( $format ) . " " . ezpI18n::tr( 'openpa_sensor/expiring', 'e' ) . " " . array_shift( $format );
        }
        else
        {
            $format = array_pop( $format );
        }

        return array( 'interval' => $interval, 'format' => $format );
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

    public function decorateUserName( $userId )
    {
        $user = eZUser::fetch( $userId );
        if ( $user instanceof eZUser )
        {
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'sensor_person', $user->attribute( 'contentobject' ) );
            return $tpl->fetch( 'design:content/view/sensor_person.tpl' );
        }
        return $userId;
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
    
    public function delete()
    {
        $itemId = $this->collaborationItem->attribute( 'id' );
        self::deleteCollaborationStuff( $itemId );
    }
    
    public static function deleteCollaborationStuff( $itemId )
    {        
        $db = eZDB::instance();
        $db->begin();
        $db->query( "DELETE FROM ezcollab_item WHERE id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );    
        $res = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        foreach( $res as $r )
        {
            $db->query( "DELETE FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $db->query( "DELETE FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_status WHERE collaboration_id = $itemId" );                        
        $db->commit();
    }
    
    public static function getCollaborationStuff( $itemId )
    {                
        $db = eZDB::instance();        
        $res['ezcollab_item'] = $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE id = $itemId" );
        $res['ezcollab_item_group_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );    
        $tmp = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_simple_message'] = array();
        foreach( $tmp as $r )
        {
            $res['ezcollab_simple_message'][] = $db->arrayQuery( "SELECT * FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $res['ezcollab_item_message_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_participant_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_status'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_status WHERE collaboration_id = $itemId" );        
        return $res;
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
