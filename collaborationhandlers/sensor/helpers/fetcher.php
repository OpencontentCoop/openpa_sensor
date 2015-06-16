<?php

class SensorPostFetcher
{
    public static $DefaultPostExpirationDaysLimit = 7;

    /**
     * @param array $parameters
     * @param bool $asCount
     * @param array $filters
     *
     * @return SensorHelper[]|array
     */
    protected static function fetchList( $parameters = array(), $asCount, array $filters = array(), eZUser $user = null )
    {
        $parameters = array_merge( array( 'as_object' => true,
                                          'offset' => false,
                                          'parent_group_id' => false,
                                          'limit' => false,
                                          'is_active' => null,
                                          'is_read' => null,
                                          'is_expiring' => null,
                                          'last_change' => null,
                                          'status' => false,
                                          'sort_by' => array( 'modified', false ) ),
            $parameters );
        $asObject = $parameters['as_object'];
        $offset = $parameters['offset'];
        $limit = $parameters['limit'];
        $statusTypes = $parameters['status'];
        $isRead = $parameters['is_read'];
        $isActive = $parameters['is_active'];
        $isExpiring = $parameters['is_expiring'];
        $parentGroupID = $parameters['parent_group_id'];

        $sortText = '';
        if ( !$asCount )
        {
            $sortCount = 0;
            $sortList = $parameters['sort_by'];
            if ( is_array( $sortList ) and
                 count( $sortList ) > 0 )
            {
                if ( count( $sortList ) > 1 and
                     !is_array( $sortList[0] ) )
                {
                    $sortList = array( $sortList );
                }
            }
            $sortingFields = '';
            if ( $sortList !== false )
            {
                foreach ( $sortList as $sortBy )
                {
                    if ( is_array( $sortBy ) and count( $sortBy ) > 0 )
                    {
                        if ( $sortCount > 0 )
                            $sortingFields .= ', ';
                        $sortField = $sortBy[0];
                        switch ( $sortField )
                        {
                            case 'created':
                            {
                                $sortingFields .= 'ci.created';
                            } break;
                            case 'modified':
                            {
                                $sortingFields .= 'ci.modified';
                            } break;
                            default:
                                {
                                eZDebug::writeWarning( 'Unknown sort field: ' . $sortField, __METHOD__ );
                                continue;
                                }
                        }
                        $sortOrder = true; // true is ascending
                        if ( isset( $sortBy[1] ) )
                            $sortOrder = $sortBy[1];
                        $sortingFields .= $sortOrder ? ' ASC' : ' DESC';
                        ++$sortCount;
                    }
                }
            }
            if ( $sortCount == 0 )
            {
                $sortingFields = ' cigl.modified DESC';
            }
            $sortText = "ORDER BY $sortingFields";
        }

        $parentGroupText = '';
        if ( $parentGroupID > 0 )
        {
            $parentGroupText = "cigl.group_id = '$parentGroupID' AND";
        }

        $isReadText = '';
        if ( $isRead !== null )
        {
            $isReadValue = $isRead ? 1 : 0;
            $isReadText = "cis.is_read = '$isReadValue' AND";
        }

        $isActiveText = '';
        if ( $isActive !== null )
        {
            $isActiveValue = $isActive ? 1 : 0;
            $isActiveText = "cis.is_active = '$isActiveValue' AND";
        }

        $lastChangeText = '';
        if ( $lastChangeText !== null )
        {
            //@todo
        }

        $filterText = '';
        if ( !empty( $filters ) )
        {
            $filterText = self::parseFetchFilters( $filters );
        }

        $ownerFilter = array(
            'table' => '',
            'where' => ''
        );
        if ( isset( $filters['owner'] ) && is_numeric( $filters['owner'] ) )
        {
            $ownerId = intval( $filters['owner'] );
            $roleId = eZCollaborationItemParticipantLink::ROLE_OWNER;
            $ownerFilter = array(
                'table' => ", ezcollab_item_participant_link cipl",
                'where' => "ci.id = cipl.collaboration_id AND cipl.participant_id = '{$ownerId}' AND cipl.participant_role = '{$roleId}' AND "
            );
        }

        if ( $user instanceof eZUser )
        {
            $userID = $user->id();
        }
        else
        {
            $userID = eZUser::currentUserID();
        }

        if ( $statusTypes === false )
        {
            $statusTypes = array( eZCollaborationItem::STATUS_ACTIVE,
                                  eZCollaborationItem::STATUS_INACTIVE );
        }
        $statusText = implode( ', ', $statusTypes );

        if ( $asCount )
            $selectText = 'count( ci.id ) as count';
        else
            $selectText = 'ci.*, cis.is_read, cis.is_active, cis.last_read';

        $isExpiringTest = '';
        if ( $isExpiring == true )
        {
            $expiryField = SensorPost::COLLABORATION_FIELD_EXPIRY;
            $secondsLimit = 60 * 60 * 24 * self::$DefaultPostExpirationDaysLimit;
            $nowDate = new DateTime();
            $now = $nowDate->getTimestamp();
            $isExpiringTest = "( CAST( nullif(ci.{$expiryField},'') AS integer ) - {$now} <= $secondsLimit OR {$now} >= CAST( nullif(ci.{$expiryField},'') AS integer ) )  AND ";
        }

        $sql = "SELECT $selectText
                FROM
                       ezcollab_item ci,
                       ezcollab_item_status cis,
                       ezcollab_item_group_link cigl,
                       ezcontentobject co
                       {$ownerFilter['table']}
                WHERE  ci.status IN ( $statusText ) AND
                       $isReadText
                       $isActiveText
                       $isExpiringTest
                       $lastChangeText
                       $filterText
                       {$ownerFilter['where']}
                       ci.id = cis.collaboration_id AND
                       ci.id = cigl.collaboration_id AND
                       ci.data_int1 = co.id AND
                       $parentGroupText
                       cis.user_id = '$userID' AND
                       cigl.user_id = '$userID'
                $sortText";
        //eZDebug::writeNotice($sql);
        $db = eZDB::instance();
        if ( !$asCount )
        {
            $sqlParameters = array();
            if ( $offset !== false and $limit !== false )
            {
                $sqlParameters['offset'] = $offset;
                $sqlParameters['limit'] = $limit;
            }
            $itemListArray = $db->arrayQuery( $sql, $sqlParameters );

            foreach( $itemListArray as $key => $value )
            {
                $itemData =& $itemListArray[$key];
                $statusObject = eZCollaborationItemStatus::create( $itemData['id'], $userID );
                $statusObject->setAttribute( 'is_read', $itemData['is_read'] );
                $statusObject->setAttribute( 'is_active', $itemData['is_active'] );
                $statusObject->setAttribute( 'last_read', $itemData['last_read'] );
                $statusObject->updateCache();
            }
            $returnItemList = eZPersistentObject::handleRows( $itemListArray, 'eZCollaborationItem', $asObject );
            eZDebugSetting::writeDebug( 'sensor-collaboration-item-list', $returnItemList );
            $data = array();
            foreach( $returnItemList as $returnItem )
            {
                try
                {
                    $data[] = SensorHelper::instanceFromCollaborationItem( $returnItem );
                }
                catch( Exception $e )
                {

                }
            }
            return $data;
        }
        else
        {
            $itemCount = (array) $db->arrayQuery( $sql );
            return $itemCount[0]['count'];
        }
    }

