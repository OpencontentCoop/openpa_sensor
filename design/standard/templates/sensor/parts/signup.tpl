<div class="signup">
    <form name="signupform" method="post" action={'/sensor/signup/'|ezurl}>
        <fieldset>
            <div class="social_sign">
                <h3>
				  <strong>{'Non sei ancora iscritto?'|i18n('openpa_sensor/signup')}<br /></strong>
                  {'Bastano 5 secondi per registrarsi!'|i18n('openpa_sensor/signup')}
				</h3>				
            </div>
            {*<p class="sign_title">{'Crealo subito: &egrave facile e gratuito!'|i18n('openpa_sensor/signup')}</p>*}
            <div class="row">                
                <div class="col-lg-8 col-md-offset-2">
                    <input autocomplete="off" id="Name" name="Name" placeholder="{'Nome e cognome'|i18n('openpa_sensor/signup')}" class="form-control" required="" type="text" value="{if is_set($name)}{$name}{/if}" />
                    <input autocomplete="off" id="Emailaddress" name="EmailAddress" placeholder="{'Indirizzo Email'|i18n('openpa_sensor/signup')}" class="form-control" required="" type="text" value="{if is_set($email)}{$email}{/if}" />
                    <input autocomplete="off" id="Password" name="Password" placeholder="{'Password'|i18n('openpa_sensor/signup')}" class="form-control" required="" type="password">
                </div>                
            </div>
			<div class="row">
			  <div class="col-md-12">
				<small>
					{"Cliccando sul bottone Iscriviti accetti <a href=%term_url>le condizioni di utilizzo</a> e confermi di aver letto la nostra <a href=%privacy_url>Normativa sull'utilizzo dei dati</a>."|i18n('openpa_sensor/signup',, hash( '%term_url', 'sensor/terms'|ezurl(), '%privacy_url', 'sensor/privacy'|ezurl() ))}
				</small>
			  </div>
			</div>
            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg" style="margin-top: 18px">{'Iscriviti'|i18n('openpa_sensor/signup')}</button>
        </fieldset>
    </form>
</div>