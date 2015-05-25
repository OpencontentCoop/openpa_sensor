<?php

class SensorPostTimelineHelper
{
    const TYPE = 0;

    /**
     * @var SensorPost
     */
    protected $post;

    public $text;

    public $creatorId;

    public $message;

    public $messageLink;

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
    }

    final public static function instance( SensorPost $post )
    {
        return new SensorPostTimelineHelper( $post );
    }

    public function add( $status, $parameters = array(), $userId = null )
    {
        $this->text = $this->getText( $status, $parameters );
        if ( $userId == null )
        {
            $this->creatorId = eZUser::currentUserID();
        }
        else
        {
            $this->creatorId = $userId;
        }
        return $this;
    }

    public function store()
    {
        $this->message = eZCollaborationSimpleMessage::create(
            SensorHelper::getSensorCollaborationHandlerTypeString() . '_comment',
            $this->text,
            $this->creatorId
        );

        $this->messageLink = eZCollaborationItemMessageLink::addMessage(
            $this->post->getCollaborationItem(),
            $this->message,
            self::TYPE,
            $this->creatorId
        );

        //l'utente che ha creato il messaggio l'ha già letto
        $timestamp = $this->messageLink->attribute( 'modified' ) + 1;
        $this->post->getCollaborationItem()->setLastRead( $this->creatorId, $timestamp );
        return $this;
    }

    public function items()
    {
        return eZPersistentObject::fetchObjectList(
            eZCollaborationItemMessageLink::definition(),
            null,
            array(
                'collaboration_id' => $this->post->getCollaborationItem()->attribute( 'id' ),
                'message_type' => self::TYPE
            ),
            array( 'created' => 'asc' ),
            null,
            true
        );
    }

    public function count()
    {
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $this->post->getCollaborationItem()->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => self::TYPE
                )
            )
        );
    }

    public function unreadCount()
    {
        $lastRead = 0;
        /** @var eZCollaborationItemStatus $status */
        $status = $this->post->getCollaborationItem()->attribute( 'user_status' );
        if ( $status )
        {
            $lastRead = $status->attribute( 'last_read' );
        }
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $this->post->getCollaborationItem()->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => self::TYPE,
                    'modified' => array( '>', $lastRead )
                )
            )
        );
    }

    protected function getText( $status, $name = null )
    {
        $message = '';
        if ( $status == SensorPost::STATUS_FIXED )
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
        elseif( $status == SensorPost::STATUS_READ )
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
        elseif( $status == SensorPost::STATUS_CLOSED )
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
        elseif( $status == SensorPost::STATUS_ASSIGNED )
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
        elseif( $status == SensorPost::STATUS_REOPENED )
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

    protected function getMessageFromText( $text )
    {
        $parts = explode( ' by ', $text );
        if ( !isset( $parts[1] ) )
        {
            $parts = explode( ' to ', $text );
        }
        if ( isset( $parts[1] ) )
        {
            $name = $parts[1];
            if ( is_numeric( $name ) )
            {
                $user = eZUser::fetch( $name );
                if ( $user instanceof eZUser )
                {
                    $tpl = eZTemplate::factory();
                    $tpl->setVariable( 'sensor_person', $user->attribute( 'contentobject' ) );
                    $name = $tpl->fetch( 'design:content/view/sensor_person.tpl' );
                }
            }
            switch( $parts[0] )
            {
                case '_fixed':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Completata da %name', false, array( '%name' => $name ) );
                    break;

                case '_read':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Letta da %name', false, array( '%name' => $name ) );
                    break;

                case '_closed':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Chiusa da %name', false, array( '%name' => $name ) );
                    break;

                case '_assigned':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Assegnata a %name', false, array( '%name' => $name ) );
                    break;

                case '_reopened':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Riaperta da %name', false, array( '%name' => $name ) );
                    break;
            }
        }
        else
        {
            switch( $parts[0] )
            {
                case '_fixed':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Completata' );
                    break;

                case '_read':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Letta' );
                    break;

                case '_closed':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Chiusa' );
                    break;

                case '_assigned':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Assegnata' );
                    break;

                case '_reopened':
                    $result = ezpI18n::tr( 'openpa_sensor/robot message', 'Riaperta' );
                    break;
            }
        }
    }
}