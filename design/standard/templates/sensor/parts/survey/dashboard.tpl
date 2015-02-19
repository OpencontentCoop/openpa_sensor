{if is_set($sensor)|not()}
    {def $sensor = sensor_root_handler()}
{/if}

{if is_set($current_user)|not()}
    {def $current_user = fetch( 'user', 'current_user' )}
{/if}

<section class="hgroup">
  <div class="row">
    <div class="col-md-12">
      <h1>
        {"Le mie consultazioni"|i18n('openpa_sensor/dashboard')}
      </h1>
    </div>
  </div>
</section>

<table class="table table-hover">
<tr>
    <th>Consultazione</th>
    <th>Data</th>
    <th>Le tue risposte</th>
    {if $simplified_dashboard|not()}<th>Tutti i risultati</th>{/if}
</tr>
{foreach $sensor.surveys as $survey}
<tr>
    <td>
        <p>
            {if $survey.survey_content.survey.enabled}<i class="fa fa-unlock"></i>{else}<i class="fa fa-lock"></i>{/if}
            <a  href="{concat('sensor/survey_user_result/',$survey.object.id)|ezurl(no)}"> {$survey.node.name|wash()|bracket_to_strong}</a>
        </p>

    </td>
    <td>
        {if $survey.survey_content.survey.valid_from_array.no_limit|not}{$survey.survey_content.survey.valid_from|l10n('shortdate')}{else}...{/if} -
        {if $survey.survey_content.survey.valid_to_array.no_limit|not}{$survey.survey_content.survey.valid_to|l10n('shortdate')}{else}...{/if}
    </td>
    {def $data = fetch( 'sensor', 'survey_data', hash( 'contentobject_id', $survey.object.id, 'user_id', $current_user.contentobject_id ) )}
    <td>
        {if $data.user_result_count|gt(0)}
            <ul class="list-unstyled">
            {foreach $data.user_results as $version => $results}
                {foreach $results as $result}
                    <li><a href="{concat('sensor/survey_user_result/',$survey.object.id, '/', $result.id)|ezurl(no)}">{'La risposta di'|i18n('openpa_sensor/survey')} {$result.tstamp|l10n(date)}</a></li>
                {/foreach}
            {/foreach}
            </ul>
        {/if}
    </td>
    {if $simplified_dashboard|not()}
        <td>

            <form class="float" action={concat('/survey/export/', $survey.object.id, '/', $survey.survey_attribute.contentclassattribute_id, '/', ezini( 'RegionalSettings', 'ContentObjectLocale' ) )|ezurl}>
                <input class="btn btn-link" type="submit" value="{'Esporta in CSV'|i18n('openpa_sensor/survey')}" />
            </form>
        </td>
    {/if}
</tr>
{/foreach}
</table>
