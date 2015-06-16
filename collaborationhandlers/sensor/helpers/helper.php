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

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param SensorUserInfo $user
     *
     * @throws Exception
     */
    protected function __construct( eZCollaborationItem $collaborationItem, SensorUserInfo $user = null )
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
        if ( $user === null )
        {
            $user = SensorUserInfo::current();
        }
        $this->currentSensorUser = $user;
        $this->currentSensorUserRoles = SensorUserPostRoles::instance(
            $this->currentSensorPost,
            $this->currentSensorUser
        );
        $this->httpActionHelper = SensorHttpActionHelper::instance( $this->currentSensorUserRoles );
    }

    /**
     * @return SensorHelperFactoryInterface
     */
    public static function factory()
    {
        //@todo move in ini
        return new OpenpaSensorHelperFactory();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param SensorUserInfo $user
     *
     * @return SensorHelper
     */
    public static function instanceFromCollaborationItem( eZCollaborationItem $collaborationItem,
                                                          SensorUserInfo $user = null )
    {
        return new SensorHelper( $collaborationItem, $user );
    }

    /**
     * @param int $objectId
     * @param SensorUserInfo $user
     *
     * @return SensorHelper
     * @throws Exception
     */
    public static function instanceFromContentObjectId( $objectId, SensorUserInfo $user = null )
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
            return new SensorHelper( $collaborationItem, $user );
    }
        throw new Exception( "$type eZCollaborationItem not found for $objectId" );
    }

    /**
     * @param eZContentObject $object
     *
     * @return SensorPost
     * @throws Exception
     */
    public static function createSensorPost( eZContentObject $object )
    {
        if ( !$object instanceof eZContentObject )
        {
            throw new Exception( "Object not found" );
        }

        $objectHelper = self::factory()->getSensorPostObjectHelper( $object  );

        $struct = new SensorPostCreateStruct();
        $struct->contentObjectId = $object->attribute( 'id');
        $struct->authorUserId = $objectHelper->getPostAuthorId();
        $authorInfo = SensorUserInfo::instance( eZUser::fetch( $struct->authorUserId ) );
        $approverIDArray = $objectHelper->getApproverIdArray();
        if ( empty( $approverIDArray ) )
        {
            $admin = eZUser::fetchByName( 'admin' );
            if ( $admin instanceof eZUser )
            {
                $approverIDArray[] = $admin->attribute( 'contentobject_id' );
                eZDebug::writeNotice(
                    "Add admin user as fallback empty participant list",
                    __METHOD__
                );
            }
        }
        $struct->approverUserIdArray = $approverIDArray;
        $struct->configParams = SensorHelper::factory()->getSensorConfigParams();

        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $object->attribute( 'data_map' );
        if ( isset( $dataMap['privacy'] ) &&  $dataMap['privacy']->attribute( 'data_int' ) == 0 )
        {
            $struct->privacy = 'private';
        }

        $struct->moderation = $objectHelper->defaultModerationStateIdentifier( $authorInfo );

        $db = eZDB::instance();
        $res = (array) $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE data_int1 = " . $struct->contentObjectId );
        if ( count( $res ) > 0 )
        {
            $collaborationID = $res[0]['id'];
            $collaborationItem = eZCollaborationItem::fetch( $collaborationID );
            $helper = self::instanceFromCollaborationItem( $collaborationItem );
            $post = $helper->currentSensorPost;
            $post->restoreFormTrash();
            $post->eventHelper->createEvent( 'on_restore' );
            return $post;
        }

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
                $struct->configParams['DefaultPostExpirationDaysInterval']
            )
         );
        $collaborationItem->store();
        $helper = self::instanceFromCollaborationItem( $collaborationItem, $authorInfo );
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
        $post->eventHelper->createEvent( 'on_create' );

        return $post;
    }

    /**
     * @param eZContentObject $object
     *
     * @return SensorPost
     * @throws Exception
     */
    public static function updateSensorPost( eZContentObject $object )
    {
        if ( !$object instanceof eZContentObject )
        {
            throw new Exception( "Object not found" );
        }
        $helper = self::instanceFromContentObjectId( $object->attribute( 'id' ) );
        $helper->collaborationItem->setAttribute( 'modified', $object->attribute( 'modified' ) );
        $helper->collaborationItem->sync();
        $post = $helper->currentSensorPost;
        $post->eventHelper->createEvent( 'on_update' );
        return $post;
    }

    /**
     * @param eZContentObject $object
     * @param bool $moveInTrash
     *
     * @throws Exception
     */
    public static function removeSensorPost( eZContentObject $object, $moveInTrash )
    {
        $helper = self::instanceFromContentObjectId( $object->attribute( 'id' ) );
        $post = $helper->currentSensorPost;
        $post->eventHelper->createEvent( 'on_remove' );
        if ( $moveInTrash )
            $post->moveToTrash();
        else
            $post->delete();
    }
    /**
     * @param eZModule $module
     */
    public function handleHttpAction( eZModule $module )
    {
        $this->httpActionHelper->handleHttpAction( $module );
    }

    public function onRead()
    {
        $this->currentSensorUserRoles->handleAction( 'read' );
    }

    /**
     * @param string $exportType
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param array $selectedList
     *
     * @return SensorPostCsvExporter
     * @throws Exception
     */
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
                'id',
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
                'last_timeline',

                //SensorPost message*Handler
                'comment_count',
                'comment_unread_count',
                'comment_items',
                'message_count',
                'message_unread_count',
                'message_items',
                'response_count',
                'response_unread_count',
                'response_items',
                'timeline_count',
                'timeline_unread_count',
                'timeline_items',
                'human_count',
                'human_unread_count',

                //SensorPost objectHandler
                'type',
                'current_object_state',
                'current_privacy_state',
                'current_moderation_state',
                'areas',
                'categories',
                'operators',
                'post_geo_array_js',
                'post_url'
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
            case 'id':
                return $this->currentSensorPost->objectHelper->getContentObject()->attribute( 'id' );
                break;

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

            case 'last_timeline':
                return $this->currentSensorPost->getLastTimelineMessage();
                break;


            case 'comment_count':
                return $this->currentSensorPost->commentHelper->count();
                break;

            case 'comment_unread_count':
                return $this->currentSensorPost->commentHelper->unreadCount();
                break;

            case 'comment_items':
                return $this->currentSensorPost->commentHelper->items();
                break;

            case 'message_count':
                return $this->currentSensorPost->messageHelper->count();
                break;

            case 'message_unread_count':
                return $this->currentSensorPost->messageHelper->unreadCount();
                break;

            case 'message_items':
                return $this->currentSensorPost->messageHelper->items();
                break;

            case 'response_count':
                return $this->currentSensorPost->responseHelper->count();
                break;

            case 'response_unread_count':
                return $this->currentSensorPost->responseHelper->unreadCount();
                break;

            case 'response_items':
                return $this->currentSensorPost->responseHelper->items();
                break;

            case 'timeline_count':
                return $this->currentSensorPost->timelineHelper->count();
                break;

            case 'timeline_unread_count':
                return $this->currentSensorPost->timelineHelper->unreadCount();
                break;

            case 'timeline_items':
                return $this->currentSensorPost->timelineHelper->items();
                break;

            case 'human_count':
                return $this->currentSensorPost->commentHelper->count()
                       + $this->currentSensorPost->messageHelper->count()
                       + $this->currentSensorPost->responseHelper->count();
                break;

            case 'human_unread_count':
                return $this->currentSensorPost->commentHelper->unreadCount()
                    + $this->currentSensorPost->messageHelper->unreadCount()
                    + $this->currentSensorPost->responseHelper->unreadCount();
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

            case 'post_url':
                return $this->currentSensorPost->objectHelper->getPostUrl();
                break;

        }

        eZDebug::writeError( "Attribute $key not found", get_called_class() );
        return false;
    }

}
