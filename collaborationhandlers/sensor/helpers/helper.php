<?php

//post_geo_array_js

class SensorHelper
{
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

    /**
     * @var SensorHttpActionHelper;
     */
    public $httpActionHelper;

    protected function __construct( eZCollaborationItem $collaborationItem )
    {
        $contentObject = eZContentObject::fetch( $collaborationItem->attribute( 'data_int1' ) );
        if ( !$contentObject instanceof eZContentObject )
        {
            throw new Exception( "Object {$collaborationItem->attribute( 'data_int1' )} not found" );
        }

        $this->sensorConfigParams = self::factory()->getSensorConfigParams();

        $this->collaborationItem = $collaborationItem;
        $this->currentSensorPost = SensorPost::instance(
            $this->collaborationItem,
            self::factory()->getSensorPostObjectHelper( $contentObject ),
            $this->sensorConfigParams
        );
        $this->currentSensorUser = SensorUserInfo::current();
        $this->currentSensorUserRoles = SensorUserPostRoles::instance(
            $this->currentSensorPost,
            $this->currentSensorUser
        );
        $this->httpActionHelper = SensorHttpActionHelper::instance( $this->currentSensorUserRoles );
    }

    public static function factory()
    {
        //@todo move in ini
        return new OpenpaSensorHelperFactory();
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
        $type = self::factory()->getSensorCollaborationHandlerTypeString();
        $collaborationItem = eZPersistentObject::fetchObject(
            eZCollaborationItem::definition(),
            null,
            array(
                'type_identifier' => $type,
                'data_int1' => intval( $objectId )
            ) );
        if ( $collaborationItem instanceof eZCollaborationItem )
        {
            return new SensorHelper( $collaborationItem );
    }
        throw new Exception( "$type eZCollaborationItem not found for $objectId" );
    }

