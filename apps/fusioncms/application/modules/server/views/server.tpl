<div class="server-page">
    <div class="server-hero">
        <div>
            <span class="server-kicker">Live realm overview</span>
            <h3>Everything important on one page</h3>
            <p>Status, uptime, online zones, recent achievements and the newest guilds without jumping across half the CMS.</p>
        </div>
        <div class="server-realm-switch">
            {foreach from=$realmCards item=realm}
                <a href="{$url}server/index/{$realm.id}" class="server-realm-chip {if $selectedRealmId == $realm.id}is-active{/if}">
                    {$realm.name}
                </a>
            {/foreach}
        </div>
    </div>

    <div class="server-card-grid">
        {foreach from=$realmCards item=realm}
            <div class="server-card {if $selectedRealmId == $realm.id}is-selected{/if}">
                <div class="server-card-head">
                    <strong>{$realm.name}</strong>
                    <span class="server-status {if $realm.online}is-online{else}is-offline{/if}">
                        {if $realm.online}Online{else}Offline{/if}
                    </span>
                </div>
                <div class="server-card-meta">{$realm.expansion}</div>
                <div class="server-card-stats">
                    <div>
                        <span class="label">Players</span>
                        <strong>{$realm.players}</strong>
                    </div>
                    <div>
                        <span class="label">Cap</span>
                        <strong>{$realm.cap}</strong>
                    </div>
                    <div>
                        <span class="label">Load</span>
                        <strong>{$realm.percentage}%</strong>
                    </div>
                    <div>
                        <span class="label">Uptime</span>
                        <strong>{$realm.uptime}</strong>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>

    <div class="server-layout">
        <section class="server-panel">
            <div class="server-panel-head">
                <h4>Who is online now</h4>
                <span>{$onlineSpotlight|@count} characters</span>
            </div>
            {if $onlineSpotlight}
                <div class="server-list">
                    {foreach from=$onlineSpotlight item=player}
                        <a class="server-list-row" href="{$url}character/{$selectedRealmId}/{$player.guid}">
                            <span class="main">
                                <strong>{$player.name}</strong>
                                <small>{$player.race} {$player.class}</small>
                            </span>
                            <span class="meta">
                                <strong>{$player.level}</strong>
                                <small>{$player.zone}</small>
                            </span>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">Nobody is online on this realm right now.</div>
            {/if}
        </section>

        <section class="server-panel">
            <div class="server-panel-head">
                <h4>Hot zones</h4>
                <span>Where the action is</span>
            </div>
            {if $activeZones}
                <div class="server-zone-grid">
                    {foreach from=$activeZones item=zone}
                        <div class="server-zone-card">
                            <strong>{$zone.zone}</strong>
                            <span>{$zone.count} online</span>
                            <small>{implode(', ', $zone.names)}</small>
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">Zone activity will appear here as soon as players are online.</div>
            {/if}
        </section>
    </div>

    <div class="server-layout">
        <section class="server-panel">
            <div class="server-panel-head">
                <h4>Latest achievements</h4>
                <span>Fresh progression</span>
            </div>
            {if $recentAchievements}
                <div class="server-feed">
                    {foreach from=$recentAchievements item=achievement}
                        <a class="server-feed-row" href="{$url}character/{$selectedRealmId}/{$achievement.guid}">
                            <span class="main">
                                <strong>{$achievement.title}</strong>
                                <small>{$achievement.name} · {$achievement.race} {$achievement.class} · level {$achievement.level}</small>
                            </span>
                            <span class="meta">
                                <strong>{$achievement.earnedAgo}</strong>
                                <small>{$achievement.earnedAt}</small>
                            </span>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">No achievement feed yet for this realm.</div>
            {/if}
        </section>

        <section class="server-panel">
            <div class="server-panel-head">
                <h4>Newest guilds</h4>
                <span>Fresh communities</span>
            </div>
            {if $recentGuilds}
                <div class="server-feed">
                    {foreach from=$recentGuilds item=guild}
                        <a class="server-feed-row" href="{$url}guild/{$selectedRealmId}/{$guild.guildId}">
                            <span class="main">
                                <strong>{$guild.name}</strong>
                                <small>Leader {$guild.leader} · {$guild.members} members</small>
                            </span>
                            <span class="meta">
                                <strong>{$guild.createdAgo}</strong>
                                <small>{$guild.createdAt}</small>
                            </span>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">Guild creation feed will show up after the first guilds are formed.</div>
            {/if}
        </section>
    </div>

    <div class="server-layout">
        <section class="server-panel">
            <div class="server-panel-head">
                <h4>New level 80 characters</h4>
                <span>Recent caps</span>
            </div>
            {if $newLevelCaps}
                <div class="server-feed">
                    {foreach from=$newLevelCaps item=cap}
                        <a class="server-feed-row" href="{$url}character/{$selectedRealmId}/{$cap.guid}">
                            <span class="main">
                                <strong>{$cap.name}</strong>
                                <small>{$cap.race} {$cap.class}</small>
                            </span>
                            <span class="meta">
                                <strong>{$cap.earnedAgo}</strong>
                                <small>{$cap.earnedAt}</small>
                            </span>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">Level-cap milestones will appear here once characters start hitting 80.</div>
            {/if}
        </section>

        <section class="server-panel">
            <div class="server-panel-head">
                <h4>Realm-first timeline</h4>
                <span>Earliest clears and milestones</span>
            </div>
            {if $realmFirsts}
                <div class="server-feed">
                    {foreach from=$realmFirsts item=first}
                        <a class="server-feed-row" href="{$url}character/{$selectedRealmId}/{$first.guid}">
                            <span class="main">
                                <strong>{$first.title}</strong>
                                <small>{$first.name} · {$first.race} {$first.class} · {$first.points} pts</small>
                            </span>
                            <span class="meta">
                                <strong>{$first.earnedAgo}</strong>
                                <small>{$first.earnedAt}</small>
                            </span>
                        </a>
                    {/foreach}
                </div>
            {else}
                <div class="server-empty">Realm-first entries will populate from achievement history.</div>
            {/if}
        </section>
    </div>

    <section class="server-panel">
        <div class="server-panel-head">
            <h4>Media wall</h4>
            <span>Screenshots and short clips</span>
        </div>
        {if $gallery}
            <div class="server-gallery">
                {foreach from=$gallery item=media}
                    <a class="server-gallery-item {if $media.thumbnail_url}has-thumb{/if}" href="{$media.media_url|default:$media.url}" target="_blank" rel="noopener noreferrer">
                        {if $media.thumbnail_url}
                            <span class="server-gallery-thumb" style="background-image:url('{$media.thumbnail_url}')"></span>
                        {/if}
                        <span class="media-type">{$media.type|default:'media'}</span>
                        <strong>{$media.title}</strong>
                        <small>{$media.caption|default:''}</small>
                    </a>
                {/foreach}
            </div>
        {else}
            <div class="server-empty">
                The gallery is live, but still empty. Add screenshots or clip cards in the admin panel under <code>Website -> Media wall</code>.
            </div>
        {/if}
    </section>

    <section class="server-panel">
        <div class="server-panel-head">
            <h4>Community</h4>
            <span>Discord and server links</span>
        </div>
        <div class="server-community-grid">
            <a class="server-community-card" href="{$url}howtoplay">
                <strong>How to Play</strong>
                <small>Client, realmlist, setup steps and FAQ</small>
            </a>
            <a class="server-community-card" href="{$url}rankings">
                <strong>Rankings</strong>
                <small>Top players, arena ladder and guild leaders</small>
            </a>
            <a class="server-community-card" href="{$url}playerbots">
                <strong>Playerbots</strong>
                <small>Live bot stats, FAQ and activity feed</small>
            </a>
            {if $discordServerId && $discordInvite}
                <a class="server-community-card discord" href="{$discordInvite}" target="_blank" rel="noopener noreferrer">
                    <strong>Discord</strong>
                    <small>Join the community and stay in sync with updates</small>
                    <img src="https://discord.com/api/guilds/{$discordServerId}/widget.png?style=banner2" alt="Discord">
                </a>
            {/if}
        </div>
    </section>
</div>
