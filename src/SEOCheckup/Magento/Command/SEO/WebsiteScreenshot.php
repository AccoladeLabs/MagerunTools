<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class WebsiteScreenshot implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Screenshotlayer";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
		$apiurl = 'http://api.screenshotlayer.com/api/capture?access_key=7bc6fe1f0c9c4c95ca1ab7ddcd64d89a&url='.$url.'&viewport=1440x900&width=720';
	    $img = 'screenshot.png';
		file_put_contents($img, file_get_contents($apiurl));
        $result->setMessage($msg);
    }
}