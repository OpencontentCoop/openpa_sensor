{ezscript_require(array('cookiechoices.js'))}
<script>
    document.addEventListener('DOMContentLoaded', function(event) {ldelim}
        cookieChoices.showCookieConsentBar(
                "{"I cookie ci aiutano ad erogare servizi di qualità. Utilizzando i nostri servizi, l'utente accetta le nostre modalità d'uso dei cookie."|i18n('openpa_sensor')}",
                "{'OK'|i18n('openpa_sensor')}",
                "{'Maggiori informazioni'|i18n('openpa_sensor')}",
                "{'sensor/info/cookie'|ezurl(no,full)}"
        );
    {rdelim});
</script>