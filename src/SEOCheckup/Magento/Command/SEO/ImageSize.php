<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class ImageSize implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "Image(s) size";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
	    $html = file_get_contents($url);
		$pattern = '/([^"]*)(.jpe?g|.png|.svg)/i';
		$m = preg_match_all($pattern,$html,$matches);
		$value = 0;
		foreach($matches[0] as $element)
		{
			if (substr($element,0,2)=='//')
				$element = 'http:'.$element;
			try
			{
				$img = get_headers($element, 1);
				$value += $img["Content-Length"];
			}
			catch (SomeException $e){}
		}
        if (1==1) {
			$result->setStatus(Result::STATUS_OK);
			$value = round(($value/8388608),2).'MB';
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