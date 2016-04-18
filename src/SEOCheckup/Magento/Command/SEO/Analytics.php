<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class Analytics implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Google Analytics";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
	    $html = file_get_contents($url);
		$ga = '//www.google-analytics.com/analytics.js'; //Check for Google Analytics script
		
		
        if (strpos($html, $ga) !== false) {
			$result->setStatus(Result::STATUS_OK);
			$value = 'Google Analytics script found.';
        } else {
            $result->setStatus(Result::STATUS_ERROR);
			$value = 'Google Analytics script not found.';
        }
		
        $msg = sprintf(
            "<info>%s of Store: <comment>%s</comment> %s - <comment>%s</comment></info>",
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
		
        $title = "Frosmo analytics";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
	    $html = file_get_contents($url);
		$frosmo = '//inpref.s3.amazonaws.com/frosmo.easy.js'; //Check for Frosmo script
		
		
        if (strpos($html, $frosmo) !== false) {
			$result->setStatus(Result::STATUS_OK);
			$value = 'Frosmo analytics script found.';
			$msg = sprintf(
				"<info>%s of Store: <comment>%s</comment> %s - <comment>%s</comment></info>",
				$title,
				$store->getCode(),
				strtoupper($result->getStatus()),
				$value
			);
			$result->setMessage($msg);
        } else {
        }
		
		$pdflines = fopen("pdflines.txt", "a");
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		fclose($pdflines);
		
    }
}