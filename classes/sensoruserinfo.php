<?php

class SensorUserInfo
{
    const FIELD_PREFIX = 'sensoruser_';

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