    /**
     * @param array $filters
     *
     * @return string
     */
    protected static function parseFetchFilters( array $filters )
    {
        $filterText = '';
        if ( isset( $filters['id'] ) && is_numeric( $filters['id'] ) )
        {
            $filterText .= "ci.data_int1 = " . intval( $filters['id'] ) . " AND ";
        }

        if ( isset( $filters['subject'] ) && !empty( $filters['subject']  ) )
        {
            $itemIdArray = array();
            $solr = new eZSolr();
            $search =$solr->search( '', array(
                    'Filter' => array( 'attr_subject_t:' . $filters['subject'] ),
                    'SearchContentClassID' => array( ObjectHandlerServiceControlSensor::postContentClass()->attribute( 'id' ) ),
                    'SearchSubTreeArray' => array( 1 ) ) );
            if ( $search['SearchCount'] > 0 )
            {
                /** @var eZFindResultNode $item */
                foreach( $search['SearchResult'] as $item )
                {
                    $itemIdArray[] = $item->attribute( 'contentobject_id' );
                }
            }
            if ( !empty( $itemIdArray ) )
            {
                $filterText .= "ci.data_int1 IN (" . implode( ', ', $itemIdArray ) . ") AND ";
            }
            else
            {
                $filterText .= "ci.id = 0  AND ";
            }
        }

        if ( isset( $filters['category'] ) && !empty( $filters['category']  ) )
        {
            $itemIdArray = array();
            $solr = new eZSolr();
            $categoryFilter = array();
            if ( count( $filters['category'] ) > 1 )
            {
                $categoryFilter[] = 'or';
            }
            foreach( $filters['category'] as $category )
            {
                if ( !empty( $category ) )
                    $categoryFilter[] = 'submeta_category___id_si:' . $category;
            }
            if ( !empty( $categoryFilter ) )
            {
                $search =$solr->search( '', array(
                        'Limitation' => array(),
                        'Filter' => $categoryFilter,
                        'SearchContentClassID' => array( ObjectHandlerServiceControlSensor::postContentClass()->attribute( 'id' ) ),
                        'SearchSubTreeArray' => array( 1 ) ) );
                if ( $search['SearchCount'] > 0 )
                {
                    /** @var eZFindResultNode $item */
                    foreach( $search['SearchResult'] as $item )
                    {
                        $itemIdArray[] = $item->attribute( 'contentobject_id' );
                    }
                }
                if ( !empty( $itemIdArray ) )
                {
                    $filterText .= "ci.data_int1 IN (" . implode( ', ', $itemIdArray ) . ") AND ";
                }
                else
                {
                    $filterText .= "ci.id = 0  AND ";
                }
            }
        }

        if ( isset( $filters['creator_id'] ) && !empty( $filters['creator_id'] ) )
        {
            $creatorId = $filters['creator_id'];
            $creatorIdArray = array();
            if ( is_numeric( $creatorId ) )
            {
                $creatorIdArray[] = $creatorId;
            }
            else
            {
                $solr = new eZSolr();
                $search = $solr->search( $creatorId, array(
                        'Limitation' => array(),
                        'SearchContentClassID' => array( 'user', 'sensor_operator' ),
                        'SearchSubTreeArray' => array( 1 ) ) );
                if ( $search['SearchCount'] > 0 )
                {
                    /** @var eZFindResultNode $item */
                    foreach( $search['SearchResult'] as $item )
                    {
                        $creatorIdArray[] = $item->attribute( 'contentobject_id' );
                    }
                }
            }
            if ( !empty( $creatorIdArray ) )
            {
                $filterText .= "ci.creator_id IN (" . implode( ', ', $creatorIdArray ) . ") AND ";
            }
            else
            {
                $filterText .= "ci.id = 0  AND ";
            }
        }

        if ( isset( $filters['creation_range']['from'] ) && !empty( $filters['creation_range']['from'] ) )
        {
            $from = DateTime::createFromFormat( 'd-m-Y', $filters['creation_range']['from'] );
            if ( $from instanceof DateTime )
            {
                if ( isset( $filters['creation_range']['to'] ) && !empty( $filters['creation_range']['to'] ) )
                {
                    $to = DateTime::createFromFormat( 'd-m-Y', $filters['creation_range']['to'] );
                }
                else
                {
                    $to = clone $from;
                }
                $from->setTime( 00, 00 );
                $to->setTime( 23, 59 );
                $filterText .= "ci.created BETWEEN " . $from->format( 'U' ) . " AND " . $to->format( 'U' ) . " AND ";
            }
            else
            {
                $filterText .= "ci.id = 0  AND ";
            }
        }

        return $filterText;
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param $limit
     * @param int $offset
     * @param string $sortBy
     * @param bool $sortOrder
     * @param bool $status
     *
     * @return array|SensorHelper[]
     */
    public static function fetchAllItems( array $filters = array(), eZCollaborationGroup $group, $limit, $offset = 0, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );
        return self::fetchList( $itemParameters, false, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     *
     * @return array|SensorHelper[]
     */
    public static function fetchAllItemsCount( array $filters = array(), eZCollaborationGroup $group )
    {
        $itemParameters = array(
            'parent_group_id' => $group->attribute( 'id' )
        );
        return self::fetchList( $itemParameters, true, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param $limit
     * @param int $offset
     * @param string $sortBy
     * @param bool $sortOrder
     * @param bool $status
     *
     * @return array|SensorHelper[]
     */
    public static function fetchUnreadItems( array $filters = array(), eZCollaborationGroup $group, $limit, $offset = 0, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => false,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );
        return self::fetchList( $itemParameters, false, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     *
     * @return array|SensorHelper[]
     */
    public static function fetchUnreadItemsCount( array $filters = array(), eZCollaborationGroup $group )
    {
        $itemParameters = array(
            'is_read' => false,
            'parent_group_id' => $group->attribute( 'id' )
        );
        return self::fetchList( $itemParameters, true, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param $limit
     * @param int $offset
     * @param string $sortBy
     * @param bool $sortOrder
     * @param bool $status
     *
     * @return array|SensorHelper[]
     */
    public static function fetchActiveItems( array $filters = array(), eZCollaborationGroup $group, $limit, $offset = 0, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => true,
            'is_active' => true,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );
        return self::fetchList( $itemParameters, false, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     *
     * @return array|SensorHelper[]
     */
    public static function fetchActiveItemsCount( array $filters = array(), eZCollaborationGroup $group )
    {
        $itemParameters = array(
            'is_read' => true,
            'is_active' => true,
            'parent_group_id' => $group->attribute( 'id' ),
        );
        return self::fetchList( $itemParameters, true, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param $limit
     * @param int $offset
     * @param string $sortBy
     * @param bool $sortOrder
     * @param bool $status
     *
     * @return array|SensorHelper[]
     */
    public static function fetchUnactiveItems( array $filters = array(), eZCollaborationGroup $group, $limit, $offset = 0, $sortBy = 'modified', $sortOrder = false, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_read' => true,
            'is_active' => false,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );
        return self::fetchList( $itemParameters, false, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     *
     * @return array|SensorHelper[]
     */
    public static function fetchUnactiveItemsCount( array $filters = array(), eZCollaborationGroup $group )
    {
        $itemParameters = array(
            'is_read' => true,
            'is_active' => false,
            'parent_group_id' => $group->attribute( 'id' )
        );
        return self::fetchList( $itemParameters, true, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     * @param $limit
     * @param int $offset
     * @param string $sortBy
     * @param bool $sortOrder
     * @param bool $status
     *
     * @return array|SensorHelper[]
     */
    public static function fetchExpiringItems( array $filters = array(), eZCollaborationGroup $group, $limit, $offset = 0, $sortBy = 'created', $sortOrder = true, $status = false )
    {
        $itemParameters = array(
            'offset' => $offset,
            'limit' => $limit,
            'sort_by' => array( $sortBy, $sortOrder ),
            'is_expiring' => true,
            'is_active' => true,
            'parent_group_id' => $group->attribute( 'id' ),
            'status' => $status
        );
        return self::fetchList( $itemParameters, false, $filters );
    }

    /**
     * @param array $filters
     * @param eZCollaborationGroup $group
     *
     * @return array|SensorHelper[]
     */
    public static function fetchExpiringItemsCount( array $filters = array(), eZCollaborationGroup $group )
    {
        $itemParameters = array(
            'is_expiring' => true,
            'is_active' => true,
            'parent_group_id' => $group->attribute( 'id' )
        );
        return self::fetchList( $itemParameters, true, $filters );
    }

    public static function fetchUserLastPost( SensorUserInfo $userInfo )
    {
        $filters = array( 'creator_id' => $userInfo->user()->id() );
        $itemParameters = array(
            'offset' => 0,
            'limit' => 1,
            'sort_by' => array( 'created', false ),
            'parent_group_id' => $userInfo->sensorCollaborationGroup()->attribute( 'id' ),
            'status' => false
        );
        $data = self::fetchList( $itemParameters, false, $filters, $userInfo->user() );
        if ( count( $data ) > 0 )
        {
           return $data[0];
        }
        return false;
    }

}