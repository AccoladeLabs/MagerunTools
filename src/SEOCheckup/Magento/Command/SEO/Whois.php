<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class Whois implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
        $title = "WHOIS Created date";
		$result = $results->createResult();
		$url = $store->getConfig('web/secure/base_url');
		
		/* www.whoisxmlapi.com credentials */
		$username = 'magetest';
		$password = 'testB027';

	    $html = file_get_contents('http://www.whoisxmlapi.com/whoisserver/WhoisService?domainName='.$url.'&username='.$username.'&password='.$password);
		$r = explode('<createdDate>', $html);
		if (isset($r[1])){
			$r = explode('</createdDate>', $r[1]);
			$value = $r[0];
		}
		
        if (strlen($html)==131) {
			$result->setStatus(Result::STATUS_ERROR);
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
            $result->setStatus(Result::STATUS_OK);
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
		
        $title = "WHOIS Expire date";
		$result = $results->createResult();
		$r = explode('<expiresDate>', $html);
		if (isset($r[1])){
			$r = explode('</expiresDate>', $r[1]);
			$value = $r[0];
		}
		
        if (strlen($html)==131) {
			$result->setStatus(Result::STATUS_ERROR);
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
            $result->setStatus(Result::STATUS_OK);
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
		
        $title = "WHOIS Estimated domain age";
		$result = $results->createResult();
		$r = explode('<estimatedDomainAge>', $html);
		if (isset($r[1])){
			$r = explode('</estimatedDomainAge>', $r[1]);
			$value = $r[0];
		}
		
        if (strlen($html)==131) {
			$result->setStatus(Result::STATUS_ERROR);
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
            $result->setStatus(Result::STATUS_OK);
			$value = $value.' days';
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