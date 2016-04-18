<?php
namespace SEO\Checkup;

use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\StoreCheck;
use N98\Magento\Command\System\CheckCommand;

class MakePDF implements StoreCheck
{
    /**
     * @param ResultCollection $results
     */

    public function check(ResultCollection $results, \Mage_Core_Model_Store $store)
    {
		include('pdf.php'); //Make PDF
    }
}