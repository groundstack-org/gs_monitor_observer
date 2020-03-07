<?php
namespace GroundStack\GsMonitorObserver\Domain\Model;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extbase\Domain\Model\FileReference;
use \TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
/**
 * Data
 */
class Data extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
    /**
     * url
     * @var string
     */
    protected $url;

    /**
     * apikey
     * @var string
     */
    protected $apikey;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        // $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
    }

    /**
     * sets the url attribute
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * returns the url attribute
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * sets the apikey attribute
     *
     * @param string $apikey
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;
    }

    /**
     * returns the apikey attribute
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

}
