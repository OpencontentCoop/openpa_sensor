{let subscribed_nodes=$handler.rules}

  {def $sensor = sensor_root_handler()}
  {if $sensor.forum_is_enabled}

  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        Notifiche di aggiornamento discussioni
      </h4>
    </div>
    <div class="panel-body">
      <ul class="list-unstyled">
        {section name=Rules loop=$subscribed_nodes sequence=array(bgdark,bglight)}
          <li><input type="checkbox" name="SelectedRuleIDArray_{$handler.id_string}[]" value="{$Rules:item.id}" /> {$Rules:item.node.name|wash}</li>
        {/section}
      </ul>
    </div>
    <div class="panel-footer">
      <input class="btn btn-xs btn-danger" type="submit" name="RemoveRule_{$handler.id_string}" value="Rimuovi selezionati" />
    </div>
  </div>

  {/if}
  {undef $sensor}

{/let}


