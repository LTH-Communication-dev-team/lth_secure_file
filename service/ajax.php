<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005-2007 Dietrich Heise (typo3-ext(at)naw.info)
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

/**
 * @author	Dietrich Heise <typo3-ext(at)naw.info>
 * @author	Helmut Hummel <typo3-ext(at)naw.info>
 */

if (!defined ('PATH_typo3conf')) 	die ('Access denied: eID only.');
//require_once(PATH_tslib . 'class.tslib_pibase.php');


class lth_secure_file {

	protected $arrExtConf = array();

	protected $intFileSize;

	protected $intLogId;

	/**
	 * The init Function, to check the access rights
	 *
	 * @return void
	 */
	function init(){
		$this->arrExtConf = $this->GetExtConf();

		/*$this->u = intval(t3lib_div::_GP('u'));
		if (!$this->u){
			$this->u = 0;
		}

		$this->hash = t3lib_div::_GP('hash');
		$this->t = t3lib_div::_GP('t');
		$this->file = t3lib_div::_GP('file');

		$this->data = $this->u . $this->file . $this->t;
		$this->checkhash = t3lib_div::hmac($this->data);*/

		// Hook for init:
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/naw_securedl/class.tx_nawsecuredl_output.php']['init'])) {
			$_params = array('pObj' => &$this);
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/naw_securedl/class.tx_nawsecuredl_output.php']['init'] as $_funcRef)   {
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

		/*if ($this->checkhash != $this->hash){
			header('HTTP/1.1 403 Forbidden');
			exit ('Access denied!');
		}

		if (intval($this->t) < time()){
			header('HTTP/1.1 403 Forbidden');
			exit ('Access denied!');
		}*/

		$this->feUserObj = tslib_eidtools::initFeUser();
		tslib_eidtools::connectDB();

                /*if ($this->u != 0) {
			$feuser = $this->feUserObj->user['uid'];
			if ($this->u != $feuser){
				header('HTTP/1.1 403 Forbidden');
				exit ('Access denied!');
			}
		}*/
	}


