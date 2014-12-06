{if is_unset( $exceeded_limit ) }
    {def $exceeded_limit=false()}
{/if}

<form action={concat($module.functions.removeobject.uri)|ezurl} method="post" name="ObjectRemove">

<div class="warning">
{if eq( $exceeded_limit, true() )}
<h3>Warning:</h3>
<p>{'The items contain more than the maximum possible nodes for subtree removal and will not be deleted. You can remove this subtree using the ezsubtreeremove.php script.'|i18n( 'design/ocbootstrap/node/removeobject' )}</p>
{else}
<h3>{"Are you sure you want to remove these items?"|i18n("design/ocbootstrap/node/removeobject")}</h3>
{/if}
<ul class="list-unstyled">
{foreach $remove_list as $remove_item}
    {if $remove_item.childCount|gt(0)}
        <li>{"%nodename and its %childcount children. %additionalwarning"
             |i18n( 'design/ocbootstrap/node/removeobject',,
                    hash( '%nodename', $remove_item.nodeName,
                          '%childcount', $remove_item.childCount,
                          '%additionalwarning', $remove_item.additionalWarning ) )}</li>
    {else}
        <li>{"%nodename %additionalwarning"
             |i18n( 'design/ocbootstrap/node/removeobject',,
                    hash( '%nodename', $remove_item.nodeName,
                          '%additionalwarning', $remove_item.additionalWarning ) )}</li>
    {/if}
{/foreach}
</ul>
</div>

{if and( $move_to_trash_allowed, eq( $exceeded_limit, false() ) )}
  <input type="hidden" name="SupportsMoveToTrash" value="1" />
  <div class="checkbox">
    <label>
      <input type="checkbox" name="MoveToTrash" value="1" checked="checked" />
      {'Move to trash'|i18n('design/ocbootstrap/node/removeobject')}</p>
      <p class="help-block"><strong>{"Note"|i18n("design/ocbootstrap/node/removeobject")}:</strong> {"If %trashname is checked, removed items can be found in the trash." |i18n( 'design/ocbootstrap/node/removeobject',, hash( '%trashname', concat( '<i>', 'Move to trash' | i18n( 'design/ocbootstrap/node/removeobject' ), '</i>' ) ) )}</p>  
    </label>    
  </div>
{/if}


<div class="buttonblock">
{include uri="design:gui/button.tpl" name=Discard id_name=CancelButton value="Annulla"|i18n("openpa_sensor/dashboard")}
{include uri="design:gui/defaultbutton.tpl" name=Store id_name=ConfirmButton value="Elimina gli elementi"|i18n("openpa_sensor/dashboard") disabled=$exceeded_limit}
</div>

</form>
