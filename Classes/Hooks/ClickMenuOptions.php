<?php
namespace LTH\Lth_secure_file\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 20014 Frans Saris <frans@beech.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * Add ClickMenuOptions in file list
 */
class ClickMenuOptions //extends AbstractBeButtons
{
    /**
     * @var \TYPO3\CMS\Backend\ClickMenu\ClickMenu
     */
    protected $parentObject;

    /**
     * Add create tx_ icon to filemenu
     *
     * @param \TYPO3\CMS\Backend\ClickMenu\ClickMenu $parentObject Back-reference to the calling object
     * @param array $menuItems Current list of menu items
     * @param string $combinedIdentifier The combined identifier
     * @param integer $uid Id of the clicked on item
     * @return array Modified list of menu items
     */
    public function main(\TYPO3\CMS\Backend\ClickMenu\ClickMenu $parentObject, $menuItems, $combinedIdentifier, $uid)
    {
        if (!$parentObject->isDBmenu) {
            
            //if ($uid)	return $menuItems;			
				// Adds the regular item:
			$LL = $this->includeLL();

			$path = PATH_site . 'fileadmin' . str_replace('1:','',$parentObject->iParts[0]);
			if(is_file($path . ".htaccess")) {
				$label = "Unlock directory";
				$icon = "unlock.gif";
			} else {
				$label = "Lock directory";
				$icon = "lock.gif";
			}
                        
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => $path, 'crdate' => time()));
                        
            $this->parentObject = $parentObject;
            $combinedIdentifier = rawurldecode($combinedIdentifier);

            //$url = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('lth_secure_file').'cm1/index.php?target='.$path;
            //$url = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('lth_secure_file').'cm1/index.php?target='.$path . '&sid=' . mt_rand();
            $url = 'ajax.php?ajaxID=lth_secure_file::ajaxControl&file=' . $path . '&sid=' . mt_rand();
            $extraMenuItems = array('Secure file' => array(
            '<a href="javascript:" onclick="showClickmenu_raw("' . $url . '");Clickmenu.hideAll();top.list.refresh();"><span class="t3-icon t3-icon-empty t3-icon-empty-empty t3-icon-empty c-roimg" id="roimg_4">' . $label . '</span></a>',
            $label,
            //'<span class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-copy">&nbsp;</span>',
            '<img src="' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath("lth_secure_file") . 'cm1/' . $icon . '" width="15" height="12" border="0" align="top" />',
            'showClickmenu_raw("' . $url . '");Clickmenu.hideAll();top.list.refresh();',
            0,
            0));
            
           // $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_devlog', array('msg' => print_r($extraMenuItems, true), 'crdate' => time()));

            /*
             * [0] => spacer
    [copy] => Array
        (
            [0] => <span class="t3-icon t3-icon-empty t3-icon-empty-empty t3-icon-empty c-roimg" id="roimg_4">&nbsp;</span><a href="#" onclick="showClickmenu_raw('/typo3/alt_clickmenu.php?table=1%3A%2Flth%2F&amp;uid=&amp;listFr=1&amp;enDisItems=&amp;backPath=%7Ca2f619af2f&amp;addParams=&amp;ajax=1&amp;reloadListFrame=1&amp;CB[el][_FILE%7Ceff7b92236]=1%3A%2Flth%2F&amp;CB[setCopyMode]=1');return false;" onmouseover="mo(4);" onmouseout="mout(4);">Copy <span class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-copy">&nbsp;</span></a>
            [1] => Copy
            [2] =>  <span class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-copy">&nbsp;</span>
            [3] => showClickmenu_raw('/typo3/alt_clickmenu.php?table=1%3A%2Flth%2F&uid=&listFr=1&enDisItems=&backPath=%7Ca2f619af2f&addParams=&ajax=1&reloadListFrame=1&CB[el][_FILE%7Ceff7b92236]=1%3A%2Flth%2F&CB[setCopyMode]=1');return false;
            [4] => 0
            [5] => 0
        )
             */
            if (count($extraMenuItems)) {
                $menuItems[] = 'spacer';
                $menuItems = array_merge($menuItems, $extraMenuItems);
            }
        }

        return $menuItems;
    }
    
    /**
	 * Reads the [extDir]/locallang.xml and returns the \$LOCAL_LANG array found in that file.
	 *
	 * @return	[type]		...
	 */
	function includeLL()	{
		return $GLOBALS['LANG']->includeLLFile('EXT:lth_secure_file/locallang.xml', false);
	}

    /**
     * Create click menu item
     *
     * @param string $title
     * @param string $shortTitle
     * @param string $icon
     * @param string $url
     * @param bool $addReturnUrl
     * @return string
     */
    protected function createLink($title, $shortTitle, $icon, $url, $addReturnUrl = true)
    {

        if (strpos($url, 'alert') !== 0) {
            $url = $this->parentObject->urlRefForCM($url, $addReturnUrl ? 'returnUrl' : '');
        }

        return $this->parentObject->linkItem(
            '<span title="' . htmlspecialchars($title) . '">' . $shortTitle . '</span>',
            $this->parentObject->excludeIcon($icon),
            $url
        );
    }
}
