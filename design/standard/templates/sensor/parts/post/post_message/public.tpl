{def $participant = first_set( $item_link.participant.participant.contentobject, fetch( content, object, hash( object_id, $item_link.participant_id ) ) )}
<div class="row">
    <figure class="col-xs-2 col-md-2">
        {include uri='design:sensor/parts/user_image.tpl' object=$participant}
    </figure>
    <div class="col-xs-10 col-md-10">
        <div class="comment_name">
            {if is_set( $participant.name )}{$participant.name|wash()}{else}?{/if}
        </div>
        <div class="comment_date"><i class="fa-time"></i>
            {if $is_read|not}<strong>{/if}{$item.created|l10n(shortdatetime)}{if $is_read|not}</strong>{/if}
            {if $message.creator_id|eq(fetch(user,current_user).contentobject_id)}
                <a class="btn btn-warning btn-sm edit-message" href="#" data-message-id="{$message.id}"><i class="fa fa-edit"></i></a>
            {/if}
        </div>
        <div class="the_comment">
            {if $message.creator_id|eq(fetch(user,current_user).contentobject_id)}
                <div id="edit-message-{$message.id}" style="display: none;">
                    <textarea name="Collaboration_OpenPASensorEditComment[{$message.id}]" class="form-control" rows="3">{$message.data_text1}</textarea>
                    <input class="btn send btn-primary btn-md pull-right" type="submit" name="CollaborationAction_EditComment" value="{'Salva'|i18n('openpa_sensor/messages')}" />
                </div>
            {/if}
            <div id="view-message-{$message.id}">
                <p>{$message.data_text1|wash()}</p>
            </div>
        </div>
    </div>
</div>