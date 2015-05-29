<table class="table table-striped table-hover">
{*<tr>  
  <th  class="text-center">{"Oggetto"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Commenti"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"In carico a"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center"></th>
</tr>*}
{foreach $item_list as $item}

<tr id="item-{$item.collaboration_item.id}"{if $item.human_unread_count|gt(0)} class="danger"{/if}>
  <td style="vertical-align: middle;white-space: nowrap;" width="1">    
    {if $item.comment_count}
      <p>
        <i class="fa fa-comments-o {if $item.comment_unread_count|gt(0)}faa-tada animated{/if}"> </i>
      </p>
    {/if}

    {if $item.message_count}
      <p>
          <i class="fa fa-comments {if $item.message_unread_count|gt(0)}faa-tada animated{/if}"> </i>
      </p>
    {/if}

    {if $item.timeline_unread_count|gt(0)}
      <p>
          <i class="fa fa-exclamation-triangle faa-tada animated"></i>
      </p>
    {/if}
  </td>
  <td>    
    <ul class="list-inline">
      <li><strong>{$item.id}</strong></li>
	  <li>
        {if $item.current_privacy_state.identifier|eq('private')}
          <span class="label label-{$item.current_privacy_state.css_class}">{$item.current_privacy_state.name}</span>
        {/if}
        {if $item.current_moderation_state.identifier|eq('waiting')}
          <span class="label label-{$item.current_moderation_state.css_class}">{$item.current_moderation_state.name}</span>
        {/if}
        <span class="label label-{$item.type.css_class}">{$item.type.name}</span>
        <span class="label label-{$item.current_object_state.css_class}">{$item.current_object_state.name}</span>
      </li>
    </ul>    
    <ul class="list-inline">
      <li><small><strong>{"Creata"|i18n('openpa_sensor/dashboard')}</strong> {$item.object.published|l10n(shortdatetime)}</small></li>
      {if $item.object.modified|ne($item.object.published)}<li><small><strong>{"Modificata"|i18n('openpa_sensor/dashboard')}</strong> {$item..object.modified|l10n(shortdatetime)}</small></li>{/if}

      {if and( fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'manage' ) ), $item.collaboration_item.user_status.is_active )}
        <li><small><strong>{"Scadenza"|i18n('openpa_sensor/dashboard')}</strong></small> <span class="label label-{$item.expiring_date.label}">{$item.expiring_date.text|wash()}</span></li>
      {/if}
    </ul>
    <p>      
      {$item.object.name|wash()}
    </p>
    <ul class="list-unstyled">      
        {if $item.object.owner}
          <li><small><strong>{"Autore"|i18n('openpa_sensor/dashboard')}</strong> {$item.object.owner.name|wash()} {if $item.object|has_attribute('on_behalf_of')}[{$item.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$item.object|attribute('on_behalf_of').content|wash()}]{/if}</small></li>
        {/if}
        {if $item.object.data_map.category.has_content}
          <li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$item.object.data_map.category href=no-link} </small></li>
        {/if}
        {if $item.current_owner}
          <li><small><strong>{"In carico a"|i18n('openpa_sensor/dashboard')}</strong> {$item.current_owner}</small></li>
        {elseif $item.last_timeline}
            <li><small>{$item.last_timeline.message_text|wash()}</small></li>
        {/if}
    </ul>
  </td>
  <td class="twìext-center"> 
      <p><a href={concat('sensor/posts/',$item.object.id)|ezurl()} class="btn btn-info btn-sm">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a></p>
      {if $item.object.can_edit}
        <p><a href={concat('sensor/edit/',$item.object.id)|ezurl()} class="btn btn-warning btn-sm">{'Edit'|i18n( 'design/admin/node/view/full' )}</a></p>
      {/if}
      {if $item.object.can_remove}
      <form method="post" action={"content/action"|ezurl}>        
          <input type="hidden" name="ContentObjectID" value="{$item.object.id}" />
          <input type="hidden" name="ContentNodeID" value="{$item.object.main_node_id}" />
          <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
          <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />                                
          <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove">{'Remove'|i18n( 'design/admin/node/view/full' )}</button>
      </form>
      {/if}      
  </td>    
</tr>

{/foreach}
</table>