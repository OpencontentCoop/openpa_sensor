<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$contentObjectId = $Params['ContentObjectID'];
$surveyResultId = $Params['SurveyResultID'];

$contentObject = eZContentObject::fetch( $contentObjectId );
$surveyResult = eZSurveyResult::fetch( $surveyResultId );
$survey = null;

$hasKernelError = null;
if ( !$contentObject instanceof eZContentObject  )
{
    $hasKernelError = eZError::KERNEL_NOT_AVAILABLE;
}

if ( !$hasKernelError && !$contentObject->attribute( 'can_read' ) )
{
    $hasKernelError = eZError::KERNEL_ACCESS_DENIED;
}

if ( $surveyResult instanceof eZSurveyResult )
{
    if ( $surveyResult->attribute( 'user_id' ) !== eZUser::currentUserID() )
    {
        $hasKernelError = eZError::KERNEL_ACCESS_DENIED;
    }
    $survey = eZSurvey::fetch( $surveyResult->attribute( 'survey_id' ) );
    if ( !$survey instanceof eZSurvey )
    {
        $hasKernelError = eZError::KERNEL_NOT_AVAILABLE;
    }
}

if ( $hasKernelError === null )
{
    if ( $survey instanceof eZSurvey )
    {
        $tpl->setVariable( 'single_result', true );
        $surveyList = $survey->fetchQuestionList();
        $tpl->setVariable( 'survey', $survey );
        $tpl->setVariable( 'survey_questions', $surveyList );
        $tpl->setVariable( 'survey_metadata', array() );
        $tpl->setVariable( 'result_id', $surveyResult->attribute( 'id' ) );
        $tpl->setVariable( 'result', $surveyResult );
    }
    else
    {
        $tpl->setVariable( 'single_result', false );
    }


    $tpl->setVariable( 'object', $contentObject );
    $tpl->setVariable( 'user', eZUser::currentUser() );

    $Result = array();
    $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    $Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
    $Result['content'] = $tpl->fetch( 'design:sensor/parts/survey/survey_user_result.tpl' );
    $Result['node_id'] = 0;

    $contentInfoArray = array( 'url_alias' => 'sensor/survey_user_result/' . $contentObjectId . '/' . $surveyResultId );
    $contentInfoArray['persistent_variable'] = false;
    if ( $tpl->variable( 'persistent_variable' ) !== false )
    {
        $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
    }
    $Result['content_info'] = $contentInfoArray;
    $Result['path'] = array();
}
else
{
    return $module->handleError( $hasKernelError, 'kernel' );
}