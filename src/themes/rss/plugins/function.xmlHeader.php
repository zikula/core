<?php
/**
 * Smarty function sets correct http header for RSS feeds
 *
 */

function smarty_function_xmlHeader()
{
    header("Content-type: application/rss+xml");
}