<?php
namespace GroundStack\GsMonitorObserver\Controller\Backend;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3\CMS\Core\Messaging\FlashMessage;

use \TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/***
 *
 * This file is part of the "gs_monitor_observer" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 
 *
 ***/

class ObserverModuleController extends ActionController {

    protected $extensionKey;
    protected $extensionConfiguration;

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view) {
        $this->extensionKey = 'gs_monitor_observer';
        // Typo3 extension manager gearwheel icon (ext_conf_template.txt)
        $this->extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$this->extensionKey];

        $this->view->assign('script', 'T3_THIS_LOCATION = ' . GeneralUtility::quoteJSvalue(rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'))) . ";");
    }

    protected $extConf;

    public function __construct() {
        // $this->extConf = GeneralUtility::makeInstance(\GroundStack\TestModul\Helper\ConfigurationHelper::class);
    }

    /**
     * action show
     * 
     * @return void
     */
    public function indexAction() {

        // DebuggerUtility::var_dump("index", "indexdebug");

        $domains = [ // TODO: get infos from tx_gsmonitorobserver_domain_model_data
            'domain' => [
                'url' => 'https://www.austria-radreisen.at',
                'apikey' => 'testings'
            ]
        ];

        $dataArray = [];
        $needsUpdateList = [];
        $eltsList = [];

        foreach ($domains as $key => $value) {
            // get installed data
            $dataArray[$value['url']] = json_decode(GeneralUtility::getURL($value['url'].'?eID=anxapi/v1/modules&access_token='.$value['apikey']), true);

            $installedVersion = $dataArray[$value['url']]['runtime']['framework_installed_version'];
            $installedVersionSplit = explode('.', $installedVersion);

            // get the current/newes version of the installed version
            // this newestVersionData is not(!) the newest LTS version!
            $newestVersionData = json_decode(GeneralUtility::getURL('https://get.typo3.org/v1/api/major/'.$installedVersion[0].'/release/latest'), true);
            
            // check if newest verstion is higher than instelled version
            $dataArray[$value['url']]['runtime']['newest_current_version'] = $newestVersionData['version'];
            $dataArray[$value['url']]['runtime']['update_necessary'] = false;
            if (intval( str_replace('.', '', $installedVersion) ) < intval( str_replace('.', '', $newestVersionData['version']) )) {
                $dataArray[$value['url']]['runtime']['update_necessary'] = true;

                $needsUpdateList[$value['url']]['toVersion'] = $newestVersionData['version'];
            }

            // provide info if installed version is elts version
            $dataArray[$value['url']]['runtime']['elts'] = $newestVersionData['elts'];
            if ($newestVersionData['elts']) {
                $eltsList[$value['url']]['installedVersion'] = $installedVersion;
            }

            // DebuggerUtility::var_dump($newestVersionData, "newestVersionData");
        }

        $this->view->assignMultiple([
            'data' => $dataArray,
            'needsUpdateList' => $needsUpdateList,
            'eltsList' => $eltsList
        ]);
    }

    public function insertNewDomain() {
        // TODO: create html form for insert url and apikey to tx_gsmonitorobserver_domain_model_data
    }
}