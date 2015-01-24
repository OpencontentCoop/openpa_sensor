{let handlers=$handler.collaboration_handlers
     selection=$handler.collaboration_selections}

{def $sensor = sensor_root_handler()}
{if $sensor.post_is_enabled}

  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        Notifiche aggiornamento segnalazioni
      </h4>
    </div>

    <div class="panel-body">
      <input type="hidden" name="CollaborationHandlerSelection" value="1" />
      {section name=Handlers loop=$handlers}
        {if $Handlers:item.info.type-identifier|eq('openpasensor')}
          {let types=$:item.notification_types}
          {section show=or($:types,$:types|gt(0))}
          {section show=is_array($:types)}
            <p>{$:item.info.type-name|wash}</p>
            {section name=Types loop=$:types}
              <input type="checkbox" name="CollaborationHandlerSelection_{$handler.id_string}[]" value="{$Handlers:item.info.type-identifier}_{$:item.value}" {if $selection|contains(concat($Handlers:item.info.type-identifier,'_',$:item.value))} checked="checked"{/if} />
              {$:item.name|wash}
            {/section}
            {section-else}
              <input type="checkbox" name="CollaborationHandlerSelection_{$handler.id_string}[]" value="{$Handlers:item.info.type-identifier}" {if $selection|contains($Handlers:item.info.type-identifier)}checked="checked"{/if} />
              {$:item.info.type-name|wash}
            {/section}
          {/section}
          {/let}
        {/if}
      {/section}
    </div>

  </div>

{/if}
{undef $sensor}

{/let}