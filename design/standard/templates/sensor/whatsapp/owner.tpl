{if $collaboration_item_status|eq(2)} {* ASSIGNED *}
{'Ti è stata assegnata una segnalazione'|i18n('openpa_sensor/mail/post')}  #{$node.contentobject_id} {$sensor_post.post_url}
{/if}
