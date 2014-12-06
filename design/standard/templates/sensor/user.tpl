<form name="Setting" method="post" action={concat( 'sensor/user/', $userID )|ezurl}>
  <section class="hgroup">
    <h1>{$userObject.name|wash()}</h1>    
  </section>

  <ul class="list-unstyled">
    <li><strong>{"Username"|i18n( 'sensor/user/setting' )}:</strong> {$user.login|wash()}</li>
    <li><strong>{"Email"|i18n( 'sensor/user/setting' )}:</strong> {$user.email|wash()}</li>    
  </ul>
  
  <div class="checkbox">
    <label class="check">{"Blocca utente"|i18n( 'sensor/user/setting' )}
      <input type="checkbox" name="is_enabled" {if $userSetting.is_enabled|not()}checked="checked"{/if} />
    </label>
  </div>
  
  <div class="checkbox">
    <label class="check">{"Impedisci all'utente di commentare"|i18n( 'sensor/user/setting' )}
      <input type="checkbox" name="sensor_deny_comment" {if $user_deny_comment}checked="checked"{/if} />
    </label>
  </div>
  
  
  <input class="btn btn-primary" type="submit" name="UpdateSettingButton" value="{'Salva'|i18n( 'sensor/user/setting' )}" />
  <input class="btn bt-default" type="submit" name="CancelSettingButton" value="{'Annulla'|i18n( 'sensor/user/setting' )}" />
  
</form>