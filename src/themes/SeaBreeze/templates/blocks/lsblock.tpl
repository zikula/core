<!-- Left Block Start -->
<div class="sbLeftBlock">
    {if ! empty($title) }{* Display title if there is one *}
    <h3 class="blocktitle">{$title}</h3>
    {/if}
    <div>
        {$content}
    </div>
</div>
<!-- Left Block End -->