<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE == 'BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
		//'name' => 'tx_lthsecurefile_cm1',
		//'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_lthsecurefile_cm1.php'
            'name' => 'LTH\\Lth_secure_file\\Hooks\\ClickMenuOptions'
	);
}

/*
 * if (TYPO3_MODE === 'BE') {
    // Add click menu item:
    $GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][] = array(
        'name' => 'BeechIt\\FalSecuredownload\\Hooks\\ClickMenuOptions'
    );
}
 */