	/**
	 * Output the requested file
	 *
	 * @param data $file
	 */
	public function fileOutput(){
		
		$access = false;
		
			//Anv�ndarnamn och grupp fr�n systemet
                $this->feUserObj = tslib_eidtools::initFeUser();
		tslib_eidtools::connectDB();
		$username = $this->feUserObj->user['username'];		
                $usergroup = $this->feUserObj->user['usergroup'];
			//Get 
		/*$groups = array();
		$this->getSubGroups($usergroup,'',$groups);
*/
			//Access denied om det inte finns n�gon anv�ndare inloggad
		if(!$username) {
			header('HTTP/1.1 403 Forbidden');
			echo "<h1>403 forbidden</h1><p>You must be logged in to access this file</p>";
			exit;
		}
/*
			//Fil med s�kv�g fr�n .htaccess
		$file = mysql_real_escape_string(t3lib_div::_GP('file'));

			//L�ser filens uid fr�n tabellen tx_dam
		$fileUid = intval($this->getFileUid($file));
		
			//H�mtar vilka sidor filen finns p�
		$ttContentUidArray = $this->getContentUid($fileUid);
		$ttContentUid = implode(',',$ttContentUidArray);
		
			//H�mtar sidornas grupper
		$pageAccessGroup = $this->getUsergroup($ttContentUid);
		
			//Kollar om anv�ndaren har access till sidorna
		if($pageAccessGroup) $pageAccessGroupArray = explode(',', $pageAccessGroup);
		foreach($groups as $key => $value) {
			if(in_array($value,$pageAccessGroupArray)) $access = true;
		}
		*/
                $file = $_GET['file'];
		$file = PATH_site . $file;

		//$GLOBALS['TSFE']->fe_user->user;
		//$file = t3lib_div::getFileAbsFileName(ltrim($this->file, '/'));
		//$fileName = basename($file);
			// This is a workaround for a PHP bug on Windows systems:
			// @see http://bugs.php.net/bug.php?id=46990
			// It helps for filenames with special characters that are present in latin1 encoding.
			// If you have real UTF-8 filenames, use a nix based OS.
			// FIXME: needs to be checked, if the website encoding really is UTF-8 and if UTF-8 filesystem is enabled
		/*if (TYPO3_OS == 'WIN') {
			$file = utf8_decode($file);
		}*/


		// Hook for pre-output:
		/*if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/lth_secure_file/res/class.lth_secure_file.php']['preOutput'])) {
			$_params = array('pObj' => &$this);
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/lth_secure_file/res/class.lth_secure_file.php']['preOutput'] as $_funcRef)   {
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}*/
		
			//Pushar filen om den finns och anv�ndaren har access
		if (file_exists($file)) {

			$this->intFileSize = filesize($file);

			//$this->logDownload(0);

			$strFileExtension = $this->getFileExtensionByFilename($file);

			if ((bool)$this->arrExtConf['forcedownload'] === TRUE){
				$forcetypes = t3lib_div::trimExplode("|", $this->arrExtConf['forcedownloadtype']);
				if (is_array($forcetypes)){
					if (in_array($strFileExtension, $forcetypes)) {
						$forcedownload = true;
					}
				}
			}

			$strMimeType = $this->getMimeTypeByFileExtension($strFileExtension);

			// Hook for output:
			// TODO: deprecate this hook?
			/*if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/naw_securedl/class.tx_nawsecuredl_output.php']['output'])) {
				$_params = array(
					'pObj' => &$this,
					'fileExtension' => '.' . $strFileExtension, // Add leading dot for compatibility in this hook
					'mimeType' => &$strMimeType,
				);
				foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/naw_securedl/class.tx_nawsecuredl_output.php']['output'] as $_funcRef)   {
					t3lib_div::callUserFunction($_funcRef, $_params, $this);
				}
			}*/

				//TODO: Check IE compatibility with these headers
			header('Pragma: private');
			header('Expires: 0'); // set expiration time
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Type: ' . $strMimeType);
			header('Content-Length: ' . $this->intFileSize);

			if ($forcedownload == true) {
				header('Content-Disposition: attachment; filename="' . $fileName . '"');
			} else {
				header('Content-Disposition: inline; filename="' . $fileName . '"');
			}

			$strOutputFunction = trim($this->arrExtConf['outputFunction']);

                        switch ($strOutputFunction) {
				case 'readfile_chunked':
                                    $this->readfile_chunked($file);
                                    break;

				case 'fpassthru':
                                    $handle = fopen($file, 'rb');
                                    fpassthru($handle);
                                    fclose($handle);
				break;

				case 'readfile':
					//fallthrough, this is the default case
				default:
                                    readfile($file);
				break;
			}

				// make sure we can detect an aborted connection, call flush
			ob_flush();
			flush();
			if (!connection_aborted() AND $strOutputFunction !== 'readfile_chunked') {
				//$this->logDownload();
			}

		} else {
			//print 'Access denied!';
			header('HTTP/1.1 403 Forbidden');
			echo "<h1>403 forbidden.</h1>";
			exit;
		}
	}
	
