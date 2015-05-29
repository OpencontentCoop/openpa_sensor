{if $sensor_post.timeline_count|gt(0)}
    <aside class="widget timeline">
        <h4>{'Cronologia'|i18n('openpa_sensor/post')}</h4>
        <ol class="list-unstyled">
            {foreach $sensor_post.timeline_items as $item}
                <li>
                    <div class="icon"><i class="fa fa-clock-o"></i></div>
                    <div class="title">{$item.message_link.created|l10n(shortdatetime)}</div>
                    <div class="content"><small>{$item.message_text}</small></div>
                </li>
            {/foreach}
            </dl>
    </aside>
{/if}