<?php

class SensorPostEventHelper implements SensorPostEventHelperInterface
{
    /**
     * @var SensorPost
     */
    protected $post;

    public $availableEvents = array(
        'on_create',
        'on_update',
        'on_assign',
        'on_fix',
        'on_force_fix',
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
        'on_add_message',
        'on_add_response',
        'on_edit_comment',
        'on_edit_message',
        'on_restore',
        'on_remove'
    );

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
    }

    public static function instance( SensorPost $post )
    {
        return new SensorPostEventHelper( $post );
    }

    public function createEvent( $eventName )
    {
        foreach( SensorNotificationHelper::postNotificationTypes() as $type )
        {
            if ( $type['identifier'] ==  $eventName )
            {
                $this->post->getCollaborationItem()->createNotificationEvent( $eventName );
            }
        }
    }

    public function handleEvent( eZNotificationEvent $event, array &$parameters )
    {
        $notificationHelper = SensorNotificationHelper::instance( $this->post );
        return $notificationHelper->handleEvent( $event, $parameters );
    }
}