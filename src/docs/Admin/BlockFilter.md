Block Filters
=============

A powerful new filter mechanism has been implemented for blocks. For any block you can set up your own filters based on 
nearly any request attribute or query parameter. These can also be used in any combination. As long as all
filter conditions evaluate to **true** the block will be displayed. Conditions can be compared using any available
comparator: not just `==`, but `!=`, `in_array()` and others. Array values must be a comma-delimited string.


Make a Block appear only on the Home page
-----------------------------------------

Create a filter: `_route` `==` `home`
