<?php

class SensorPostResponseHelper
{
    const TYPE = 2;

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
        //@todo customize handler
        return new SensorPostResponseHelper( $post );
    }

    public function add( $text )
    {
        if ( trim( $text ) != '' )
        {
            $this->text = $text;
        }
        return $this;
    }

    public function store()
    {
        if ( $this->text !== null )
        {
            if ( $this->creatorId == null )
            {
                $this->creatorId = eZUser::currentUserID();
            }
            $this->message = eZCollaborationSimpleMessage::create(
                SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_comment',
                $this->text,
                $this->creatorId
            );
            $this->message->store();

            $this->messageLink = eZCollaborationItemMessageLink::addMessage(
                $this->post->getCollaborationItem(),
                $this->message,
                self::TYPE,
                $this->creatorId
            );

            //l'utente che ha creato il messaggio l'ha già letto
            $timestamp = $this->messageLink->attribute( 'modified' ) + 1;
            $this->post->getCollaborationItem()->setLastRead( $this->creatorId, $timestamp );
        }
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
}