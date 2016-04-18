<?php

namespace SEOCheckup\Magento\Command\SEO;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HTMLSize extends AbstractCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('seo:check')
            ->setDescription('Check HTML size');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$url = \Mage::app()->getStore()->getBaseUrl();
		$url = str_replace('n98-magerun.phar/', '', $url);
		
		$title = "Site HTML size";
	    $html = file_get_contents($url);
		$size = mb_strlen($html, 'UTF-8')/8000;
		$size = round($size, 2);
		
        if ($size != 0) {
			$value = $size.' KB';
        } else {
			$value = 'not available';
        }
        $output->writeln($title.': '.$value);
		 
		$pdflines = fopen("pdflines.txt", "a");
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		fclose($pdflines);
    }
}
