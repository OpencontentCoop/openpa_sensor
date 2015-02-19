{if is_set($sensor)|not()}
{def $sensor = sensor_root_handler()}
{/if}

{def $valid_surveys = $sensor.valid_surveys}

{if count($valid_surveys)|eq(1)}

    {include uri='design:sensor/parts/survey/single_survey.tpl' survey=$valid_surveys[0]}

{else}

    <div class="service_teaser vertical wow animated flipInX animated">
        {if $sensor.survey_container_node|has_attribute( 'image' )}
            <div class="service_photo">
                <figure style="background-image:url({$sensor.survey_container_node|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
            </div>
        {/if}
        <div class="service_details">
            <h2 class="section_header skincolored">
                {$sensor.survey_container_node.data_map.name.content|wash()}
            </h2>
            {if $sensor.survey_container_node|has_attribute('short_description')
                {attribute_view_gui attribute=$sensor.survey_container_node.data_map.short_description}
                <hr />
            {/if}
            {foreach $valid_surveys as $survey}
                <div>
                    <h4>{$survey.node.name|wash()}</h4>
                    {if $survey.node|has_attribute('abstract')}
                        {attribute_view_gui attribute=$survey.node|attribute('abstract')}
                    {/if}
                    {def $data = fetch( 'sensor', 'survey_data', hash( 'contentobject_id', $survey.object.id, 'user_id', $current_user.contentobject_id ) )}
                    {if $data.can_add_response}
                        <a href="{concat('sensor/survey/',$survey.node.node_id)|ezurl(no)}" class="btn btn-primary btn-block btn-lg">{'Rispondi'|i18n('openpa_sensor/survey')}</a>
                    {elseif $data.can_modify_response}
                        <a href="{concat('sensor/survey/',$survey.node.node_id)|ezurl(no)}" class="btn btn-warning btn-block btn-lg">{'Modifica risposta'|i18n('openpa_sensor/survey')}</a>
                    {/if}
                    {if $data.user_result_count|gt(0)}
                        <small><a {if and( $data.can_add_response|not(), $data.can_modify_response|not() )}class="btn btn-block btn-lg btn-success"{/if} href="{concat('sensor/survey_user_result/',$survey.object.id)|ezurl(no)}">{'Vedi risposte'|i18n('openpa_sensor/survey')}</a></small>
                    {/if}
                    {undef $data}
                </div>
                {delimiter}<hr />{/delimiter}
            {/foreach}
        </div>
    </div>

{/if}
