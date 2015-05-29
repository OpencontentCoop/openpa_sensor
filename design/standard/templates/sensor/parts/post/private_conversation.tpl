{if or( $sensor_post.message_count, and( $sensor_post.can_comment, $sensor_post.can_send_private_message ) )}
<p>
    <a class="btn btn-info btn-lg btn-block" data-toggle="collapse" href="#collapseConversation" aria-expanded="false" aria-controls="collapseConversation">
        {'Messaggi privati'|i18n('openpa_sensor/messages')}
        {if $sensor_post.message_count|gt(0)}<span class="badge">{$sensor_post.message_count}</span>{/if}
    </a>
</p>
<div class="collapse" id="collapseConversation">
    {if $sensor_post.message_count}
    <div class="comment">
        {foreach $sensor_post.message_items as $item}
            <p>
                {include uri='design:sensor/parts/post/post_message/private.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
            </p>
        {/foreach}
    </div>
    {/if}
    {if and( $sensor_post.can_comment, $sensor_post.can_send_private_message )}
        <div class="new_comment">
            <p>
                <textarea name="Collaboration_OpenPASensorItemPrivateMessage" class="form-control" placeholder="{'Aggiungi messaggio'|i18n('openpa_sensor/messages')}" rows="4"></textarea>
                <input class="btn send btn-info btn-sm btn-block" type="submit" name="CollaborationAction_PrivateMessage" value="{'Invia messaggio'|i18n('openpa_sensor/messages')}" />
            </p>
            <strong>{'Chi può leggere questo messaggio?'|i18n('openpa_sensor/messages')} </strong>
            {foreach $sensor_post.participants as $participant_role}
                {if $participant_role.role_id|eq(5)}{skip}{/if}
                {foreach $participant_role.items as $participant}
                    {if $participant.contentobject}
                        {if fetch(user,current_user).contentobject_id|eq($participant.contentobject.id)}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" checked="checked" disabled="disabled" />
                                    <small>{$participant.contentobject.name|wash()}</small>
                                    <input name="Collaboration_OpenPASensorItemPrivateMessageReceiver[]" type="hidden" value="{$participant.contentobject.id}" />
                                </label>
                            </div>
                        {else}
                            <div class="checkbox">
                                <label>
                                    <input name="Collaboration_OpenPASensorItemPrivateMessageReceiver[]" checked="checked" type="checkbox" value="{$participant.contentobject.id}" />
                                    <small>{$participant.contentobject.name|wash()}</small>
                                </label>
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            {/foreach}

        </div>
    {/if}
</div>
{/if}