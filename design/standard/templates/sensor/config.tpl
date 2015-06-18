{def $sensor = sensor_root_handler()}
{def $locales = fetch( 'content', 'translation_list' )}
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

{if $sensor.moderation_is_enabled}
  <div class="alert alert-warning">
    {'Moderazione attivata'|i18n('openpa_sensor/config')}
  </div>
{/if}

<div class="row">
  <div class="col-md-12">
	{*
	<h2>
	  {'Impostazioni'|i18n('openpa_sensor/config')}
	</h2>
  *}
  <ul class="list-unstyled">
    <li>{'Modifica impostazioni generali'|i18n('openpa_sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$root redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>

    {if $sensor.post_is_enabled}
      <li>{'Modifica informazioni Sensor'|i18n('openpa_sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$sensor.post_container_node redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>
    {/if}

    {if $sensor.forum_is_enabled}
      <li>{'Modifica informazioni Dimmi'|i18n('openpa_sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$sensor.forum_container_node redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>
    {/if}

    {if $sensor.survey_is_enabled}
      <li>{'Modifica informazioni Consultazioni'|i18n('openpa_sensor/config')} {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$sensor.survey_container_node redirect_if_discarded='/sensor/config' redirect_after_publish='/sensor/config'}</li>
    {/if}
  </ul>
		
	<hr />
		
  <div class="row">
  
    <div class="col-md-3">    
      <ul class="nav nav-pills nav-stacked">
        <li role="presentation" {if $current_part|eq('users')}class="active"{/if}><a href="{'sensor/config/users'|ezurl(no)}">{'Utenti'|i18n('openpa_sensor/config')}</a></li>
        {if $sensor.post_is_enabled}
          <li role="presentation" {if $current_part|eq('operators')}class="active"{/if}><a href="{'sensor/config/operators'|ezurl(no)}">{'Operatori'|i18n('openpa_sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('categories')}class="active"{/if}><a href="{'sensor/config/categories'|ezurl(no)}">{'Aree tematiche'|i18n('openpa_sensor/config')}</a></li>
          <li role="presentation" {if $current_part|eq('areas')}class="active"{/if}><a href="{'sensor/config/areas'|ezurl(no)}">{'Punti sulla mappa'|i18n('openpa_sensor/config')}</a></li>
        {/if}
        {if $sensor.forum_is_enabled}
          <li role="presentation" {if $current_part|eq('dimmi')}class="active"{/if}><a href="{'sensor/config/dimmi'|ezurl(no)}">{'Discussioni Dimmi'|i18n('openpa_sensor/config')}</a></li>
        {/if}
        {if $sensor.survey_is_enabled}
          <li role="presentation" {if $current_part|eq('survey')}class="active"{/if}><a href="{'sensor/config/survey'|ezurl(no)}">{'Consultazioni'|i18n('openpa_sensor/config')}</a></li>
        {/if}
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
          {include name=cattree uri='design:sensor/parts/walk_item_table.tpl' item=$category recursion=0 redirect_if_discarded='/sensor/config/categories' redirect_after_publish='/sensor/config/categories' redirect_if_cancel='/sensor/config/categories' redirect_after_remove='/sensor/config/categories'}		
          {/foreach}
        </table>
		<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/sensor_category/',$categories[0].node.parent_node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/sensor_category/?parent=',$categories[0].node.parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi'|i18n('openpa_sensor/config')} {$categories[0].node.class_name}</a></div>
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
              {if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'behalf', 'user_id', $operator.contentobject_id ) )}
                <span title="{"L'utente può inserire segnalazioni per conto di altri"|i18n('openpa_sensor/config')}"><i class="fa fa-life-ring"></i></span>
              {/if}
            </td>
            <td>              
              {foreach $operator.object.available_languages as $language}
                {foreach $locales as $locale}
                  {if $locale.locale_code|eq($language)}
                    <img src="{$locale.locale_code|flag_icon()}" />
                  {/if}
                {/foreach}
              {/foreach}
            </td>
            <td width="1">
              <a href="{concat('sensor/user/',$operator.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>            
            <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$operator redirect_if_discarded='/sensor/config/operators' redirect_after_publish='/sensor/config/operators'}</td>
            <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$operator redirect_if_cancel='/sensor/config/operators' redirect_after_remove='/sensor/config/operators'}</td>
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
		<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/sensor_operator/',$operators[0].parent_node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/sensor_operator/?parent=',$operators[0].parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi'|i18n('openpa_sensor/config')} {$operators[0].class_name}</a></div>
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
              {*<a href="{$user.url_alias|ezurl(no)}">{$user.name|wash()}</a>*}{$user.name|wash()} <small><em>{$user.data_map.user_account.content.email|wash()}</em></small>
              {if $userSetting.is_enabled|not()}</span>{/if}
            </td>
            <td width="1">
              {*include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$user*}
              <a href="{concat('sensor/user/',$user.contentobject_id)|ezurl(no)}"><i class="fa fa-user"></i></a>
            </td>
            <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$user redirect_if_cancel='/sensor/config/users' redirect_after_remove='/sensor/config/users'}</td>
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
		
		<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/user/',ezini("UserSettings", "DefaultUserPlacement"))|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
        
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
          {include name=areatree uri='design:sensor/parts/walk_item_table.tpl' item=$area recursion=0 redirect_if_discarded='/sensor/config/areas' redirect_after_publish='/sensor/config/areas' redirect_if_cancel='/sensor/config/areas' redirect_after_remove='/sensor/config/areas'}
          {/foreach}
        </table>
		<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/sensor_area/',$areas[0].node.parent_node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
        <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/sensor_area/?parent=',$areas[0].node.parent_node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi'|i18n('openpa_sensor/config')} {$areas[0].node.class_name}</a></div>
      </div>
      {/if}

      {if and( $current_part|eq('dimmi'), $sensor.forum_is_enabled )}
        <div class="tab-pane active" id="dimmi">
          <form action="#">
            <fieldset>
              <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
            </fieldset>
          </form>
          <table class="table table-hover">
            {foreach $forums as $forum}
              {include name=forumtree uri='design:sensor/parts/walk_item_table.tpl' item=$forum recursion=0 insert_child_class=true() redirect_if_discarded='/sensor/config/dimmi' redirect_after_publish='/sensor/config/dimmi'  redirect_if_cancel='/sensor/config/dimmi' redirect_after_remove='/sensor/config/dimmi'}
            {/foreach}
          </table>
		  <div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/dimmi_forum/',$sensor.forum_container_node.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
          <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/dimmi_forum/?parent=',$sensor.forum_container_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi discussione'|i18n('openpa_sensor/config')}</a></div>
        </div>
      {/if}

      {if and( $current_part|eq('survey'), $sensor.survey_is_enabled )}
        <div class="tab-pane active" id="survey">
          <form action="#">
            <fieldset>
              <input type="text" name="search" value="" class="quick_search form-control" placeholder="{'Cerca'|i18n('openpa_sensor/config')}" autofocus />
            </fieldset>
          </form>
          <table class="table table-hover">
            {foreach $sensor.surveys as $survey}
              <tr>
                <td>
                  {*<a href="{$survey.node.url_alias|ezurl(no)}">{$survey.object.name|wash()}</a>*}{$survey.object.name|wash()}
                </td>
                <td>
                  {foreach $survey.object.available_languages as $language}
                    {foreach $locales as $locale}
                      {if $locale.locale_code|eq($language)}
                        <img src="{$locale.locale_code|flag_icon()}" />
                      {/if}
                    {/foreach}
                  {/foreach}
                </td>
                <td>
                  {if $survey.survey_content.survey.enabled}<i class="fa fa-unlock"></i>{else}<i class="fa fa-lock"></i>{/if}
                </td>
                <td>
                  <small>
                    <i class="fa fa-clock-o"></i>
                    {if $survey.survey_content.survey.valid_from_array.no_limit|not}{$survey.survey_content.survey.valid_from|l10n('shortdate')}{else}...{/if} -
                    {if $survey.survey_content.survey.valid_to_array.no_limit|not}{$survey.survey_content.survey.valid_to|l10n('shortdate')}{else}...{/if}
                  </small>
                </td>
                <td>
                  {if $survey.survey_content.survey.persistent}<i class="fa fa-save"></i>{/if}
                </td>
                <td>
                  {if $survey.survey_content.survey.one_answer}<i class="fa fa-thumbs-up "></i>{/if}
                </td>
                <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$survey.node redirect_if_discarded='/sensor/config/survey' redirect_after_publish='/sensor/config/survey'}</td>
                <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$survey.node redirect_if_cancel='/sensor/config/survey' redirect_after_remove='/sensor/config/survey'}</td>
              </tr>
            {/foreach}
          </table>
		  <div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/consultation_survey/',$sensor.survey_container_node.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
          <div class="pull-right"><a class="btn btn-danger" href="{concat('openpa/add/consultation_survey/?parent=',$sensor.survey_container_node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i> {'Aggiungi consultazione'|i18n('openpa_sensor/config')}</a></div>
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
                <td>              
                  {foreach $child.object.available_languages as $language}
                    {foreach $locales as $locale}
                      {if $locale.locale_code|eq($language)}
                        <img src="{$locale.locale_code|flag_icon()}" />
                      {/if}
                    {/foreach}
                  {/foreach}
                </td>
                <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$child redirect_if_discarded=concat('/sensor/config/data-',$item.contentobject_id) redirect_after_publish=concat('/sensor/config/data-',$item.contentobject_id)}</td>
                <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$child redirect_if_cancel=concat('/sensor/config/data-',$item.contentobject_id) redirect_after_remove=concat('/sensor/config/data-',$item.contentobject_id)}</td>
              </tr>
              {/foreach}
            </table>
			<div class="pull-left"><a class="btn btn-info" href="{concat('exportas/csv/', $item.children[0].class_identifier, '/',$item.node_id)|ezurl(no)}">{'Esporta in CSV'|i18n('openpa_sensor/config')}</a></div>
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