<?php

class SensorCollaborationHandler extends eZCollaborationItemHandler
{
    /*!
     Initializes the handler
    */
    function SensorCollaborationHandler()
    {
        $this->eZCollaborationItemHandler(
            SensorHelper::factory()->getSensorCollaborationHandlerTypeString(),
            ezpI18n::tr( 'openpa_sensor/settings', 'Notifiche SensorCivico' ),
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
        return ezpI18n::tr( 'openpa_sensor/settings', 'Notifiche SensorCivico' );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return array|null
     */
    function content( $collaborationItem )
    {
        return array(
            "content_object_id" => $collaborationItem->attribute( "data_int1" ),
            "last_change" => $collaborationItem->attribute( SensorPost::COLLABORATION_FIELD_LAST_CHANGE),
            "helper" => self::helper( $collaborationItem ),
            "item_status" => $collaborationItem->attribute( SensorPost::COLLABORATION_FIELD_STATUS)
        );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return SensorHelper
     * @throws Exception
     */
    static function helper( $collaborationItem )
    {
        $helper = null;
        try
        {
            $helper = SensorHelper::instanceFromCollaborationItem( $collaborationItem );
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage() );
        }
        return $helper;
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
        self::helper( $collaborationItem )->onRead();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function messageCount( $collaborationItem )
    {
        $post = SensorPost::instance( $collaborationItem );
        return $post->commentHelper->count();
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function unreadMessageCount( $collaborationItem )
    {
        $post = SensorPost::instance( $collaborationItem );
        return $post->commentHelper->unreadCount();
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
        $helper = self::helper( $collaborationItem );
        $helper->handleHttpAction( $module );
        $module->redirectTo( 'sensor/posts/' . $collaborationItem->attribute( 'data_int1' ) );
        return;
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
     * @param eZNotificationEvent $event
     * @param eZCollaborationItem $item
     * @param array $parameters
     *
     * @return int
     */
    static function handleCollaborationEvent( $event, $item, &$parameters )
    {        
        $participantList = eZCollaborationItemParticipantLink::fetchParticipantList( array( 'item_id' => $item->attribute( 'id' ),
                                                                                            'offset' => 0,
                                                                                            'limit' => 100,
                                                                                            'as_object' => false ) );
        $userIDList = array();
        /** @var eZCollaborationItemParticipantLink[] $participantMap */
        $participantMap = array();
        foreach ( $participantList as $participant )
        {
            if ( is_array( $participant ) ) $participant = new OpenPATempletizable( $participant );
            $userIDList[] = $participant->attribute( 'participant_id' );
            $participantMap[$participant->attribute('participant_id' )] = $participant;
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

        /** @var SensorCollaborationHandler $itemHandler */
        $itemHandler = $item->attribute( 'handler' );

        $db = eZDB::instance();
        $db->begin();
        
        $userCollection = array();
        foreach( $userList as $subscriber )
        {
            $contentObjectID = $subscriber['contentobject_id'];
            $participant = $participantMap[$contentObjectID];
            $participantRole = $participant->attribute( 'participant_role' );
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

        try
        {
            $helper = SensorHelper::instanceFromCollaborationItem( $item );
            $object = $helper->currentSensorPost->objectHelper->getContentObject();
            $node = $object->attribute( 'main_node' );
        }
        catch( Exception $e )
        {
            eZDebugSetting::writeError( 'sensor', $e->getMessage(), __METHOD__ );
            return eZNotificationEventHandler::EVENT_SKIPPED;
        }

        foreach( $userCollection as $participantRole => $collectionItems )
        {
            $templateName = $itemHandler->notificationParticipantTemplate( $participantRole );
            $templatePath = 'design:sensor/mail/' . $templateName;
            if ( !$templateName )
            {
                $templateName = eZCollaborationItemHandler::notificationParticipantTemplate( $participantRole );
                $itemInfo = $itemHandler->attribute( 'info' );
                $typeIdentifier = $itemInfo['type-identifier'];
                $templatePath = 'design:notification/handler/ezcollaboration/view/' . $typeIdentifier . '/' . $templateName;
            }

            $tpl->setVariable( 'collaboration_item', $item );
            $tpl->setVariable( 'collaboration_participant_role', $participantRole );
            $tpl->setVariable( 'collaboration_item_status', $item->attribute( SensorPost::COLLABORATION_FIELD_STATUS ) );
            $tpl->setVariable( 'sensor_post', $helper );
            $tpl->setVariable( 'object', $object );
            $tpl->setVariable( 'node', $node );

            $tpl->fetch( $templatePath );

            $body = $tpl->variable( 'body' );
            $subject = $tpl->variable( 'subject' );

            if ( !empty( $body ) )
            {
                $tpl->setVariable( 'title', $subject );
                $tpl->setVariable( 'content', $body );
                $templateResult = $tpl->fetch( 'design:sensor/mail/mail_pagelayout.tpl' ); 

                
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
                else
                {
                    $parameters['content_type'] = 'text/html';
                }

                $collection = eZNotificationCollection::create(
                    $event->attribute( 'id' ),
                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    eZCollaborationNotificationHandler::TRANSPORT
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $templateResult );
                $collection->store();
                foreach ( $collectionItems as $collectionItem )
                {
                    $skip = false;
                    if ( class_exists( 'OCWhatsAppConnector' ) )
                    {
                        if ( strpos( $collectionItem['email'], '@s.whatsapp.net' ) !== false )
                        {
                            $skip = true;
                        }
                    }
                    if ( !$skip )
                    {
                        $collection->addItem( $collectionItem['email'] );
                    }
                }
            }
        }

        $db->commit();

        return eZNotificationEventHandler::EVENT_HANDLED;
    }

}