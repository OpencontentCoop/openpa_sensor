{def $post = object_handler($object).control_sensor}
<div class="clearfix">
<h4><a href={concat('sensor/posts/',$object.id)|ezurl()}>{$object.name|wash()}</a></h4>
<ul class="list-inline">
<li>
  <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
  <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
  {if $post.current_privacy_status.identifier|eq('private')}
    <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
  {/if}
  </li>
</ul>
<p>{attribute_view_gui attribute=$object|attribute('description')}</p>
<ul class="list-unstyled">
    <li><small><i class="fa fa-clock-o"></i> Pubblicata il {$object.published|l10n(shortdatetime)}</small></li>
    {if $object.modified|gt($item.object.published)}
        <li><small><i class="fa fa-clock-o"></i> Ultima modifica del {$object.modified|l10n(shortdatetime)}</small></li>
    {/if}
    <li><small><i class="fa fa-user"></i> In carico a {$post.current_owner}</small></li>
    <li><small><i class="fa fa-comment"></i> {$post.comment_count} commenti</small></li>
</ul>
<p><a href={concat('sensor/posts/',$object.id)|ezurl()} class="pull-right btn btn-info btn-sm" style="color:#fff">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a></p>
</div>