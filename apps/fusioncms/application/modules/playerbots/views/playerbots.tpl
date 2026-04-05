<div class="playerbots-page">
    <div class="playerbots-hero">
        <div>
            <span class="playerbots-kicker">Random bot ecosystem</span>
            <h3>Playerbots, explained with live data</h3>
            <p>The page mixes practical server-side stats with a plain-language explanation of what random bots are doing for your realm.</p>
        </div>
        <div class="playerbots-realm-switch">
            {foreach from=$realmCards item=realm}
                <a href="{$url}playerbots/index/{$realm.id}" class="playerbots-realm-chip {if $selectedRealmId == $realm.id}is-active{/if}">
                    {$realm.name}
                </a>
            {/foreach}
        </div>
    </div>

    <div class="playerbots-stat-grid">
        <div class="playerbots-stat-card">
            <span class="label">Detection mode</span>
            <strong>{$overview.botDetection}</strong>
        </div>
        <div class="playerbots-stat-card">
            <span class="label">Random bots online</span>
            <strong>{$overview.onlineRandomBots}</strong>
        </div>
        <div class="playerbots-stat-card">
            <span class="label">Bot accounts</span>
            <strong>{$overview.randomBotAccounts}</strong>
        </div>
        <div class="playerbots-stat-card">
            <span class="label">Playerbots DB</span>
            <strong>{$overview.playerbotsDatabase}</strong>
        </div>
    </div>

    <div class="playerbots-layout">
        <section class="playerbots-panel">
            <div class="playerbots-panel-head">
                <h4>What this adds to the server</h4>
            </div>
            <div class="playerbots-copy">
                {foreach from=$features item=feature}
                    <div class="playerbots-copy-row">{$feature}</div>
                {/foreach}
            </div>
        </section>

        <section class="playerbots-panel">
            <div class="playerbots-panel-head">
                <h4>Quick FAQ</h4>
            </div>
            <div class="playerbots-faq">
                {foreach from=$faq item=item}
                    <div class="playerbots-faq-item">
                        <strong>{$item.title}</strong>
                        <p>{$item.body}</p>
                    </div>
                {/foreach}
            </div>
        </section>
    </div>

    <div class="playerbots-layout">
        <section class="playerbots-panel">
            <div class="playerbots-panel-head">
                <h4>Online bot level spread</h4>
            </div>
            <div class="playerbots-bars">
                {foreach from=$levelBrackets item=bracket}
                    <div class="playerbots-bar-row">
                        <span>{$bracket.label}</span>
                        <strong>{$bracket.count}</strong>
                    </div>
                {/foreach}
            </div>
        </section>

        <section class="playerbots-panel">
            <div class="playerbots-panel-head">
                <h4>Role mix right now</h4>
            </div>
            <div class="playerbots-bars">
                {foreach from=$roleBreakdown item=role}
                    <div class="playerbots-bar-row">
                        <span>{$role.label}</span>
                        <strong>{$role.count}</strong>
                    </div>
                {/foreach}
            </div>
        </section>
    </div>

    <section class="playerbots-panel">
        <div class="playerbots-panel-head">
            <h4>Recent random bot events</h4>
            <span>{if $recentEvents}{$recentEvents|@count} latest entries{else}No live event feed yet{/if}</span>
        </div>
        {if $recentEvents}
            <div class="playerbots-feed">
                {foreach from=$recentEvents item=event}
                    <div class="playerbots-feed-row">
                        <span class="main">
                            <strong>{$event.event}</strong>
                            <small>{$event.name} · {$event.class} · level {$event.level}</small>
                        </span>
                        <span class="meta">
                            <strong>{$event.time}</strong>
                            <small>{if $event.data}{$event.data}{elseif $event.value}{$event.value}{else}runtime event{/if}</small>
                        </span>
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="playerbots-empty">
                The event feed will appear automatically once <code>playerbots_random_bots</code> starts collecting runtime events on this realm.
            </div>
        {/if}
    </section>
</div>
