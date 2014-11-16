{def $participant = first_set( $item_link.participant.participant.contentobject, fetch( content, object, hash( object_id, $item_link.participant_id ) ) )}
<div class="row">
    <figure class="col-sm-2 col-md-2">
        {include uri='design:sensor/parts/user_image.tpl' object=$participant}
    </figure>
    <div class="col-sm-10 col-md-10">
        <div class="comment_name"> {$participant.name|wash()}</div>
        <div class="comment_date"><i class="fa-time"></i>
            {if $is_read|not}<strong>{/if}
                {$item.created|l10n(shortdatetime}
            {if $is_read|not}</strong>{/if}
        </div>
        <div class="the_comment">
            <p>{$message.data_text1}</p>
        </div>
    </div>
</div>