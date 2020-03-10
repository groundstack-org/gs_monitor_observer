<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function() {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'GroundStack.GsMonitorObserver',
        'Observer',
        'Monitor Observer'
    );

    if (TYPO3_MODE === 'BE') {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'GroundStack.GsMonitorObserver', // NameSpace . instead \
            'web', // Make module a submodule of 'web'
            'observermodule', // Submodule key
            '', // Position
            [
                'Backend\ObserverModule' => 'index, newData, addNewData, updateData, newKeyPair',
            ],
            [
                'access' => 'systemMaintainer',
                'icon'   => 'EXT:gs_monitor_observer/Resources/Public/Icons/user_mod_observermodule.svg',
                'labels' => 'LLL:EXT:gs_monitor_observer/Resources/Private/Language/locallang.xlf',
                'navigationComponentId' => '', // hide pagetree
                'inheritNavigationComponentFromMainModule' => false
            ]
        );
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('observer_module', 'Configuration/TypoScript', 'Monitor Observer Module');

    // \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_gsmonitorobserver_domain_model_data');

});