	/*function getSubGroups($grList, $idList='', &$groups)    {
	 
    	// Fetching records of the groups in $grList (which are not blocked by lockedToDomain either):
                 //$lockToDomain_SQL = ' AND (lockToDomain=\'\' OR lockToDomain IS NULL OR lockToDomain=\''.$this->authInfo['HTTP_HOST'].'\')';
                // if (!$this->authInfo['showHiddenRecords'])      $hiddenP = 'AND hidden=0 ';
       	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,subgroup', 'fe_groups', 'deleted=0 '.$hiddenP.' AND uid IN ('.$grList.')'.$lockToDomain_SQL);
 		$groupRows = array();   // Internal group record storage
 
                         // The groups array is filled
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))      {
        	if(!in_array($row['uid'], $groups)) {
        		$groups[] = $row['uid'];
        	}
            $groupRows[$row['uid']] = $row;
        }
        	// Traversing records in the correct order
        $include_staticArr = t3lib_div::intExplode(',', $grList);
        foreach($include_staticArr as $uid)     {       // traversing list
            	// Get row:
        	$row=$groupRows[$uid];
            if (is_array($row) && !t3lib_div::inList($idList,$uid)) {       // Must be an array and $uid should not be in the idList, because then it is somewhere previously in the grouplist
                                         // Include sub groups
          		if (trim($row['subgroup']))     {
                	$theList = implode(',',t3lib_div::intExplode(',',$row['subgroup']));    // Make integer list
                    $this->getSubGroups($theList, $idList.','.$uid, $groups);               // Call recursively, pass along list of already processed groups so they are not recursed again.
              	}
            }
    	}
	}
	
	protected function getFileUid($file)
	{
		$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("uid", "tx_dam", "CONCAT('/',file_path,file_name) = '$file'", "", "");
	
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uid = $row['uid'];
		}
		
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $uid; 
	}
	
	protected function getContentUid($uid)
	{
		$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
		"sys_refindex.tablename AS tablenames, sys_refindex.recuid AS uid_foreign, sys_refindex.ref_uid AS uid_local, sys_refindex.field, sys_refindex.softref_key",
		"tx_dam, sys_refindex", 
		"tx_dam.deleted=0 AND sys_refindex.deleted=0 AND tx_dam.uid=sys_refindex.ref_uid AND sys_refindex.ref_table='tx_dam' AND sys_refindex.ref_uid IN ($uid) AND NOT sys_refindex.softref_key=''",
		"",
		"sys_refindex.sorting", 
		"1000"
		);
	
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$rows[] = $row['uid_foreign'];
		}
		
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $rows; 
	}
	
	protected function getUsergroup($ttContentUid)
	{
		$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
		"P.fe_group",
		"pages P INNER JOIN tt_content TT ON P.uid = TT.pid",
		"TT.uid IN($ttContentUid) and P.deleted = 0 AND TT.deleted = 0"
		);
	
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if($fe_group) $fe_group .= ',';
			$fe_group .= $row['fe_group'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $fe_group; 
	}


	protected function logDownload($intFileSize = null)
	{
		if ($this->isLoggingEnabled()) {

			if (is_null($intFileSize)) {
				$intFileSize = $this->intFileSize;
			}

			$data_array = array (
				'tstamp' => time(),
				'file_name' => $this->file,
				'file_size' => $intFileSize,
				'user_id' => intval($this->feUserObj->user['uid']),
			);

			if (is_null($this->intLogId)) {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_nawsecuredl_counter', $data_array);
				$this->intLogId = intval($GLOBALS['TYPO3_DB']->sql_insert_id());
			} else {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_nawsecuredl_counter', '`uid`='.$this->intLogId, $data_array);
			}

		}
	}

*/

	protected function GetExtConf()
	{
		static $arrExtConf=array();

		if (!$arrExtConf) {
			$arrExtConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_securedl']);
		}

		return $arrExtConf;
	}


