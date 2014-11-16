<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
  {def $message_limit=2
       $message_offset=0
       $content_object=fetch("content","object",hash("object_id",$collab_item.content.content_object_id))
       $current_participant=fetch("collaboration","participant",hash("item_id",$collab_item.id))
       $participant_list=fetch("collaboration","participant_map",hash("item_id",$collab_item.id))
       $message_list=fetch("collaboration","message_list",hash("item_id",$collab_item.id,"limit",$message_limit,"offset",$message_offset))}

  {if or( $content_object|not(), $content_object.can_read|not() )}

    <div class="warning message-warning">
      <h2>La segnalazione {$collab_item.content.content_object_id} non &egrave; accessibile o &egrave; stata rimossa.</h2>
    </div>

  {else}


      <h1>{$content_object.name|wash()} <small>{$collab_item.data_int3}</small></h1>

    {if $collab_item.content.helper.can_assign}
      <input class="defaultbutton" type="submit" name="CollaborationAction_Assign" value="Assign" />
      <input type="text" name="Collaboration_OpenPASensorItemAssignTo[]" value="" /><br />
    {/if}
    {if $collab_item.content.helper.can_fix}
      <input class="defaultbutton" type="submit" name="CollaborationAction_Fix" value="Fix" /><br />
    {/if}
    {if $collab_item.content.helper.can_close}
      <input class="defaultbutton" type="submit" name="CollaborationAction_Close" value="Close" /><br />
    {/if}

      <textarea name="Collaboration_OpenPASensorItemComment" cols="40" rows="5" class="box"></textarea><br />
      <input class="defaultbutton" type="submit" name="CollaborationAction_Comment" value="Aggiungi un messaggio" />

      {if $collab_item.content.helper.can_send_private_message}
      <select name="Collaboration_OpenPASensorItemCommentPrivateReceiver">
          <option></option>
      {foreach $collab_item.content.helper.participants as $user}
          {if $user.id|ne($current_participant.participant_id)}
          <option value="{$user.id}">{$user.name|wash()}</option>
          {/if}
      {/foreach}
      </select>
      {/if}

      {if $collab_item.content.helper.can_add_observer}
          <div>
          <input class="defaultbutton" type="submit" name="CollaborationAction_AddObserver" value="Aggiungi cc" />
          <select name="Collaboration_OpenPASensorItemAddObserver">
              <option></option>
              {foreach fetch( content, tree, hash( 'parent_node_id', 1, 'class_filter_type', 'include', 'class_filter_array', array( 'user' ) ) ) as $user}
                  {if $user.contentobject_id|ne($current_participant.participant_id)}
                      <option value="{$user.contentobject_id}">{$user.name|wash()}</option>
                  {/if}
              {/foreach}
          </select>
          </div>
      {/if}


      <input type="hidden" name="CollaborationActionCustom" value="custom" />
      <input type="hidden" name="CollaborationTypeIdentifier" value="openpasensor" />
      <input type="hidden" name="CollaborationItemID" value="{$collab_item.id}" />



<hr />
      <div>
      {section name=Role loop=$participant_list}
          <label>{$:item.name|wash}:</label>
          {section name=Participant loop=$:item.items}
              <p>{collaboration_participation_view view=text_linked collaboration_participant=$:item}</p>
          {/section}
      {/section}
      </div>
<hr />
      {if $message_list}
          <table class="table table-striped table-condensed" width="100%" cellspacing="0" cellpadding="4" border="0">
              {foreach $message_list as $item sequence array(bglight,bgdark) as $_style}

                  {if or(
                    $item.message_type|eq(0),
                    $item.message_type|eq(1),
                    or( $item.message_type|eq($current_participant.participant_id), $item.participant_id|eq($current_participant.participant_id) )
                  )}

                      {collaboration_simple_message_view view=element sequence=$_style is_read=$current_participant.last_read|gt($item.modified) item_link=$item collaboration_message=$item.simple_message}

                  {/if}
              {/foreach}
          </table>
      {/if}

  {/if}

</form>
