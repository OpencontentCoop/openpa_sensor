<?php

class SensorHelper
{
    const ANONYMOUS_CAN_COMMENT = false;

    const ITEM_STATUS = 'data_int3';
    
    const ITEM_LAST_CHANGE = 'data_int2';

    const MESSAGE_TYPE_ROBOT = 0;

    const MESSAGE_TYPE_PUBLIC = 1;
    
    const MESSAGE_TYPE_RESPONSE = 2;

    const STATUS_WAITING = 0;

    const STATUS_READ = 1;

    const STATUS_ASSIGNED = 2;

    const STATUS_CLOSED = 3;

    const STATUS_FIXED = 4;

    const STATUS_REOPENED = 6;

    /**
     * @var eZCollaborationItem
     */
    protected $collaborationItem;

    protected function __construct( eZCollaborationItem $collaborationItem )
    {
        $this->collaborationItem = $collaborationItem;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     *
     * @return SensorHelper
     */
    public static function instanceFromCollaborationItem( eZCollaborationItem $collaborationItem )
    {
        return new static( $collaborationItem );
    }

    /**
     * @param int $objectId
     *
     * @return SensorHelper
     * @throws Exception
     */
    public static function instanceFromContentObjectId( $objectId )
    {
        $type = OpenPASensorCollaborationHandler::TYPE_STRING;
        $collaborationItem = eZPersistentObject::fetchObject(
            eZCollaborationItem::definition(),
            null,
            array(
                'type_identifier' => $type,
                'data_int1' => intval( $objectId )
            ) );
        if ( $collaborationItem instanceof eZCollaborationItem )
        {
            return new static( $collaborationItem );
        }
        throw new Exception( "$type eZCollaborationItem not found for $objectId" );
    }

    public function attributes()
    {
        return array(
            'collaboration_item',
            'can_do_something',
            'current_status',
            'current_owner',
            'can_respond',
            'can_comment',
            'can_assign',
            'can_close',
            'can_fix',
            'can_add_observer',
            'can_send_private_message',
            'can_add_category',
            'can_add_area',
            'can_change_privacy',
            'participants',
            'has_owner',
            'owner_id',
            'owner_name',
            'sensor',
            'object',
            'public_message_count',
            'response_message_count',
            'human_unread_message_count',
            'human_message_count',
            'human_messages',
            'robot_unread_message_count',
            'robot_message_count',
            'robot_messages'
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        switch( $key )
        {

            case 'collaboration_item':
                return $this->collaborationItem;
                break;

            case 'current_status':
                return $this->collaborationItem->attribute( self::ITEM_STATUS );
                break;

            case 'can_do_something':
                return ( $this->canAssign()
                       || $this->canAddObserver()
                       || $this->canClose()
                       || $this->canFix()
                       || $this->attribute( 'can_add_category' )
                       || $this->attribute( 'can_add_area' ) );
                break;

            case 'can_add_category':
                return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
                break;
            
            case 'can_add_area':
                return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
                break;
            
            case 'can_assign':
                return $this->canAssign();
                break;
            
            case 'can_respond':
                return $this->canRespond();
                break;
            
            case 'can_comment':
                return $this->canComment();
                break;

            case 'can_change_privacy':
                return $this->canChangePrivacy();
                break;
            
            case 'can_close':
                return $this->canClose();
                break;

            case 'can_fix':
                return $this->canFix();
                break;

            case 'can_send_private_message':
                return $this->canSendPrivateMessage();
                break;

            case 'can_add_observer':
                return $this->canAddObserver();
                break;

            case 'participants':
                $ids = $this->participantIds();
                $users = array();
                foreach( $ids as $id )
                {
                    $obj = eZContentObject::fetch( $id );
                    if ( $obj instanceof eZContentObject ) $users[] = $obj;
                }
                return $users;
                break;
            
            case 'has_owner':
                {
                    $ids = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OWNER );
                    return count( $ids ) > 0;
                }
            
            case 'owner_id':
                $ids = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OWNER );
                if ( count( $ids ) )
                {
                    return array_shift( $ids );
                }
                //elseif ( !$this->is( self::STATUS_WAITING ) )
                //{
                //    $ids = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_APPROVER );
                //    if ( count( $ids ) )
                //    {
                //        return array_shift( $ids );
                //    }
                //}
                return null;
                break;

            case 'current_owner':
                $objectId = $this->attribute( 'owner_id' );
                if ( $objectId !== null )
                {
                    $object = eZContentObject::fetch( $objectId );
                    if ( $object instanceof eZContentObject )
                    {
                        $tpl = eZTemplate::factory();
                        $tpl->setVariable( 'sensor_person', $object );
                        return $tpl->fetch( 'design:content/view/sensor_person.tpl' );
                    }
                }
                return false;
                break;
            
            case 'owner_name':
                $id = $this->attribute( 'owner_id' );                
                if ( $id )
                {
                    $object = eZContentObject::fetch( $id );
                    if ( $object )
                    {
                        return $object->attribute( 'name' );
                    }
                }
                break;
            
            case 'sensor':
                {
                    return $this->getControlSensor();
                } break;
                
            case 'object':
                {
                    return $this->getContentObject();
                } break;

            case 'human_message_count':
                break;

            case 'public_message_count':
                {
                    return eZCollaborationItemMessageLink::fetchItemCount(
                        array(
                            'item_id' => $this->collaborationItem->attribute( 'id' ),
                            'conditions' => array(
                                'message_type' => SensorHelper::MESSAGE_TYPE_PUBLIC
                            )
                        )
                    );
                } break;

            case 'response_message_count':
            {
                return eZCollaborationItemMessageLink::fetchItemCount(
                    array(
                        'item_id' => $this->collaborationItem->attribute( 'id' ),
                        'conditions' => array(
                            'message_type' => SensorHelper::MESSAGE_TYPE_RESPONSE
                        )
                    )
                );
            } break;
            
            case 'human_messages':
            {
                return eZPersistentObject::fetchObjectList(
                    eZCollaborationItemMessageLink::definition(),
                    null,
                    array(
                        'collaboration_id' => $this->collaborationItem->attribute( 'id' ),
                        'message_type' => array( '!=', SensorHelper::MESSAGE_TYPE_ROBOT )
                    ),
                    array( 'created' => 'asc' ),
                    null,
                    true );
            } break;
            
            case 'human_unread_message_count':
                {
                    $lastRead = 0;
                    /** @var eZCollaborationItemStatus $status */
                    $status = $this->collaborationItem->attribute( 'user_status' );
                    if ( $status )
                    {
                        $lastRead = $status->attribute( 'last_read' );
                    }
                    return eZCollaborationItemMessageLink::fetchItemCount(
                        array(
                            'item_id' => $this->collaborationItem->attribute( 'id' ),
                            'conditions' => array(
                                'message_type' => array( array( SensorHelper::MESSAGE_TYPE_PUBLIC, SensorHelper::MESSAGE_TYPE_RESPONSE, eZUser::currentUserID() ) ),
                                'modified' => array( '>', $lastRead )
                            )
                        )
                    );
                } break;
            
            case 'robot_message_count':
                {
                    return eZCollaborationItemMessageLink::fetchItemCount(
                        array(
                            'item_id' => $this->collaborationItem->attribute( 'id' ),
                            'conditions' => array(
                                'message_type' => SensorHelper::MESSAGE_TYPE_ROBOT
                            )
                        )
                    );
                } break;

            case 'robot_messages':
            {
                return eZPersistentObject::fetchObjectList(
                    eZCollaborationItemMessageLink::definition(),
                    null,
                    array(
                        'collaboration_id' => $this->collaborationItem->attribute( 'id' ),
                        'message_type' => SensorHelper::MESSAGE_TYPE_ROBOT
                    ),
                    array( 'created' => 'asc' ),
                    null,
                    true );
            } break;
            
            case 'robot_unread_message_count':
                {
                    $lastRead = 0;
                    /** @var eZCollaborationItemStatus $status */
                    $status = $this->collaborationItem->attribute( 'user_status' );
                    if ( $status )
                    {
                        $lastRead = $status->attribute( 'last_read' );
                    }
                    return eZCollaborationItemMessageLink::fetchItemCount(
                        array(
                            'item_id' => $this->collaborationItem->attribute( 'id' ),
                            'conditions' => array(
                                'message_type' => SensorHelper::MESSAGE_TYPE_ROBOT,
                                'modified' => array( '>', $lastRead )
                            )
                        )
                    );
                } break;

            default:
                eZDebug::writeError( "Attribute $key not found", get_called_class() );
                return false;
        }
    }
    
