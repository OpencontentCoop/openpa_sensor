<?php

class OpenPASensorCollaborationHandler extends eZCollaborationItemHandler
{
    const TYPE_STRING = 'openpasensor';

    /*!
     Initializes the handler
    */
    function OpenPASensorCollaborationHandler()
    {
        $this->eZCollaborationItemHandler(
            OpenPASensorCollaborationHandler::TYPE_STRING,
            ezpI18n::tr( 'openpa_sensor/collaboration', 'Sensor' ),
            array(
                'use-messages' => true,
                'notification-types' => true,
                'notification-collection-handling' => eZCollaborationItemHandler::NOTIFICATION_COLLECTION_PER_PARTICIPATION_ROLE
            )
        );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return string
     */
    function title( $collaborationItem )
    {
        return ezpI18n::tr( 'openpa_sensor/collaboration', 'Sensor' );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return array|null
     */
    function content( $collaborationItem )
    {
        return array(
            "content_object_id" => $collaborationItem->attribute( "data_int1" ),
            "helper" => self::handler( $collaborationItem ),
            "item_status" => $collaborationItem->attribute( SensorHelper::ITEM_STATUS )
        );
    }

    function notificationParticipantTemplate( $participantRole )
    {
        if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER )
        {
            return 'approver.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
        {
            return 'author.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OBSERVER )
        {
            return 'observer.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OWNER )
        {
            return 'owner.tpl';
        }
        else
            return false;
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return SensorHelper
     * @throws Exception
     */
    static function handler( $collaborationItem )
    {
        $className = $collaborationItem->attribute( "data_text1" );
        if ( $className == 'SensorHelper' )
        {
            /** @var SensorHelper $className */
            return $className::instanceFromCollaborationItem( $collaborationItem );
        }
        throw new Exception( "Handler class $className not found or not implement SensorHelper" );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return eZContentObject
     */
    static function contentObject( $collaborationItem )
    {
        $contentObjectID = $collaborationItem->contentAttribute( 'content_object_id' );
        return eZContentObject::fetch( $contentObjectID );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @param bool $viewMode
     */
    function readItem( $collaborationItem, $viewMode = false )
    {
        $collaborationItem->setLastRead();
        self::handler( $collaborationItem )->onRead();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function messageCount( $collaborationItem )
    {
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $collaborationItem->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => array( array( SensorHelper::MESSAGE_TYPE_ROBOT, SensorHelper::MESSAGE_TYPE_PUBLIC, eZUser::currentUserID() ) )
                )
            )
        );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function unreadMessageCount( $collaborationItem )
    {
        $lastRead = 0;
        /** @var eZCollaborationItemStatus $status */
        $status = $collaborationItem->attribute( 'user_status' );
        if ( $status )
        {
            $lastRead = $status->attribute( 'last_read' );
        }
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $collaborationItem->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => array( array( SensorHelper::MESSAGE_TYPE_ROBOT, SensorHelper::MESSAGE_TYPE_PUBLIC, eZUser::currentUserID() ) ),
                    'modified' => array( '>', $lastRead )
                )
            )
        );
    }

    /**
     * @param int $collaborationItemId
     * @return bool
     */
    static function checkItem( $collaborationItemId )
    {
        /** @var eZCollaborationItem $collaborationItem */
        $collaborationItem = eZCollaborationItem::fetch( $collaborationItemId );
        if ( $collaborationItem !== null )
        {
            return $collaborationItem->attribute( 'data_int3' );
        }
        return false;
    }

    /**
     * @param eZModule $module
     * @param eZCollaborationItem $collaborationItem
     * @return mixed
     */
    function handleCustomAction( $module, $collaborationItem )
    {
        $handler = self::handler( $collaborationItem );

        if ( $this->isCustomAction( 'Assign' )
             && $this->hasCustomInput( 'OpenPASensorItemAssignTo' )
             && $handler->canAssign() )
        {
            $userIds = (array) $this->customInput( 'OpenPASensorItemAssignTo' );
            $handler->assignTo( $userIds );
        }

        if ( $this->isCustomAction( 'Fix' )
             && $handler->canFix() )
        {
            $handler->fix();
        }

        if ( $this->isCustomAction( 'Close' )
             && $handler->canClose() )
        {
            $handler->close();
        }

        if ( $this->isCustomAction( 'AddObserver' ) && $this->hasCustomInput( 'OpenPASensorItemAddObserver' )
             && $handler->canAddObserver() )
        {
            $handler->addObserver( $this->customInput( 'OpenPASensorItemAddObserver' ) );
        }

        if ( $this->hasCustomInput( 'OpenPASensorItemComment' ) || $this->isCustomAction( 'Comment' ) )
        {
            $messageText = $this->customInput( 'OpenPASensorItemComment' );
            $privateReceiver = SensorHelper::MESSAGE_TYPE_PUBLIC;
            if ( $this->hasCustomInput( 'OpenPASensorItemCommentPrivateReceiver' ) )
            {
                $privateReceiverPost = $this->customInput( 'OpenPASensorItemCommentPrivateReceiver' );
                if ( $privateReceiverPost > 0 )
                {
                    $privateReceiver = $privateReceiverPost;
                }
            }
            $handler->addComment( $messageText, eZUser::currentUserID(), $privateReceiver );
        }

//        $redirectView = 'item';
//        $redirectParameters = array( 'full', $collaborationItem->attribute( 'id' ) );
//
//        if ( $handler->is( SensorHelper::STATUS_CLOSED ) )
//        {
//            $redirectView = 'view';
//            $redirectParameters = array( 'summary' );
//        }

        if ( $handler->hasRedirect( $module ) )
        {
            return $handler->redirect( $module );
        }
        else
        {
            $redirectView = 'view';
            $redirectParameters = array( 'summary' );
            return $module->redirectToView( $redirectView, $redirectParameters );
        }
    }

    /**
     * @param eZNotificationEvent $event
     * @param eZCollaborationItem $item
     * @param array $parameters
     *
     * @return int
     */
    static function handleCollaborationEvent( $event, $item, &$parameters )
    {
        $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $item->attribute( 'id' ),
                                                                                            'participant_type' => eZCollaborationItemParticipantLink::TYPE_USER,
                                                                                            'as_object' => false ) );

        $userIDList = array();
        $participantMap = array();
        foreach ( $participantList as $participant )
        {
            $userIDList[] = $participant['participant_id'];
            $participantMap[$participant['participant_id']] = $participant;
        }

        $collaborationIdentifier = $event->attribute( 'data_text1' );
        $ruleList = eZCollaborationNotificationRule::fetchItemTypeList( $collaborationIdentifier, $userIDList, false );
        $userIDList = array();
        foreach ( $ruleList as $rule )
        {
            $userIDList[] = $rule['user_id'];
        }
        $userList = array();
        if ( count( $userIDList ) > 0 )
        {
            $db = eZDB::instance();
            $userIDListText = $db->generateSQLINStatement( $userIDList, 'contentobject_id', false, false, 'int' );
            $userList = $db->arrayQuery( "SELECT contentobject_id, email FROM ezuser WHERE $userIDListText" );
        }
        else
            return eZNotificationEventHandler::EVENT_SKIPPED;

        /** @var OpenPASensorCollaborationHandler $itemHandler */
        $itemHandler = $item->attribute( 'handler' );

        $db = eZDB::instance();
        $db->begin();

        $userCollection = array();
        foreach( $userList as $subscriber )
        {
            $contentObjectID = $subscriber['contentobject_id'];
            $participant = $participantMap[$contentObjectID];
            $participantRole = $participant['participant_role'];
            $userItem = array( 'participant' => $participant,
                               'email' => $subscriber['email'] );
            if ( !isset( $userCollection[$participantRole] ) )
            {
                $userCollection[$participantRole] = array();
            }
            $userCollection[$participantRole][] = $userItem;
        }

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();
        foreach( $userCollection as $participantRole => $collectionItems )
        {
            $templateName = $itemHandler->notificationParticipantTemplate( $participantRole );
            if ( !$templateName )
                $templateName = eZCollaborationItemHandler::notificationParticipantTemplate( $participantRole );

            $itemInfo = $itemHandler->attribute( 'info' );
            $typeIdentifier = $itemInfo['type-identifier'];

            $tpl->setVariable( 'collaboration_item', $item );
            $tpl->setVariable( 'collaboration_participant_role', $participantRole );

            $tpl->setVariable( 'item_status', $item->attribute( SensorHelper::ITEM_STATUS ) );

            $result = $tpl->fetch( 'design:notification/handler/ezcollaboration/view/' . $typeIdentifier . '/' . $templateName );

            $body = $tpl->variable( 'body' );
            $subject = $tpl->variable( 'subject' );

            if ( !empty( $body ) )
            {
                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $parameters['message_id'] = $tpl->variable( 'message_id' );
                }
                if ( $tpl->hasVariable( 'references' ) )
                {
                    $parameters['references'] = $tpl->variable( 'references' );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $parameters['reply_to'] = $tpl->variable( 'reply_to' );
                }
                if ( $tpl->hasVariable( 'from' ) )
                {
                    $parameters['from'] = $tpl->variable( 'from' );
                }
                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $parameters['content_type'] = $tpl->variable( 'content_type' );
                }

                $collection = eZNotificationCollection::create(
                    $event->attribute( 'id' ),
                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    eZCollaborationNotificationHandler::TRANSPORT
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $body );
                $collection->store();
                foreach ( $collectionItems as $collectionItem )
                {
                    $collection->addItem( $collectionItem['email'] );
                }
            }
        }

        $db->commit();

        return eZNotificationEventHandler::EVENT_HANDLED;
    }

}