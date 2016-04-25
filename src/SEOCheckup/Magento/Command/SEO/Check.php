<?php

namespace SEOCheckup\Magento\Command\SEO;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Check extends AbstractCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('seo:check')
            ->setDescription('Seo Checkup')
			->addArgument('url', InputArgument::OPTIONAL, 'An optional URL to check')
			->setHelp("When run without a URL, the current Magento directory is checked. Otherwise, an external scan is run on the URL given.");
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$this->detectMagento($output, true);
		if (!$this->initMagento()) {
			return;
		}

		# If a URL was given, set it, otherwise get it from the settings and take note either way.
		if ($input->getArgument('url')) {
			$url = $input->getArgument('url');
			$external = true;
		} else {
			$url = \Mage::app()->getStore()->getBaseUrl();
			$external = false;
		}
		$url = str_replace('n98-magerun.phar/', '', $url);
		if (substr($url,0,6)=='https;'){
			$url = str_replace('https;', 'https:', $url);
		}
		else if(substr($url,0,5)=='http;'){
			$url = str_replace('http;', 'http:', $url);
		}
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
		$txt = $title.' of store: '.$value;
		
		
        $title = "Google Analytics";
	    $html = file_get_contents($url);
		$ga = '//www.google-analytics.com/analytics.js'; //Check for Google Analytics script
        if (strpos($html, $ga) !== false) {
			$value = 'Google Analytics script found';
        } else {
			$value = 'Google Analytics script not found';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Frosmo analytics";
	    $html = file_get_contents($url);
		$frosmo = '//inpref.s3.amazonaws.com/frosmo.easy.js'; //Check for Frosmo script
        if (strpos($html, $frosmo) !== false) {
			$value = 'Frosmo analytics script found';
        } else {
			$value = 'Frosmo analytics script not found';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Default Title";
        $getvalue = \Mage::app()->getConfig('design/head/default_title');
        if ('Magento Commerce' == $getvalue) {
            $value = 'ERROR - not changed';
        } else {
            $value = 'OK';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");

		
        $title = "HTML Head Default Description";
        $getvalue = \Mage::app()->getConfig('design/head/default_description');
        if ('Default Description' == $getvalue) {
            $value = 'ERROR - not changed';
        } else {
            $value = 'OK';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Default Keywords";
        $getvalue = \Mage::app()->getConfig('design/head/default_keywords');
        if ('Magento, Varien, E-commerce' == $getvalue) {
            $value = 'ERROR - not changed';
        } else {
            $value = 'OK';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Default Robots";
        $getvalue = \Mage::app()->getConfig('design/head/default_robots');
        if ('INDEX,FOLLOW' != $getvalue) {
            $value = 'ERROR - not changed';
        } else {
            $value = 'OK';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Demo Notice";
        $getvalue = \Mage::app()->getConfig('design/head/demonotice');
        if ($getvalue) {
            $value = 'ERROR - not changed';
        } else {
            $value = 'OK';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Image ALT attributes";
	    $html = file_get_contents($url);
		preg_match_all('~(http.*\.)(jpe?g|png|[tg]iff?|svg)~i', $html, $images);
		preg_match_all('/alt="([\s\S])/', $html, $imgalt);
		$imgcount = sizeof($images[0]);
		$imgaltcount = sizeof($imgalt[0]);
		$percent = $imgaltcount/$imgcount;
        if ($percent>=0.75) {
			$value = $imgaltcount.'/'.$imgcount.' images have alt tags';
        } else if ($imgcount==0){
			$value = 'no images found';
		} else {
			$value = 'only '.$imgaltcount.'/'.$imgcount.' images have alt tags';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Image(s) size";
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
		$value = round(($value/8388608),2).'MB';
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Merge CSS Files";
		$rewrite = \Mage::app()->getConfig('dev/css/merge_css_files');
        if (!$rewrite) {
			$value = '0';
        } else {
			$value = '1';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' for store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Merge JavaScript Files ";
		$rewrite = \Mage::app()->getConfig('dev/js/merge_files');
        if (!$rewrite) {
			$value = '0';
        } else {
			$value = '1';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' for store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Microdata";
	    $html = file_get_contents($url);
		$microdata = '<script type="application/ld+json">';
        if (strpos($html, $microdata) !== false) {
			$value = 'OK';
        } else {
			$value = 'missing';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "CSS styles";
		$html = file_get_contents($url);
		$pattern = '~(//.*\.)(css)~i';
		$m = preg_match_all($pattern,$html,$matches);
		$value = sizeof($matches[0]);
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "JavaScript files";
		$html = file_get_contents($url);
		$pattern = '~(//.*\.)(js)~i';
		$m = preg_match_all($pattern,$html,$matches);
		$value = sizeof($matches[0]);
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Response";
	    $ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
        if (200==$retcode) {
			$value='online';
        } else if (401==$retcode){
			$value='Error 401 - Unauthorized: Access is denied';
		} else {
			$value='offline';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");

		
		
		$title = "Using Web Server Rewrites";
		$rewrite = \Mage::app()->getConfig('web/seo/use_rewrite');
        if (!$rewrite) {
			$value = '0';
        } else {
			$value = '1';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Robots.txt";
        if (!file_exists('robots.txt')) {
			$value='0';
        } else {
			$value='1';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Sitemap.xml";
        if (!file_exists('sitemap.xml')) {
			$value='0';
        } else {
			$value='1';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");

		
        $title = "WHOIS Created date";
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
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "WHOIS Expire date";
		$r = explode('<expiresDate>', $html);
		if (isset($r[1])){
			$r = explode('</expiresDate>', $r[1]);
			$value = $r[0];
		}
        if (strlen($html)==131) {
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "WHOIS Estimated domain age";
		$r = explode('<estimatedDomainAge>', $html);
		if (isset($r[1])){
			$r = explode('</estimatedDomainAge>', $r[1]);
			$value = $r[0];
		}
		
        if (strlen($html)==131) {
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        } else {
			$value = $value.' days';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
		fclose($pdflines);
		include('pdf.php'); //Make PDF
    }
}
