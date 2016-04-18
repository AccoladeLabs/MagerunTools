<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class Minification implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "CSS styles";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
		$url = 'https://divestock.com';
		$html = file_get_contents($url);
		$pattern = '~(//.*\.)(css)~i';
		$m = preg_match_all($pattern,$html,$matches);
		$value = sizeof($matches[0]);
        if ($value>0) {
			$result->setStatus(Result::STATUS_OK);
		} else {
            $result->setStatus(Result::STATUS_ERROR);
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
		
        $title = "JavaScript files";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
		$url = 'https://divestock.com';
		$html = file_get_contents($url);
		$pattern = '~(//.*\.)(js)~i';
		$m = preg_match_all($pattern,$html,$matches);
		$value = sizeof($matches[0]);
        if ($value>0) {
			$result->setStatus(Result::STATUS_OK);
		} else {
            $result->setStatus(Result::STATUS_ERROR);
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
    }
}