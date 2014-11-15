<div class="ngconnect-success">
    {if $verify_user_email}
        <div class="attribute-header">
            <h1 class="long">{'User registered'|i18n( 'design/ezwebin/user/success' )}</h1>
        </div>

        <div class="feedback">
            <p>{'Your account was successfully created. An email will be sent to the specified address. Follow the instructions in that email to activate your account.'|i18n( 'design/ezwebin/user/success' )}</p>
        </div>
    {else}
        <div class="attribute-header">
            <h1 class="long">{'User registered'|i18n( 'design/ezwebin/user/success' )}</h1>
        </div>

        <div class="feedback">
            <h2>{'Your account was successfully created.'|i18n( 'design/ezwebin/user/success' )}</h2>
        </div>
    {/if}

    <form action={'ngconnect/success'|ezurl} method="post">
        <div class="buttonblock">
            <input class="defaultbutton" type="submit" name="OkButton" value="{'OK'|i18n( 'extension/ngconnect/ngconnect/success' )}" tabindex="1" />
        </div>
    </form>
</div>
