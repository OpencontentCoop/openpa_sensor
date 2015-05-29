
<div id="post_comments">
    {if $sensor_post.comment_count}
    <div class="comment">
        <h4>{'Commenti'|i18n('openpa_sensor/messages')}</h4>
        {foreach $sensor_post.comment_items as $item}
            {include uri='design:sensor/parts/post/post_message/public.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
        {/foreach}
    </div>
    {/if}
    {if $sensor_post.can_comment}
        <div class="new_comment">
            <h4>{'Aggiungi un commento'|i18n('openpa_sensor/messages')}</h4>
            <div class="row">
                <div class="col-sm-8 col-md-8"><br>
                    <textarea name="Collaboration_OpenPASensorItemComment" class="form-control" placeholder="{'Commenti'|i18n('openpa_sensor/messages')}" rows="7"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-8 col-md-8">
                    <input class="btn send btn-primary btn-lg btn-block" type="submit" name="CollaborationAction_Comment" value="{'Pubblica il commento'|i18n('openpa_sensor/messages')}" />
                </div>
            </div>
        </div>
    {/if}
</div>

<div id="post_messages">
    {if $sensor_post.response_count}
    <div class="comment">
        <h4>{'Risposte ufficiali'|i18n('openpa_sensor/messages')}</h4>
        {foreach $sensor_post.response_items as $item}
            {include uri='design:sensor/parts/post/post_message/response.tpl' is_read=cond( $sensor_post.current_participant, $sensor_post.current_participant.last_read|gt($item.modified), true()) item_link=$item message=$item.simple_message}
        {/foreach}
    </div>
    {/if}
    {if $sensor_post.can_respond}
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
</div>
<script>
{literal}
$(document).ready(function(){
    $("a.edit-message").bind( 'click', function(e){
        var id = $(this).data('message-id');
        $('#edit-message-'+id).toggle();
        $('#view-message-'+id).toggle();
        e.preventDefault();
    });
});
{/literal}
</script>