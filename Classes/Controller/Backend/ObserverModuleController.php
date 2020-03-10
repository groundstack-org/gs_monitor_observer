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

use \TYPO3\CMS\Core\Http\RequestFactory;

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

    /**
     * @var RequestFactory
     */
    private $requestFactory;

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

    public function __construct(RequestFactory $requestFactory) {
        $this->requestFactory = $requestFactory;
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

        foreach ($domains as $key => $value) {
            $dataArray = [];
            $needsUpdateList = [];
            $eltsList = [];
            // get installed data
            $uid = $value->getUid();
            $pid = $value->getPid();
            $url = $value->getUrl();
            $apiKey = $value->getApikey();
            $privateKey = $value->getPrivatekey();
            $publicKey = $value->getPublickey();

            $token = $this->getJwtToken($url, $apiKey);

            if (is_string($token)) {
                // Get info from api
                $dataArray[$url] = $this->sendWithJwtToken($url, $token);

                // $dataArray[$url]['uid'] = $uid;
                // $dataArray[$url]['pid'] = $pid;
                $dataArray[$url]['apikey'] = $apiKey;
                $dataArray[$url]['privatekey'] = $privateKey;
                $dataArray[$url]['publickey'] = $publicKey;

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

    /**
     * initialize updateDataAction
     *
     * @return void
     */
    public function initializeUpdateDataAction() {
        if ($this->arguments->hasArgument('updateData')) {
            $this->arguments->getArgument('updateData')->getPropertyMappingConfiguration()->skipProperties('newUrl');
            $this->arguments->getArgument('updateData')->getPropertyMappingConfiguration()->skipProperties('newApikey');
        }
    }

    /**
     * updateDataAction
     * Updates a entry at the database
     *
     * @param \GroundStack\GsMonitorObserver\Domain\Model\Data $updateData
     * @return void
     */
    public function updateDataAction(\GroundStack\GsMonitorObserver\Domain\Model\Data $updateData) {
        $oldEntry = $this->dataRepository->findByUrl($updateData->getUrl())[0];
        // get skipped properties
        $updateDataSkipped = $this->getControllerContext()->getRequest()->getArgument('updateData');
        $newUrl = $updateDataSkipped['newUrl']; // TODO: validate domain
        $newApiKey = $updateDataSkipped['newApikey'];

        $oldEntry->setUrl($newUrl);
        $oldEntry->setApikey($newApiKey);

        $this->dataRepository->update($oldEntry);

        $this->redirect('index');
    }

    /**
     * getJwtToken
     *
     * @param string $url
     * @param string $apiKey
     * @return void
     */
    public function getJwtToken(string $url, string $apiKey) {
        $target = $url . '/gs-monitor-api/v1/data';
        $additionalOptions = [
            // Additional headers for this specific request
            'headers' => [
                'Cache-Control' => 'no-cache',
                'api-key' => $apiKey
            ],
            // Additional options, see http://docs.guzzlephp.org/en/latest/request-options.html
            'allow_redirects' => false,
            'cookies' => false,
        ];

        // Return a PSR-7 compliant response object
        $response = $this->requestFactory->request($target, 'POST', $additionalOptions);
        if ($response->getStatusCode() === 200) {
            if (strpos($response->getHeaderLine('Content-Type'), 'application/json; charset=UTF-8') === 0) {
                $token = $response->getHeaderLine('Authorization');
                if (!empty($token)) {
                    return $token;
                }
            }
        }

        return false;
    }

    public function sendWithJwtToken(string $url, string $jwtToken) {
        $target = $url.'/gs-monitor-api/v1/data';
        $additionalOptions = [
            // Additional headers for this specific request
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Authorization' => 'Bearer ' . $jwtToken
            ],
            // Additional options, see http://docs.guzzlephp.org/en/latest/request-options.html
            'allow_redirects' => false,
            'cookies' => false,
        ];

        // Return a PSR-7 compliant response object
        $response = $this->requestFactory->request($target, 'POST', $additionalOptions);

        if ($response->getStatusCode() === 200) {
            if (strpos($response->getHeaderLine('Content-Type'), 'application/json; charset=UTF-8') === 0) {
                $domain = $this->dataRepository->findByUrl($url)[0];
                $private_key = $domain->getPrivatekey();

                $responseContent = json_decode($response->getBody(), true);
                $responseDecoded = base64_decode($responseContent['secretInfo']);

                openssl_private_decrypt($responseDecoded, $decrypted, $private_key);
                $responseArray = json_decode($decrypted, true);

                $responseData = openssl_decrypt(base64_decode($responseContent['encryptedData']), $responseArray['cipher'], $responseArray['password'], 0, base64_decode($responseArray['iv']));

                return json_decode($responseData, true);
            }
        }

        return false;
    }

    /**
     * newKeyPairAction
     * Generate new private / public key pair
     *
     * @param string $url
     * @return void
     */
    public function newKeyPairAction(string $url = '') {

        if(!empty($url)) {
            // Configuration settings for the key
            $config = array(
                'digest_alg' => 'sha512',
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            );

            // Create the private and public key
            $res = openssl_pkey_new($config);

            // Extract the private key into $private_key
            openssl_pkey_export($res, $private_key);

            // Extract the public key into $public_key
            $publickey=openssl_pkey_get_details($res);
            $publickey=$publickey["key"];

            // Write to LocalConfiguration.php
            // $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            // $configurationManager = $objectManager->get('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
            // $configurationManager->updateLocalConfiguration([
            //     'EXTENSIONS'=> [
            //         'gs_monitor_observer' => [
            //             'privateKey' => $private_key,
            //             'publicKey' => $public_key
            //         ]
            //     ]
            // ]);

            // Save Key to Database
            $entry = $this->dataRepository->findByUrl($url)[0];
            $entry->setPrivatekey($private_key);
            $entry->setPublickey($public_key);
            $this->dataRepository->update($entry);

            $this->forward(
                'index',
                NULL,
                NULL,
                []
            );
        }

        $this->forward(
            'index',
            NULL,
            NULL,
            [
                'error' => 'No key generated'
            ]
        );
    }
}
