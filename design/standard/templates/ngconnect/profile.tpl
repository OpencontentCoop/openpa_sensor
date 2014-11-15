{set scope=global persistent_variable='sensor'}

<div class="ngconnect-profile">
    <h1 class="long">{'Profile setup'|i18n( 'extension/ngconnect/ngconnect/profile' )}</h1>

    {if is_set($validation_error)}
        <div class="warning alert">
            <p>{$validation_error|wash}</p>
        </div>
    {/if}

    <h2>{'Welcome'|i18n( 'extension/ngconnect/ngconnect/profile' )}</h2>

    {if $forced_redirect|not}
        <p>{'One more step is needed to activate your account and to be able to sign in using regular username and password.'|i18n( 'extension/ngconnect/ngconnect/profile' )}</p>
        {if ezini( 'ProfileGenerationSettings', 'LoginUser', 'ngconnect.ini' )|eq( 'enabled' )}
            <p>{'If you already have a regular account, input your details under "Login to existing account" section.'|i18n( 'extension/ngconnect/ngconnect/profile' )}</p>
        {/if}
        {if ezini( 'ProfileGenerationSettings', 'CreateUser', 'ngconnect.ini' )|eq( 'enabled' )}
            <p>{'If you do not have a regular account, enter your desired username and password under "Create new account" section.'|i18n( 'extension/ngconnect/ngconnect/profile' )}</p>
        {/if}
        {if ezini( 'ProfileGenerationSettings', 'Skip', 'ngconnect.ini' )|eq( 'enabled' )}
            <p>{'If you do not wish to create a regular account, simply click the "Skip" button below, and we won\'t bother you with this again.'|i18n( 'extension/ngconnect/ngconnect/profile' )}</p>
        {/if}
    {else}
        <p>{'Account with the email address from your social network (%1) already exists. Please enter your login details below to connect to that account.'|i18n( 'extension/ngconnect/ngconnect/profile', , array( $network_email ) )}</p>
        <p>{'If you forgot your password, request a new one'|i18n( 'extension/ngconnect/ngconnect/profile' )} <a href={'user/forgotpassword'|ezurl}>{'here'|i18n( 'extension/ngconnect/ngconnect/profile' )}</a>.</p>
    {/if}

    {if and( $forced_redirect|not, ezini( 'ProfileGenerationSettings', 'Skip', 'ngconnect.ini' )|eq( 'enabled' ) )}
        <div class="block">
            <form action={'ngconnect/profile'|ezurl} method="post">
                <div class="form-group">
                    <input class="defaultbutton" type="submit" name="SkipButton" value="{'Skip'|i18n( 'extension/ngconnect/ngconnect/profile' )}" tabindex="1" />
                </div>
            </form>
        </div>
    {/if}

    {if or( $forced_redirect, ezini( 'ProfileGenerationSettings', 'LoginUser', 'ngconnect.ini' )|eq( 'enabled' ) )}
        <h2>{'Login to existing account'|i18n( 'extension/ngconnect/ngconnect/profile' )}</h2>

        <div class="block">
            <form action={'ngconnect/profile'|ezurl} method="post">
                <div class="form-group">
                    <label>{'Username'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label>
                    <input class="halfbox" type="text" size="10" name="Login" id="id1" value="{cond( ezhttp_hasvariable( 'Login', 'post' ), ezhttp( 'Login', 'post' ), '' )}" tabindex="2" />
                </div>

                <div class="form-group">
                    <label>{'Password'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label><div class="labelbreak"></div>
                    <input class="halfbox" type="password" size="10" name="Password" id="id2" value="" tabindex="3" />
                </div>

                <div class="buttonblock">
                    <input class="defaultbutton" type="submit" name="LoginButton" value="{'Login'|i18n( 'extension/ngconnect/ngconnect/profile' )}" tabindex="4" />
                </div>
            </form>
        </div>
    {/if}

    {if and( $forced_redirect|not, ezini( 'ProfileGenerationSettings', 'CreateUser', 'ngconnect.ini' )|eq( 'enabled' ) )}
        <h2>{'Create new account'|i18n( 'extension/ngconnect/ngconnect/profile' )}</h2>

        <div class="block">
            <form action={'ngconnect/profile'|ezurl} method="post">
                <div class="form-group">
                    <label>{'Username'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label>
                    <input class="halfbox" type="text" size="10" name="data_user_login" value="{cond( ezhttp_hasvariable( 'data_user_login', 'post' ), ezhttp( 'data_user_login', 'post' ), '' )}" tabindex="5" />
                </div>

                <div class="form-group">
                    <label>{'E-mail'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label>
                    <input class="halfbox" type="text" size="10" name="data_user_email" value="{cond( and( ezhttp_hasvariable( 'data_user_email', 'post' ), ezhttp( 'data_user_email', 'post' )|count ), ezhttp( 'data_user_email', 'post' ), $network_email )}" tabindex="6" />
                </div>

                <div class="form-group">
                    <label>{'Password'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label>
                    <input class="halfbox" type="password" size="10" name="data_user_password" value="" tabindex="7" />
                </div>

                <div class="form-group">
                    <label>{'Repeat password'|i18n( 'extension/ngconnect/ngconnect/profile' )}</label>
                    <input class="halfbox" type="password" size="10" name="data_user_password_confirm" value="" tabindex="8" />
                </div>

                <div class="buttonblock">
                    <input class="defaultbutton" type="submit" name="SaveButton" value="{'Save'|i18n( 'extension/ngconnect/ngconnect/profile' )}" tabindex="9" />
                </div>
            </form>
        </div>
    {/if}
</div>
