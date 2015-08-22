<?php

class TrentoSensorPostActionHandler extends SensorPostActionHandler
{
    /**
     * Aggiunta di un'azione dedicata per la lettura del post da ws
     * @see SensorPostActionHandler::__construct
     */
    protected function __construct( SensorUserPostRoles $userPostRoles )
    {
        parent::__construct( $userPostRoles );
        $this->actions['ws_read'] = array(
            'call_function' => 'wsRead',
            'parameters' => array()
        );
    }
    
    /**
     * Azione di lettura da ws
     */
    public function wsRead()
    {
        $this->post->getCollaborationItem()->setLastRead();
        if ( $this->userPostRoles->isApprover()
             && ( $this->post->isWaiting() || $this->post->isReopened() ) )
        {
            $this->post->setStatus( SensorPost::STATUS_READ );
            $this->post->timelineHelper->add( SensorPost::STATUS_READ )->store();
            $this->post->eventHelper->createEvent( 'on_read' );
        }
    }
    
    /**
     * Override dell'azione di default per evitare il cambio stato
     */
    public function read()
    {
        $this->post->getCollaborationItem()->setLastRead();
        //if ( $this->userPostRoles->isApprover()
        //     && ( $this->post->isWaiting() || $this->post->isReopened() ) )
        //{
        //    $this->post->setStatus( SensorPost::STATUS_READ );
        //    $this->post->timelineHelper->add( SensorPost::STATUS_READ )->store();
        //    $this->post->eventHelper->createEvent( 'on_read' );
        //}        
    }
}
