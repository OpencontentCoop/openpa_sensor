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
        
      <aside class="widget">
        <h4>{'Soggetti coinvolti'|i18n('openpa_sensor/post')}</h4>
        <dl class="dl">
          {foreach $sensor_post.participants as $participant_role}
            <dt>{$participant_role.role_name|wash}:</dt>
            <dd><ul class="list-unstyled">
            {foreach $participant_role.items as $participant}
                {if $participant.contentobject}
                  <li>
                    <small>
					{include uri='design:content/view/sensor_person.tpl' sensor_person=$participant.contentobject}
					{if and( $participant_role.role_id|eq(5), $sensor_post.object|has_attribute('on_behalf_of') )}
					  [{$sensor_post.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$sensor_post.object|attribute('on_behalf_of').content|wash()}]
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
        
      {if $sensor_post.can_do_something}
        <aside class="widget well well-sm">      

          {if $sensor_post.can_add_area}
            <h4>{'Quartiere/Zona'|i18n('openpa_sensor/post')}</h4>
            <div class="form-group">
            <div class="row">
              <div class="col-xs-8">
              <select data-placeholder="{'Seleziona Quartiere/Zona'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemArea[]" class="chosen form-control">
                <option></option>
                {foreach $sensor_post.areas.tree as $area}
                {include name=area uri='design:sensor/parts/walk_item_option.tpl' item=$area recursion=0 attribute=$sensor_post.object.data_map.area}
                {/foreach}
              </select>              
              </div>
              <div class="col-xs-4">
              <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddArea" value="{'Associa'|i18n('openpa_sensor/post')}" />
              </div>
            </div>
            </div>
          {/if}
	  
          {if $sensor_post.can_add_category}
            <h4>{'Area tematica'|i18n('openpa_sensor/post')}</h4>
            <div class="form-group">
            <div class="row">
              <div class="col-xs-8">
              <select data-placeholder="{'Seleziona area tematica'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemCategory[]" class="chosen form-control">
                <option></option>
                {foreach $sensor_post.categories.tree as $category}
                {include name=cattree uri='design:sensor/parts/walk_item_option.tpl' item=$category recursion=0 attribute=$sensor_post.object.data_map.category}
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

          {if $sensor_post.can_set_expiry}
            <h4>{'Scadenza'|i18n('openpa_sensor/post')} <small>{'in giorni'|i18n('openpa_sensor/post')}</small></h4>
            <div class="form-group">
              <div class="row">
                <div class="col-xs-8">
                  <input type="text" class="form-control" name="Collaboration_OpenPASensorItemExpiry" value="{$sensor_post.expiration_days|wash()}" />
                </div>
                <div class="col-xs-4">
                  <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_SetExpiry" value="{'Imposta'|i18n('openpa_sensor/post')}" />
                </div>
              </div>
            </div>
          {/if}

          {if or(
            $sensor_post.can_assign,
            $sensor_post.can_add_observer,
            $sensor_post.can_fix,
            $sensor_post.can_close,
            and( $sensor_post.current_privacy_state.identifier|ne('private'), $sensor_post.can_change_privacy ),
            and( $sensor_post.current_moderation_state.identifier|eq('waiting'), $sensor_post.can_moderate )
          )}
            <h4>{'Azioni'|i18n('openpa_sensor/post')}</h4>
          {/if}
        
          {if $sensor_post.can_assign}
          <div class="form-group">
            <div class="row">
              <div class="col-xs-8">
                <select data-placeholder="{'Seleziona operatore'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemAssignTo[]" class="chosen form-control">
                  <option></option>                                
                  {foreach $sensor_post.operators as $user}
                      <option value="{$user.contentobject_id}">{include uri='design:content/view/sensor_person.tpl' sensor_person=$user.object}</option>
                  {/foreach}
                </select>
              </div>
              <div class="col-xs-4">
                <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_Assign" value="{if $sensor_post.has_owner|not()}{'Assegna'|i18n('openpa_sensor/post')}{else}{'Riassegna'|i18n('openpa_sensor/post')}{/if}" />
              </div>
            </div>
          </div>
          {/if}
      
          {if $sensor_post.can_add_observer}
          <div class="form-group">
            <div class="row">
              <div class="col-xs-8">
                <select data-placeholder="{'Seleziona operatore'|i18n('openpa_sensor/post')}" name="Collaboration_OpenPASensorItemAddObserver" class="chosen form-control">
                  <option></option>
                  {foreach $sensor_post.operators as $user}
                      <option value="{$user.contentobject_id}">{include uri='design:content/view/sensor_person.tpl' sensor_person=$user.object}</option>
                  {/foreach}
                </select>
              </div>
              <div class="col-xs-4">
                <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddObserver" value="{'Aggiungi cc'|i18n('openpa_sensor/post')}" />
              </div>
            </div>
          </div>
          {/if}
      
          {if $sensor_post.can_fix}
          <div class="form-group">
            <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Fix" value="{'Intervento terminato'|i18n('openpa_sensor/post')}" /><br />
          </div>
          {/if}
      
          {if $sensor_post.can_close}
          <div class="form-group">
            <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Close" value="{'Chiudi'|i18n('openpa_sensor/post')}" />
          </div>
          {/if}

          {if $sensor_post.can_change_privacy}
            {if $sensor_post.current_privacy_state.identifier|eq('public')}
              <div class="form-group">
              <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_MakePrivate" value="{'Rendi la segnalazione privata'|i18n('openpa_sensor/post')}" />
              </div>
            {elseif $sensor_post.current_privacy_state.identifier|eq('private')}
              <div class="form-group">
                <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_MakePublic" value="{'Rendi la segnalazione pubblica'|i18n('openpa_sensor/post')}" />
              </div>
            {/if}
          {/if}

          {if and( $sensor_post.current_moderation_state.identifier|eq('waiting'), $sensor_post.can_moderate )}
            <div class="form-group">
              {*
              <select name="Collaboration_OpenPASensorItemModerationIdentifier" class="form-control">
                <option value="approved">{'Approva'|i18n('openpa_sensor/post')}</option>
                <option value="refused">{'Rifiuta'|i18n('openpa_sensor/post')}</option>
              </select>
              *}
              <input class="btn btn-default btn-lg btn-block" type="submit" name="CollaborationAction_Moderate" value="{'Elimina moderazione'|i18n('openpa_sensor/post')}" />
            </div>
          {/if}

        </aside>
      {/if}
    
    </div>
  </div>
  <input type="hidden" name="CollaborationActionCustom" value="custom" />
  <input type="hidden" name="CollaborationTypeIdentifier" value="openpasensor" />
  <input type="hidden" name="CollaborationItemID" value="{$sensor_post.collaboration_item.id}" />

</form>

{/if} {* if error *}
