<?php

class SensorPostMessageHelper
{
    /**
     * @var SensorPost
     */
    protected $post;

    public $text;

    public $creatorId;

    public $receivers;

    public $message;

    public $messageLinks = array();

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
    }

    final public static function instance( SensorPost $post )
    {
        return new SensorPostMessageHelper( $post );
    }

    public function add( $text )
    {
        if ( trim( $text ) != '' )
        {
            $this->text = $text;
        }
        return $this;
    }

    public function to( $receivers )
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function store()
    {
        if ( $this->text !== null && count( $this->receivers ) > 0 )
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
            $this->message->setAttribute( 'data_text2', implode( ',', $this->receivers ) );
            $this->message->store();

            foreach( $this->receivers as $receiver )
            {
                $messageLink = eZCollaborationItemMessageLink::addMessage(
                    $this->post->getCollaborationItem(),
                    $this->message,
                    $receiver,
                    $this->creatorId
                );
                $timestamp = $messageLink->attribute( 'modified' ) + 1;
                $this->post->getCollaborationItem()->setLastRead( $this->creatorId, $timestamp );
                $this->messageLinks[] = $messageLink;
            }
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
                'message_type' => eZUser::currentUserID()
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
                    'message_type' => eZUser::currentUserID()
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
                    'message_type' => eZUser::currentUserID(),
                    'modified' => array( '>', $lastRead )
                )
            )
        );
    }
}