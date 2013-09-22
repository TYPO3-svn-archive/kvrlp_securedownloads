<?php
namespace KVRLP\KvrlpSecuredownloads\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alexander Wende <alexander.wende@kv-rlp.de>, 
 *           Kassenärztliche Vereinigung Rheinland-Pfalz
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * dispatcher for eID fileProvider request
 *
 * @author Alexander Wende <alexander.wende@kv-rlp.de>
 */

//init environment
$eidUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Utility\\EidUtility');
$GLOBALS['TSFE'] =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',$GLOBALS['TYPO3_CONF_VARS'], 4, 0);
$GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
$GLOBALS['TSFE']->fe_user =  $eidUtility->initFeUser();
$GLOBALS['TSFE']->initUserGroups();
//@todo check why realurl config is needed
$GLOBALS['TSFE']->config['config']['tx_realurl_enable'] = 1; 

//set parameter
$id = urldecode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET(id));
$identifier = urldecode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET(identifier));
$hash = urldecode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET(h));
$sid = urldecode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET(sid));

//init fileProvider
$fileProvider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('KVRLP\\KvrlpSecuredownloads\\Resource\\FileProvider');
$fileProvider->setPageById($id);
$fileProvider->setIdentifier($identifier);
$fileProvider->setStorageById($_GET['sid']);
$fileProvider->setRequestHash($hash);

//try to provide file, if fails, catch exception
try{
    $fileProvider->provideFile();
}catch (\KVRLP\KvrlpSecuredownloads\Exception\HashFailedException $hashFailedException){
    \TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('Hash failed for identifier: '.$identifier ,'kvrlp_securedownloads',\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_NOTICE);
    $GLOBALS['TSFE']->pageNotFoundAndExit();
}catch (\KVRLP\KvrlpSecuredownloads\Exception\NotFoundException $notFoundException){
    \TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('File not found: '.$identifier ,'kvrlp_securedownloads',\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_NOTICE);
    $GLOBALS['TSFE']->pageNotFoundAndExit();
}catch (\KVRLP\KvrlpSecuredownloads\Exception\NoFileAccessException $noFileAccessException){
    \TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('No Access to file: '.$identifier ,'kvrlp_securedownloads',\TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_NOTICE);
    $GLOBALS['TSFE']->pageNotFoundAndExit();
}
