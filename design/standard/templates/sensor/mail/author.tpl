{if $collaboration_item_status|eq(0)} {* WAITING*}
{set-block scope=root variable=subject}{'Nuova segnalazione'|i18n('openpa_sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'La tua segnalazione è stata registrata'|i18n('openpa_sensor/mail/post')}</h2>
                        <p>{'La tua segnalazione sarà presa in carico da un operatore al più presto'|i18n('openpa_sensor/mail/post')}</p>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'>{'Dettagli della segnalazione'|i18n('openpa_sensor/mail/post')}</h4>
                    </td>
                </tr>
                <tr>
                    <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                        <p><strong>{$node|attribute('subject').contentclass_attribute_name}:</strong> {$node.name|wash()}</p>
                        <p><strong>{$node|attribute('type').contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node|attribute('type')}</p>
                        {if $node|has_attribute('geo')}
                            <p><strong>{$node|attribute('geo').contentclass_attribute_name}:</strong> {$node|attribute('geo').content.address}</p>
                        {elseif $node|has_attribute('area')}
                            <p><strong>{$node|attribute('area').contentclass_attribute_name}:</strong> {attribute_view_gui attribute=$node|attribute('area')}</p>
                        {/if}
                        <p><strong>{$node|attribute('description').contentclass_attribute_name}:</strong> <small>{attribute_view_gui attribute=$node|attribute('description')}</small></p>
                        {if $node|has_attribute('attachment')}
                        <p><strong>{$node|attribute('attachment').contentclass_attribute_name}:</strong> {$node|attribute('attachment').content.original_filename|wash()}</p>
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="http://{ezini( 'SiteSettings', 'SiteURL' )}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Controlla l'andamento della risoluzione"|i18n('openpa_sensor/mail/post')}</a></h3>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <p>                            
                            {'Per vedere tutte le tue segnalazioni clicca %dashboard_link_start%qui%dashboard_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%dashboard_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/sensor/dashboard/>' ), '%dashboard_link_end%', '</a>' ))}<br />
                            {'Per disabilitare le notifiche email clicca %notification_link_start%qui%notification_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%notification_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{/set-block}

{elseif $collaboration_item_status|eq(2)} {* ASSIGNED *}
{set-block scope=root variable=subject}{'La tua segnalazione è stata presa in carico'|i18n('openpa_sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'La tua segnalazione è stata presa in carico'|i18n('openpa_sensor/mail/post')}</h2>                        
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'><h4 style='color: #f90f00 !important'><strong>{$node|attribute('subject').contentclass_attribute_name}:</strong> {$node.name|wash()}</h4></h4>
                    </td>
                </tr>
                <tr>
                    <td align='left' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>                        
                        <p><strong>{"Assegnata a"|i18n('openpa_sensor/mail/post')}:</strong> {$collaboration_item.content.helper.owner_name|wash()}</p>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="http://{ezini( 'SiteSettings', 'SiteURL' )}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Controlla l'andamento della risoluzione"|i18n('openpa_sensor/mail/post')}</a></h3>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <p>                            
                            {'Per vedere tutte le tue segnalazioni clicca %dashboard_link_start%qui%dashboard_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%dashboard_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/sensor/dashboard/>' ), '%dashboard_link_end%', '</a>' ))}<br />
                            {'Per disabilitare le notifiche email clicca %notification_link_start%qui%notification_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%notification_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{/set-block}

{elseif $collaboration_item_status|eq(3)} {* CLOSED *}
{set-block scope=root variable=subject}{'La tua segnalazione è stata risolta'|i18n('openpa_sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'La tua segnalazione è stata risolta'|i18n('openpa_sensor/mail/post')}</h2>                        
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'><strong>{$node|attribute('subject').contentclass_attribute_name}:</strong> {$node.name|wash()}</h4>
                    </td>
                </tr>                
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="http://{ezini( 'SiteSettings', 'SiteURL' )}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Visualizza la storia della tua segnalazione"|i18n('openpa_sensor/mail/post')}</a></h3>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <p>                            
                            {'Per vedere tutte le tue segnalazioni clicca %dashboard_link_start%qui%dashboard_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%dashboard_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/sensor/dashboard/>' ), '%dashboard_link_end%', '</a>' ))}<br />
                            {'Per disabilitare le notifiche email clicca %notification_link_start%qui%notification_link_end%'|i18n('openpa_sensor/mail/post',, hash( '%notification_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{/set-block}
{/if}