	protected function readfile_chunked($strFileName)
	{
		$chunksize = intval($this->arrExtConf['outputChunkSize']); // how many bytes per chunk
		$timeout = ini_get('max_execution_time');
		$buffer = '';
		$bytes_sent = 0;
		$handle = fopen($strFileName, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle) && (!connection_aborted()) ) {
			set_time_limit($timeout);
			$buffer = fread($handle, $chunksize);
			print $buffer;
			$bytes_sent += $chunksize;
			ob_flush();
			flush();
			$this->logDownload(t3lib_div::intInRange($bytes_sent, 0, $this->intFileSize));
		}
		return fclose($handle);
	}

	/**
	 * Extracts the file extension out of a complete file name.
	 *
	 * @param string $strFileName
	 */
	protected function getFileExtensionByFilename($strFileName)
	{
		return t3lib_div::strtolower(ltrim(strrchr($strFileName, '.'), '.'));
	}

	/**
	 * Looks up the mime type for a give file extension
	 *
	 * @param string $strFileExtension lowercase file extension
	 * @return string mime type
	 */
	protected function getMimeTypeByFileExtension($strFileExtension)
	{
			// Check files with unknown file extensions, if they are image files (currently disabled)
		$checkForImageFiles = FALSE;

			// Array with key/value pairs consisting of file extension (without dot in front) and mime type
		$arrMimeTypes = array(
				// MS-Office filetypes
			'pps' => 'application/vnd.ms-powerpoint',
			'doc' => 'application/msword',
			'xls' => 'application/vnd.ms-excel',
			'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xps' => 'application/vnd.ms-xpsdocument',

				// Open-Office filetypes
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ott' => 'application/vnd.oasis.opendocument.text-template',
			'odg' => 'application/vnd.oasis.opendocument.graphics',
			'otg' => 'application/vnd.oasis.opendocument.graphics-template',
			'odp' => 'application/vnd.oasis.opendocument.presentation',
			'otp' => 'application/vnd.oasis.opendocument.presentation-template',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
			'odc' => 'application/vnd.oasis.opendocument.chart',
			'otc' => 'application/vnd.oasis.opendocument.chart-template',
			'odi' => 'application/vnd.oasis.opendocument.image',
			'oti' => 'application/vnd.oasis.opendocument.image-template',
			'odf' => 'application/vnd.oasis.opendocument.formula',
			'otf' => 'application/vnd.oasis.opendocument.formula-template',
			'odm' => 'application/vnd.oasis.opendocument.text-master',
			'oth' => 'application/vnd.oasis.opendocument.text-web',

				// Media file types
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo',
			'pdf' => 'application/pdf',
			'svg' => 'image/svg+xml',
			'flv' => 'video/x-flv',
			'swf' => 'application/x-shockwave-flash',
			'htm' => 'text/html',
			'html' => 'text/html',
		);

			// Read all additional MIME types from the EM configuration into the array $strAdditionalMimeTypesArray
		if ($this->arrExtConf['additionalMimeTypes']) {

			$strAdditionalFileExtension = '';
			$strAdditionalMimeType = '';
			$arrAdditionalMimeTypeParts = t3lib_div::trimExplode(',', $this->arrExtConf['additionalMimeTypes'], TRUE);

			foreach($arrAdditionalMimeTypeParts as $strAdditionalMimeTypeItem) {
				list($strAdditionalFileExtension, $strAdditionalMimeType) = t3lib_div::trimExplode('|', $strAdditionalMimeTypeItem);
				if(!empty($strAdditionalFileExtension) && !empty($strAdditionalMimeType)) {
					$strAdditionalFileExtension = t3lib_div::strtolower($strAdditionalFileExtension);
					$arrMimeTypes[$strAdditionalFileExtension] = $strAdditionalMimeType;
				}
			}

			unset($strAdditionalFileExtension);
			unset($strAdditionalMimeType);
		}

		//TODO: Add hook to be able to manipulate and/or add mime types

			// Check if an specific MIME type is configured for this file extension
		if (array_key_exists($strFileExtension, $arrMimeTypes)) {
			$strMimeType = $arrMimeTypes[$strFileExtension];
		} else if ($checkForImageFiles) {
				// files bigger than 32MB are now 'application/octet-stream' by default (getimagesize memory_limit problem)
			if ($this->intFileSize < 1024*1024*32){
				$arrImageInfos = @getimagesize($file);
				$intImageType = (int)$arrImageInfos[2];
			}

			$arrImageMimeType[0] = 'application/octet-stream';
			$arrImageMimeType[1] = 'image/gif';
			$arrImageMimeType[2] = 'image/jpeg';
			$arrImageMimeType[3] = 'image/png';

			$strMimeType = $arrImageMimeType[$intImageType];
		} else {
			$strMimeType = 'application/octet-stream';
		}

		return $strMimeType;
	}

	/**
	 * Checks if logging has been enabled in configuration
	 *
	 * @return bool
	 
	protected function isLoggingEnabled()
	{
		return (bool)$this->arrExtConf['log'];
	}*/
}

$securedl = t3lib_div::makeInstance('lth_secure_file');
$securedl->init();
if($_GET['action']=='fileOutput') {
    $securedl->fileOutput();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_secure_file/res/class.lth_secure_file.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/lth_secure_file/res/class.lth_secure_file.php']);
}