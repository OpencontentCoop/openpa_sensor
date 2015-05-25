<?php

class SensorPost
{
    const STATUS_WAITING = 0;

    const STATUS_READ = 1;

    const STATUS_ASSIGNED = 2;

    const STATUS_CLOSED = 3;

    const STATUS_FIXED = 4;

    const STATUS_REOPENED = 6;

    const COLLABORATION_FIELD_OBJECT_ID = 'data_int1';

    const COLLABORATION_FIELD_LAST_CHANGE = 'data_int2';

    const COLLABORATION_FIELD_STATUS = 'data_int3';

    const COLLABORATION_FIELD_HANDLER = 'data_text1';

    const COLLABORATION_FIELD_EXPIRY = 'data_text3';

    const SITE_DATA_FIELD_PREFIX = 'sensorpost_';

    /**
     * @var eZCollaborationItem
     */
    protected $collaborationItem;

    /**
     * @var eZCollaborationItemParticipantLink[]
     */
    protected $participantList;

    /**
     * @var array
     */
    public $configParameters;

    /**
     * @var SensorPostEventHelper
     */
    public $eventHelper;

    /**
     * @var SensorPostTimelineHelper
     */
    public $timelineHelper;

    /**
     * @var SensorPostCommentHelper
     */
    public $commentHelper;

    /**
     * @var SensorPostMessageHelper
     */
    public $messageHelper;

    /**
     * @var SensorPostResponseHelper
     */
    public $responseHelper;

    protected function __construct( eZCollaborationItem $collaborationItem, array $configParameters )
    {
        $this->collaborationItem = $collaborationItem;
        $this->participantList = eZCollaborationItemParticipantLink::fetchParticipantList(
            array(
                'item_id' => $this->collaborationItem->attribute( 'id' ),
                'limit' => 100
            )
        );
        $this->configParameters = $configParameters;
        $this->eventHelper = SensorPostEventHelper::instance( $this );
        $this->timelineHelper = SensorPostTimelineHelper::instance( $this );
        $this->commentHelper = SensorPostCommentHelper::instance( $this );
        $this->messageHelper = SensorPostMessageHelper::instance( $this );
        $this->responseHelper = SensorPostResponseHelper::instance( $this );
    }

    final public static function instance( eZCollaborationItem $collaborationItem, $configParameters = array() )
    {
        return new SensorPost( $collaborationItem, $configParameters );
    }

    public function restoreFormTrash()
    {
        $participants = $this->getParticipants();
        foreach( $participants as $participantID )
        {
            $this->restoreParticipant( $participantID );
        }
    }

    public function moveToTrash()
    {
        $participants = $this->getParticipants();
        foreach( $participants as $participantID )
        {
            $this->trashParticipant( $participantID );
        }
    }

    public function attributes()
    {
        return array(
            'collaboration_item',
            'object',
            'current_status',
            'current_owner',
            'participants',
            'has_owner',
            'owner_id',
            'owner_ids',
            'owner_name',
            'owner_names',
            'expiring_date',
            'expiration_days',
            'resolution_time'
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
            case 'collaboration_item':
                return $this->getCollaborationItem();
                break;

            case 'object':
                return $this->getContentObject();
                break;

            case 'current_status':
                return $this->getCurrentStatus();
                break;

            case 'current_owner':
                return $this->getMainOwnerText();
                break;

            case 'participants':
                return $this->getParticipants( null, true );
                break;

            case 'has_owner':
                return $this->hasOwner();
                break;

            case 'owner_id':
                return $this->getMainOwner();
                break;

            case 'owner_ids':
                return $this->getOwners();
                break;

            case 'owner_name':
                return $this->getMainOwnerName();
                break;

            case 'owner_names':
                return $this->getOwnerNames();
                break;

            case 'expiring_date':
                return $this->getExpiringDate();
                break;

            case 'expiration_days':
                return $this->getExpirationDays();
                break;

            case 'resolution_time':
                return $this->getResolutionTime();
                break;

        }
        eZDebug::writeError( "Attribute $key not found", get_called_class() );
        return false;
    }

    public function getCollaborationItem()
    {
        return $this->collaborationItem;
    }

    public function getContentObject()
    {
        $objectId = $this->collaborationItem->attribute( self::COLLABORATION_FIELD_OBJECT_ID );
        $object = eZContentObject::fetch( $objectId );
        if ( $object instanceof eZContentObject )
        {
            return $object;
        }
        return null;
    }

