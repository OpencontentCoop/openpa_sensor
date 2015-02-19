<?php

class SurveyHelper
{
    /**
     * @var eZContentObject
     */
    protected $object;

    /**
     * @var eZContentObjectAttribute
     */
    protected $surveyAttribute;

    /**
     * @var eZSurvey
     */
    protected $survey;

    /**
     * @var array
     */
    protected $surveyValidation;

    /**
     * @var integer[]
     */
    protected $surveyVersionIds;

    /**
     * @var eZUSer
     */
    protected $user;

    public static function instance( $contentObjectId, $contentObjectAttributeId = null )
    {
        //@todo
        return new SurveyHelper( $contentObjectId, $contentObjectAttributeId );
    }

    protected function __construct( $contentObjectId, $contentObjectAttributeId = null )
    {
        $this->object = eZContentObject::fetch( $contentObjectId );
        if ( !$this->object instanceof eZContentObject )
        {
            throw new Exception( "Object $contentObjectId not found" );
        }
        $surveyAttribute = false;
        foreach( $this->object->attribute( 'data_map' ) as $attribute )
        {
            if ( $contentObjectAttributeId
                 && $attribute->attribute( 'id' ) == $contentObjectAttributeId
                 && $attribute->attribute( 'data_type_string' ) == 'ezsurvey' )
            {
                $surveyAttribute = $attribute;
                break;
            }
            elseif( $attribute->attribute( 'data_type_string' ) == 'ezsurvey' )
            {
                $surveyAttribute = $attribute;
                break;
            }
        }
        if ( $surveyAttribute instanceof eZContentObjectAttribute )
        {
            $this->surveyAttribute = $surveyAttribute;
            $surveyContent = $this->surveyAttribute->attribute( 'content' );
            $this->survey = $surveyContent['survey'];
            $this->surveyValidation = $surveyContent['survey_validation'];

            $versions = eZPersistentObject::fetchObjectList(
                eZSurvey::definition(),
                array( 'id', 'contentobjectattribute_version' ),
                array( 'contentobject_id' => $this->object->attribute( 'id' ), 'contentobjectattribute_id' => $this->surveyAttribute->attribute( 'id' ) ),
                null,
                null,
                false
            );
            foreach( $versions as $version  )
            {
                $versionNumber = $version['contentobjectattribute_version'];
                if ( $this->surveyAttribute->attribute('version') == $version['contentobjectattribute_version'] )
                {
                    $versionNumber = 'current';
                }
                $this->surveyVersionIds[$versionNumber] = $version['id'];
                /*
                $this->surveyVersions = eZPersistentObject::fetchObject(
                    eZSurvey::definition(),
                    null,
                    array( 'id' => array( $this->surveyVersionIds ) ),
                    true
                );
                */
            }
        }
    }

    public function setUser( $userId )
    {
        $user = eZUser::fetch( $userId );
        if ( !$user instanceof eZUser )
        {
            throw new Exception( "User $userId not found" );
        }
        $this->user = $user;
    }

    protected function hasValidUser()
    {
        return $this->user instanceof eZUser && $this->user->id() != eZUser::anonymousId();
    }

    protected function needLogin()
    {
        if ( $this->hasValidUser() )
        {
            return isset( $this->surveyValidation['one_answer_need_login'] );
        }
        return true;
    }

    protected function canAddResponse()
    {
        if ( $this->hasValidUser() )
        {
            if ( !$this->survey->attribute( 'valid' ) )
            {
                return false;
            }

            if ( isset( $this->surveyValidation['one_answer_need_login'] ) )
            {
                return false;
            }

            if ( isset( $this->surveyValidation['one_answer_count'] ) && $this->surveyValidation['one_answer_count'] > 0 )
            {
                return false;
            }

            if ( $this->survey->attribute( 'persistent' ) && !$this->survey->attribute( 'one_answer' ) )
            {
                return false;
            }

            return true;

        }
        return false;
    }
    protected function canModifyResponse()
    {
        if ( $this->hasValidUser() )
        {
            $result = new eZSurveyResult();
            $hasResult = $result->fetchAlreadyPosted( $this->survey->attribute( 'id' ), $this->user->id() );
            return $hasResult && ( $this->survey->attribute( 'persistent' ) && !$this->survey->attribute( 'one_answer' ) );
        }
        return false;
    }

    protected function userResults()
    {
        $data = array();
        if ( $this->hasValidUser() )
        {
            /** @var eZSurveyResult[] $results */
            $results = eZPersistentObject::fetchObjectList( eZSurveyResult::definition(), null, array( 'survey_id' =>  array( $this->surveyVersionIds ), 'user_id' => $this->user->id() ), array( 'tstamp' => 'desc' ) );
            foreach( $results as $result )
            {
                foreach( $this->surveyVersionIds as $versionNumber => $surveyId )
                {
                    if ( $result->attribute( 'survey_id' ) == $surveyId )
                    {
                        if ( !isset( $data[$versionNumber] ) )
                        {
                            $data[$versionNumber] = array();
                        }
                        $data[$versionNumber][] = $result;
                    }
                }
            }
        }
        return $data;
    }

    protected function userResultCount()
    {
        if ( $this->hasValidUser() )
        {
            return eZPersistentObject::count( eZSurveyResult::definition(), array( 'survey_id' => array( $this->surveyVersionIds ), 'user_id' => $this->user->id() ) );
        }
        return 0;
    }

    protected function allResults()
    {
        return eZPersistentObject::fetchObjectList( eZSurveyResult::definition(), null, array( 'survey_id' => $this->survey->attribute( 'id' ) ), array( 'tstamp' => 'desc' ) );
    }

    protected function allResultCount()
    {
        return eZPersistentObject::count( eZSurveyResult::definition(), array( 'survey_id' => $this->survey->attribute( 'id' ) ) );
    }

    public function attributes()
    {
        return array(
            'need_login',
            'can_add_response',
            'can_modify_response',
            'user_results',
            'user_result_count',
            'all_results',
            'all_result_count'
        );
    }

    public function attribute( $key )
    {
        switch ( $key )
        {
            case 'need_login': return $this->needLogin(); break;
            case 'can_add_response': return $this->canAddResponse(); break;
            case 'can_modify_response': return $this->canModifyResponse(); break;
            case 'user_results': return $this->userResults(); break;
            case 'user_result_count': return $this->userResultCount(); break;
            case 'all_results': return $this->allResults(); break;
            case 'all_result_count': return $this->allResultCount(); break;
            default:
                eZDebug::writeError( "Attribute $key not found", __METHOD__ );
        }
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }

}