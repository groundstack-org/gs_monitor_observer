<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {

    $version9 = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('9.3');
    // if TYPO3 version 9 or higher:
    // if($version9) {
        // TYPO3 >= 9 uses middleware instead of hooks
    // } else {
        // Add Hooks
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = HauerHeinrich\HhExtCookieConsent\Hooks\CookieHook::class . '->setHeaderAfterCached';
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = HauerHeinrich\HhExtCookieConsent\Hooks\CookieHook::class . '->setHeaderBeforeCached';
    // }

    // \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    //     'GroundStack.GsMonitorObserver',
    //     'ObserverModule',
    //     [
    //         'ObserverModule' => 'index, newData',
    //     ],
    //     // non-cacheable actions
    //     [
    //         'ObserverModule' => '',
    //     ]
    // );

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_monitorobserver'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_monitorobserver'] = [];
    }
});
