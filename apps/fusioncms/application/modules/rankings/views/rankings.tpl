<div class="rankings-page">
    <div class="rankings-hero">
        <div>
            <span class="rankings-kicker">Competitive overview</span>
            <h3>Top players, guilds and arena teams</h3>
            <p>One page for server leaders instead of splitting players across multiple sideboxes and modules.</p>
        </div>
        <div class="rankings-realm-switch">
            {foreach from=$realmCards item=realm}
                <a href="{$url}rankings/index/{$realm.id}" class="rankings-realm-chip {if $selectedRealmId == $realm.id}is-active{/if}">
                    {$realm.name}
                </a>
            {/foreach}
        </div>
    </div>

    <div class="rankings-layout">
        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>Top level players</h4></div>
            <div class="rankings-list">
                {foreach from=$topLevelPlayers item=player}
                    <a class="rankings-row" href="{$url}character/{$selectedRealmId}/{$player.guid}">
                        <span class="main"><strong>#{$player.rank} {$player.name}</strong><small>{$player.race} {$player.class}</small></span>
                        <span class="meta"><strong>Lv{$player.level}</strong><small>{if $player.online}Online{else}Offline{/if}</small></span>
                    </a>
                {/foreach}
            </div>
        </section>

        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>Top achievement players</h4></div>
            <div class="rankings-list">
                {foreach from=$topAchievementPlayers item=player}
                    <a class="rankings-row" href="{$url}character/{$selectedRealmId}/{$player.guid}">
                        <span class="main"><strong>#{$player.rank} {$player.name}</strong><small>{$player.guild|default:'No guild'}</small></span>
                        <span class="meta"><strong>{$player.achievement_points}</strong><small>points</small></span>
                    </a>
                {/foreach}
            </div>
        </section>
    </div>

    <div class="rankings-layout">
        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>Top PvP kills</h4></div>
            <div class="rankings-list">
                {foreach from=$topKillPlayers item=player}
                    <a class="rankings-row" href="{$url}character/{$selectedRealmId}/{$player.guid}">
                        <span class="main"><strong>#{$player.rank} {$player.name}</strong><small>{$player.guild|default:'No guild'}</small></span>
                        <span class="meta"><strong>{$player.totalKills}</strong><small>kills</small></span>
                    </a>
                {/foreach}
            </div>
        </section>

        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>Top guilds</h4></div>
            <div class="rankings-list">
                {foreach from=$topGuilds item=guild}
                    <a class="rankings-row" href="{$url}guild/{$selectedRealmId}/{$guild.guildid}">
                        <span class="main"><strong>#{$guild.rank} {$guild.name}</strong><small>Leader {$guild.leaderName}</small></span>
                        <span class="meta"><strong>{$guild.achievement_points}</strong><small>avg achievement pts</small></span>
                    </a>
                {/foreach}
            </div>
        </section>
    </div>

    <div class="rankings-layout">
        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>2v2 ladder</h4></div>
            <div class="rankings-list">
                {foreach from=$teams2v2 item=team}
                    <div class="rankings-row">
                        <span class="main"><strong>{$team.name}</strong><small>{$team.members|@count} members</small></span>
                        <span class="meta"><strong>{$team.rating}</strong><small>rating</small></span>
                    </div>
                {/foreach}
            </div>
        </section>
        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>3v3 ladder</h4></div>
            <div class="rankings-list">
                {foreach from=$teams3v3 item=team}
                    <div class="rankings-row">
                        <span class="main"><strong>{$team.name}</strong><small>{$team.members|@count} members</small></span>
                        <span class="meta"><strong>{$team.rating}</strong><small>rating</small></span>
                    </div>
                {/foreach}
            </div>
        </section>
        <section class="rankings-panel">
            <div class="rankings-panel-head"><h4>5v5 ladder</h4></div>
            <div class="rankings-list">
                {foreach from=$teams5v5 item=team}
                    <div class="rankings-row">
                        <span class="main"><strong>{$team.name}</strong><small>{$team.members|@count} members</small></span>
                        <span class="meta"><strong>{$team.rating}</strong><small>rating</small></span>
                    </div>
                {/foreach}
            </div>
        </section>
    </div>
</div>
