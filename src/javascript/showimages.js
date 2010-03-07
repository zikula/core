/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Core
 * @subpackage Javascript
*/

// ----------------------------------------------------------------------
// Original Author of file: Francisco Burzi
// Purpose of file: showimage javascript
// ----------------------------------------------------------------------
function showimage()
{
  //if (!document.images)
  if (document.images['avatar'].src)
	 return document.images.avatar.src= 'images/avatar/' + document.Register.user_avatar.options[document.Register.user_avatar.selectedIndex].value
}
