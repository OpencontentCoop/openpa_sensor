{ezscript_require( array( 'ezjsc::jquery', 'jquery.quicksearch.min.js' ) )}
{literal}
<script type="text/javascript">
$(document).ready(function(){  
  $('input.quick_search').quicksearch('table tr');
});  
</script>
{/literal}
<section class="hgroup">
  <h1>{'Settings'|i18n('openpa_sensor/menu')}</h1>    
</section>

<div class="row">
  <div class="col-md-12">
	
	<h2>
	  {'Impostazioni'|i18n('openpa_sensor/config')}
	</h2>
	{'Modifica impostazioni generali'|i18n('openpa_sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$root}
		
	<hr />
		
  <div class="row">
  
    <div class="col-md-3">    
      <ul class="nav nav-pills nav-stacked">
        <li role="presentation" {if $current_part|eq('users')}class="active"{/if}><a href="{'sensor/config/users'|ezurl(no)}">{'Utenti'|i18n('openpa_sensor/config')}</a></li>
        <li role="presentation" {if $current_part|eq('operators')}class="active"{/if}><a href="{'sensor/config/operators'|ezurl(no)}">{'Operatori'|i18n('openpa_sensor/config')}</a></li>
        <li role="presentation" {if $current_part|eq('categories')}class="active"{/if}><a href="{'sensor/config/categories'|ezurl(no)}">{'Aree tematiche'|i18n('openpa_sensor/config')}</a></li>        
        <li role="presentation" {if $current_part|eq('areas')}class="active"{/if}><a href="{'sensor/config/areas'|ezurl(no)}">{'Punti sulla mappa'|i18n('openpa_sensor/config')}</a></li>        
        {if $data|count()|gt(0)}
          {foreach $data as $item}
            <li role="presentation" {if $current_part|eq(concat('data-',$item.contentobject_id))}class="active"{/if}><a href="{concat('sensor/config/data-',$item.contentobject_id)|ezurl(no)}">{$item.name|wash()}</a></li>
          {/foreach}
        {/if}
      </ul>
    </div>
      
    <div class="col-md-9">    
      
      {if $current_part|eq('categories')}            
      <div class="tab-pane active" id="categories">
        <form action="#">
          <fieldset>
            <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
          </fieldset>
        </form>
        {if $categories|count()|gt(0)}        
        <table class="table table-hover">          
          {foreach $categories as $category}
          {include name=cattree uri='design:sensor/parts/walk_item_table.tpl' item=$category recursion=0}		
          {/foreach}
        </table>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/', $categories[0].node.class_identifier, '/?parent=',$categories[0].node.parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi area tematica'|i18n('openpa_sensor/config')}</a></div>
        {/if}
      </div>
      {/if}
      
      {if $current_part|eq('operators')}
      <div class="tab-pane active" id="operators">
        <form action="#">
          <fieldset>
            <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
          </fieldset>
        </form>
        <table class="table table-hover">
          {foreach $operators as $operator}
          {def $userSetting = $operator|user_settings()}
          <tr>
            <td>
              {if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if}
              {*<a href="{$operator.url_alias|ezurl(no)}">{$operator.name|wash()}</a>*}{$operator.name|wash()}
              {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
              <a href="{concat('sensor/user/',$operator.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>
            <td width="1">
              {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$operator}
            </td>
            {*<td width="1">
              {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'setting' ))}
                <form name="Setting" method="post" action={concat( 'user/setting/', $operator.contentobject_id )|ezurl}>                  
                  <input type="hidden" name="is_enabled" value={if $userSetting.is_enabled|not()}"1"{else}""{/if} />
                  <button class="btn-link btn-xs" type="submit" name="UpdateSettingButton" title="{if $userSetting.is_enabled}{'Blocca'|i18n('openpa_sensor/config')}{else}{'Sblocca'|i18n('openpa_sensor/config')}{/if}">{if $userSetting.is_enabled}<i class="fa fa-ban"></i>{else}<i class="fa fa-check-circle"></i>{/if}</button>
                  
                </form>
              {/if}
            </td>*}
          </tr>
          {undef $userSetting}
          {/foreach}          
        </table>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/', $operators[0].class_identifier, '/?parent=',$operators[0].parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi operatore'|i18n('openpa_sensor/config')}</a></div>
      </div>
      {/if}
      
      {if $current_part|eq('users')}
      <div class="tab-pane active" id="users">
        <form action="#">
          <fieldset>
            <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
          </fieldset>
        </form>
        <table class="table table-hover">
          {def $users_count = fetch( content, list_count, hash( parent_node_id, $user_parent_node.node_id ) )
               $users = fetch( content, list, hash( parent_node_id, $user_parent_node.node_id, limit, 30, offset, $view_parameters.offset, sort_by, array( 'name', 'asc' ) ) )}
          {foreach $users as $user}
          {def $userSetting = $user|user_settings()}
          <tr>
            <td>
              {if $userSetting.is_enabled|not()}<span style="text-decoration: line-through">{/if}
              {*<a href="{$user.url_alias|ezurl(no)}">{$user.name|wash()}</a>*}{$user.name|wash()}
              {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
              {*include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$user*}
              <a href="{concat('sensor/user/',$user.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>
            {*<td width="1">
              {if fetch( 'user', 'has_access_to', hash( 'module', 'user', 'function', 'setting' ))}
                <form name="Setting" method="post" action={concat( 'user/setting/', $operator.contentobject_id )|ezurl}>                  
                  <input type="hidden" name="is_enabled" value={if $userSetting.is_enabled|not()}"1"{else}""{/if} />
                  <button class="btn-link btn-xs" type="submit" name="UpdateSettingButton" title="{if $userSetting.is_enabled}{'Blocca'|i18n('openpa_sensor/config')}{else}{'Sblocca'|i18n('openpa_sensor/config')}{/if}">{if $userSetting.is_enabled}<i class="fa fa-ban"></i>{else}<i class="fa fa-check-circle"></i>{/if}</button>
                  
                </form>
              {/if}
            </td>*}
          </tr>
          {undef $userSetting}          
          {/foreach}
          
        </table>
        
        {include name=navigator
                     uri='design:navigator/google.tpl'
                     page_uri='sensor/config/users'
                     item_count=$users_count
                     view_parameters=$view_parameters
                     item_limit=30}
        {undef $users $users_count}
      </div>
      {/if}
      
      {if $current_part|eq('areas')}
      <div class="tab-pane active" id="areas">
        <form action="#">
          <fieldset>
            <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
          </fieldset>
        </form>
        <table class="table table-hover">
          {foreach $areas as $area}
          {include name=areatree uri='design:sensor/parts/walk_item_table.tpl' item=$area recursion=0}		
          {/foreach}
        </table>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/', $areas[0].node.class_identifier, '/?parent=',$areas[0].node.parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi punto sulla mappa'|i18n('openpa_sensor/config')}</a></div>
      </div>
      {/if}
      
      {if $data|count()|gt(0)}
        {foreach $data as $item}
          {if $current_part|eq(concat('data-',$item.contentobject_id))}
          <div class="tab-pane active" id="{$item.name|slugize()}">            
            {if $item.children_count|gt(0)}
            <form action="#">
              <fieldset>
                <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
              </fieldset>
            </form>
            <table class="table table-hover">
              {foreach $item.children as $child}
              <tr>
                <td>
                  {*<a href="{$child.url_alias|ezurl(no)}">{$child.name|wash()}</a>*}{$child.name|wash()}
                </td>
                <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$child}</td>
                <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$child}</td>
              </tr>
              {/foreach}
            </table>
            <div class="pull-right"><a class="btn btn-danger"<a href="{concat('openpa/add/', $item.children[0].class_identifier, '/?parent=',$item.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi %classname'|i18n('openpa_sensor/config',, hash( '%classname', $item.children[0].class_name ))}</a></div>
            {/if}
          </div>
          {/if}
        {/foreach}
      {/if}      
    </div>
  
  </div>
  
  </div>
</div>