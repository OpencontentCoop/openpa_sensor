<?php

class SensorPostActionHandler
{
    /**
     * @var SensorUserPostRoles
     */
    protected $userPostRoles;

    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var SensorUserInfo
     */
    protected $userInfo;

    protected $actions = array();

    protected function __construct( SensorUserPostRoles $userPostRoles )
    {
        $this->userPostRoles = $userPostRoles;
        $this->post = $this->userPostRoles->getPost();
        $this->userInfo = $this->userPostRoles->getUserInfo();

        $this->actions = array(
            'read' => array(
                'call_function' => 'read',
                'parameters' => array()
            ),
            'assign' => array(
                'call_function' => 'assign',
                'check_role' => array( 'can_assign' ),
                'parameters' => array(
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'fix' => array(
                'call_function' => 'fix',
                'check_role' => array( 'can_fix' ),
                'parameters' => array()
            ),
            'close' => array(
                'call_function' => 'close',
                'check_role' => array( 'can_close' ),
                'parameters' => array()
            ),
            'make_private' => array(
                'call_function' => 'makePrivate',
                'check_role' => array( 'can_change_privacy' ),
                'parameters' => array()
            ),
            'moderate' => array(
                'call_function' => 'moderate',
                'check_role' => array( 'can_moderate' ),
                'parameters' => array(
                    'status' => array(
                        'required' => true
                    )
                )
            ),
            'add_observer' => array(
                'call_function' => 'addObserver',
                'check_role' => array( 'can_add_observer' ),
                'parameters' => array(
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'add_category' => array(
                'call_function' => 'addCategory',
                'check_role' => array( 'can_add_category' ),
                'parameters' => array(
                    'category_id' => array(
                        'required' => true
                    ),
                    'assign_to_approver' => array(
                        'required' => false
                    )
                )
            ),
            'add_area' => array(
                'call_function' => 'addArea',
                'check_role' => array( 'can_add_area' ),
                'parameters' => array(
                    'expiry_days' => array(
                        'required' => true
                    )
                )
            ),
            'set_expiry' => array(
                'call_function' => 'setExpiry',
                'check_role' => array( 'can_set_expiry' ),
                'parameters' => array(
                    'expiry_days' => array(
                        'required' => true
                    )
                )
            ),
            'add_comment' => array(
                'call_function' => 'addComment',
                'check_role' => array( 'can_comment' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    )
                )
            ),
            'add_message' => array(
                'call_function' => 'addMessage',
                'check_role' => array( 'can_send_private_message' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    ),
                    'participant_ids' => array(
                        'required' => true
                    )
                )
            ),
            'add_response' => array(
                'call_function' => 'addResponse',
                'check_role' => array( 'can_respond' ),
                'parameters' => array(
                    'text' => array(
                        'required' => true
                    )
                )
            ),
        );
    }

    final public static function instance( SensorUserPostRoles $userPostRoles )
    {
        //@todo make handler customizable
        return new SensorPostActionHandler( $userPostRoles );
    }

    final public function handleAction( $actionName, $actionParameters )
    {
        if ( array_key_exists( $actionName, $this->actions ) )
        {
            if ( isset( $action['check_role'] ) )
            {
                foreach( $action['check_role'] as $role )
                {
                    if ( !$this->userPostRoles->getUserInfo()->attribute( $role ) )
                    {
                        return false;
                    }
                }
            }

            $action = $this->actions[$actionName];
            $arguments = array();

            foreach ( $action['parameters'] as $parameterName => $parameterOptions )
            {
                if ( !isset( $actionParameters[$parameterName] ) && $parameterOptions['required'] == true )
                {
                    throw new InvalidArgumentException(
                        "$parameterName parameter is required for action $actionName"
                    );
                }
                else
                {
                    $arguments[] = isset( $actionParameters[$parameterName] ) ?
                        $actionParameters[$parameterName] : isset( $parameterOptions['default'] ) ?
                            $parameterOptions['default'] : null;
                }
            }

            return call_user_func_array( array( $this, $action['call_function'] ), $arguments );
        }
        else
        {
            throw new BadFunctionCallException( "$actionName action not available" );
        }
    }

    protected function read()
    {
        if ( $this->userPostRoles->isApprover()
             && ( $this->post->isWaiting() || $this->post->isReopened() ) )
        {
            $this->post->setStatus( SensorPost::STATUS_READ );
            $this->post->timelineHelper->add( SensorPost::STATUS_READ )->store();
            $this->post->eventHelper->handleEvent( 'on_read' );
        }
    }

    protected function assign( $participantIds )
    {
        //@todo verificare multi owner
        $isChanged = false;
        $currentOwnerIds = $this->post->getOwners();
        $makeOwnerIds = array_diff( $participantIds, $currentOwnerIds );
        $makeObserverIds = array_intersect( $currentOwnerIds, $participantIds );
        foreach( $makeOwnerIds as $id )
        {
            $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OWNER );
            $isChanged = true;
        }
        if ( $isChanged )
        {
            foreach( $makeObserverIds as $id )
            {
                $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OBSERVER );
            }
            $this->post->setStatus( SensorPost::STATUS_ASSIGNED );
            $this->post->timelineHelper->add( SensorPost::STATUS_ASSIGNED, $makeOwnerIds )->store();
            $this->post->eventHelper->handleEvent( 'on_assign' );
        }
    }

