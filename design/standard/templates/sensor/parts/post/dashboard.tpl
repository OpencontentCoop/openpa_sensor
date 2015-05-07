<section class="hgroup">
  <div class="row">
    <div class="col-md-8">
      <h1>
        {"Le mie segnalazioni"|i18n('openpa_sensor/dashboard')}
        {if $simplified_dashboard|not()}<br /><small>{"Segnalazioni da leggere, in corso e chiuse"|i18n('openpa_sensor/dashboard')}</small>{/if}
      </h1>
    </div>
    <div class="col-md-4">
      <small>
        <strong>{"Legenda:"|i18n('openpa_sensor/dashboard')}</strong><br />
        <i class="fa fa-comments-o"></i> {"indica la presenza di messaggi"|i18n('openpa_sensor/dashboard')} <br />
        <i class="fa fa-comments"></i> {"indica la presenza di messaggi non letti"|i18n('openpa_sensor/dashboard')} <br />
        <i class="fa fa-exclamation-triangle"></i> {"indica la presenza di variazioni in cronologia non lette"|i18n('openpa_sensor/dashboard')}
      </small>
    </div>
  </div>
</section>

{if $simplified_dashboard}

  {if $all_items|count()|gt(0)}
    {include uri="design:sensor/parts/post/table_items.tpl" item_list=$all_items name=all_items}
    {include name=navigator
            uri='design:navigator/google.tpl'
            page_uri='sensor/dashboard'
            item_count=$all_items_count
            view_parameters=$view_parameters
            item_limit=$limit}
  {/if}

