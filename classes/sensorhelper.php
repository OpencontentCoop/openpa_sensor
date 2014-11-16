<?php

class SensorHelper
{
    const ANONYMOUS_CAN_COMMENT = false;

    const ITEM_STATUS = 'data_int3';

    const MESSAGE_TYPE_ROBOT = 0;

    const MESSAGE_TYPE_PUBLIC = 1;

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
            'can_assign',
            'can_close',
            'can_fix',
            'can_add_observer',
            'can_send_private_message',
            'participants'
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
                return $this->canAssign() || $this->canAddObserver() || $this->canClose() || $this->canFix();
                break;

            case 'can_assign':
                return $this->canAssign();
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

            default:
                eZDebug::writeError( "Attribute $key not found", get_called_class() );
                return false;
        }
    }

    /**
     * @param $contentObjectID
     * @return eZCollaborationItem
     * @throws Exception
     */
    public static function createCollaborationItem( $contentObjectID )
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
        $collaborationItem->setAttribute( 'data_int3', false );
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

        $collaborationItem->createNotificationEvent();
        $handler->setStatus( self::STATUS_WAITING );

        return $collaborationItem;
    }

    public function onRead()
    {
        if ( $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER )
             && ( $this->is( self::STATUS_WAITING ) || $this->is( self::STATUS_REOPENED ) ) )
        {
            $this->addComment( $this->getCommentMessage( self::STATUS_READ ), false, self::MESSAGE_TYPE_ROBOT );
            $this->setStatus( self::STATUS_READ );
        }
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

    public function addComment( $text, $creatorID = false, $type = self::MESSAGE_TYPE_PUBLIC )
    {
        $userCanComment = true;

        if ( eZUser::currentUser()->isAnonymous() && !self::ANONYMOUS_CAN_COMMENT )
        {
            $userCanComment = false;
        }

        if ( $type !== self::MESSAGE_TYPE_PUBLIC && $type !== self::MESSAGE_TYPE_ROBOT && !$this->canSendPrivateMessage() )
        {

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

            if ( $this->is( self::STATUS_CLOSED ) && $this->userIsA( eZCollaborationItemParticipantLink::ROLE_AUTHOR ) )
            {
                $this->collaborationItem->createNotificationEvent();
                $this->setStatus( self::STATUS_REOPENED );
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
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    public function canSendPrivateMessage()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER )
               || $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    public function canFix()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER ) && $this->is( self::STATUS_ASSIGNED );
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
            $message = $this->getCommentMessage( self::STATUS_FIXED );
        }
        $this->addComment( $message, false, self::MESSAGE_TYPE_ROBOT );
        $this->collaborationItem->createNotificationEvent();
        $this->setStatus( self::STATUS_FIXED );
    }

    public function close( $message = null )
    {
        if ( !$message )
        {
            $message = $this->getCommentMessage( self::STATUS_CLOSED );
        }
        $this->addComment( $message, false, self::MESSAGE_TYPE_ROBOT );
        $this->collaborationItem->createNotificationEvent();
        $this->setStatus( self::STATUS_CLOSED );
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
                    $this->changeParticipantRole(
                        $id,
                        eZCollaborationItemParticipantLink::ROLE_OBSERVER
                    );
                }
            }

            foreach ( $userIds as $userId )
            {
                if ( !in_array( $userId, $listByOwner ) )
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
                $message = $this->getCommentMessage( self::STATUS_ASSIGNED );
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

    public function moveItem( $participantID, $groupName )
    {
        $newGroup = $this->collaborationGroup( $participantID );
        if ( $groupName !== $newGroup->attribute( 'title' ) )
        {
            $newGroup = $this->createCollaborationGroupChild(
                $newGroup,
                $participantID,
                $groupName
            );
        }

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
        $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $this->collaborationItem->attribute( 'id' ) ) );
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

    protected function setStatus( $status )
    {
        $this->collaborationItem->setAttribute( self::ITEM_STATUS, $status );
        $timestamp = time();
        $this->collaborationItem->setAttribute( 'modified', $timestamp );
        if ( $status == self::STATUS_CLOSED )
        {
            $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_INACTIVE );
            foreach( $this->participantIds() as $participantID )
            {
                $this->collaborationItem->setIsActive( false, $participantID );
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
        }
        $this->collaborationItem->sync();
    }

    protected function getCommentMessage( $status )
    {
        $message = '';
        if ( $status == self::STATUS_FIXED )
        {
            $message = '_fixed';
        }
        elseif( $status == self::STATUS_READ )
        {
            $message = '_read';
        }
        elseif( $status == self::STATUS_CLOSED )
        {
            $message = '_closed';
        }
        elseif( $status == self::STATUS_ASSIGNED )
        {
            $message = '_assigned';
        }
        elseif( $status == self::STATUS_REOPENED )
        {
            $message = '_reopened';
        }
        return $message;
    }

}