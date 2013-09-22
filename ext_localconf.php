<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


/** @var \TYPO3\CMS\Core\Resource\Driver\DriverRegistry $registry */
$registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\Driver\DriverRegistry');
$registry->registerDriverClass('KVRLP\KvrlpSecuredownloads\Resource\Driver\LocalSecureDriver', 'LocalSecure', 'Local secure Filesystem', 'FILE:EXT:kvrlp_securedownloads/Configuration/FlexForm/LocalSecureDriverFlexForm.xml');

$TYPO3_CONF_VARS['FE']['eID_include']['kvrlpSecureDownloads'] = t3lib_extMgm::extPath('kvrlp_securedownloads').'Classes/Utility/eIDDispatcher.php';

