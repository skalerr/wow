<div class="howtoplay-page">
    <div class="howtoplay-hero">
        <div>
            <span class="howtoplay-kicker">Onboarding</span>
            <h3>From zero to login in a few minutes</h3>
            <p>Set the correct realmlist, create your account on the site, use a clean 3.3.5a client and keep your setup predictable.</p>
        </div>
        <div class="howtoplay-realm-switch">
            {foreach from=$realmCards item=realm}
                <a href="{$url}howtoplay/index/{$realm.id}" class="howtoplay-realm-chip {if $selectedRealmId == $realm.id}is-active{/if}">
                    {$realm.name}
                </a>
            {/foreach}
        </div>
    </div>

    <div class="howtoplay-steps">
        <div class="howtoplay-step">
            <span>1</span>
            <strong>Create account</strong>
            <small>Use <a href="{$url}register">Register</a> on the site and keep the same login for the game client.</small>
        </div>
        <div class="howtoplay-step">
            <span>2</span>
            <strong>Set realmlist</strong>
            <small><code>set realmlist {$realmlist}</code></small>
        </div>
        <div class="howtoplay-step">
            <span>3</span>
            <strong>Start clean client</strong>
            <small>Use a clean WotLK 3.3.5a client and only the addons or patches listed below.</small>
        </div>
        <div class="howtoplay-step">
            <span>4</span>
            <strong>Join community</strong>
            <small>Use Discord and the in-site pages for rankings, server status and playerbots.</small>
        </div>
    </div>

    <div class="howtoplay-layout">
        <section class="howtoplay-panel">
            <div class="howtoplay-panel-head">
                <h4>Current realm snapshot</h4>
            </div>
            {if $selectedRealm}
                <div class="howtoplay-realm-card">
                    <strong>{$selectedRealm.name}</strong>
                    <span>{if $selectedRealm.online}Online{else}Offline{/if}</span>
                    <small>{$selectedRealm.players} players online · {$selectedRealm.uptime} uptime</small>
                </div>
            {/if}
            <div class="howtoplay-command">
                <label>Realmlist</label>
                <code>set realmlist {$realmlist}</code>
            </div>
        </section>

        <section class="howtoplay-panel">
            <div class="howtoplay-panel-head">
                <h4>Useful links</h4>
            </div>
            <div class="howtoplay-links">
                <a href="{$url}server">Server</a>
                <a href="{$url}rankings">Rankings</a>
                <a href="{$url}playerbots">Playerbots</a>
                <a href="{$url}armory">Armory</a>
            </div>
            {if $discordServerId && $discordInvite}
                <a class="howtoplay-discord" href="{$discordInvite}" target="_blank" rel="noopener noreferrer">
                    <img src="https://discord.com/api/guilds/{$discordServerId}/widget.png?style=banner2" alt="Discord">
                </a>
            {/if}
        </section>
    </div>

    <div class="howtoplay-layout">
        <section class="howtoplay-panel">
            <div class="howtoplay-panel-head">
                <h4>Downloads</h4>
            </div>
            {if $downloads}
                <div class="howtoplay-resource-list">
                    {foreach from=$downloads item=item}
                        <a class="howtoplay-resource" href="{$item.url}" target="_blank" rel="noopener noreferrer">
                            <strong>{$item.title}</strong>
                            <small>{$item.caption|default:''}</small>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="howtoplay-empty">Add launchers, clients or patch links in <code>application/modules/howtoplay/config/howtoplay.php</code>.</div>
            {/if}
        </section>

        <section class="howtoplay-panel">
            <div class="howtoplay-panel-head">
                <h4>Addons</h4>
            </div>
            {if $addons}
                <div class="howtoplay-resource-list">
                    {foreach from=$addons item=item}
                        <a class="howtoplay-resource" href="{$item.url}" target="_blank" rel="noopener noreferrer">
                            <strong>{$item.title}</strong>
                            <small>{$item.caption|default:''}</small>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="howtoplay-empty">Add server-approved addons here instead of sending players to random addon packs.</div>
            {/if}
        </section>
    </div>

    <section class="howtoplay-panel">
        <div class="howtoplay-panel-head">
            <h4>FAQ</h4>
        </div>
        <div class="howtoplay-faq">
            {foreach from=$faq item=item}
                <div class="howtoplay-faq-item">
                    <strong>{$item.title}</strong>
                    <p>{$item.body}</p>
                </div>
            {/foreach}
        </div>
    </section>
</div>
