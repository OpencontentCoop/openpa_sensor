{ezscript_require(array('ezjsc::jquery', 'plugins/chosen.jquery.js'))}
{ezcss_require('plugins/chosen.css')}
<script>{literal}$(document).ready(function(){$("select.chosen").chosen({width:'100%'});});{/literal}</script>

{if is_set( $error )}
  <div class="alert alert-danger">{$error}</div>
{else}

<section class="hgroup">
  <h1>
    <span class="label label-primary">{$sensor_post.object.id}</span>
    {$sensor_post.object.name|wash()} <small>{$sensor_post.object.owner.name|wash()} {if $sensor_post.object|has_attribute('on_behalf_of')}[{$sensor_post.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$sensor_post.object|attribute('on_behalf_of').content|wash()}]{/if}</small>
    {if $sensor_post.object.can_edit}
      <a class="btn btn-warning btn-sm" href="{concat('sensor/edit/',$sensor_post.object.id)|ezurl(no)}"><i class="fa fa-edit"></i></a>
    {/if}
    {if $sensor_post.object.can_remove}
    <form method="post" action={"content/action"|ezurl} style="display: inline">
        <input type="hidden" name="ContentObjectID" value="{$sensor_post.object.id}" />
        <input type="hidden" name="ContentNodeID" value="{$sensor_post.object.main_node_id}" />
        <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
        <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />
        <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
    </form>
    {/if}
  </h1>
    <ul class="breadcrumb pull-right">
      <li>
        <span class="label
        label-{$sensor_post.type.css_class}">{$sensor_post.type.name}</span> <span
        class="label
        label-{$sensor_post.current_object_state.css_class}">{$sensor_post.current_object_state.name}</span>
        {if $sensor_post.current_privacy_state.identifier|eq('private')}
          <span class="label
          label-{$sensor_post.current_privacy_state.css_class}">{$sensor_post.current_privacy_state.name}</span>
        {/if}
        {if $sensor_post.current_moderation_state.identifier|eq('waiting')}
          <span class="label label-{$sensor_post.current_moderation_state.css_class}">{$sensor_post.current_moderation_state.name}</span>
        {/if}
      </li>
    </ul>
</section>

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">
  <div class="row">
    <div class="col-md-8">
    
      <div class="row">
        <div class="col-md-4">
          <aside class="widget">            
            {include uri='design:sensor/parts/post/map.tpl'}
          </aside>
        </div>
        <div class="col-md-8">
          <p>{attribute_view_gui attribute=$sensor_post.object|attribute('description')}</p>
          {if $sensor_post.object|has_attribute('attachment')}
            <p>{attribute_view_gui attribute=$sensor_post.object|attribute('attachment')}</p>
          {/if}
          {if $sensor_post.object|has_attribute('image')}
            <figure>{attribute_view_gui attribute=$sensor_post.object|attribute('image') image_class='large' alignment=center}</figure>
          {/if}
          <ul class="list-inline">
            <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('openpa_sensor/post')} {$sensor_post.object.published|l10n(shortdatetime)}</small></li>
            {if $sensor_post.object.modified|gt($sensor_post.object.published)}
                <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor/post')} {$sensor_post.object.modified|l10n(shortdatetime)}</small></li>
            {/if}
          </ul>
          <ul class="list-inline">
            {if $sensor_post.current_owner}
              <li><small><i class="fa fa-user"></i> {'In carico a'|i18n('openpa_sensor/post')} {$sensor_post.current_owner}</small></li>
            {/if}
            <li><small><i class="fa fa-comments"></i> {$sensor_post.comment_count} {'commenti'|i18n('openpa_sensor/post')}</small></li>
            <li><small><i class="fa fa-comment"></i> {$sensor_post.response_count} {'risposte ufficiali'|i18n('openpa_sensor/post')}</small></li>
            {if $sensor_post.object|has_attribute( 'category' )}
              <li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$sensor_post.object.data_map.category href=no-link}</small></li>
            {/if}
          </ul>              
        </div>        
      </div>
      
      <div class="row">
        <div class="col-md-12">
          {include uri='design:sensor/parts/post/post_messages.tpl'}
        </div>
      </div>

    </div>
    <div class="col-md-4" id="sidebar">

      {include uri='design:sensor/parts/post/actions.tpl'}

      {include uri='design:sensor/parts/post/participants.tpl'}

      {include uri='design:sensor/parts/post/timeline.tpl'}

    
    </div>
  </div>
  <input type="hidden" name="CollaborationActionCustom" value="custom" />
  <input type="hidden" name="CollaborationTypeIdentifier" value="openpasensor" />
  <input type="hidden" name="CollaborationItemID" value="{$sensor_post.collaboration_item.id}" />

</form>

{/if} {* if error *}
