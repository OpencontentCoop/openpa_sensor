{def $sensor = sensor_root_handler()}
{set-block scope=root variable=subject}{'Benvenuto in %1'|i18n('openpa_sensor/mail/registration',,array($sensor.site_title))}{/set-block}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'Grazie di voler partecipare!'|i18n('openpa_sensor/mail/registration')}</h2>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'>{'Ecco le informazione del tuo account SensorCivico'|i18n('openpa_sensor/mail/registration')}</h4>
                    </td>
                </tr>
                <tr>
                    <td align='center' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                        <p>
                            <strong>{'Nome'|i18n('openpa_sensor/mail/registration')}:</strong>
                            {$user.contentobject.name|wash()}
                        </p>
                        <p>
                            <strong>{'Indirizzo email'|i18n('openpa_sensor/mail/registration')}:</strong>
                            {$user.email|wash()}
                        </p>
                    </td>
                </tr>                
                <tr>
                    <td align='center' bgcolor='#f90f00' valign='top'>
                        <h3>                          
                          <a href="http://{$sensor.sensor_url}/sensor/activate/{$hash}/{$user.contentobject.main_node_id}" style="color: #ffffff !important">
                            {'Click the following URL to confirm your account'|i18n('design/standard/user/register')}
                          </a>
                        </h3>
                    </td>
                </tr>                
                <tr>
                    <td align='center' valign='top'>
                        <p>                            
                            {'Se desideri cambiare le impostazioni del tuo profilo clicca %profile_link_start%qui%profile_link_end%'|i18n('openpa_sensor/mail/registration',, hash( '%profile_link_start%', concat( '<a href=http://', $sensor.sensor_url, '/user/edit/>' ), '%profile_link_end%', '</a>' ))}<br />
                            {'Per abilitare o disabilitare le notifiche email clicca %notification_link_start%qui%notification_link_end%'|i18n('openpa_sensor/mail/registration',, hash( '%notification_link_start%', concat( '<a href=http://', $sensor.sensor_url, '/notification/settings/>' ), '%notification_link_end%', '</a>' ))}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>