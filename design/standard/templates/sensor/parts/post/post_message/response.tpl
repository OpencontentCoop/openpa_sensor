<div class="row">
    <div class="col-xs-12 col-md-12">
      <div class="well">
        <div class="comment_name"> <small>{'RISPOSTA DEL RESPONSABILE'|i18n('openpa_sensor/messages')}</small></div>
        <div class="comment_date"><i class="fa-time"></i>
            {if $is_read|not}<strong>{/if}{$item.created|l10n(shortdatetime)}{if $is_read|not}</strong>{/if}
        </div>
        <div class="the_comment">
            <p>{$message.data_text1}</p>
        </div>
      </div>
    </div>
</div>