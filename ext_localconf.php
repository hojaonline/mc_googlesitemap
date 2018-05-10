<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Pits.' . $_EXTKEY,
	'Contents',
	array(
		'Content' => 'content',
		
	),
	// non-cacheable actions
	array(
		'Content' => 'content',
		
	)
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Pits.' . $_EXTKEY,
	'Index',
	array(
		'Content' => 'index',
		
	),
	// non-cacheable actions
	array(
		'Content' => 'index',
		
	)
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Pits.' . $_EXTKEY,
	'Pages',
	array(
		'Content' => 'pages',
		
	),
	// non-cacheable actions
	array(
		'Content' => 'pages',
		
	)
);