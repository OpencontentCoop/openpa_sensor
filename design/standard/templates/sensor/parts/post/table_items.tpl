<table class="table table-hover">
{*<tr>  
  <th  class="text-center">{"Oggetto"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Commenti"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"In carico a"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center"></th>
</tr>*}
{foreach $item_list as $item}

{def $post = $item.content.helper.sensor}

<tr id="item-{$item.id}"{if $item.content.helper.human_unread_message_count} class="danger"{/if}>  
  <td style="vertical-align: middle;white-space: nowrap;" width="1">    
    <p>      
      {if $item.content.helper.human_message_count}
        <i class="fa {if $item.content.helper.human_unread_message_count}fa-comments faa-tada animated{else}fa-comments-o{/if}"> </i> {*if $item.content.helper.human_unread_message_count}<strong>{$item.content.helper.human_unread_message_count}</strong>{/if} {$item.content.helper.human_message_count*}
      {/if}
    </p>
    <p>
      {if $item.content.helper.robot_unread_message_count}
        <i class="fa fa-exclamation-triangle faa-tada animated"></i>
      {/if}
    </p>
  </td>
  <td>    
    <ul class="list-inline">
      {if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'config' ) )}
		<li><strong>{$item.content.helper.object.id}</strong></li>
	  {/if}
	  <li>
        {if $post.current_privacy_status.identifier|eq('private')}
          <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
        {/if}
        {if $post.current_moderation_status.identifier|eq('waiting')}
          <span class="label label-{$post.current_moderation_status.css_class}">{$post.current_moderation_status.name}</span>
        {/if}
        <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
        <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
      </li>
      <li><small><strong>{"Creata"|i18n('openpa_sensor/dashboard')}</strong> {$item.created|l10n(shortdatetime)}</small></li>
      {if $item.modified|ne($item.created)}<li><small><strong>{"Modificata"|i18n('openpa_sensor/dashboard')}</strong> {$item.modified|l10n(shortdatetime)}</small></li>{/if}
    </ul>
    <p>      
      {$item.content.helper.object.name|wash()}    
    </p>
    <p>
      <small>
        {if $item.content.helper.object.owner}
          <i class="fa fa-user"></i> {$item.content.helper.object.owner.name|wash()} {if $item.content.helper.object|has_attribute('on_behalf_of')}[{$item.content.helper.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$item.content.helper.object|attribute('on_behalf_of').content|wash()}]{/if}
        {/if}
        {if $item.content.helper.object.data_map.category.has_content}
          &middot;  <i class="fa fa-tags"></i> {attribute_view_gui attribute=$item.content.helper.object.data_map.category href=no-link} 
        {/if}
        {if $post.current_owner}
          &middot; <strong>{"In carico a"|i18n('openpa_sensor/dashboard')}</strong> {$post.current_owner}
        {/if}
      </small>
    </p>    
  </td>  
  <td class="text-center" style="vertical-align: middle;white-space: nowrap;" width="1">
    <a href={concat('sensor/posts/',$item.content.content_object_id)|ezurl()} class="btn btn-info btn-sm">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a>
    {if $item.content.helper.object.can_remove}
    <form method="post" action={"content/action"|ezurl} style="display: inline">        
        <input type="hidden" name="ContentObjectID" value="{$item.content.helper.object.id}" />                        
        <input type="hidden" name="ContentNodeID" value="{$item.content.helper.object.main_node_id}" />
        <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
        <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />                                
        <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
    </form>
    {/if}
  </td>
</tr>

{undef $post}

{/foreach}
</table>