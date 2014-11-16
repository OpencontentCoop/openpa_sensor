{if is_set( $error )}
    <div class="alert alert-danger">{$error}</div>
{else}

<form method="post" action={"collaboration/action/"|ezurl} xmlns="http://www.w3.org/1999/html">

    <section class="hgroup">
        <h1>{$object.name|wash()}</h1>
        <h2>{$object.owner.name|wash()}</h2>
        <ul class="breadcrumb pull-right">
            <li><span class="label label-{$post.type.css_class}">{$post.type.name}</span></li>
            <li><span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span></li>
            {if $post.current_privacy_status.identifier|eq('private')}
                <li><span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span></li>
            {/if}
        </ul>
    </section>

    <div class="row">
        <div class="col-md-8">

            <div class="clearfix">
                <p class="pull-left">
                    {if $object|has_attribute('geo')}
                        <i class="fa fa-map-marker"></i> {$object|attribute('geo').content.address}
                    {elseif $object|has_attribute('area')}
                        {attribute_view_gui attribute=$object|attribute('area')}
                    {/if}
                </p>
                {*<p class="pull-right">
                    <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
                    <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
                    {if $post.current_privacy_status.identifier|eq('private')}
                        <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
                    {/if}
                </p>*}
            </div>
            <p>
                {attribute_view_gui attribute=$object|attribute('description')}
            </p>
            {if $object|has_attribute('attachment')}
                <p>{attribute_view_gui attribute=$object|attribute('attachment')}</p>
            {/if}
            <ul class="list-inline">
                <li><small><i class="fa fa-clock"></i> {'Pubblicata il'|i18n('openpa_sensor')} {$object.published|l10n(shortdate)}</small></li>
                {if $object.modified|gt($object.published)}
                    <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor')} {$object.modified|l10n(shortdate)}</small></li>
                {/if}
                <li><small><i class="fa fa-user"></i> {'In carico a'|i18n('openpa_sensor')} {$post.current_owner}</small></li>
                <li><small><i class="fa fa-comment"></i> {$post.comment_count} commenti</small></li>
            </ul>

            {include uri='design:sensor/parts/post_messages.tpl'}

        </div>
        <div class="col-md-4" id="sidebar">

            <aside class="widget">
                <h4>Partecipanti</h4>
                <dl class="dl-horizontal">
                    {foreach $participant_list as $item}
                        <dt>{$item.name|wash}:</dt>
                        <dd>
                        {foreach $item.items as $p}
                            {$p.participant.contentobject.name|wash()}{delimiter}, {/delimiter}
                        {/foreach}
                        </dd>
                    {/foreach}
                </dl>
            </aside>

            {def $m = fetch("collaboration","message_list",hash("item_id",$collaboration_item.id))}
            {if $m|count|gt(0)}
            <aside class="widget">
                <h4>Timeline</h4>
                <dl class="dl-horizontal">
                    {foreach $m as $item}
                        {if $item.message_type|eq(0)}
                            <dt>{$item.created|l10n(shortdatetime)}</dt>
                            <dd>{$item.simple_message.data_text1|i18n('openpa_sensor')}</dd>
                        {/if}
                    {/foreach}
                </dl>
            </aside>
            {/if}

            {if $helper.can_do_something}
            <aside class="widget">
                <h4>Azioni</h4>
                {if $helper.can_assign}
                    <div class="form-group">
                    <div class="row">
                        <div class="col-xs-7">
                            <select name="Collaboration_OpenPASensorItemAssignTo[]" class="form-control">
                                <option></option>
                                {foreach $sensor.operators as $user}
                                    {if $user.contentobject_id|ne($current_participant.participant_id)}
                                        <option value="{$user.contentobject_id}">{$user.name|wash()}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-xs-5">
                            <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_Assign" value="Assegna" />
                        </div>
                    </div>
                    </div>
                {/if}

                {if $helper.can_add_observer}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-xs-7">
                            <select name="Collaboration_OpenPASensorItemAddObserver" class="form-control">
                                <option></option>
                                {foreach $sensor.operators as $user}
                                    {if $user.contentobject_id|ne($current_participant.participant_id)}
                                        <option value="{$user.contentobject_id}">{$user.name|wash()}</option>
                                    {/if}
                                {/foreach}
                            </select>
                            </div>
                            <div class="col-xs-5">
                                <input class="btn btn-info btn-block" type="submit" name="CollaborationAction_AddObserver" value="Aggiungi cc" />
                            </div>
                        </div>
                    </div>
                {/if}

                {if $helper.can_fix}
                    <div class="form-group">
                        <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Fix" value="Chiudi" /><br />
                    </div>
                {/if}

                {if $helper.can_close}
                    <div class="form-group">
                        <input class="btn btn-success btn-lg btn-block" type="submit" name="CollaborationAction_Close" value="Chiudi" /><br />
                    </div>
                {/if}

            </aside>
            {/if}

        </div>
    </div>


    <input type="hidden" name="CollaborationActionCustom" value="custom" />
    <input type="hidden" name="CollaborationTypeIdentifier" value="openpasensor" />
    <input type="hidden" name="CollaborationItemID" value="{$collaboration_item.id}" />


</form>

{/if} {* if error *}