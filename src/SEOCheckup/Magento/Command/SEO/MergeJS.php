<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class MergeJS implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Merge JavaScript Files ";
		$result = $results->createResult();
		$rewrite = $store->getConfig('dev/js/merge_files');
        if (!$rewrite) {
			$result->setStatus(Result::STATUS_ERROR);
			$value = '0';
        } else {
            $result->setStatus(Result::STATUS_OK);
			$value = '1';
        }
		
        $msg = sprintf(
            "<info>%s for Store: <comment>%s</comment> %s - value is <comment>%s</comment></info>",
            $title,
            $store->getCode(),
            strtoupper($result->getStatus()),
            $value
        );
        $result->setMessage($msg);
		
		$pdflines = fopen("pdflines.txt", "a");
		$txt = $title.' for store: '.$value;
		fwrite($pdflines, $txt."\n");
		fclose($pdflines);
    }
}