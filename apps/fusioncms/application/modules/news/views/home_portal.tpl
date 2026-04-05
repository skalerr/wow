<section class="home-portal">
    <div class="home-portal-hero">
        <div class="home-portal-copy">
            <span class="home-portal-kicker">{$serverName|default:'Server'} portal</span>
            <h2>All the useful stuff is finally in one place</h2>
            <p>Status, rankings, onboarding, playerbots and fresh progression updates without digging through random sideboxes.</p>
            <div class="home-portal-actions">
                <a href="{$url}register" class="home-portal-btn primary">Create account</a>
                <a href="{$url}howtoplay" class="home-portal-btn">How to Play</a>
                <a href="{$url}rankings" class="home-portal-btn">Rankings</a>
                <a href="{$url}playerbots" class="home-portal-btn">Playerbots</a>
            </div>
            <div class="home-portal-realmlist">
                <span>Realmlist</span>
                <code>set realmlist {$realmlist}</code>
            </div>
        </div>

        {if $discordServerId && $discordInvite}
            <a class="home-portal-discord" href="{$discordInvite}" target="_blank" rel="noopener noreferrer">
                <img src="https://discord.com/api/guilds/{$discordServerId}/widget.png?style=banner2" alt="Discord">
            </a>
        {/if}
    </div>

    <div class="home-portal-grid">
        <div class="home-portal-panel">
            <div class="home-portal-head"><h4>Realm status</h4></div>
            <div class="home-portal-list">
                {foreach from=$realmCards item=realm}
                    <a class="home-portal-row" href="{$url}server/index/{$realm.id}">
                        <span class="main"><strong>{$realm.name}</strong><small>{$realm.expansion}</small></span>
                        <span class="meta"><strong>{$realm.players}</strong><small>{if $realm.online}Online{else}Offline{/if}</small></span>
                    </a>
                {/foreach}
            </div>
        </div>

        <div class="home-portal-panel">
            <div class="home-portal-head"><h4>New level 80s</h4></div>
            <div class="home-portal-list">
                {foreach from=$recentCaps item=item}
                    <a class="home-portal-row" href="{$url}character/{$homeRealmId}/{$item.guid}">
                        <span class="main"><strong>{$item.name}</strong><small>{$item.race} {$item.class}</small></span>
                        <span class="meta"><strong>{$item.earnedAgo}</strong><small>{$item.earnedAt}</small></span>
                    </a>
                {/foreach}
            </div>
        </div>

        <div class="home-portal-panel">
            <div class="home-portal-head"><h4>Realm-firsts</h4></div>
            <div class="home-portal-list">
                {foreach from=$realmFirsts item=item}
                    <a class="home-portal-row" href="{$url}character/{$homeRealmId}/{$item.guid}">
                        <span class="main"><strong>{$item.title}</strong><small>{$item.name}</small></span>
                        <span class="meta"><strong>{$item.earnedAgo}</strong><small>{$item.points} pts</small></span>
                    </a>
                {/foreach}
            </div>
        </div>

        <div class="home-portal-panel">
            <div class="home-portal-head"><h4>Top players</h4></div>
            <div class="home-portal-list">
                {foreach from=$topPlayers item=item}
                    <a class="home-portal-row" href="{$url}character/{$homeRealmId}/{$item.guid}">
                        <span class="main"><strong>#{$item.rank} {$item.name}</strong><small>{$item.race} {$item.class}</small></span>
                        <span class="meta"><strong>Lv{$item.level}</strong><small>{if $item.online}Online{else}Offline{/if}</small></span>
                    </a>
                {/foreach}
            </div>
        </div>

        <div class="home-portal-panel">
            <div class="home-portal-head"><h4>Guild leaders</h4></div>
            <div class="home-portal-list">
                {foreach from=$topGuilds item=item}
                    <a class="home-portal-row" href="{$url}guild/{$homeRealmId}/{$item.guildid}">
                        <span class="main"><strong>#{$item.rank} {$item.name}</strong><small>Leader {$item.leaderName}</small></span>
                        <span class="meta"><strong>{$item.achievement_points}</strong><small>avg pts</small></span>
                    </a>
                {/foreach}
            </div>
        </div>
    </div>
</section>
