{def $message_limit=100
     $message_offset=0
     $message_list=$helper.human_messages
     $hasMessage = false()}

{foreach $message_list as $item}
    {if or( $item.message_type|eq(1), $item.message_type|eq(2) )}
        {set $hasMessage = true()}
        {break}
    {elseif and($item.message_type|ne(1),$item.message_type|ne(0),$current_participant,or( $item.message_type|eq($current_participant.participant_id), $item.participant_id|eq($current_participant.participant_id) ))}
        {set $hasMessage = true()}
        {break}
    {/if}
{/foreach}
{if $hasMessage}
<div id="post_comments">
    <div class="comment">
        <h4{'Commenti'|i18n('openpa_sensor/messages')}</h4>
        {foreach $message_list as $item}
            {if $item.message_type|eq(1)}
                {include uri='design:sensor/parts/post/post_message/public.tpl'
                        is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                        item_link=$item
                        message=$item.simple_message}
            
            {elseif $item.message_type|eq(2)}
                {include uri='design:sensor/parts/post/post_message/response.tpl'
                        is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                        item_link=$item
                        message=$item.simple_message}
            
            {elseif and(
                $item.message_type|ne(2),
                $item.message_type|ne(1),
                $item.message_type|ne(0),
                $current_participant,
                or( $item.message_type|eq($current_participant.participant_id), $item.participant_id|eq($current_participant.participant_id) )
            )}
                {include uri='design:sensor/parts/post/post_message/private.tpl'
                        is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                        item_link=$item
                        message=$item.simple_message}

            {/if}
        {/foreach}
    </div>
</div>
{/if}
{if and( fetch( user, current_user ).is_logged_in, $collaboration_item.content.helper.can_comment )}
<div class="new_comment">
    <h4>{'Aggiungi un commento'|i18n('openpa_sensor/messages')}</h4>
    <div class="row">
        <div class="col-sm-8 col-md-8"><br>
            <textarea name="Collaboration_OpenPASensorItemComment" class="form-control" placeholder="{'Commenti'|i18n('openpa_sensor/messages')}" rows="7"></textarea>
        </div>
    </div>
    <div class="row"><br>        
        <div class="col-sm-8 col-md-8">
            {if $collaboration_item.content.helper.can_send_private_message}
                <select name="Collaboration_OpenPASensorItemCommentPrivateReceiver" class="form-control input-lg">
                    <option>{'Visibile a tutti'|i18n('openpa_sensor/messages')}</option>
                    {foreach $collaboration_item.content.helper.participants as $user}
                        {if $user.id|ne($current_participant.participant_id)}
                            <option value="{$user.id}">{'Visibile solo a'|i18n('openpa_sensor/messages')} {$user.name|wash()}</option>
                        {/if}
                    {/foreach}
                </select>
            {/if}
        </div>
		<div class="col-sm-8 col-md-8">
            <input class="btn send btn-primary btn-lg btn-block" type="submit" name="CollaborationAction_Comment" value="{'Pubblica il commento'|i18n('openpa_sensor/messages')}" />
        </div>
    </div>
</div>
{/if}
{if and( fetch( user, current_user ).is_logged_in, $collaboration_item.content.helper.can_respond )}
<hr />
<div class="new_comment">
    <h4>{'Aggiungi risposta ufficiale'|i18n('openpa_sensor/messages')}</h4>
    <div class="row">
        <div class="col-sm-8 col-md-8"><br>
            <textarea name="Collaboration_OpenPASensorItemResponse" class="form-control" placeholder="{'Risposta ufficiale'|i18n('openpa_sensor/messages')}" rows="7"></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-md-8">
            <input class="btn send btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Respond" value="{'Pubblica la risposta ufficiale'|i18n('openpa_sensor/messages')}" />
        </div>
    </div>
</div>
{/if}