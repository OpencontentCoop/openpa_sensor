{if or( $sensor_post.can_do_something, or( $sensor_post.message_count, and( $sensor_post.can_comment, $sensor_post.can_send_private_message ) ) )}
    <aside class="widget well well-sm">

        {if $sensor_post.can_add_area}
            <strong>{'Quartiere/Zona'|i18n('openpa_sensor/post')}</strong>
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
            <strong>{'Area tematica'|i18n('openpa_sensor/post')}</strong>
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
            <strong>{'Scadenza'|i18n('openpa_sensor/post')} <small>{'in giorni'|i18n('openpa_sensor/post')}</small></strong>
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
            <strong>{'Azioni'|i18n('openpa_sensor/post')}</strong>
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
                <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Fix" value="{'Intervento terminato'|i18n('openpa_sensor/post')}" />
            </div>
        {/if}

        {if $sensor_post.can_force_fix}
            <div class="form-group">
                <input class="btn btn-danger btn-lg btn-block" type="submit" name="CollaborationAction_ForceFix" value="{'Forza chiusura'|i18n('openpa_sensor/post')}" />
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

        {include uri='design:sensor/parts/post/private_conversation.tpl'}

    </aside>
{/if}