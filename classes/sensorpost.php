<?php

class SensorPost
{
    const STATUS_WAITING = 0;

    const STATUS_READ = 1;

    const STATUS_ASSIGNED = 2;

    const STATUS_CLOSED = 3;

    const STATUS_FIXED = 4;

    const STATUS_REOPENED = 6;

    protected function __construct( eZCollaborationItem $collaborationItem )
    {

    }

    final public static function instance( eZCollaborationItem $collaborationItem )
    {
        //@todo customize handler
        return new SensorPost( $collaborationItem );
    }

    public function is( $key )
    {
        return false;
    }
}