<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addService($_EXTKEY, '',  'tx_lthsecurefile_sv1',
	array(
		'title' => 'LTH secure file',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_lthsecurefile_sv1.php',
		'className' => 'tx_lthsecurefile_sv1',
	)
);

$TYPO3_CONF_VARS['FE']['eID_include']['lth_secure_file'] = 'EXT:lth_secure_file/service/ajax.php';
//Ajax in BE?
$TYPO3_CONF_VARS['BE']['AJAX']['lth_secure_file::ajaxControl'] = t3lib_extMgm::extPath('lth_secure_file').'service/be_ajax.php:lth_secure_file_ajax->ajaxControl';