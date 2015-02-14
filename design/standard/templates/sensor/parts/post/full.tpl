{ezscript_require(array('ezjsc::jquery', 'plugins/chosen.jquery.js'))}
{ezcss_require('plugins/chosen.css')}
<script>{literal}$(document).ready(function(){$("select.chosen").chosen({width:'100%'});});{/literal}</script>

{if is_set( $error )}
  <div class="alert alert-danger">{$error}</div>
{else}

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">

  <section class="hgroup">
    <h1>
      {$object.name|wash()} <small>{$object.owner.name|wash()} {if $object|has_attribute('on_behalf_of')}[{$object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$object|attribute('on_behalf_of').content|wash()}]{/if}</small>
    </h1>
      <ul class="breadcrumb pull-right">
        <li>
          <span class="label
          label-{$post.type.css_class}">{$post.type.name}</span> <span
          class="label
          label-{$post.current_status.css_class}">{$post.current_status.name}</span>
          {if $post.current_privacy_status.identifier|eq('private')}
            <span class="label
            label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
          {/if}
        </li>
      </ul>
  </section>

  <div class="row">
    <div class="col-md-8">
    
      <div class="row">
        <div class="col-md-4">
          <aside class="widget">            
            {include uri='design:sensor/parts/post/map.tpl'}
          </aside>
        </div>
        <div class="col-md-8">
          <p>{attribute_view_gui attribute=$object|attribute('description')}</p>
          {if $object|has_attribute('attachment')}
            <p>{attribute_view_gui attribute=$object|attribute('attachment')}</p>
          {/if}
          {if $object|has_attribute('image')}
            <figure>{attribute_view_gui attribute=$object|attribute('image') image_class='large' alignment=center}</figure>
          {/if}
          <ul class="list-inline">
            <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('openpa_sensor/post')} {$object.published|l10n(shortdatetime)}</small></li>
            {if $object.modified|gt($object.published)}
                <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor/post')} {$object.modified|l10n(shortdatetime)}</small></li>
            {/if}
          </ul>
          <ul class="list-inline">
            <li><small><i class="fa fa-user"></i> {'In carico a'|i18n('openpa_sensor/post')} {$post.current_owner}</small></li>
            <li><small><i class="fa fa-comments"></i> {$post.comment_count} {'commenti'|i18n('openpa_sensor/post')}</small></li>
            <li><small><i class="fa fa-comment"></i> {$post.response_count} {'risposte ufficiali'|i18n('openpa_sensor/post')}</small></li>
            {if $object.data_map.category.has_content}
              <li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$object.data_map.category}</small></li>
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
        
      <aside class="widget">
        <h4>{'Soggetti coinvolti'|i18n('openpa_sensor/post')}</h4>
        <dl class="dl">
          {foreach $participant_list as $item}
            <dt>{$item.name|wash}:</dt>            
            <dd><ul class="list-unstyled">
            {foreach $item.items as $p}
                {if is_set( $p.participant.contentobject )}
                  <li>
                    <small>
                      {$p.participant.contentobject.name|wash()}
                      {if and( $item.role_id|eq(5), $object|has_attribute('on_behalf_of') )}
                        [{$object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$object|attribute('on_behalf_of').content|wash()}]
                      {/if}
                    </small>
                  </li>
                {else}
                  <li>?</li>
                {/if}
            {/foreach}
            </ul></dd>
          {/foreach}
        </dl>
      </aside>
    
      {def $m = fetch("collaboration","message_list",hash("item_id",$collaboration_item.id, "sort_by", array("created",true())))
           $hasTimeline = false()}
      {foreach $m as $item}
          {if $item.message_type|eq(0)}
              {set $hasTimeline = true()}
              {break}
          {/if}
      {/foreach}
      {if $hasTimeline}
      <aside class="widget timeline">
        <h4>{'Cronologia'|i18n('openpa_sensor/post')}</h4>
        <ol class="list-unstyled">
          {foreach $m as $item}
            {if $item.message_type|eq(0)}
              <li>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
                <div class="title">{$item.created|l10n(shortdatetime)}</div>
                <div class="content"><small>{$item.simple_message.data_text1|sensor_robot_message()}</small></div>
              </li>
            {/if}
          {/foreach}
        </dl>
      </aside>
      {/if}
        
      {if $helper.can_do_something}
      <aside class="widget well well-sm">      
        
		{if $helper.can_add_area}
		  <h4>{'Quartiere/Zona'|i18n('openpa_sensor/post')}</h4>
		  <div class="form-group">
			<div class="row">
			  <div class="col-xs-8">
				<select data-placeholder="{'Seleziona Quartiere/Zona'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemArea[]" class="chosen form-control">
				  <option></option>
				  {foreach $post.areas.tree as $area}
					{include name=area uri='design:sensor/parts/walk_item_option.tpl' item=$area recursion=0 attribute=$object.data_map.area}
				  {/foreach}
				</select>              
			  </div>
			  <div class="col-xs-4">
				<input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddArea" value="{'Associa'|i18n('openpa_sensor/post')}" />
			  </div>
			</div>
		  </div>
		{/if}
	  
		{if $helper.can_add_category}
		  <h4>{'Area tematica'|i18n('openpa_sensor/post')}</h4>
		  <div class="form-group">
			<div class="row">
			  <div class="col-xs-8">
				<select data-placeholder="{'Seleziona area tematica'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemCategory[]" class="chosen form-control">
				  <option></option>
				  {foreach $post.categories.tree as $category}
					{include name=cattree uri='design:sensor/parts/walk_item_option.tpl' item=$category recursion=0 attribute=$object.data_map.category}
				  {/foreach}
				</select>
				{if openpaini( 'SensorConfig', 'CategoryAutomaticAssign', 'disabled' )|eq( 'enabled' )}
				<div class="checkbox">
				  <label>
					<input type="checkbox" name="Collaboration_OpenPASensorItemAssignToCategoryApprover"> {"Assegna al responsabile dell'area selezionata"|i18n('openpa_sensor/post')}
				  </label>
				</div>
				{/if}
			  </div>
			  <div class="col-xs-4">
				<input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddCategory" value="{'Associa'|i18n('openpa_sensor/post')}" />
			  </div>
			</div>
		  </div>
		{/if}
		
		<h4>{'Azioni'|i18n('openpa_sensor/post')}</h4>
        
        {if $helper.can_assign}
        <div class="form-group">
          <div class="row">
            <div class="col-xs-8">
              <select data-placeholder="{'Seleziona operatore'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemAssignTo[]" class="chosen form-control">
                <option></option>                                
                {foreach $post.operators as $user}
                  {if $user.contentobject_id|ne($current_participant.participant_id)}
                    <option value="{$user.contentobject_id}">{$user.name|wash()}</option>
                  {/if}
                {/foreach}
              </select>
            </div>
            <div class="col-xs-4">
              <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_Assign" value="{if $helper.has_owner|not()}{'Assegna'|i18n('openpa_sensor/post')}{else}{'Riassegna'|i18n('openpa_sensor/post')}{/if}" />
            </div>
          </div>
        </div>
        {/if}
    
        {if $helper.can_add_observer}
        <div class="form-group">
          <div class="row">
            <div class="col-xs-8">
              <select data-placeholder="{'Seleziona operatore'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemAddObserver" class="chosen form-control">
                <option></option>
                {foreach $post.operators as $user}
                  {if $user.contentobject_id|ne($current_participant.participant_id)}
                    <option value="{$user.contentobject_id}">{$user.name|wash()}</option>
                  {/if}
                {/foreach}
              </select>
            </div>
            <div class="col-xs-4">
              <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddObserver" value="{'Aggiungi cc'|i18n('openpa_sensor/post')}" />
            </div>
          </div>
        </div>
        {/if}
    
        {if $helper.can_fix}
        <div class="form-group">
          <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Fix" value="{'Intervento terminato'|i18n('openpa_sensor/post')}" /><br />
        </div>
        {/if}
    
        {if $helper.can_close}
        <div class="form-group">
          <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Close" value="{'Chiudi'|i18n('openpa_sensor/post')}" />
        </div>
        {/if}
		
		{if and( $post.current_privacy_status.identifier|ne('private'), $helper.can_change_privacy )}
		  <div class="form-group">
			<input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_MakePrivate" value="{'Rendi la segnalazione privata'|i18n('openpa_sensor/post')}" />
		  </div>
		{/if}
    
      </aside>
      {/if}
    
    </div>
  </div>

  <input type="hidden" name="CollaborationActionCustom" value="custom" />
  <input type="hidden" name="CollaborationTypeIdentifier" value="openpasensor" />
  <input type="hidden" name="CollaborationItemID" value="{$collaboration_item.id}" />

</form>

{/if} {* if error *}