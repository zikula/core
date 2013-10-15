{if $enablePanel == 'left'}
    <div data-role="panel" id="leftPanel" data-position="left" data-display="push">
        {blockposition name=$panelBlockPositionName}
    </div><!-- /panel -->
{elseif $enablePanel == 'right'}
    <div data-role="panel" id="rightPanel" data-position="right" data-display="push">
        {blockposition name=$panelBlockPositionName}
    </div><!-- /panel -->
{/if}

{if $enableSwipeToOpenPanel && $enablePanel != ''}
    {if $enablePanel == 'left'}
        {assign var='swipeToOpenPanel' value='right'}
        {assign var='swipeToClosePanel' value='left'}
    {elseif $enablePanel == 'right'}
        {assign var='swipeToOpenPanel' value='left'}
        {assign var='swipeToClosePanel' value='right'}
    {/if}
    {pageaddvarblock}
        <script type="text/javascript">
            jQuery(function() {
                // Bind the swipe handler callback function to the swipe event on page.
                jQuery("#page").on("swipe{{$swipeToOpenPanel}}", openPanel);
                jQuery("#page").on("swipe{{$swipeToClosePanel}}", closePanel);

                function openPanel() {
                    jQuery("#{{$enablePanel}}Panel").panel("open");
                }
                function closePanel() {
                    jQuery("#{{$enablePanel}}Panel").panel("close");
                }
            });
        </script>
    {/pageaddvarblock}
{/if}