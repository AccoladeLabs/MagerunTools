<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class Microdata implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Microdata";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
	    $html = file_get_contents($url);
		$microdata = '<script type="application/ld+json">';
		
        if (strpos($html, $microdata) !== false) {
            $result->setStatus(Result::STATUS_OK);
			$value = 'OK';
        } else {
			$result->setStatus(Result::STATUS_ERROR);
			$value = 'missing';
        }
		
        $msg = sprintf(
            "<info>%s of Store: <comment>%s</comment> %s - value is <comment>%s</comment></info>",
            $title,
            $store->getCode(),
            strtoupper($result->getStatus()),
            $value
        );
        $result->setMessage($msg);
		
		$pdflines = fopen("pdflines.txt", "a");
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		fclose($pdflines);
    }
}