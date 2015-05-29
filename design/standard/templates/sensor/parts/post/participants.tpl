<aside class="widget">
    <h4>{'Soggetti coinvolti'|i18n('openpa_sensor/post')}</h4>
    <dl class="dl">
        {foreach $sensor_post.participants as $participant_role}
            <dt>{$participant_role.role_name|wash}:</dt>
            <dd><ul class="list-unstyled">
                    {foreach $participant_role.items as $participant}
                        {if $participant.contentobject}
                            <li>
                                <small>
                                    {include uri='design:content/view/sensor_person.tpl' sensor_person=$participant.contentobject}
                                    {if and( $participant_role.role_id|eq(5), $sensor_post.object|has_attribute('on_behalf_of') )}
                                        [{$sensor_post.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$sensor_post.object|attribute('on_behalf_of').content|wash()}]
                                    {/if}
                                </small>
                            </li>
                        {else}
                            <li>?</li>
                        {/if}
                    {/foreach}
                </ul></dd>
        {/foreach}
    </dl>
</aside>