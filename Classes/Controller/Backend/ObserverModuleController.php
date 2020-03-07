<?php
namespace GroundStack\GsMonitorObserver\Controller\Backend;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Extbase\Annotation\Inject;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3\CMS\Core\Messaging\FlashMessage;

use \TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;

use GroundStack\GsMonitorObserver\Domain\Repository\DataRepository;

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
     * Persistence Manager
     * 
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @Inject
     */
    protected $persistenceManager;

    /**
     * @var DataRepository
     */
    protected $dataRepository = null;

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
     * @param DataRepository $dataRepository
     */
    public function injectDataRepository(DataRepository $dataRepository) {
        $this->dataRepository = $dataRepository;
    }

    /**
     * action show
     * 
     * @return void
     */
    public function indexAction() {
        $domains = $this->dataRepository->findAll();

        $dataArray = [];
        $needsUpdateList = [];
        $eltsList = [];

        foreach ($domains as $key => $value) {
            // get installed data
            $url = $value->getUrl();
            $apiKey = $value->getApikey();
            $dataArray[$url] = json_decode(GeneralUtility::getURL($url.'?eID=anxapi/v1/modules&access_token='.$apiKey), true);

            $installedVersion = $dataArray[$url]['runtime']['framework_installed_version'];
            $installedVersionSplit = explode('.', $installedVersion);

            // get the current/newes version of the installed version
            // this newestVersionData is not(!) the newest LTS version!
            $newestVersionData = json_decode(GeneralUtility::getURL('https://get.typo3.org/v1/api/major/'.$installedVersion[0].'/release/latest'), true);
            
            // check if newest verstion is higher than instelled version
            $dataArray[$url]['runtime']['newest_current_version'] = $newestVersionData['version'];
            $dataArray[$url]['runtime']['update_necessary'] = false;
            if (intval( str_replace('.', '', $installedVersion) ) < intval( str_replace('.', '', $newestVersionData['version']) )) {
                $dataArray[$url]['runtime']['update_necessary'] = true;

                $needsUpdateList[$url]['toVersion'] = $newestVersionData['version'];
            }

            // provide info if installed version is elts version
            $dataArray[$url]['runtime']['elts'] = $newestVersionData['elts'];
            if ($newestVersionData['elts']) {
                $eltsList[$url]['installedVersion'] = $installedVersion;
            }
        }

        $this->view->assignMultiple([
            'data' => $dataArray,
            'needsUpdateList' => $needsUpdateList,
            'eltsList' => $eltsList
        ]);
    }

    /**
     * newDataAction
     * 
     * @param \GroundStack\GsMonitorObserver\Domain\Model\Data $data
     */
    public function newDataAction(\GroundStack\GsMonitorObserver\Domain\Model\Data $data = NULL) {
        $this->view->assignMultiple([
            'test' => 'newData',
            'data' => $data
        ]);
    }

    /**
     * addNewDataAction
     * Insert new Data to the database
     * 
     * @param \GroundStack\GsMonitorObserver\Domain\Model\Data $newData
     */
    public function addNewDataAction(\GroundStack\GsMonitorObserver\Domain\Model\Data $newData = null) {

        // if input of domain / URL is no domain e. g. given ddlkasdfkl <- this is no domain / URL
        if( preg_match('/((https?:\/\/|(ftp:\/\/)).*|(.*\.[^\.]{2,6})(\/.*)?)(\:.*)?$/m', $newData->getUrl()) === 1) {
            $this->dataRepository->add($newData);
            $this->persistenceManager->persistAll();
            // $this->dataRepository->update($newData);

            $this->addFlashMessage('The object '.$newData->getUrl().' was created.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
            $this->redirect('index');
        }

        $this->addFlashMessage('The object was not created. Domain / URL wrong, must be like https://www.domain.tld!', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
        $this->forward(
            'newData',
            NULL,
            NULL,
            [
                'data' => $newData
            ]
        );
    }

    // TODO: add UpdateAction for existing DB entry
    public function updateDataAction() {
        
    }
}