{else}

  <div class="row">
    <div class="col-md-9">

      <ul class="nav nav-pills" style="margin-bottom: 10px">
        {foreach $list_types as $type}
          <li role="presentation" {if $current_list.identifier|eq($type.identifier)}class="active"{/if}>
            <a href="{concat('sensor/dashboard/(list)/',$type.identifier,$filters_query)|ezurl(no)}">
              {$type.name|wash()}
              <span class="badge">{$type.count}</span>
            </a>
          </li>
        {/foreach}
        <li role="presentation" class="pull-right">
            <a href="{concat('sensor/dashboard/post/default/csv/',$filters_query)|ezurl(no)}">
              <small><i class="fa fa-download"></i>
              {if $filters_query|eq('')}
                {"Esporta CSV"|i18n('openpa_sensor/dashboard')}
              {else}
                {"Esporta risultati CSV"|i18n('openpa_sensor/dashboard')}
              {/if}
              </small>
            </a>
          </li>
      </ul>
      <div class="tab-pane active">
        {include name=navigator
                uri='design:navigator/google.tpl'
                page_uri=concat('sensor/dashboard/(list)/',$current_list.identifier,$filters_query)
                item_count=$current_list.count
                view_parameters=$view_parameters
                item_limit=$limit}
        {include uri="design:sensor/parts/post/table_items.tpl" item_list=$items name=items}
        {include name=navigator
                uri='design:navigator/google.tpl'
                page_uri=concat('sensor/dashboard/(list)/',$current_list.identifier,$filters_query)
                item_count=$current_list.count
                view_parameters=$view_parameters
                item_limit=$limit}
      </div>
    </div>
    
    <div class="col-md-3" id="sidebar">
      {ezscript_require(array('ezjsc::jquery','ezjsc::jqueryUI'))}
      <script type="text/javascript">
      {literal}$(function() {
        $( ".from_picker" ).datepicker({defaultDate: "+1w",changeMonth: true,changeYear: true,dateFormat: "dd-mm-yy",numberOfMonths: 1});
        $( ".to_picker" ).datepicker({defaultDate: "+1w",changeMonth: true,changeYear: true,dateFormat: "dd-mm-yy",numberOfMonths: 1});    
      });{/literal}
      </script>
      
      <div class="well dashboard-search">
        <form method="get" action="{'sensor/dashboard/post'|ezurl(no)}" class="form">
          <div class="form-group">
            <label class="" for="searchId">Cerca per ID</label>
            <input type="text" value="{if is_set($filters.id)}{$filters.id|wash()}{/if}" placeholder="Cerca per ID" name="filters[id]" id="searchId" class="form-control">
          </div>
          <div class="form-group">
            <label class="" for="searchCreatorId">Cerca per autore</label>
            <input type="text" value="{if is_set($filters.creator_id)}{$filters.creator_id|wash()}{/if}" placeholder="Cerca per autore" name="filters[creator_id]" id="searchCreatorId" class="form-control">
          </div>
          <div class="form-group">
            <label class="" for="searchSubject">Cerca per oggetto</label>
            <input type="text" value="{if is_set($filters.subject)}{$filters.subject|wash()}{/if}" placeholder="Cerca per oggetto" name="filters[subject]" id="searchSubject" class="form-control">
          </div>
          {def $fake_relation_list = array()}
          {if is_set( $filters.category )}
            {foreach $filters.category as $category}
              {set $fake_relation_list = $fake_relation_list|append( hash( 'contentobject_id', $category ) )}
            {/foreach}
          {/if}
          {def $fake_attribute = hash( 'content', hash( 'relation_list', $fake_relation_list ) )}      
          <div class="form-group">
            <label class="" for="searchCategory">Cerca per area tematica</label>
            <select data-placeholder="{'Cerca per area tematica'|i18n('openpa_sensor/post')}" name="filters[category][]" class="chosen form-control" id='searchCategory'>
              <option value="">{'Cerca per area tematica'|i18n('openpa_sensor/post')}</option>				  
              {foreach sensor_root_handler().categories.tree as $category}
                {include name=cattree uri='design:sensor/parts/walk_item_option.tpl' item=$category recursion=0 attribute=$fake_attribute}
              {/foreach}
            </select>
          </div>
          <div class="form-group">
            <label for="from" class="">Data creazione (inizio)</label>
            <input type="text" class="form-control from_picker" name="filters[creation_range][from]" placeholder="Data creazione (inizio)" value="{if is_set($filters.creation_range.from)}{$filters.creation_range.from|wash()}{/if}" />
          </div>
          <div class="form-group">
            <label for="to" class="">Data creazione (fine)</label>
            <input class="form-control to_picker" type="text" name="filters[creation_range][to]" placeholder="Data creazione (fine)" value="{if is_set($filters.creation_range.to)}{$filters.creation_range.to|wash()}{/if}" />
          </div>
          <button class="btn btn-info" type="submit"><span class="fa fa-search"></span> Cerca</button>
          <a class="btn btn-danger pull-right" title="Reset" href="{'sensor/dashboard/post'|ezurl(no)}"><span class="fa fa-close"></span> Annulla</a>      
        </form>
      </div>
      
      <aside class="widget" style="height: 570px;overflow-y: auto">
        <h4 class="section_header">In scadenza</h4>
        {def $expiring_items = fetch( 'sensor', 'items', hash( 'type', 'expiring', 'limit', 100 ) )}
        {if count( $expiring_items )|gt(0)}
          <ul class="media-list">
          {foreach $expiring_items as $item}              
            <li class="media">
              <a class="media-date" href={concat('sensor/posts/',$item.content.content_object_id)|ezurl()} style="opacity: 1">
                {$item.content.helper.expiring_date.timestamp|datetime('custom', '%j')}<span>{$item.content.helper.expiring_date.timestamp|datetime('custom', '%M')}
              </a>
              <h5>
                <a href={concat('sensor/posts/',$item.content.content_object_id)|ezurl()}>
                  <strong>{$item.content.helper.object.id}</strong> {$item.content.helper.object.name|wash()}  
                </a>
              </h5>
              <small>{$item.content.helper.expiring_date.text|wash()}</small>
            </li>              
          {/foreach}
          </ul>
        {/if}
      </aside>
      
    </div>    
  </div>
{/if}
