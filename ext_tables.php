<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Pits.' . $_EXTKEY, 'Contents', 'Google Sitemap for Contents'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Pits.' . $_EXTKEY, 'Index', 'Google Sitemap for Index'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Pits.' . $_EXTKEY, 'Pages', 'Google Sitemap for Pages'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Mc Google Site Map');
$pluginSignature = str_replace('_', '', $_EXTKEY) . '_contents';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform.xml');


$pluginSignature = str_replace('_', '', $_EXTKEY) . '_pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pages.xml');

$pluginSignature = str_replace('_', '', $_EXTKEY) . '_index';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_index.xml');