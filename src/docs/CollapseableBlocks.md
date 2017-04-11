Collapseable Blocks
===================

In the BlocksModule settings, an admin may **enable block collapse icons**. This feature adds a small icon next to each
block title that when clicked will 'minimize' (collapse) the block. The icon can be clicked again to maximize or restore
the block.

The state of the collapse is maintained through javascript local storage and is only available on "modern" browsers.

Block developers should enclose **all** the block content in a block-level element (`<div>`, `<ul>` or similar) in order
to take full advantage of the feature.

Block developers can disable the "collapsibility" of a block by adding the `nonCollapsible` class to the Html element
that immediately follows the title element (likely the block-level element referenced above).
