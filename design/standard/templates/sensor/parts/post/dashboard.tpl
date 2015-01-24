<section class="hgroup">
  <div class="row">
    <div class="col-md-8">
      <h1>
        {"Le mie segnalazioni"|i18n('openpa_sensor/dashboard')}
        {if $simplified_dashboard|not()}<br /><small>Segnalazioni da leggere, in corso e chiuse</small>{/if}
      </h1>
    </div>
    <div class="col-md-4">
      <small>
        <strong>Legenda:</strong><br />
        <i class="fa fa-comments-o"></i> indica la presenza di messaggi <br />
        <i class="fa fa-comments"></i> indica la presenza di messaggi non letti <br />
        <i class="fa fa-exclamation-triangle"></i> indica la presenza di variazioni in cronologia non lette
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

  <ul class="nav nav-pills" style="margin-bottom: 10px">
    {foreach $list_types as $type}
      <li role="presentation" {if $current_list.identifier|eq($type.identifier)}class="active"{/if}>
        <a href="{concat('sensor/dashboard/(list)/',$type.identifier)|ezurl(no)}">
          {$type.name|wash()}
          <span class="badge">{$type.count}</span>
        </a>
      </li>
    {/foreach}
  </ul>
  <div class="tab-pane active">
    {include uri="design:sensor/parts/post/table_items.tpl" item_list=$items name=items}
    {include name=navigator
            uri='design:navigator/google.tpl'
            page_uri=concat('sensor/dashboard/(list)/',$current_list.identifier)
            item_count=$current_list.count
            view_parameters=$view_parameters
            item_limit=$limit}
  </div>

{/if}