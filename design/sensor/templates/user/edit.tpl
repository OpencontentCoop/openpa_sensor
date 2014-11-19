<form action={concat($module.functions.edit.uri,"/",$userID)|ezurl} method="post" name="Edit">


<section class="hgroup">
  <h1>{"User profile"|i18n("design/ocbootstrap/user/edit")}</h1>
</section>

<dl class="dl-horizontal">
  
  <dt>{"Username"|i18n("design/ocbootstrap/user/edit")}</dt>
  <dd>{$userAccount.login|wash}</dd>
  
  <dt>{"Email"|i18n("design/ocbootstrap/user/edit")}</dt>
  <dd>{$userAccount.email|wash()}</dd>
  
  <dt>{"Name"|i18n("design/ocbootstrap/user/edit")}</dt>
  <dd>{$userAccount.contentobject.name|wash}</dd>
  
</dl>
  
  
<input class="button btn btn-info" type="submit" name="EditButton" value="{'Edit profile'|i18n('design/ocbootstrap/user/edit')}" />
<input class="button btn btn-info" type="submit" name="ChangePasswordButton" value="{'Change password'|i18n('design/ocbootstrap/user/edit')}" />

</form>
