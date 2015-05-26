<?php

class SensorPostEventHelper
{
    /**
     * @var SensorPost
     */
    protected $post;

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
    }

    final public static function instance( SensorPost $post )
    {
        //@todo customize handler
        return new SensorPostEventHelper( $post );
    }

    public function handleEvent( $eventName )
    {
        //on_create
        //on_assign
        //on_fix
        //on_close
        //on_add_observer
        //on_add_category
        //on_add_area
        //on_set_expiry
        //on_add_comment
        //on_add_response
    }
}