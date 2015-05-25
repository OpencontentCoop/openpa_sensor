<?php

class SensorUserInfo
{
    const FIELD_PREFIX = 'sensoruser_';

    const MAIN_COLLABORATION_GROUP_NAME = 'Sensor';
    const TRASH_COLLABORATION_GROUP_NAME = 'Trash';

    const ANONYMOUS_CAN_COMMENT = false;

    /**
     * @var eZUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $info = array();

    protected function __construct( eZUser $user )
    {
        $this->user = $user;
        $this->refreshInfo();
    }

    /**
     * @return SensorUserInfo
     */
    public static function current()
    {
        return new static( eZUser::currentUser() );
    }

    /**
     * @param eZUser $user
     *
     * @return SensorUserInfo
     */
    public static function instance( eZUser $user )
    {
        return new static( $user );
    }

    /**
     * @return eZUser
     */
    public function user()
    {
        return $this->user;
    }

    public function participateAs( SensorPost $post, $role )
    {
        $link = eZCollaborationItemParticipantLink::fetch(
            $post->getCollaborationItem()->attribute( 'id' ),
            $this->user()->id()
        );

        if ( !$link instanceof eZCollaborationItemParticipantLink )
        {
            $link = eZCollaborationItemParticipantLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $this->user()->id(),
                $role,
                eZCollaborationItemParticipantLink::TYPE_USER
            );
            $link->store();
            $group = $this->sensorCollaborationGroup();
            eZCollaborationItemGroupLink::addItem(
                $group->attribute( 'id' ),
                $post->getCollaborationItem()->attribute( 'id' ),
                $this->user()->id()
            );
            $post->getCollaborationItem()->setIsActive( true, $this->user()->id() );
        }
        else
        {
            $link->setAttribute( 'participant_role', $role );
            $link->sync();
        }
        $GLOBALS['eZCollaborationItemParticipantLinkListCache'] = array();
    }

    public function restoreParticipation( SensorPost $post )
    {
        /** @var eZCollaborationItemGroupLink $group */
        $groupLink = eZPersistentObject::fetchObject(
            eZCollaborationItemGroupLink::definition(),
            null,
            array( 'collaboration_id' => $post->getCollaborationItem()->attribute( 'id' ),
                   'user_id' => $this->user()->id()
            )
        );
        if ( $groupLink instanceof eZCollaborationItemGroupLink )
        {
            $db = eZDB::instance();
            $db->begin();
            $groupLink->remove();
            $sensorGroup = $this->sensorCollaborationGroup();
            $sensorGroupLink = eZCollaborationItemGroupLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $sensorGroup->attribute( 'id' ),
                $this->user()->id()
            );
            $sensorGroupLink->store();
            $db->commit();
        }
    }

    public function trashParticipation( SensorPost $post )
    {
        /** @var eZCollaborationItemGroupLink $group */
        $groupLink = eZPersistentObject::fetchObject(
            eZCollaborationItemGroupLink::definition(),
            null,
            array( 'collaboration_id' => $post->getCollaborationItem()->attribute( 'id' ),
                   'user_id' => $this->user()->id()
            )
        );
        if ( $groupLink instanceof eZCollaborationItemGroupLink )
        {
            $db = eZDB::instance();
            $db->begin();
            $groupLink->remove();
            $trashGroup = $this->trashCollaborationGroup();
            $trashGroupLink = eZCollaborationItemGroupLink::create(
                $post->getCollaborationItem()->attribute( 'id' ),
                $trashGroup->attribute( 'id' ),
                $this->user()->id()
            );
            $trashGroupLink->store();
            $db->commit();
        }
    }

    /**
     * @return eZCollaborationGroup
     */
    protected function sensorCollaborationGroup()
    {
        return $this->getCollaborationGroup( self::MAIN_COLLABORATION_GROUP_NAME );
    }

    protected function trashCollaborationGroup()
    {
        return $this->getCollaborationGroup( self::TRASH_COLLABORATION_GROUP_NAME );
    }

    protected function getCollaborationGroup( $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array(
                'user_id' => $this->user()->id(),
                'title' => $groupName
            )
        );
        if ( !$group instanceof eZCollaborationGroup && $groupName != '' )
        {
            $group = eZCollaborationGroup::instantiate(
                $this->user()->id(),
                $groupName
            );
        }
        return $group;
    }

    protected function getChildCollaborationGroup( $parentGroup, $groupName )
    {
        $group = eZPersistentObject::fetchObject(
            eZCollaborationGroup::definition(),
            null,
            array(
                'user_id' => $this->user()->id(),
                'title' => $groupName
            )
        );
        if ( !$group instanceof eZCollaborationGroup )
        {
            /** @var eZCollaborationGroup $parentGroup */
            $group = eZCollaborationGroup::create( $this->user()->id(), $groupName );
            $parentGroup->addChild( $group );
        }
        return $group;
    }

    public function setModerationMode( $enable = true )
    {
        $this->setInfo( 'moderate', intval( $enable ) );
    }

    public function hasModerationMode()
    {
        return $this->info['moderate'] == 1;
    }

    public function setBlockMode( $enable = true )
    {
        eZUserOperationCollection::setSettings(  $this->user->id(), !$enable, 0 );
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasBlockMode()
    {
        /** @var eZUserSetting $userSetting */
        $userSetting = eZUserSetting::fetch( $this->user->id() );
        return $userSetting->attribute( 'is_enabled' ) == false;
    }

    public function setDenyCommentMode( $enable = true )
    {
        if ( $enable )
        {
            eZPreferences::setValue( 'sensor_deny_comment', 1, $this->user->id() );
        }
        else
        {
            $db = eZDB::instance();
            $db->query( "DELETE FROM ezpreferences WHERE user_id = {$this->user->id()} AND name = 'sensor_deny_comment'" );
        }
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasDenyCommentMode()
    {
        if ( $this->user->isAnonymous() )
        {
            return !self::ANONYMOUS_CAN_COMMENT;
        }
        return eZPreferences::value( 'sensor_deny_comment', $this->user );
    }

    public function setCanBehalfOfMode( $enable = true )
    {
        $role = eZRole::fetchByName( 'Sensor Assistant' );
        if ( $role instanceof eZRole )
        {
            if ( $enable )
            {
                $role->assignToUser( $this->user->id() );
            }
            else
            {
                $role->removeUserAssignment( $this->user->id() );
            }
        }
        eZUser::purgeUserCacheByUserId(  $this->user->id() );
    }

    public function hasCanBehalfOfMode()
    {
        $result = $this->user->hasAccessTo( 'sensor', 'behalf' );
        return $result['accessWord'] != 'no';
    }

    protected function setInfo( $name, $value )
    {
        $this->info[$name] = $value;
        $this->refreshInfo();
    }

    protected function refreshInfo()
    {
        $name = self::FIELD_PREFIX . $this->user->id();
        $siteData = eZSiteData::fetchByName( $name );
        if ( !$siteData instanceof eZSiteData )
        {
            $row = array(
                'name'        => $name,
                'value'       => serialize( self::defaultInfo() )
            );
            $siteData = new eZSiteData( $row );
        }
        else
        {
            $info = unserialize( $siteData->attribute( 'value' ) );
            $siteData->setAttribute( 'value', serialize( array_merge( $info, $this->info ) ) );
        }
        $siteData->store();
        $this->info = unserialize( $siteData->attribute( 'value' ) );
    }

    protected static function defaultInfo()
    {
        return array(
            'moderate' => 0
        );
    }

    public function attributes()
    {
        return array(
            'has_alerts',
            'alerts',
            'has_block_mode',
            'has_deny_comment_mode',
            'has_moderation_mode',
            'has_can_behalf_of_mode'
        );
    }

    public function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

    public function attribute( $name )
    {
        switch( $name )
        {
            case 'has_block_mode':
                return $this->hasBlockMode();
                break;

            case 'has_deny_comment_mode':
                return $this->hasDenyCommentMode();
                break;

            case 'has_moderation_mode':
                return $this->hasModerationMode();
                break;

            case 'has_can_behalf_of_mode':
                return $this->hasCanBehalfOfMode();
                break;

            case 'has_alerts':
                return $this->hasModerationMode();
                break;

            case 'alerts':
                $messages = array();
                if ( $this->hasModerationMode() )
                {
                    $activate = false;
                    if ( eZPersistentObject::fetchObject( eZUserAccountKey::definition(), null,  array( 'user_id' => $this->user->id() ), true ) )
                    {
                        $activate = ' Attiva il tuo profilo per partecipare!';
                    }
                    $messages[] = ezpI18n::tr('openpa_sensor/alerts', 'Il tuo account è ora in moderazione, tutte le tue attività non saranno rese pubbliche.' . $activate );
                }
                return $messages;
                break;

            default:
                eZDebug::writeError( "Attribute $name not found", __METHOD__ );
                return null;
        }
    }
}