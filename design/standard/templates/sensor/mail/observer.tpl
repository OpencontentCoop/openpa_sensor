{if $collaboration_item_status|eq(4)} {* FIXED *}
{set-block scope=root variable=subject}{'Segnalazione chiusa da operatore'|i18n('openpa_sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{"La segnalazione è stata chiusa dall'operatore"|i18n('openpa_sensor/mail/post')}</h2>                        
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'><strong>{$node|attribute('subject').contentclass_attribute_name}:</strong> {$node.name|wash()}</h4>
                    </td>
                </tr>                
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="http://{ezini( 'SiteSettings', 'SiteURL' )}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Vai alla segnalazione"|i18n('openpa_sensor/mail/post')}</a></h3>
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
{set-block scope=root variable=subject}{'Segnalazione risolta'|i18n('openpa_sensor/mail/post')}{/set-block}
{set-block scope=root variable=body}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'La segnalazione è stata risolta'|i18n('openpa_sensor/mail/post')}</h2>                        
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'><strong>{$node|attribute('subject').contentclass_attribute_name}:</strong> {$node.name|wash()}</h4>
                    </td>
                </tr>                
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3><a href="http://{ezini( 'SiteSettings', 'SiteURL' )}/sensor/posts/{$object.id}" style="color: #ffffff !important">{"Visualizza la storia della segnalazione"|i18n('openpa_sensor/mail/post')}</a></h3>
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
