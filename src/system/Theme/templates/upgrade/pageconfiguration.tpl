page = {$pagetemplate}
{if isset($defaultpalette) and $defaultpalette neq ''}
    palette = {$defaultpalette}
{/if}

[blocktypes]

[blockpositions]
{foreach from=$templates key=position item=template}
{$position} = {$template}
{/foreach}

[blockinstances]