    /**
     * @param SensorPostCreateStruct $struct
     *
     * @return SensorPost
     * @throws Exception
     */
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
            $helper = self::instanceFromCollaborationItem( $collaborationItem );
            $post = $helper->currentSensorPost;
            $post->restoreFormTrash();
            $post->eventHelper->handleEvent( 'on_restore' );
            return $post;
        }

        $struct = ezpEvent::getInstance()->filter( 'sensor/create_struct', $struct );

        $collaborationItem = eZCollaborationItem::create(
            self::factory()->getSensorCollaborationHandlerTypeString(),
            $struct->authorUserId
        );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_OBJECT_ID, $struct->contentObjectId );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_HANDLER, 'SensorHelper' );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_STATUS, false );
        $collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_LAST_CHANGE, 0 );
        $collaborationItem->setAttribute(
            SensorPost::COLLABORATION_FIELD_EXPIRY,
            SensorPost::expiryTimestamp(
                $collaborationItem->attribute( 'created' ),
                $struct->configParameters['DefaultPostExpirationDaysInterval']
            )
         );
        $collaborationItem->store();

        $helper = self::instanceFromCollaborationItem( $collaborationItem );
        $post = $helper->currentSensorPost;

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
        $this->httpActionHelper->handleHttpAction();
    }

    public function onRead()
    {
        $this->currentSensorUserRoles->handleAction( 'read' );
    }

    public static function instantiateExporter( $exportType, array $filters, eZCollaborationGroup $group, $selectedList )
    {
        //@todo
        if ( $exportType == 'csv' )
        {
            return new SensorPostCsvExporter( $filters, $group, $selectedList );
        }
        throw new Exception( "$exportType format not handled" );
    }
    
    public static function availableListTypes()
    {
        $listTypes = array(
            array(
                'identifier' => 'unread',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "Da leggere" ),
                'count_function' => array( 'SensorPostFetcher', 'fetchUnreadItemsCount' ),
                'list_function' => array( 'SensorPostFetcher', 'fetchUnreadItems' )
            ),
            array(
                'identifier' => 'active',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "In corso" ),
                'count_function' => array( 'SensorPostFetcher', 'fetchActiveItemsCount' ),
                'list_function' => array( 'SensorPostFetcher', 'fetchActiveItems' )
            ),
            array(
                'identifier' => 'unactive',
                'name' => ezpI18n::tr( 'openpa_sensor/dashboard', "Chiuse" ),
                'count_function' => array( 'SensorPostFetcher', 'fetchUnactiveItemsCount' ),
                'list_function' => array( 'SensorPostFetcher', 'fetchUnactiveItems' )
            )
        );
        return $listTypes;
    }

    public function attributes()
    {
        return array_merge(

            $this->currentSensorUserRoles->attributes(),

            array(

                //SensorPost
                'collaboration_item',
                'object',
                'current_status',
                'current_owner',
                'current_participant',
                'participants',
                'has_owner',
                'owner_id',
                'owner_ids',
                'owner_name',
                'owner_names',
                'expiring_date',
                'expiration_days',
                'resolution_time',

                //SensorPost message*Handler
                'comment_count',
                'comment_items',
                'message_count',
                'message_items',
                'response_count',
                'response_items',
                'timeline_count',
                'timeline_items',

                //SensorPost objectHandler
                'type',
                'current_object_state',
                'current_privacy_state',
                'current_moderation_state',
                'areas',
                'categories',
                'operators',
                'post_geo_array_js'
            )
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        if ( $this->currentSensorUserRoles->hasAttribute( $key ) )
        {
            return $this->currentSensorUserRoles->attribute( $key );
        }

        switch( $key )
        {
            case 'collaboration_item':
                return $this->currentSensorPost->getCollaborationItem();
                break;

            case 'object':
                return $this->currentSensorPost->objectHelper->getContentObject();
                break;

            case 'current_status':
                return $this->currentSensorPost->getCurrentStatus();
                break;

            case 'current_owner':
                return $this->currentSensorPost->getMainOwnerText();
                break;

            case 'current_participant':
                return $this->currentSensorPost->getCurrentParticipant();
                break;

            case 'participants':
                return $this->currentSensorPost->getParticipants( null, true );
                break;

            case 'has_owner':
                return $this->currentSensorPost->hasOwner();
                break;

            case 'owner_id':
                return $this->currentSensorPost->getMainOwner();
                break;

            case 'owner_ids':
                return $this->currentSensorPost->getOwners();
                break;

            case 'owner_name':
                return $this->currentSensorPost->getMainOwnerName();
                break;

            case 'owner_names':
                return $this->currentSensorPost->getOwnerNames();
                break;

            case 'expiring_date':
                return $this->currentSensorPost->getExpiringDate();
                break;

            case 'expiration_days':
                return $this->currentSensorPost->getExpirationDays();
                break;

            case 'resolution_time':
                return $this->currentSensorPost->getResolutionTime();
                break;


            case 'comment_count':
                return $this->currentSensorPost->commentHelper->count();
                break;

            case 'comment_items':
                return $this->currentSensorPost->commentHelper->items();
                break;

            case 'message_count':
                return $this->currentSensorPost->messageHelper->count();
                break;

            case 'message_items':
                return $this->currentSensorPost->messageHelper->items();
                break;

            case 'response_count':
                return $this->currentSensorPost->responseHelper->count();
                break;

            case 'response_items':
                return $this->currentSensorPost->responseHelper->items();
                break;

            case 'timeline_count':
                return $this->currentSensorPost->timelineHelper->count();
                break;

            case 'timeline_items':
                return $this->currentSensorPost->timelineHelper->items();
                break;


            case 'type':
                return $this->currentSensorPost->objectHelper->getType();
                break;

            case 'current_object_state':
                return $this->currentSensorPost->objectHelper->getCurrentState();
                break;

            case 'current_privacy_state':
                return $this->currentSensorPost->objectHelper->getCurrentPrivacyState();
                break;

            case 'current_moderation_state':
                return $this->currentSensorPost->objectHelper->getCurrentModerationState();
                break;

            case 'areas':
                return $this->currentSensorPost->objectHelper->getPostAreas();
                break;

            case 'categories':
                return $this->currentSensorPost->objectHelper->getPostCategories();
                break;

            case 'operators':
                return $this->currentSensorPost->objectHelper->getOperators();
                break;

            case 'post_geo_array_js':
                return $this->currentSensorPost->objectHelper->getPostGeoJsArray();
                break;
        }

        eZDebug::writeError( "Attribute $key not found", get_called_class() );
        return false;
    }

}
