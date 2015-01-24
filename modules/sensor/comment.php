<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$forumId = $Params['ForumID'];
$forumReplyId = $Params['ForumReplyID'];
$forum = eZContentObjectTreeNode::fetch( $forumId );
$node = $reply = false;
if ( is_numeric( $forumReplyId ) )
{
    $reply = eZContentObjectTreeNode::fetch( $forumReplyId );
    $node = $reply;
}
else
{
    $forumReplyId = 0;
    $node = $forum;
}

$offset = 0;
if ( isset( $Params['UserParameters']['offset'] ) )
{
    $offset = $Params['UserParameters']['offset'];
}

$class = ObjectHandlerServiceControlSensor::forumCommentClass();

if ( $node instanceof eZContentObjectTreeNode && $class instanceof eZContentClass )
{
    $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );
    $object = eZContentObject::createWithNodeAssignment( $node,
        $class->attribute( 'id' ),
        $languageCode,
        false );
    if ( $object )
    {
        $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' ) . '/' . $languageCode . '/(forum)/' . $forumId . '/(reply)/' . $forumReplyId . '/(offset)/' . $offset . '#edit' );
        return;
    }
    else
        return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );