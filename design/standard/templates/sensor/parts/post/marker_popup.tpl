<div class="clearfix">
<h4><a href={concat('sensor/posts/',$sensor_post.object.id)|ezurl()}>{$sensor_post.object.name|wash()}</a></h4>
<ul class="list-inline">
<li>
  <span class="label label-{$sensor_post.type.css_class}">{$post.type.name}</span>
  <span class="label label-{$sensor_post.current_object_state.css_class}">{$sensor_post.current_object_state.name}</span>
  {if $sensor_post.current_privacy_state.identifier|eq('private')}
    <span class="label label-{$sensor_post.current_privacy_state.css_class}">{$sensor_post.current_privacy_state.name}</span>
  {/if}
  </li>
</ul>
<p>{attribute_view_gui attribute=$sensor_post.object|attribute('description')}</p>
<ul class="list-unstyled">
    <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('openpa_sensor/post')} {$sensor_post.object.published|l10n(shortdatetime)}</small></li>
    {if $sensor_post.object.modified|gt($sensor_post.object.published)}
        <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor/post')} {$sensor_post.object.modified|l10n(shortdatetime)}</small></li>
    {/if}
    {if $post.current_owner}
    <li><small><i class="fa fa-user"></i> {'In carico a'|i18n('openpa_sensor/post')} {$sensor_post.current_owner}</small></li>
    {/if}
    <li><small><i class="fa fa-comment"></i> {$sensor_post.comment_count} {'commenti'|i18n('openpa_sensor/post')}</small></li>
	<li><small><i class="fa fa-comment"></i> {$sensor_post.response_count} {'risposte ufficiali'|i18n('openpa_sensor/post')}</small></li>
</ul>
<p><a href={concat('sensor/posts/',$sensor_post.id)|ezurl()} class="pull-right btn btn-info btn-sm" style="color:#fff">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a></p>
</div>