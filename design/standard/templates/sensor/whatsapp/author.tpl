{if $collaboration_item_status|eq(2)} {* ASSIGNED *}
{'La tua segnalazione è stata presa in carico'|i18n('openpa_sensor/mail/post')} #{$node.contentobject_id} {$post_url}

{elseif $collaboration_item_status|eq(3)} {* CLOSED *}
{'La tua segnalazione è stata risolta'|i18n('openpa_sensor/mail/post')} #{$node.contentobject_id} {$post_url}

{/if}
