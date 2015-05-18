<?php

class SensorUserPostRoles
{
    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var SensorUserInfo
     */
    protected $userInfo;

    public function __construct( SensorPost $post, SensorUserInfo $userInfo )
    {
        $this->post = $post;
        $this->userInfo = $userInfo;
    }

    final public static function instance( SensorPost $post, SensorUserInfo $userInfo )
    {
        //@todo customize handler
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
            'can_send_private_message',
            'can_add_observer'
        );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

    public function attribute( $key )
    {
        switch ( $key )
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

            case 'can_send_private_message':
                return $this->canSendPrivateMessage();
                break;

            case 'can_add_observer':
                return $this->canAddObserver();
                break;
        }
        eZDebug::writeError( "Attribute $key not found", get_called_class() );

        return false;
    }

    protected function userIsA( $roleId )
    {
        $list = $this->post->participantIds( $roleId );
        return in_array( $this->userInfo->user()->id(), $list );
    }

    protected function isApprover()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_APPROVER );
    }

    protected function isObserver()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OBSERVER );
    }

    protected function isAuthor()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_AUTHOR );
    }

    protected function isOwner()
    {
        return $this->userIsA( eZCollaborationItemParticipantLink::ROLE_OWNER );
    }

    protected function canDoSomething()
    {
        return ( $this->canAssign()
                 || $this->canAddObserver()
                 || $this->canClose()
                 || $this->canFix()
                 || $this->canAddCategory()
                 || $this->canAddArea()
                 || $this->canModerate() );
    }

    protected function canAddCategory()
    {
        return $this->isApprover();
    }

    protected function canAddArea()
    {
        return $this->isApprover();
    }

    protected function canAssign()
    {
        return
            !$this->post->is( SensorPost::STATUS_CLOSED )
            && (
                $this->isApprover()
                || ( $this->isOwner() && $this->post->is( SensorPost::STATUS_ASSIGNED ) )
            );
    }

    protected function canRespond()
    {
        return $this->canClose();
    }

    protected function canComment()
    {

    }

    protected function canChangePrivacy()
    {

    }

    protected function canModerate()
    {

    }

    protected function canClose()
    {

    }

    protected function canFix()
    {

    }

    protected function canSendPrivateMessage()
    {

    }

    protected function canAddObserver()
    {

    }

}