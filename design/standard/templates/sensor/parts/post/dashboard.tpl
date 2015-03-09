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
  {ezscript_require(array('ezjsc::jquery','ezjsc::jqueryUI'))}
  <script type="text/javascript">
  {literal}
  $(function() {
    $( ".from_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1

    });
    $( ".to_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1
    });    
  });
  {/literal}
  </script>
  
  <div class="well dashboard-search">
    <form method="get" action="{'sensor/dashboard/post'|ezurl(no)}" class="form-inline">
      <div class="form-group">
        <label class="sr-only" for="searchId">Cerca per ID</label>
        <input type="text" value="{if is_set($filters.id)}{$filters.id|wash()}{/if}" placeholder="Cerca per ID" name="filters[id]" id="searchId" class="form-control">
      </div>
      <div class="form-group">
        <label class="sr-only" for="searchCreatorId">Cerca per autore</label>
        <input type="text" value="{if is_set($filters.creator_id)}{$filters.creator_id|wash()}{/if}" placeholder="Cerca per autore" name="filters[creator_id]" id="searchCreatorId" class="form-control">
      </div>
      <div class="form-group">
        <label class="sr-only" for="searchSubject">Cerca per oggetto</label>
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
        <select data-placeholder="{'Cerca per area tematica'|i18n('openpa_sensor/post')}" name="filters[category][]" class="chosen form-control">
				  <option value="">{'Cerca per area tematica'|i18n('openpa_sensor/post')}</option>				  
          {foreach sensor_root_handler().categories.tree as $category}
					{include name=cattree uri='design:sensor/parts/walk_item_option.tpl' item=$category recursion=0 attribute=$fake_attribute}
				  {/foreach}
				</select>
      </div>
      <div class="form-group">
        <label for="from" class="sr-only">Dalla data</label>
        <input type="text" class="form-control from_picker" name="filters[expiring_range][from]" placeholder="Scade da" value="{if is_set($filters.expiring_range.from)}{$filters.expiring_range.from|wash()}{/if}" />
      </div>
      <div class="form-group">
        <label for="to" class="sr-only">Alla data</small></label>
        <input class="form-control to_picker" type="text" name="filters[expiring_range][to]" placeholder="Scade a" value="{if is_set($filters.expiring_range.to)}{$filters.expiring_range.to|wash()}{/if}{/if}" />
      </div>
      <button class="btn btn-info" type="submit"><span class="fa fa-search"></span></button>
      <a class="btn btn-danger" title="Reset" href="{'sensor/dashboard/post'|ezurl(no)}"><span class="fa fa-close"></span></a>      
    </form>
  </div>

  <ul class="nav nav-pills" style="margin-bottom: 10px">
    {foreach $list_types as $type}
      <li role="presentation" {if $current_list.identifier|eq($type.identifier)}class="active"{/if}>
        <a href="{concat('sensor/dashboard/(list)/',$type.identifier,$filters_query)|ezurl(no)}">
          {$type.name|wash()}
          <span class="badge">{$type.count}</span>
        </a>
      </li>
    {/foreach}
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

{/if}