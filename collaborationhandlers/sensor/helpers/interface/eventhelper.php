<?php

interface SensorPostEventHelperInterface
{
    /**
     * @param string $eventName
     *
     * @return void
     */
    public function createEvent( $eventName );

    /**
     * @param eZNotificationEvent $event
     * @param array $parameters
     *
     * @return int eZNotificationEventHandler constant
     */
    public function handleEvent( eZNotificationEvent $event, array &$parameters );
}