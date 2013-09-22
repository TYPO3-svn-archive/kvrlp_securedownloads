<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/*
 * add frontend_user_group to sys_file tca 
 */
$tmp_kvrlp_securedownloads_columns = array(

	'frontend_user_group' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:kvrlp_securedownloads/Resources/Private/Language/locallang_db.xlf:tx_kvrlpsecuredownloads_domain_model_file.frontend_user_group',
		'config' => array(
			'type' => 'select',
			'foreign_table' => 'fe_groups',
			'size' => 10,
			'autoSizeMax' => 30,
			'maxitems' => 9999,
			'multiple' => 0,
			'wizards' => array(
				'_PADDING' => 1,
				'_VERTICAL' => 1,
				'edit' => array(
					'type' => 'popup',
					'title' => 'Edit',
					'script' => 'wizard_edit.php',
					'icon' => 'edit2.gif',
					'popup_onlyOpenIfSelected' => 1,
					'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
				'add' => Array(
					'type' => 'script',
					'title' => 'Create new',
					'icon' => 'add.gif',
					'params' => array(
						'table' => 'fe_groups',
						'pid' => '###CURRENT_PID###',
						'setValue' => 'prepend'
						),
					'script' => 'wizard_add.php',
				),
			),
		),
	),
);

t3lib_extMgm::addTCAcolumns('sys_file',$tmp_kvrlp_securedownloads_columns);

$TCA['sys_file']['columns'][$TCA['sys_file']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:kvrlp_securedownloads/Resources/Private/Language/locallang_db.xlf:sys_file.tx_extbase_type.Tx_KvrlpSecuredownloads_File','Tx_KvrlpSecuredownloads_File');

$TCA['sys_file']['types']['Tx_KvrlpSecuredownloads_File']['showitem'] = $TCA['sys_file']['types']['1']['showitem'];
$TCA['sys_file']['types']['Tx_KvrlpSecuredownloads_File']['showitem'] .= ',--div--;LLL:EXT:kvrlp_securedownloads/Resources/Private/Language/locallang_db.xlf:tx_kvrlpsecuredownloads_domain_model_file,';
$TCA['sys_file']['types']['Tx_KvrlpSecuredownloads_File']['showitem'] .= ',frontend_user_group';
$TCA['sys_file']['types']['1']['showitem'] = 'fileinfo, name, title, description, alternative, storage,frontend_user_group';
?>