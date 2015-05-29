<?php

class SensorUserPostRoles
{
    const ROLE_STANDARD = 1;

    const ROLE_OBSERVER = 2;

    const ROLE_OWNER = 3;

    const ROLE_APPROVER = 4;

    const ROLE_AUTHOR = 5;

    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var SensorUserInfo
     */
    protected $userInfo;

    /**
     * @var SensorPostActionHandler
     */
    public $actionHandler;

    protected function __construct( SensorPost $post, SensorUserInfo $userInfo )
    {
        $this->post = $post;
        $this->userInfo = $userInfo;
        $this->actionHandler = SensorPostActionHandler::instance( $this );
    }

    final public static function instance( SensorPost $post, SensorUserInfo $userInfo )
    {
        return new SensorUserPostRoles( $post, $userInfo );
    }

    public function attributes()
    {
        return array(
            'can_do_something',
            'can_add_category',
            'can_add_area',
            'can_assign',
            'can_respond',
            'can_comment',
            'can_change_privacy',
            'can_moderate',
            'can_close',
            'can_fix',
            'can_force_fix',
            'can_send_private_message',
            'can_add_observer',
            'can_set_expiry'
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'can_do_something':
                return $this->canDoSomething();
                break;

            case 'can_add_category':
                return $this->canAddCategory();
                break;

            case 'can_add_area':
                return $this->canAddArea();
                break;

            case 'can_assign':
                return $this->canAssign();
                break;

            case 'can_respond':
                return $this->canRespond();
                break;

            case 'can_comment':
                return $this->canComment();
                break;

            case 'can_change_privacy':
                return $this->canChangePrivacy();
                break;

            case 'can_moderate':
                return $this->canModerate();
                break;

            case 'can_close':
                return $this->canClose();
                break;

            case 'can_fix':
                return $this->canFix();
                break;

            case 'can_force_fix':
                return $this->canForceFix();
                break;

            case 'can_send_private_message':
                return $this->canSendPrivateMessage();
                break;

            case 'can_add_observer':
                return $this->canAddObserver();
                break;

            case 'can_set_expiry':
                return $this->canSetExpiry();
                break;

        }
        return false;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function handleAction( $actionName, $actionParameters = array() )
    {
        $this->actionHandler->handleAction( $actionName, $actionParameters );
    }

    public function isApprover()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    public function isObserver()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OBSERVER );
    }

    public function isAuthor()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_AUTHOR );
    }

    public function isOwner()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER );
    }

    protected function userIsA( $roleId )
    {
        $list = $this->post->getParticipants( $roleId );
        return in_array( $this->userInfo->user()->id(), $list );
    }

    public function canDoSomething()
    {
        return (
            $this->canAssign()
            || $this->canAddObserver()
            || $this->canClose()
            || $this->canFix()
            || $this->canAddCategory()
            || $this->canAddArea()
            || $this->canModerate()
            || $this->canSetExpiry()
            || $this->canForceFix()
        );
    }

    public function canAddCategory()
    {
        return $this->isApprover();
    }

    public function canAddArea()
    {
        return $this->isApprover();
    }

    public function canAssign()
    {
        return
            !$this->post->isClosed()
            && (
                $this->isApprover()
                || ( $this->isOwner() && $this->post->isAssigned() )
            );
    }

    public function canRespond()
    {
        return $this->canClose();
    }

    public function canComment()
    {
        return !(bool)$this->userInfo->hasDenyCommentMode() && $this->post->commentsIsOpen();

    }

    public function canChangePrivacy()
    {
        return $this->isApprover();
    }

    public function canSetExpiry()
    {
        return !$this->post->isClosed()
               && $this->isApprover();
    }

    public function canModerate()
    {
        return $this->isApprover();
    }

    public function canClose()
    {
        return $this->isApprover()
               && !$this->post->isClosed()
               && !$this->post->isAssigned();
    }

    public function canFix()
    {
        return $this->isOwner() && $this->post->isAssigned();
    }

    public function canForceFix()
    {
        return $this->post->isAssigned() && $this->isApprover();
    }

    public function canSendPrivateMessage()
    {
        return $this->isOwner()
               || $this->isObserver()
               || $this->isApprover();
    }

    public function canAddObserver()
    {
        return !$this->post->isClosed() && $this->isApprover();
    }

}