    public function getContentObject()
    {
        $objectId = $this->collaborationItem->attribute( "data_int1" );
        $object = eZContentObject::fetch( $objectId );
        if ( $object instanceof eZContentObject )
        {
            return $object;
        }
        return null;
    }

    public function getControlSensor()
    {        
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            return OpenPAObjectHandler::instanceFromContentObject( $object )->attribute( 'control_sensor' );
        }
        return null;
    }

    /**
     * @param $contentObjectID
     * @return eZCollaborationItem
     * @throws Exception
     */
    public static function createCollaborationItem( $contentObjectID )
    {
        $db = eZDB::instance();
        $collaborationItem = false;
        $res = $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE data_int1 = $contentObjectID" );
        if ( count( $res ) > 0 )
        {
            $collaborationID = $res[0]['id'];
            $collaborationItem = eZCollaborationItem::fetch( $collaborationID );
            $helper = self::instanceFromCollaborationItem( $collaborationItem );
            $helper->restoreFormTrash();
        }
        
        if ( !$collaborationItem instanceof eZCollaborationItem )
        {            
            $object = eZContentObject::fetch( $contentObjectID );
            if ( !$object instanceof eZContentObject )
            {
                throw new Exception( "Object $contentObjectID not found" );
            }
            $sensor = OpenPAObjectHandler::instanceFromContentObject( $object )->attribute( 'control_sensor' );
            $authorID = $sensor->attribute( 'author_id' );
            $approverIDArray = $sensor->attribute( 'approver_id_array' );
            if ( empty( $approverIDArray ) )
            {
                $admin = eZUser::fetchByName( 'admin' );
                if ( $admin instanceof eZUser )
                {
                    $approverIDArray[] = $admin->attribute( 'contentobject_id' );
                    eZDebug::writeNotice( "Add admin user as fallback empty participant list", __METHOD__ );
                }
            }
            $collaborationItem = eZCollaborationItem::create( OpenPASensorCollaborationHandler::TYPE_STRING, $authorID );
            $collaborationItem->setAttribute( 'data_int1', $contentObjectID );
            $collaborationItem->setAttribute( 'data_text1', get_called_class() );
            $collaborationItem->setAttribute( self::ITEM_STATUS, false );
            $collaborationItem->setAttribute( self::ITEM_LAST_CHANGE, 0 );
            $collaborationItem->store();
    
            $handler = self::instanceFromCollaborationItem( $collaborationItem );
    
            $participantList = array(
                array(
                    'id' => array( $authorID ),
                    'role' => eZCollaborationItemParticipantLink::ROLE_AUTHOR
                ),
                array(
                    'id' => $approverIDArray,
                    'role' => eZCollaborationItemParticipantLink::ROLE_APPROVER
                )
            );
            foreach ( $participantList as $participantItem )
            {
                foreach( $participantItem['id'] as $participantID )
                {
                    $participantRole = $participantItem['role'];
                    $handler->addParticipant( $participantID, $participantRole );
                }
            }
    
            $handler->setStatus( self::STATUS_WAITING );
            $collaborationItem->createNotificationEvent();
        }

        return $collaborationItem;
    }

    public function onRead()
    {
        if ( $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER )
             && ( $this->is( self::STATUS_WAITING ) || $this->is( self::STATUS_REOPENED ) ) )
        {            
            $this->addComment( $this->getCommentMessage( self::STATUS_READ, $this->userName() ), false, self::MESSAGE_TYPE_ROBOT );
            $this->setStatus( self::STATUS_READ );
        }
    }
    
    protected function userName( $id = null )
    {
        if ( !$id )
        {
            $id = eZUser::currentUserID();
        }
        $user = eZUser::fetch( $id );
        if ( $user instanceof eZUser )
        {
            return $id;
        }
        return false;
    }
    
    public function addCategory( array $categoryList, $autoAssign = false )
    {
        if ( empty( $categoryList ) )
        {
            return false;
        }
        
        $unique = OpenPAINI::variable( 'SensorConfig', 'CategoryCount', 'unique' ) == 'unique';
        
        if ( $unique )
            $userIds = $this->modifyPostCategories( array_shift( $categoryList ) );
        else
            $userIds = $this->modifyPostCategories( implode( '-', $categoryList ) );
        
        if ( !empty( $userIds ) && OpenPAINI::variable( 'SensorConfig', 'CategoryAutomaticAssign', 'disabled' ) == 'enabled' )
        {
            $this->assignTo( $userIds );
        }
    }
    
    public function addArea( array $areaList )
    {        
        if ( empty( $areaList ) )
        {
            return false;
        }
        
        $areasString = implode( '-', $areaList );
        
        $object = $this->getContentObject();
        
        if ( $object instanceof eZContentObject )
        {
            $dataMap = $object->attribute( 'data_map' );
            if ( isset( $dataMap['area'] ) )
            {
                $dataMap['area']->fromString( $areasString );
                $dataMap['area']->store();
                eZContentCacheManager::clearContentCacheIfNeeded( $object->attribute( 'id' ) );
                eZSearch::addObject( $object, true );
            }
        }
        return true;
        
    }
    
    protected function modifyPostCategories( $categoriesString )
    {
        $userIds = array();
        
        if ( $categoriesString == '' ) return array();
        
        $object = $this->getContentObject();
        
        if ( $object instanceof eZContentObject )
        {
            $dataMap = $object->attribute( 'data_map' );
            if ( isset( $dataMap['category'] ) )
            {
                $dataMap['category']->fromString( $categoriesString );
                $dataMap['category']->store();
                eZContentCacheManager::clearContentCacheIfNeeded( $object->attribute( 'id' ) );
                eZSearch::addObject( $object, true );
            }            
            if ( OpenPAINI::variable( 'SensorConfig', 'CategoryAutomaticAssign', 'disabled' ) == 'enabled' )
            {
                $categories = explode( '-', $categoriesString );
                foreach( $categories as $categoryId )
                {
                    $category = eZContentObject::fetch( $categoryId );
                    if ( $category instanceof eZContentObject )
                    {
                        $categoryDataMap = $category->attribute( 'data_map' );
                        if ( isset( $categoryDataMap['approver'] ) )
                        {
                            $userIds = array_merge( $userIds, explode( '-', $categoryDataMap['approver']->toString() ) );
                        }
                    }
                }
            }
        }
        return array_unique( $userIds );
    }
    

    public function addObserver( $userId )
    {
        $list = $this->participantIds();
        $listByObserver = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OBSERVER );
        if ( !in_array( $userId, $listByObserver ) )
        {
            if ( !in_array( $userId, $list ) )
            {
                $this->addParticipant(
                    $userId,
                    eZCollaborationItemParticipantLink::ROLE_OBSERVER
                );
            }
        }
    }
    
    public function canRespond()
    {
        return $this->canClose();
    }
    
    public function canComment()
    {
        return ! (bool) eZPreferences::value( 'sensor_deny_comment', eZUser::currentUser() );
    }

    public function addResponse( $text )
    {
        if ( trim( $text ) != '' && $this->canRespond() )
        {
            $creatorID = eZUser::currentUserID();
            $message = eZCollaborationSimpleMessage::create( OpenPASensorCollaborationHandler::TYPE_STRING.'_comment', $text, $creatorID );
            $message->store();
            $messageLink = eZCollaborationItemMessageLink::addMessage( $this->collaborationItem, $message, self::MESSAGE_TYPE_RESPONSE, $creatorID );

            //l'utente che ha creato il messaggio l'ha già letto
            $timestamp = $messageLink->attribute( 'modified' ) + 1;
            $this->collaborationItem->setLastRead( $creatorID, $timestamp );
            $this->setStatus();
        }
    }

    public function addComment( $text, $creatorID = false, $type = self::MESSAGE_TYPE_PUBLIC )
    {
        $userCanComment = $this->canComment();

        if ( eZUser::currentUser()->isAnonymous() && !self::ANONYMOUS_CAN_COMMENT )
        {
            $userCanComment = false;
        }

        if ( $type !== self::MESSAGE_TYPE_PUBLIC && $type !== self::MESSAGE_TYPE_ROBOT && !$this->canSendPrivateMessage() )
        {
            $userCanComment = false;
        }

        if ( trim( $text ) != '' && $userCanComment )
        {
            if ( $creatorID === false )
            {
                $creatorID = eZUser::currentUserID();
            }
            $message = eZCollaborationSimpleMessage::create( OpenPASensorCollaborationHandler::TYPE_STRING.'_comment', $text, $creatorID );
            $message->store();
            $messageLink = eZCollaborationItemMessageLink::addMessage( $this->collaborationItem, $message, $type, $creatorID );

            //l'utente che ha creato il messaggio l'ha già letto
            $timestamp = $messageLink->attribute( 'modified' ) + 1;
            $this->collaborationItem->setLastRead( $creatorID, $timestamp );

            if ( $this->is( self::STATUS_CLOSED ) && $this->userIsA( eZCollaborationItemParticipantLink::ROLE_AUTHOR ) && OpenPAINI::variable( 'SensorConfig', 'AuthorCanReopen', 'disabled' ) == 'enabled' )
            {
                $this->collaborationItem->createNotificationEvent();
                $this->setStatus( self::STATUS_REOPENED );
                $this->addComment( $this->getCommentMessage( self::STATUS_REOPENED, $this->userName() ), false, self::MESSAGE_TYPE_ROBOT );                
            }
            else
            {
                $this->setStatus(); // touch to clear cache
            }            
        }
    }

    public function canAssign()
    {
        return !$this->is( self::STATUS_CLOSED )
               && ( $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER )
                    || ( $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER ) && $this->is( self::STATUS_ASSIGNED ) ) );
    }

    public function canAddObserver()
    {
        return !$this->is( self::STATUS_CLOSED ) && $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    public function canSendPrivateMessage()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER )
               || $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OBSERVER )
               || $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    public function canFix()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER ) && $this->is( self::STATUS_ASSIGNED );
    }

    public function canChangePrivacy()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }
    
    public function makePrivate()
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {                    
            OpenPABase::sudo(
                function() use( $object ){
                    ObjectHandlerServiceControlSensor::setState( $object, 'privacy', 'private' );
                }
            );  
        }
        
    }
    
    public function canClose()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER )
               && !$this->is( self::STATUS_CLOSED )
               && !$this->is( self::STATUS_ASSIGNED );
    }

    public function fix( $message = null )
    {
        $listByOwner = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OWNER );
        if ( !empty( $listByOwner ) )
        {
            foreach ( $listByOwner as $id )
            {
                $this->changeParticipantRole(
                    $id,
                    eZCollaborationItemParticipantLink::ROLE_OBSERVER
                );
            }
        }
        if ( !$message )
        {
            $message = $this->getCommentMessage( self::STATUS_FIXED, $this->userName() );
        }
        $this->addComment( $message, false, self::MESSAGE_TYPE_ROBOT );
        $this->collaborationItem->createNotificationEvent();
        $this->setStatus( self::STATUS_FIXED );
    }

    public function close( $message = null )
    {
        if ( !$message )
        {
            $message = $this->getCommentMessage( self::STATUS_CLOSED, $this->userName() );
        }
        $this->addComment( $message, false, self::MESSAGE_TYPE_ROBOT );
        $this->collaborationItem->createNotificationEvent();
        $this->setStatus( self::STATUS_CLOSED );
    }

    public function forceAssignTo( $userId )
    {
        $list = $this->participantIds();
        $listByOwner = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OWNER );
        foreach ( $listByOwner as $id )
        {
            if ( $id == $userId )
            {
                $this->changeParticipantRole(
                    $id,
                    eZCollaborationItemParticipantLink::ROLE_OBSERVER
                );
            }
        }
        if ( !in_array( $userId, $list ) )
        {
            $this->addParticipant(
                $userId,
                eZCollaborationItemParticipantLink::ROLE_OWNER
            );
        }
        else
        {
            $this->changeParticipantRole(
                $userId,
                eZCollaborationItemParticipantLink::ROLE_OWNER
            );
        }
    }

    public function assignTo( array $userIds, $message = null )
    {
        if ( !empty( $userIds ) )
        {

            $list = $this->participantIds();
            $listByOwner = $this->participantIds( eZCollaborationItemParticipantLink::ROLE_OWNER );

            if ( !empty( $listByOwner ) )
            {
                foreach ( $listByOwner as $id )
                {
                    if ( !in_array( $id, $userIds ) ) // in caso di riassegnazione allo stesso utente non gli viene cambiato il ruolo
                    {
                        $this->changeParticipantRole(
                            $id,
                            eZCollaborationItemParticipantLink::ROLE_OBSERVER
                        );
                    }
                }
            }

            foreach ( $userIds as $userId )
            {
                if ( !in_array( $userId, $listByOwner ) ) // in caso di riassegnazione allo stesso utente il ticket non viene riasseganto
                {
                    if ( !in_array( $userId, $list ) )
                    {
                        $this->addParticipant(
                            $userId,
                            eZCollaborationItemParticipantLink::ROLE_OWNER
                        );
                    }
                    else
                    {
                        $this->changeParticipantRole(
                            $userId,
                            eZCollaborationItemParticipantLink::ROLE_OWNER
                        );
                    }
                }
            }
            if ( !$message )
            {
                $message = $this->getCommentMessage( self::STATUS_ASSIGNED, $this->userName( $userIds[0] ) );
            }
            $this->addComment( $message, false, self::MESSAGE_TYPE_ROBOT );
            $this->collaborationItem->createNotificationEvent();
            $this->setStatus( self::STATUS_ASSIGNED );
        }
    }

    public function is( $status )
    {
        return $this->collaborationItem->attribute( self::ITEM_STATUS ) == $status;
    }
    
    public static function currentUserCollaborationGroup()
    {
        $participantID = eZUser::currentUserID();
        $collaboration = new eZCollaborationItem( array() ); //@todo
        $helper = new static( $collaboration );
        return $helper->collaborationGroup( $participantID );
    }

    protected function customCollaborationGroupName()
    {
        return 'Sensor';
    }

    protected function collaborationGroup( $participantID )
    {        
        $profile = eZCollaborationProfile::instance( $participantID );

        if ( $this->customCollaborationGroupName() !== false )
        {
            $groupName = $this->customCollaborationGroupName();
            return $this->createCollaborationGroup( $participantID, $groupName );
        }
        else
        {
            return $profile->attribute( 'main_group' );
        }
    }
    
    protected function collaborationGroupTrash( $participantID )
    {                
        return $this->createCollaborationGroup( $participantID, 'Trash' );        
    }
    
    public function moveToTrash()
    {
        $participants = $this->participantIds();
        foreach( $participants as $participantID )
        {
            $this->moveItem( $participantID, $this->collaborationGroupTrash( $participantID ) );
        }
    }
    
    public function restoreFormTrash()
    {        
        $participants = $this->participantIds();
        foreach( $participants as $participantID )
        {
            $this->moveItem( $participantID, $this->collaborationGroup( $participantID ) );
        }
    }

    protected function createCollaborationGroup( $participantID, $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array( 'user_id' => $participantID, 'title' => $groupName )
        );
        if ( !$group instanceof eZCollaborationGroup )
        {
            /** @var eZCollaborationGroup $parentGroup */
            $group = eZCollaborationGroup::instantiate( $participantID, $groupName );
        }
        return $group;
    }

    protected function createCollaborationGroupChild( $parentGroup, $participantID, $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array( 'user_id' => $participantID, 'title' => $groupName )
        );
        if ( !$group instanceof eZCollaborationGroup )
        {
            /** @var eZCollaborationGroup $parentGroup */
            $group = eZCollaborationGroup::create( $participantID, $groupName );
            $parentGroup->addChild( $group );
        }
        return $group;
    }

    public function moveItem( $participantID, $newGroup )
    {
        //$newGroup = $this->collaborationGroup( $participantID );
        //if ( $groupName !== $newGroup->attribute( 'title' ) )
        //{
        //    $newGroup = $this->createCollaborationGroupChild(
        //        $newGroup,
        //        $participantID,
        //        $groupName
        //    );
        //}

        $db = eZDB::instance();

        /** @var eZCollaborationItemGroupLink $group */
        $groupLink = eZPersistentObject::fetchObject(
            eZCollaborationItemGroupLink::definition(),
            null,
            array( 'collaboration_id' => $this->collaborationItem->attribute( 'id' ),
                   'user_id' => $participantID )
        );
        if ( $groupLink instanceof eZCollaborationItemGroupLink )
        {
            $db->begin();
            $groupLink->remove();
            $newGroupLink = eZCollaborationItemGroupLink::create( $this->collaborationItem->attribute( 'id' ), $newGroup->attribute( 'id' ) , $participantID );
            $newGroupLink->store();
            $db->commit();
        }
        else
        {
            $db->rollback();
        }
    }

    public function hasRedirect( eZModule $module )
    {
        return true;
    }

    public function redirect( eZModule $module )
    {
        return $module->redirectTo( 'sensor/posts/' . $this->collaborationItem->attribute( 'data_int1' ) );
    }

    protected function addParticipant( $participantID, $participantRole )
    {
        if ( eZUser::fetch( $participantID ) instanceof eZUser )
        {
            $link = eZCollaborationItemParticipantLink::create(
                $this->collaborationItem->attribute( 'id' ),
                $participantID,
                $participantRole,
                eZCollaborationItemParticipantLink::TYPE_USER
            );
            $link->store();
            $group = $this->collaborationGroup( $participantID );
            eZCollaborationItemGroupLink::addItem(
                $group->attribute( 'id' ),
                $this->collaborationItem->attribute( 'id' ),
                $participantID
            );
            $this->collaborationItem->setIsActive( true, $participantID );
        }
    }

    protected function changeParticipantRole( $participantID, $newParticipantRole )
    {
        $participantLink = eZCollaborationItemParticipantLink::fetch( $this->collaborationItem->attribute( 'id' ), $participantID );
        if ( $participantLink instanceof eZCollaborationItemParticipantLink )
        {
            $participantLink->setAttribute( 'participant_role', $newParticipantRole );
            $participantLink->sync();
            $GLOBALS['eZCollaborationItemParticipantLinkListCache'] = array();
        }
    }

    protected function userIsA( $roleId, $userId = null )
    {
        if ( $userId == null )
        {
            $userId = eZUser::currentUserID();
        }
        $list = $this->participantIds( $roleId );
        return in_array( $userId, $list );
    }

    protected function participantIds( $role = null )
    {
        $participantIds = array();
        /** @var eZCollaborationItemParticipantLink[] $participantList */
        $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $this->collaborationItem->attribute( 'id' ), 'limit' => 100 ) );        
        foreach( $participantList as $participant )
        {
            if ( $role !== null )
            {
                if ( $role == $participant->attribute( 'participant_role' ) )
                    $participantIds[] = $participant->attribute( 'participant_id' );
            }
            else
            {
                $participantIds[] = $participant->attribute( 'participant_id' );
            }
        }
        return $participantIds;
    }

    protected function setStatus( $status = null )
    {
        $timestamp = time();
        $content = $this->collaborationItem->content();
        $id = $content['content_object_id'];
        $object = eZContentObject::fetch( $id );
        if ( $status !== null )
        {
            $this->collaborationItem->setAttribute( self::ITEM_STATUS, $status );
            $this->collaborationItem->setAttribute( 'modified', $timestamp );
            $this->collaborationItem->setAttribute( self::ITEM_LAST_CHANGE, $timestamp );
            if ( $status == self::STATUS_READ )
            {
                if ( $object instanceof eZContentObject )
                {
                    OpenPABase::sudo(
                        function() use( $object ){
                            ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'open' );
                        }
                    );                
                }
            }
            elseif ( $status == self::STATUS_CLOSED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_INACTIVE );
                foreach( $this->participantIds() as $participantID )
                {
                    $this->collaborationItem->setIsActive( false, $participantID );
                }
                if ( $object instanceof eZContentObject )
                {                    
                    OpenPABase::sudo(
                        function() use( $object ){
                            ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'close' );
                        }
                    );  
                }
            }
            elseif ( $status == self::STATUS_WAITING )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                foreach( $this->participantIds() as $participantID )
                {
                    $this->collaborationItem->setIsActive( true, $participantID );
                }
            }
            elseif ( $status == self::STATUS_REOPENED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                foreach( $this->participantIds() as $participantID )
                {
                    $this->collaborationItem->setIsActive( true, $participantID );
                }
                if ( $object instanceof eZContentObject )
                {
                    OpenPABase::sudo(
                        function() use( $object ){
                            ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'pending' );
                        }
                    );                
                }
            }
            $this->collaborationItem->sync();
        }
        if ( $object instanceof eZContentObject )
        {
            $object->setAttribute( 'modified', $timestamp );
            $object->store();
            eZContentCacheManager::clearContentCacheIfNeeded( $id );
        }        
    }

    protected function getCommentMessage( $status, $name = null )
    {
        $message = '';
        if ( $status == self::STATUS_FIXED )
        {            
            if ( $name )
            {
                $message = '_fixed by ' .  $name;    
            }
            else
            {
                $message = '_fixed';    
            }
            
        }
        elseif( $status == self::STATUS_READ )
        {
            if ( $name )
            {
                $message = '_read by ' .  $name;    
            }
            else
            {
                $message = '_read';
            }
        }
        elseif( $status == self::STATUS_CLOSED )
        {
            if ( $name )
            {
                $message = '_closed by ' .  $name;    
            }
            else
            {
                $message = '_closed';
            }
        }
        elseif( $status == self::STATUS_ASSIGNED )
        {
            if ( $name )
            {
                $message = '_assigned to ' .  $name;    
            }
            else
            {
                $message = '_assigned';
            }
        }
        elseif( $status == self::STATUS_REOPENED )
        {
            if ( $name )
            {
                $message = '_reopened by ' .  $name;    
            }
            else
            {
                $message = '_reopened';
            }
        }
        return $message;
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
    
    /**
     * @see eZCollaborationItem::fetchListTool
     */
    public static function fetchListTool( $parameters = array(), $asCount )
    {
        $parameters = array_merge( array( 'as_object' => true,
                                          'offset' => false,
                                          'parent_group_id' => false,
                                          'limit' => false,
                                          'is_active' => null,
                                          'is_read' => null,
                                          'last_change' => null,
                                          'status' => false,
                                          'sort_by' => array( 'modified', false ) ),
                                   $parameters );
        $asObject = $parameters['as_object'];
        $offset = $parameters['offset'];
        $limit = $parameters['limit'];
        $statusTypes = $parameters['status'];
        $isRead = $parameters['is_read'];
        $isActive = $parameters['is_active'];
        $parentGroupID = $parameters['parent_group_id'];

        $sortText = '';
        if ( !$asCount )
        {
            $sortCount = 0;
            $sortList = $parameters['sort_by'];
            if ( is_array( $sortList ) and
                 count( $sortList ) > 0 )
            {
                if ( count( $sortList ) > 1 and
                     !is_array( $sortList[0] ) )
                {
                    $sortList = array( $sortList );
                }
            }
            if ( $sortList !== false )
            {
                $sortingFields = '';
                foreach ( $sortList as $sortBy )
                {
                    if ( is_array( $sortBy ) and count( $sortBy ) > 0 )
                    {
                        if ( $sortCount > 0 )
                            $sortingFields .= ', ';
                        $sortField = $sortBy[0];
                        switch ( $sortField )
                        {
                            case 'created':
                            {
                                $sortingFields .= 'ezcollab_item.created';
                            } break;
                            case 'modified':
                            {
                                $sortingFields .= 'ezcollab_item.modified';
                            } break;
                            default:
                            {
                                eZDebug::writeWarning( 'Unknown sort field: ' . $sortField, __METHOD__ );
                                continue;
                            }
                        }
                        $sortOrder = true; // true is ascending
                        if ( isset( $sortBy[1] ) )
                            $sortOrder = $sortBy[1];
                        $sortingFields .= $sortOrder ? ' ASC' : ' DESC';
                        ++$sortCount;
                    }
                }
            }
            if ( $sortCount == 0 )
            {
                $sortingFields = ' ezcollab_item_group_link.modified DESC';
            }
            $sortText = "ORDER BY $sortingFields";
        }

        $parentGroupText = '';
        if ( $parentGroupID > 0 )
        {
            $parentGroupText = "ezcollab_item_group_link.group_id = '$parentGroupID' AND";
        }

        $isReadText = '';
        if ( $isRead !== null )
        {
            $isReadValue = $isRead ? 1 : 0;
            $isReadText = "ezcollab_item_status.is_read = '$isReadValue' AND";
        }

        $isActiveText = '';
        if ( $isActive !== null )
        {
            $isActiveValue = $isActive ? 1 : 0;
            $isActiveText = "ezcollab_item_status.is_active = '$isActiveValue' AND";
        }
        
        $lastChangeText = '';
        if ( $lastChangeText !== null )
        {
            //@todo
        }

        $userID = eZUser::currentUserID();

        $statusText = '';
        if ( $statusTypes === false )
        {
            $statusTypes = array( eZCollaborationItem::STATUS_ACTIVE,
                                  eZCollaborationItem::STATUS_INACTIVE );
        }
        $statusText = implode( ', ', $statusTypes );

        if ( $asCount )
            $selectText = 'count( ezcollab_item.id ) as count';
        else
            $selectText = 'ezcollab_item.*, ezcollab_item_status.is_read, ezcollab_item_status.is_active, ezcollab_item_status.last_read';

        $sql = "SELECT $selectText
                FROM
                       ezcollab_item,
                       ezcollab_item_status,
                       ezcollab_item_group_link
                WHERE  ezcollab_item.status IN ( $statusText ) AND
                       $isReadText
                       $isActiveText
                       $lastChangeText
                       ezcollab_item.id = ezcollab_item_status.collaboration_id AND
                       ezcollab_item.id = ezcollab_item_group_link.collaboration_id AND
                       $parentGroupText
                       ezcollab_item_status.user_id = '$userID' AND
                       ezcollab_item_group_link.user_id = '$userID'
                $sortText";

        $db = eZDB::instance();
        if ( !$asCount )
        {
            $sqlParameters = array();
            if ( $offset !== false and $limit !== false )
            {
                $sqlParameters['offset'] = $offset;
                $sqlParameters['limit'] = $limit;
            }
            $itemListArray = $db->arrayQuery( $sql, $sqlParameters );

            foreach( $itemListArray as $key => $value )
            {
                $itemData =& $itemListArray[$key];
                $statusObject = eZCollaborationItemStatus::create( $itemData['id'], $userID );
                $statusObject->setAttribute( 'is_read', $itemData['is_read'] );
                $statusObject->setAttribute( 'is_active', $itemData['is_active'] );
                $statusObject->setAttribute( 'last_read', $itemData['last_read'] );
                $statusObject->updateCache();
            }
            $returnItemList = eZPersistentObject::handleRows( $itemListArray, 'eZCollaborationItem', $asObject );
            eZDebugSetting::writeDebug( 'collaboration-item-list', $returnItemList );
            return $returnItemList;
        }
        else
        {
            $itemCount = $db->arrayQuery( $sql );
            return $itemCount[0]['count'];
        }
    }
    
    public static function fetchAllItems( $group, $limit, $offset = 0, $lastChange = null, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => null,
            'is_active' => null,
            'last_change' => $lastChange,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );        
        return SensorHelper::fetchListTool( $itemParameters, false );  
    }
    
    public static function fetchAllItemsCount( $group )
    {
        $itemParameters = array(            
            'is_read' => null,
            'is_active' => null,            
            'parent_group_id' => $group->attribute( 'id' )            
        );        
        return SensorHelper::fetchListTool( $itemParameters, true ); 
    }
    
    public static function fetchUnreadItems( $group, $limit, $offset = 0, $lastChange = null, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => false,
            'is_active' => null,
            'last_change' => $lastChange,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );        
        return SensorHelper::fetchListTool( $itemParameters, false );  
    }
    
    public static function fetchUnreadItemsCount( $group )
    {
        $itemParameters = array(            
            'is_read' => false,
            'is_active' => null,            
            'parent_group_id' => $group->attribute( 'id' )       
        );        
        return SensorHelper::fetchListTool( $itemParameters, true );  
    }
    
    public static function fetchActiveItems( $group, $limit, $offset = 0, $lastChange = null, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => true,
            'is_active' => true,
            'last_change' => $lastChange,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );        
        return SensorHelper::fetchListTool( $itemParameters, false );  
    }
    
    public static function fetchActiveItemsCount( $group )
    {
        $itemParameters = array(
            'is_read' => true,
            'is_active' => true,
            'parent_group_id' => $group->attribute( 'id' ),
        );        
        return SensorHelper::fetchListTool( $itemParameters, true );  
    }

    public static function fetchUnactiveItems( $group, $limit, $offset = 0, $lastChange = null, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => true,
            'is_active' => false,
            'last_change' => $lastChange,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );        
        return SensorHelper::fetchListTool( $itemParameters, false );  
    }
    
    public static function fetchUnactiveItemsCount( $group )
    {
        $itemParameters = array(
            'is_read' => true,
            'is_active' => false,
            'parent_group_id' => $group->attribute( 'id' )
        );        
        return SensorHelper::fetchListTool( $itemParameters, true );  
    }
}