<?php

class SensorPostEventHelper
{
    /**
     * @var SensorPost
     */
    protected $post;

    protected $createNotificationEvents = array();

    public $availableEvents = array(
        'on_create',
        'on_assign',
        'on_fix',
        'on_close',
        'on_make_private',
        'on_make_public',
        'on_moderate',
        'on_add_observer',
        'on_add_category',
        'on_add_area',
        'on_set_expiry',
        'on_add_comment',
        'on_reopen',
        'on_add_response',
        'on_edit_comment',
        'on_edit_message'
    );

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
        $this->createNotificationEvents = array(
            'on_create',
            'on_reopen',
            'on_fix',
            'on_close',
            'on_assign'
        );
    }

    public static function instance( SensorPost $post )
    {
        return new SensorPostEventHelper( $post );
    }

    public function handleEvent( $eventName )
    {
        if ( in_array( $eventName, $this->createNotificationEvents ) )
        {
            $this->post->getCollaborationItem()->createNotificationEvent();
        }
    }
}