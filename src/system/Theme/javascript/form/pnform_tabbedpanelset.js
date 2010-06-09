// Copyright Zikula Foundation 2006 - license GNU/LGPLv2.1 (or at your option, any later version).

var pnFormTabbedPanelSet =
{
  handleTabClick: function(tabIndex, tabCount, baseId)
  {
    for (var i=1; i<=tabCount; ++i)
    {
      var panelId = baseId + "_" + i;
      var panelTabId = baseId + "Tab_" + i;

      var panel = document.getElementById(panelId);
      var panelTab = document.getElementById(panelTabId);

      if (i == tabIndex)
      {
        panel.style.display = "block";
        panelTab.className = 'linktab selected';
      }
      else
      {
        panel.style.display = "none";
        panelTab.className = 'linktab';
      }
    }

    var selectedIndexInput = document.getElementById(baseId + "SelectedIndex");
    selectedIndexInput.value = tabIndex;

    return false;
  }
}
