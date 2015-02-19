<div class="service_teaser vertical wow animated flipInX animated">
    {if $survey.node|has_attribute('image')}
        <div class="service_photo">
            <figure style="background-image:url({$survey.node|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
        </div>
    {/if}

    <div class="service_details">
        <h2 class="section_header skincolored">
            <a href="{concat('sensor/survey/',$survey.node.node_id)|ezurl(no)}">{$survey.node.name|wash()}</a>
        </h2>

        {attribute_view_gui attribute=$survey.node|attribute('abstract')}

        {if $survey.node|has_attribute( 'call_to_action' )}
            <div class="alert alert-warning">
                {attribute_view_gui attribute=$survey.node.object.data_map.call_to_action}
            </div>
        {/if}

        {def $data = fetch( 'sensor', 'survey_data', hash( 'contentobject_id', $survey.object.id, 'user_id', $current_user.contentobject_id ) )}
        {if $data.need_login}
            <a href="#login" class="btn btn-primary btn-lg btn-block">{'Accedi per rispondere'|i18n('openpa_sensor/menu')}</a>
        {else}
            {if $data.can_add_response}
                <a href="{concat('sensor/survey/',$survey.node.node_id)|ezurl(no)}" class="btn btn-primary btn-block btn-lg">{'Rispondi'|i18n('openpa_sensor/survey')}</a>
            {elseif $data.can_modify_response}
                <a href="{concat('sensor/survey/',$survey.node.node_id)|ezurl(no)}" class="btn btn-warning btn-block btn-lg">{'Modifica risposta'|i18n('openpa_sensor/survey')}</a>
            {/if}
            {if $data.user_result_count|gt(0)}
                <p><a {if and( $data.can_add_response|not(), $data.can_modify_response|not() )}class="btn btn-lg btn-block btn-success"{/if} href="{concat('sensor/survey_user_result/',$survey.object.id)|ezurl(no)}">{'Vedi risposte'|i18n('openpa_sensor/survey')}</a></p>
            {/if}
        {/if}
        {undef $data}

    </div>
</div>