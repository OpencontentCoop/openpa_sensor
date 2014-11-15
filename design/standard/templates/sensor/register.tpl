<section class="hgroup" id="login">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-md-offset-3">
            <div class="signup">
                <form name="signupform" method="post" action={'/sensor/signup/'|ezurl}>
                    {if $invalid_form}
                        <fieldset>
                            <div class="alert alert-warning">
                                {foreach $errors as $error}<p>{$error}</p>{/foreach}
                            </div>
                            <div class="row">
                                <div class="col-lg-2"></div>
                                <div class="col-lg-8">
                                    <input id="Name" name="Name" placeholder="{'Nome e cognome'|i18n('openpa_sensor')}" class="form-control" required="" type="text" value="{if $name}{$name}{/if}" />
                                    <input id="Emailaddress" name="EmailAddress" placeholder="{'Indirizzo Email'|i18n('openpa_sensor')}" class="form-control" required="" type="text" value="{if $email}{$email}{/if}" />
                                    <input id="Password" name="Password" placeholder="{'Password'|i18n('openpa_sensor')}" class="form-control" required="" type="password">
                                </div>
                                <div class="col-lg-2"></div>
                            </div>
                            <button name="RegisterButton" type="submit" class="btn btn-success btn-lg">{'Iscriviti'|i18n('openpa_sensor')}</button>
                        </fieldset>
                    {elseif $show_captcha}
                        {def $bypass_captcha = false()}
                        {if $bypass_captcha|not}
                            <fieldset>
                                <legend>{'Security text:'|i18n( 'ezcomments/comment/add/form' )}</legend>
                                {if ezini( 'RecaptchaSetting', 'PublicKey', 'ezcomments.ini' )|eq('')}
                                    <div class="message-warning">
                                        {'reCAPTCHA API key missing.'|i18n( 'ezcomments/comment/add' )}
                                    </div>
                                {else}
                                    <script type="text/javascript">
                                        {def $theme = ezini( 'RecaptchaSetting', 'Theme', 'ezcomments.ini' )}
                                        {def $language = ezini( 'RecaptchaSetting', 'Language', 'ezcomments.ini' )}
                                        {def $tabIndex = ezini( 'RecaptchaSetting', 'TabIndex', 'ezcomments.ini' )}
                                        var RecaptchaOptions = {literal}{{/literal} theme : '{$theme}',
                                            lang : '{$language}',
                                            tabindex : {$tabIndex} {literal}}{/literal};
                                    </script>
                                    {if $theme|eq('custom')}
                                        {*Customized theme start*}
                                        <p>
                                            {'Enter security code'|i18n( 'ezcomments/comment/add/form' )}
                                            <a href="javascript:;" onclick="Recaptcha.reload();">
                                                {'Try another'|i18n( 'ezcomments/comment/add/form' )}
                                            </a>
                                        </p>
                                        <div id="recaptcha_image" style="margin: 0 auto"></div>
                                        <div style="width: 300px;margin: 0 auto">
                                        <p>
                                            <input style="width: 300px;font-size: 2em" type="text" class="box" id="recaptcha_response_field" name="recaptcha_response_field" />
                                        </p>
                                        <button name="CaptchaButton" type="submit" class="btn btn-success btn-lg btn-block">{'Prosegui'|i18n('openpa_sensor')}</button>
                                        </div>
                                        {*Customized theme end*}
                                    {/if}
                                    {fetch( 'sensor', 'recaptcha_html' )}

                                {/if}
                            </fieldset>
                        {/if}
                        {undef $bypass_captcha}

                    {/if}
                </form>
            </div>
        </div>
    </div>
</section>
