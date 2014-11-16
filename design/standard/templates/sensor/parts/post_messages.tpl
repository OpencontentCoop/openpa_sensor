{def $message_limit=20
     $message_offset=0
     $message_list=fetch("collaboration","message_list",hash("item_id",$collaboration_item.id,"limit",$message_limit,"offset",$message_offset))}
<div id="post_comments">
    <div class="comment">
        <h4>Commenti</h4>
        {foreach $message_list as $item}

            {if $item.message_type|eq(0)}
                {*include uri='design:sensor/parts/post_message/robot.tpl'
                         is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                         item_link=$item
                         message=$item.simple_message*}
            {elseif $item.message_type|eq(1)}
                {include uri='design:sensor/parts/post_message/public.tpl'
                        is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                        item_link=$item
                        message=$item.simple_message}
            {elseif and(
                $current_participant,
                or( $item.message_type|eq($current_participant.participant_id), $item.participant_id|eq($current_participant.participant_id) )
            )}
                {include uri='design:sensor/parts/post_message/private.tpl'
                        is_read=cond( $current_participant, $current_participant.last_read|gt($item.modified), true())
                        item_link=$item
                        message=$item.simple_message}
            {/if}
        {/foreach}
    </div>
</div>
{if fetch( user, current_user ).is_logged_in}
<div class="new_comment">
    <h4>{'Aggiungi un messaggio'|i18n('openpa_sensor')}</h4>
    <div class="row">
        <div class="col-sm-8 col-md-8"><br>
            <textarea name="Collaboration_OpenPASensorItemComment" class="form-control" placeholder="Comments" rows="7"></textarea><br />
        </div>
    </div>
    <div class="row"><br>
        <div class="col-sm-8 col-md-8">
            <input class="btn send btn-primary" type="submit" name="CollaborationAction_Comment" value="{'Aggiungi un messaggio'|i18n('openpa_sensor')}" />
        </div>
    </div>
</div>
{/if}