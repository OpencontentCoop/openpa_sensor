{if $item_count|gt(0)}

<section class="hgroup">
  <h1>{"Le mie segnalazioni"|i18n('openpa_sensor/dashboard')}</h1>
</section>

{def $item_class = ''
     $row_class = ''
     $post = false()}
<table class="table table-condensed">
<tr>
  <th  class="text-center">{"Creata"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Modificata"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Tipo"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Stato"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Oggetto"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"Commenti"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center">{"In carico a"|i18n('openpa_sensor/dashboard')}</th>
  <th  class="text-center"></th>
</tr>
{foreach $item_list as $item}

{set $post = $item.content.helper.sensor}

{set $item_class = 'status_read'
     $row_class = ''}
{if $item.user_status.is_active}
  {set $row_class="active"}  
{else}
  {set $item_class="status_inactive"}
{/if}

{if $item.unread_message_count}
  {set $row_class="warning"}  
{/if}

{if $item.user_status.is_read|not()}  
  {set $row_class="danger"}
  {set $item_class="status_unread"}
{/if}

<tr class="{$row_class}">
  <td class="text-center"  width="1">
    <small style="white-space: nowrap">{$item.created|l10n(shortdatetime)}</small>    
  </td>
  <td class="text-center"  width="1">
    <small style="white-space: nowrap">{$item.modified|l10n(shortdatetime)}</small>    
  </td>    
  <td class="text-center"><span class="label label-{$post.type.css_class}">{$post.type.name}</span></td>
  <td class="text-center"><span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span></td>
  <td>
    {if $post.current_privacy_status.identifier|eq('private')}
      <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
    {/if}
    {$item.content.helper.object.name|wash()}
  </td>
  <td class="text-center">
    {if $item.use_messages}
    <p>
      {$item.content.helper.human_message_count}
      {if $item.content.helper.human_unread_message_count}<span class="badge">{$item.content.helper.human_unread_message_count}</span>{/if}
    </p>    
    {/if}
  </td>
  <td class="text-center">
    <p>{$post.current_owner}</p>
  </td>
  <td class="text-center">
    <a href={concat('sensor/posts/',$item.content.content_object_id)|ezurl()} class="btn btn-info btn-sm">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a>
  </td>
</tr>
{/foreach}
</table>

{/if}