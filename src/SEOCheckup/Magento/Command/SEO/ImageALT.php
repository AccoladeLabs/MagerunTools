<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class ImageALT implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Image ALT attributes";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
	    $html = file_get_contents($url);
		preg_match_all('~(http.*\.)(jpe?g|png|[tg]iff?|svg)~i', $html, $images);
		preg_match_all('/alt="([\s\S])/', $html, $imgalt);
		$imgcount = sizeof($images[0]);
		$imgaltcount = sizeof($imgalt[0]);
		$percent = $imgaltcount/$imgcount;
        if ($percent>=0.75) {
			$result->setStatus(Result::STATUS_OK);
			$value = $imgaltcount.'/'.$imgcount.' images have alt tags';
        } else if ($imgcount==0){
            $result->setStatus(Result::STATUS_ERROR);
			$value = 'no images found';
		} else {
            $result->setStatus(Result::STATUS_ERROR);
			$value = 'only '.$imgaltcount.'/'.$imgcount.' images have alt tags';
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