    protected function fix()
    {
        //@todo verificare multi owner
        $this->post->addParticipant( $this->userInfo->user()->id(), SensorUserPostRoles::ROLE_OBSERVER );
        if ( !$this->post->hasOwner() )
        {
            $this->post->setStatus( SensorPost::STATUS_FIXED );
        }
        else
        {
            $this->post->touch();
        }
        $this->post->timelineHelper->add( SensorPost::STATUS_FIXED )->store();
        $this->post->eventHelper->handleEvent( 'on_fix' );
    }

    protected function close()
    {
        $this->post->setStatus( SensorPost::STATUS_CLOSED );
        $this->post->timelineHelper->add( SensorPost::STATUS_CLOSED )->store();
        $this->post->eventHelper->handleEvent( 'on_close' );
    }

    protected function makePrivate()
    {
        ezpEvent::getInstance()->notify( 'sensor/make_private', array( $this->post->getContentObject() ) );
        $this->post->touch();
    }

    protected function moderate( $status )
    {
        ezpEvent::getInstance()->notify( 'sensor/moderate', array( $this->post->getContentObject(), $status ) );
        $this->post->touch();
    }

    protected function addObserver( $participantIds )
    {
        $isChanged = false;
        $currentObserverIds = $this->post->getParticipants( SensorUserPostRoles::ROLE_OBSERVER );
        $makeObserverIds = array_intersect( $currentObserverIds, $participantIds );
        foreach( $makeObserverIds as $id )
        {
            $this->post->addParticipant( $id, SensorUserPostRoles::ROLE_OBSERVER );
            $isChanged = true;
        }
        if ( $isChanged )
        {
            $this->post->eventHelper->handleEvent( 'on_add_observer' );
            $this->post->touch();
        }
    }

    protected function addCategory( array $categoryIdList, $autoAssign = false )
    {
        if ( !empty( $categoryIdList ) )
        {

            if ( $this->post->configParameters['UniqueCategoryCount'] )
            {
                $categoryIdList = $categoryIdList[0];
            }

            $this->post->setCategories( $categoryIdList );
            $this->post->eventHelper->handleEvent( 'on_add_category' );

            if ( $this->post->configParameters['CategoryAutomaticAssign'] || $autoAssign )
            {
                $userIds = $this->post->getApproverIdsByCategory();
                if ( !empty( $userIds ) )
                {
                    $this->assign( $userIds );
                }
            }
            $this->post->touch();
        }
    }

    protected function addArea( $areaIdList )
    {
        if ( empty( $areaList ) )
        {
            $this->post->setAreas( $areaIdList );
            $this->post->eventHelper->handleEvent( 'on_add_area' );
            $this->post->touch();
        }
    }

    protected function setExpiry( $days )
    {
        $value = intval( $days );
        if ( $value > 0 )
        {
            $this->post->setExpiry( $value );
            $this->post->eventHelper->handleEvent( 'on_set_expiry' );
            $this->post->touch();
        }
    }

    protected function addComment( $text )
    {
        $this->post->commentHelper->add( $text )->store();
        $this->post->eventHelper->handleEvent( 'on_add_comment' );
        if ( $this->post->isClosed() && $this->userPostRoles->isAuthor() && $this->post->configParameters['AuthorCanReopen'] )
        {
            $this->post->setStatus( SensorPost::STATUS_REOPENED );
            $this->post->timelineHelper->add( SensorPost::STATUS_REOPENED )->store();
            $this->post->eventHelper->handleEvent( 'on_reopen' );
        }
        else
        {
            $this->post->touch();
        }
    }

    public function addMessage( $text, $privateReceivers = array() )
    {
        $this->post->messageHelper->add( $text )->to( $privateReceivers )->store();
        $this->post->eventHelper->handleEvent( 'on_add_message' );
        $this->post->touch();
    }

    protected function addResponse( $text )
    {
        $this->post->responseHelper->add( $text )->store();
        $this->post->eventHelper->handleEvent( 'on_add_response' );
        $this->post->touch();
    }


}