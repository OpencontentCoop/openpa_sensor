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
                'notification-types' => SensorNotificationHelper::notificationTypes(),
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
        $helper = self::helper( $collaborationItem );
        return $helper->attribute( 'human_count' );
    }

    /**
     * @param eZCollaborationItem $collaborationItem
     * @return int
     */
    function unreadMessageCount( $collaborationItem )
    {
        $helper = self::helper( $collaborationItem );
        return $helper->attribute( 'human_unread_count' );
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


    /**
     * @param eZNotificationEvent $event
     * @param eZCollaborationItem $item
     * @param array $parameters
     *
     * @return int
     */
    static function handleCollaborationEvent( $event, $item, &$parameters )
    {
        $helper = self::helper( $item );
        if ( $helper  )
            return $helper->currentSensorPost->eventHelper->handleEvent( $event, $parameters );
        else
            return eZNotificationEventHandler::EVENT_SKIPPED;
    }

}