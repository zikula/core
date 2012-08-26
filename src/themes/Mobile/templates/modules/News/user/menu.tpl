{formutil_getpassedvalue name='func' default='main' noprocess=true assign='func'}
{formutil_getpassedvalue name='theme' default='' noprocess=true assign='theme'}

{* assign the page title if News is the current module *}
{modgetname assign='module'}
{if $module eq 'News'}
  {if $func eq 'main'}
    {servergetvar name='REQUEST_URI' default='/' assign='requesturi'}
    {assign var='requesturi' value=$requesturi|replace:$baseuri:''}
    {if $requesturi neq '/' AND $requesturi neq "/`$modvars.ZConfig.entrypoint`"}
      {pagesetvar name='title' __value='News'}
    {/if}
  {elseif $func eq 'view' AND $catname|default:'' neq ''}
    {pagesetvar name='title' value=$catname}
  {/if}
{/if}

<h2>{gt text='News'}{if $func eq 'view' AND $catname|default:'' neq ''} &raquo; {$catname}{/if}</h2>