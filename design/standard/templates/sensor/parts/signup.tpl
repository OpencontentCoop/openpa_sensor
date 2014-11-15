<div class="signup">
    <form name="signupform" method="post" action={'/sensor/signup/'|ezurl}>
        <fieldset>
            <div class="social_sign">
                <h3>{'Non hai un account?'|i18n('openpa_sensor')}</h3>
            </div>
            <p class="sign_title">{'Crealo subito: &egrave facile e gratuito!'|i18n('openpa_sensor')}</p>
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <input id="Name" name="Name" placeholder="{'Nome e cognome'|i18n('openpa_sensor')}" class="form-control" required="" type="text" value="{if $name}{$name}{/if}" />
                    <input id="Emailaddress" name="EmailAddress" placeholder="{'Indirizzo Email'|i18n('openpa_sensor')}" class="form-control" required="" type="text" value="{if $email}{$email}{/if}" />
                    <input id="Password" name="Password" placeholder="{'Password'|i18n('openpa_sensor')}" class="form-control" required="" type="password">

                    <small>
                        {"Cliccando sul bottone Iscriviti accetti <a href=%term_url>le condizioni di utilizzo</a> e confermi di aver letto la nostra <a href=%privacy_url>Normativa sull'utilizzo dei dati</a>."|i18n('openpa_sensor',, hash( '%term_url', 'sensor/terms'|ezurl(), '%privacy_url', 'sensor/privacy'|ezurl() ))}
                    </small>
                </div>
                <div class="col-lg-2"></div>
            </div>
            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg">{'Iscriviti'|i18n('openpa_sensor')}</button>
        </fieldset>
    </form>
</div>