    public function getContentObjectAttribute( $identifier )
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            $dataMap = $object->attribute( 'data_map' );
            if ( isset( $dataMap[$identifier] ) )
            {
                return $dataMap[$identifier];
            }
        }
        return false;
    }

    public function setContentObjectAttribute( $identifier, $stringValue )
    {
        $attribute = $this->getContentObjectAttribute( $identifier );
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $attribute->fromString( stringValue );
            $attribute->store();
            eZContentCacheManager::clearContentCacheIfNeeded( $this->getContentObject()->attribute( 'id' ) );
            eZSearch::addObject( $this->getContentObject(), true );
            return true;
        }
        return false;
    }

        public function getCurrentStatus()
    {
        return $this->collaborationItem->attribute( self::COLLABORATION_FIELD_STATUS );
    }

    public function isWaiting()
    {
        return $this->is( SensorPost::STATUS_WAITING );
    }

    public function isRead()
    {
        return $this->is( SensorPost::STATUS_READ );
    }

    public function isAssigned()
    {
        return $this->is( SensorPost::STATUS_ASSIGNED );
    }

    public function isClosed()
    {
        return $this->is( SensorPost::STATUS_CLOSED );
    }

    public function isFixed()
    {
        return $this->is( SensorPost::STATUS_FIXED );
    }

    public function isReopened()
    {
        return $this->is( SensorPost::STATUS_REOPENED );
    }

    /**
     * @param null|int $byRole
     * @param bool $asObject
     *
     * @return int[]|eZContentObject[]
     */
    public function getParticipants( $byRole = null, $asObject = false )
    {
        $participantIds = array();
        foreach( $this->participantList as $participant )
        {
            if ( $byRole !== null )
            {
                if ( $byRole == $participant->attribute( 'participant_role' ) )
                {
                    $participantIds[] = $participant->attribute( 'participant_id' );
                }
            }
            else
            {
                $participantIds[] = $participant->attribute( 'participant_id' );
            }
        }
        if ( $asObject )
        {
            $participants = array();
            foreach( $participantIds as $participantId )
            {
                $participant = eZContentObject::fetch( $participantId );
                if ( $participant instanceof eZContentObject )
                {
                    $participants[] = $participant;
                }
            }
            return $participants;
        }
        else
        {
            return $participantIds;
        }
    }

    public function hasOwner()
    {
        return count( $this->getParticipants( SensorUserPostRoles::ROLE_OWNER ) ) > 0;
    }

    public function getMainOwner( $asObject = false )
    {
        if ( $this->hasOwner() )
        {
            $ownerIds = $this->getParticipants( SensorUserPostRoles::ROLE_OWNER );
            $ownerId = array_shift( $$ownerIds );
            if ( $asObject )
            {
                return eZContentObject::fetch( $ownerId );
            }
            return $ownerId;
        }
        return null;
    }

    public function getMainOwnerText()
    {
        $text = '';
        $mainOwner = $this->getMainOwner( true );
        if ( $mainOwner instanceof eZContentObject )
        {
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'sensor_person', $mainOwner );
            $text = $tpl->fetch( 'design:content/view/sensor_person.tpl' );
        }
        return $text;
    }

    public function getMainOwnerName()
    {
        $name = false;
        $mainOwner = $this->getMainOwner( true );
        if ( $mainOwner instanceof eZContentObject )
        {
            $name = $mainOwner->attribute( 'name' );
        }
        return $name;
    }

    public function getOwners( $asObject = false )
    {
        if ( $this->hasOwner() )
        {
            $ownerIds = $this->getParticipants( SensorUserPostRoles::ROLE_OWNER );
            if ( $asObject )
            {
                $owners = array();
                foreach( $ownerIds as $ownerId )
                {
                    $owner = eZContentObject::fetch( $ownerId );
                    if ( $owner instanceof eZContentObject )
                    {
                        $owners[] = $owner;
                    }
                }
                return $owners;
            }
            return $ownerIds;
        }
        return null;
    }

    public function getOwnerNames()
    {
        $names = array();
        $owners = $this->getOwners( true );
        foreach( $owners as $owner )
        {
            $names[] = $owner->attribute( 'name' );
        }
        return $names;
    }

    public function getExpiringDate()
    {
        $data = array(
            'text' => null,
            'timestamp' => null,
            'label' => 'default'
        );
        try
        {
            $date = new DateTime();
            $expiryTimestamp = intval( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_EXPIRY ) );
            if ( $expiryTimestamp <= 15 ) //bc compat
            {
                $expiryTimestamp = self::expiryTimestamp( $this->collaborationItem->attribute( 'created' ) );
            }
            $date->setTimestamp( $expiryTimestamp );
            if ( $date instanceof DateTime )
            {
                $data['timestamp'] = $date->format( 'U' );
                $diff = self::getDateDiff( $date );
                /** @var DateInterval $interval */
                $interval = $diff['interval'];
                $format = $diff['format'];
                $text = ezpI18n::tr( 'openpa_sensor/expiring', 'Scade fra' );
                if ( $interval->invert )
                {
                    $text = ezpI18n::tr( 'openpa_sensor/expiring', 'Scaduto da' );
                    $data['label'] = 'danger';
                }
                $data['text'] = $text . ' ' . $interval->format( $format );
            }
            else
            {
                throw new Exception( "Invalid creation date in collaboration item" );
            }
        }
        catch( Exception $e )
        {
            $data['text'] = $e->getMessage();
        }
        return $data;
    }

    public function getResolutionTime()
    {
        $data = array(
            'text' => null,
            'timestamp' => null
        );
        if ( $this->isClosed() )
        {
            $responses = $this->attribute( 'robot_messages' ); //@todo
            if ( count( $responses ) >= 1 )
            {
                $response = array_pop( $responses );
                $start = new DateTime();
                $start->setTimestamp( $this->collaborationItem->attribute( "created" ) );
                $end = new DateTime();
                $end->setTimestamp( $response->attribute( "created" ) );
                if ( $start instanceof DateTime )
                {
                    $diff = self::getDateDiff( $start, $end );
                    $interval = $diff['interval'];
                    $format = $diff['format'];
                    if ( $interval instanceof DateInterval )
                    {
                        $data['text'] = $interval->format( $format );
                    }
                    $data['timestamp'] = $end->format( 'U' );
                }
            }
        }
        return $data;
    }

    public function getExpirationDays()
    {
        $expiryTimestamp = intval( $this->collaborationItem->attribute( self::COLLABORATION_FIELD_EXPIRY ) );
        if ( $expiryTimestamp <= 15 ) //bc compat
        {
            return $this->configParameters['DefaultPostExpirationDaysInterval'];
        }
        else
        {
            $start = new DateTime();
            $start->setTimestamp( $this->collaborationItem->attribute( 'created' ) );
            $end = new DateTime();
            $end->setTimestamp( $expiryTimestamp );
            $diff = $end->diff( $start );
            if ( $diff instanceof DateInterval )
            {
                return $diff->days;
            }
        }
        return -1;
    }

    public function deactivateParticipants()
    {
        foreach( $this->getParticipants() as $id )
        {
            $this->collaborationItem->setIsActive( false, $id );
        }
    }

    public function activateParticipants()
    {
        foreach( $this->getParticipants() as $id )
        {
            $this->collaborationItem->setIsActive( true, $id );
        }
    }

    public function storeActivesParticipants()
    {
        $activeParticipants = $this->getActivesParticipants();
        $content = $this->collaborationItem->content();

        $name = self::SITE_DATA_FIELD_PREFIX . $content['content_object_id'];
        $siteData = eZSiteData::fetchByName( $name );

        $removeIfNeeded = false;

        if ( !$siteData instanceof eZSiteData)
        {
            $row = array(
                'name' => $name,
                'value' => serialize( array() )
            );
            $siteData = new eZSiteData( $row );
            $currentActiveParticipants = array();
        }
        else
        {
            $currentActiveParticipants = unserialize( $siteData->attribute( 'value' ) );
            $removeIfNeeded = true;
        }

        if ( count( $activeParticipants ) > 0 )
        {
            if ( serialize( $currentActiveParticipants ) != serialize( $activeParticipants ) )
            {
                $siteData->setAttribute( 'value', serialize( $activeParticipants ) );
                $siteData->store();
            }
        }
        elseif( $removeIfNeeded )
        {
            $siteData->remove();
        }
    }

    public static function getStoredActivesParticipantsByPostId( $id )
    {
        $name = self::SITE_DATA_FIELD_PREFIX . $id;
        $siteData = eZSiteData::fetchByName( $name );
        $activeParticipants = array();
        if ( $siteData instanceof eZSiteData)
        {
            $activeParticipants = unserialize( $siteData->attribute( 'value' ) );
        }
        return $activeParticipants;
    }

    public function touch()
    {
        $this->setStatus();
    }

    public function setAreas( $areaIdList )
    {
        $areaIdList = ezpEvent::getInstance()->filter( 'sensor/set_areas',  $areaIdList );
        $areasString = implode( '-', $areaIdList );
        return $this->setContentObjectAttribute( 'area', $areasString );
    }

    public function setCategories( $categoryIdList )
    {
        $categoryIdList = ezpEvent::getInstance()->filter( 'sensor/set_categories',  $categoryIdList );
        $categoryString = implode( '-', $categoryIdList );
        return $this->setContentObjectAttribute( 'category', $categoryString );
    }

    public function getApproverIdsByCategory()
    {
        $userIds = array();
        $category = $this->getContentObjectAttribute( 'category' );
        if ( $category instanceof eZContentObjectAttribute )
        {
            $categories = explode( '-', $category->toString() );
            foreach( $categories as $categoryId )
            {
                $category = eZContentObject::fetch( $categoryId );
                if ( $category instanceof eZContentObject )
                {
                    /** @var eZContentObjectAttribute[] $categoryDataMap */
                    $categoryDataMap = $category->attribute( 'data_map' );
                    if ( isset( $categoryDataMap['approver'] ) )
                    {
                        $userIds = array_merge( $userIds, explode( '-', $categoryDataMap['approver']->toString() ) );
                    }
                }
            }
        }
        $userIds = ezpEvent::getInstance()->filter( 'sensor/user_by_categories', $userIds );
        return $userIds;
    }

    public function setExpiry( $value )
    {
        $this->collaborationItem->setAttribute(
            self::COLLABORATION_FIELD_EXPIRY,
            self::expiryTimestamp( $this->collaborationItem->attribute( 'created' ), $value )
        );
        $this->collaborationItem->store();
    }

    public function setStatus( $status = null )
    {
        $timestamp = time();
        $object = $this->getContentObject();
        if ( $status !== null )
        {
            $this->collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_STATUS, $status );
            $this->collaborationItem->setAttribute( 'modified', $timestamp );
            $this->collaborationItem->setAttribute( SensorPost::COLLABORATION_FIELD_LAST_CHANGE, $timestamp );

            if ( $status == SensorPost::STATUS_CLOSED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_INACTIVE );
                $this->deactivateParticipants();
            }
            elseif ( $status == SensorPost::STATUS_WAITING )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                $this->activateParticipants();
            }
            elseif ( $status == SensorPost::STATUS_REOPENED )
            {
                $this->collaborationItem->setAttribute( 'status', eZCollaborationItem::STATUS_ACTIVE );
                $this->activateParticipants();
            }
            $this->collaborationItem->sync();

        }
        if ( $object instanceof eZContentObject )
        {
            ezpEvent::getInstance()->notify( 'sensor/set_status', array( $object, $status ) );

            $object->setAttribute( 'modified', $timestamp );
            $object->store();
            $this->storeActivesParticipants();
            eZContentCacheManager::clearContentCacheIfNeeded( $object->attribute( 'id' ) );
        }
    }

    public function addParticipant( $participantID, $participantRole )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->participateAs( $this, $participantRole );
            ezpEvent::getInstance()->notify( 'sensor/add_participant', array( $user, $participantRole, $this ) );
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    public function restoreParticipant( $participantID )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->restoreParticipation( $this );
            ezpEvent::getInstance()->notify( 'sensor/restore_participant', array( $user, $this ) );
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    public function trashParticipant( $participantID )
    {
        $user = eZUser::fetch( $participantID );
        if ( $user instanceof eZUser )
        {
            $sensorUserInfo = SensorUserInfo::instance( $user );
            $sensorUserInfo->trashParticipation( $this );
            ezpEvent::getInstance()->notify( 'sensor/trash_participant', array( $user, $this ) );
        }
        else
        {
            throw new InvalidArgumentException( "User $participantID not found" );
        }
    }

    protected function getActivesParticipants()
    {
        $activeParticipants = array();
        $conditions = array(
            'collaboration_id' => $this->collaborationItem->attribute( 'id' ),
            'is_active' => 1
        );

        $resources = eZPersistentObject::fetchObjectList(
            eZCollaborationItemStatus::definition(),
            array( 'user_id' ),
            $conditions,
            null,
            null,
            false
        );

        foreach( $resources as $row )
        {
            $activeParticipants[] = $row['user_id'];
        }
        sort( $activeParticipants );
        return $activeParticipants;
    }

    protected function is( $key )
    {
        return $this->collaborationItem->attribute( self::COLLABORATION_FIELD_STATUS ) == $key;
    }

    protected function expiryTimestamp( $creationTimestamp, $days = null )
    {
        if ( $days === null )
        {
            $days = $this->configParameters['DefaultPostExpirationDaysInterval'];
        }
        $creation = new DateTime();
        $creation->setTimestamp( $creationTimestamp );
        $creation->add( self::expiringInterval( $days ) );
        return $creation->format( 'U' );
    }

    protected static function expiringInterval( $days )
    {
        $expiringIntervalString = 'P' . intval( $days ) . 'D';
        $expiringInterval = new DateInterval( $expiringIntervalString );
        if ( !$expiringInterval instanceof DateInterval )
        {
            throw new Exception( "Invalid interval {$expiringIntervalString}" );
        }
        return $expiringInterval;
    }

    public static function getDateDiff( $start, $end = null )
    {
        if ( !( $start instanceof DateTime ) )
        {
            $start = new DateTime( $start );
        }

        if ( $end === null )
        {
            $end = new DateTime();
        }

        if ( !( $end instanceof DateTime ) )
        {
            $end = new DateTime( $start );
        }

        $interval = $end->diff( $start );
        $translate = function ( $nb, $str )
        {
            $string = $nb > 1 ? $str . 's' : $str;
            switch ( $string )
            {
                case 'year';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'anno' );
                    break;
                case 'years';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'anni' );
                    break;
                case 'month';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'mese' );
                    break;
                case 'months';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'mesi' );
                    break;
                case 'day';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'giorno' );
                    break;
                case 'days';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'giorni' );
                    break;
                case 'hour';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'ora' );
                    break;
                case 'hours';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'ore' );
                    break;
                case 'minute';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'minuto' );
                    break;
                case 'minutes';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'minuti' );
                    break;
                case 'second';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'secondo' );
                    break;
                case 'seconds';
                    $string = ezpI18n::tr( 'openpa_sensor/expiring', 'secondi' );
                    break;
            }
            return $string;
        };

        $format = array();
        if ( $interval->y !== 0 )
        {
            $format[] = "%y " . $translate( $interval->y, "year" );
        }
        if ( $interval->m !== 0 )
        {
            $format[] = "%m " . $translate( $interval->m, "month" );
        }
        if ( $interval->d !== 0 )
        {
            $format[] = "%d " . $translate( $interval->d, "day" );
        }
        if ( $interval->h !== 0 )
        {
            $format[] = "%h " . $translate( $interval->h, "hour" );
        }
        if ( $interval->i !== 0 )
        {
            $format[] = "%i " . $translate( $interval->i, "minute" );
        }
        if ( $interval->s !== 0 )
        {
            if ( !count( $format ) )
            {
                return ezpI18n::tr( 'openpa_sensor/expiring', 'meno di un minuto' );
            }
            else
            {
                $format[] = "%s " . $translate( $interval->s, "second" );
            }
        }

        // We use the two biggest parts
        if ( count( $format ) > 1 )
        {
            $format = array_shift( $format ) . " " . ezpI18n::tr( 'openpa_sensor/expiring', 'e' ) . " " . array_shift( $format );
        }
        else
        {
            $format = array_pop( $format );
        }

        return array( 'interval' => $interval, 'format' => $format );
    }

    public function delete()
    {
        $itemId = $this->collaborationItem->attribute( 'id' );
        self::deleteCollaborationStuff( $itemId );
    }

    public static function deleteCollaborationStuff( $itemId )
    {
        $db = eZDB::instance();
        $db->begin();
        $db->query( "DELETE FROM ezcollab_item WHERE id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );
        $res = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        foreach( $res as $r )
        {
            $db->query( "DELETE FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $db->query( "DELETE FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $db->query( "DELETE FROM ezcollab_item_status WHERE collaboration_id = $itemId" );
        $db->commit();
    }

    public static function getCollaborationStuff( $itemId )
    {
        $db = eZDB::instance();
        $res['ezcollab_item'] = $db->arrayQuery( "SELECT * FROM ezcollab_item WHERE id = $itemId" );
        $res['ezcollab_item_group_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_group_link WHERE collaboration_id = $itemId" );
        $tmp = $db->arrayQuery( "SELECT message_id FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_simple_message'] = array();
        foreach( $tmp as $r )
        {
            $res['ezcollab_simple_message'][] = $db->arrayQuery( "SELECT * FROM ezcollab_simple_message WHERE id = {$r['message_id']}" );
        }
        $res['ezcollab_item_message_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_message_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_participant_link'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_participant_link WHERE collaboration_id = $itemId" );
        $res['ezcollab_item_status'] = $db->arrayQuery( "SELECT * FROM ezcollab_item_status WHERE collaboration_id = $itemId" );
        return $res;
    }

}