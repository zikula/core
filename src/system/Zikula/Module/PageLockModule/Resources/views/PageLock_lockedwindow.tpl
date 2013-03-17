{*  $Id: PageLock_lockedwindow.tpl 27056 2009-10-21 15:46:43Z drak $  *}
<div id="pageLockOverlayForm">
    <img src="images/ajax/icon_animated_busy2.gif" alt=""/>
    <!--<cbm:Image runat="server" ImageUrl="indicator_circle.gif" ImageAlign="Right"/>-->
    <h4>{gt text="This page is locked"}</h4>
    <p>{gt text="This page is locked because another user is working on it. Please wait: the page will be unlocked automatically when the other user has finished, and you will be informed."}</p>
    <p>{gt text="Locked by %s." tag1=$lockedBy}.</p>
    <div id="pageLockOverlayLED" style="width: 10px; height: 10px; padding: 0px; border: 1px solid black; background-color: #B0001D; float: right;"></div>
</div>
