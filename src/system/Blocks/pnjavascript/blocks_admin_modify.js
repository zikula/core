/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.com
 * @version $Id: blocks.js 23364 2007-12-31 13:32:41Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

 Event.observe(window, 'load', blocks_modify_init, false);

 function blocks_modify_init()
{
	Event.observe('blocks_advanced_placement_onclick', 'click', blocks_advanced_placement_onclick, false);
	$('block_placement_advanced').hide();
	$('blocks_advanced_placement_onclick').removeClassName('z-hide');
	$('blocks_advanced_placement_onclick').addClassName('z-show');
}

function blocks_advanced_placement_onclick()
{
	 switchdisplaystate('block_placement_advanced');
}