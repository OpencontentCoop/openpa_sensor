{if $collaboration_item_status|eq(4)} {* FIXED *}
{'Segnalazione chiusa da operatore'|i18n('openpa_sensor/mail/post')} #{$node.contentobject_id}{$sensor_post.post_url}

{elseif $collaboration_item_status|eq(3)} {* CLOSED *}
{'Segnalazione risolta'|i18n('openpa_sensor/mail/post')} #{$node.contentobject_id}{$sensor_post.post_url}

{/if}
