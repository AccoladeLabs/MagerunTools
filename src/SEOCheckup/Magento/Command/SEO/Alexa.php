<?php

namespace SEOCheckup\Magento\Command\SEO;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Alexa extends AbstractCommand
{


    protected function configure()
    {
        parent::configure();
        $this
            ->setName('seo:alexa')
            ->setDescription('Check Alexa rank');
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
		
        $title = "Alexa rank";
	    $html = file_get_contents('http://www.alexa.com/siteinfo/'.$url);
		$r = explode('/awis -->', $html);
		if (isset($r[1])){
			$r = explode('</strong>', $r[1]);
			$value = trim(preg_replace('/\s\s+/', ' ', $r[0]));
		}
		
        if (strpos($value, '<span') !== false) {
			$value = '0';
        } else {
        }
        $output->writeln($title.': '.$value);
		 
		$pdflines = fopen("pdflines.txt", "a");
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		fclose($pdflines);
    }
}
