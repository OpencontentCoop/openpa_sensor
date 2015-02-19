<style>.survey-result-item label{ldelim}display:none{rdelim}</style>

<section class="hgroup">
    <h1>{$object.name|wash()}</h1>
    <h2>
        {if $single_result}
            {'La risposta di'|i18n('openpa_sensor/survey')} {$result.tstamp|l10n(date)}
        {/if}
    </h2>

    {if $single_result}
        <ul class="breadcrumb pull-right">
            <li><a href="{concat('sensor/survey_user_result/',$object.id)|ezurl(no)}">{'Le mie risposte a questa consultazione'|i18n('openpa_sensor/survey')}</a></li>
        </ul>
    {else}
        <ul class="breadcrumb pull-right">
            <li><a href="{'sensor/dashboard/survey'|ezurl(no)}">{'Le mie consultazioni'|i18n('openpa_sensor/survey')}</a></li>
        </ul>
    {/if}
</section>

{if $single_result}
    <table class="table">
    {foreach $survey_questions as $question}
        {set-block variable=$data}{survey_question_result_gui view=item question=$question result_id=$result.id metadata=$survey_metadata}{/set-block}
        {if $data|trim()|ne('')}
        <tr class="survey-result-item">
            <th>
                {$question.question_number}. {$question.text|wash('xhtml')}
            </th>
            <td>
                {$data}
            </td>
        </tr>
        {/if}
    {/foreach}
    </table>
{else}
    {def $data = fetch( 'sensor', 'survey_data', hash( 'contentobject_id', $object.id, 'user_id', $user.contentobject_id ) )}
    {if $data.user_result_count|gt(0)}
        <ul class="list-unstyled">
            {foreach $data.user_results as $version => $results}
                {foreach $results as $result}
                    <li><a href="{concat('sensor/survey_user_result/',$object.id, '/', $result.id)|ezurl(no)}">{'La risposta di'|i18n('openpa_sensor/survey')} {$result.tstamp|l10n(date)}</a></li>
                {/foreach}
            {/foreach}
        </ul>
    {/if}
{/if}