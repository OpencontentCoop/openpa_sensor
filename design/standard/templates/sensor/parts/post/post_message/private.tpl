{def $participant = first_set( $item_link.participant.participant.contentobject, fetch( content, object, hash( object_id, $item_link.participant_id ) ) )}
<div class="{if $message.creator_id|eq($current_user.contentobject_id)} text-right{/if}">
    <small>
        <ul class="list-unstyled">
            <li>
                {if $message.creator_id|eq($current_user.contentobject_id)}
                    <a class="edit-message" href="#" data-message-id="{$message.id}"><i class="fa fa-edit"></i></a>
                {/if}
                {if $is_read|not}<strong>{/if}{$item.created|l10n(shortdatetime)}{if $is_read|not}</strong>{/if}
            </li>
            {if $message.creator_id|eq($current_user.contentobject_id)|not()}
                <li>{'Da:'|i18n('openpa_sensor/messages')} {$participant.name|wash()}</li>
            {/if}
            {def $receiversIds = cond(  $message.data_text2|ne(''), $message.data_text2|explode(','), array() )}
            {if count($receiversIds)|gt(0)}
                {def $index = 0}
                {foreach $receiversIds as $receiversId}
                    {if $item_link.participant_id|ne($receiversId)}
                        {def $obj = fetch( content, object, hash( object_id, $receiversId, load_data_map, false() ))}
                        {if $obj}
                            <li>{if $index|eq(0)}{'a:'|i18n('openpa_sensor/messages')}{set $index = $index|inc()} {/if}{$obj.name|wash()}</li>
                        {/if}
                        {undef $obj}
                    {/if}
                {/foreach}
            {/if}
            {undef $receiversIds}
        </ul>
    </small>
    {if $message.creator_id|eq(fetch(user,current_user).contentobject_id)}
        <div id="edit-message-{$message.id}" style="display: none;">
            <textarea name="Collaboration_OpenPASensorEditMessage[{$message.id}]" class="form-control" rows="3">{$message.data_text1}</textarea>
            <input class="btn btn-info btn-sm btn-block" type="submit" name="CollaborationAction_EditMessage" value="{'Salva'|i18n('openpa_sensor/messages')}" />
        </div>
    {/if}
    <div id="view-message-{$message.id}">
        <p>
            {$message.data_text1|wash()}
        </p>
    </div>
</div>
