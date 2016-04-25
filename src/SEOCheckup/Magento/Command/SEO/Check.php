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
		# Add protocol to the beginning of the URL if it doesn't already include it 
		if (preg_match("(https?:\/\/)", $url) === 0) {
			$url = "http://" . $url;
		}
		# Grab the response headers for parsing
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTPHEADER => ["Accept-Encoding: gzip, deflate"]
		]);
		$result = curl_exec($ch);
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		# Begin parsing the data from the header.
		$data = explode("\n", $result);
		$headers = [];
		# Store the data in key => value pairs for easy access.
		foreach ($data as $index => $info) {
			if ($info == "") {
				continue;
			}
			$info = explode(":", $info);
			if (count($info) > 1) {
				$index = trim($info[0]);
				$info = trim($info[1]);
			}
			$headers[$index] = $info;
		};
		# Grab the body HTML once for parsing later
		$body = file_get_contents($url);
		# Add URL info to output and PDF file
		$txt = "URL: " . $url;
		$output->writeln($txt);
		$pdflines = fopen("pdflines.txt", "a");
		fwrite($pdflines, $txt."\n");
		
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
	    $html = file_get_contents('http://www.alexa.com/siteinfo/'. urlencode($url));
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
		
		# Store settings should only be checked if it's an internal check, otherwise the title tags should be scanned
		$title = "HTML Head Title";
		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/default_title');
			$output->writeln('Get Value: '.$getvalue);
			if ('Magento Commerce' == $getvalue || '' == $getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
		} else {
			$matches = [];
			preg_match("/\<title\>(.*?)\<\/title\>/i", $body, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == '') {
					$value = 'ERROR - not set';
				} else {
					$value = $matches[1];
				}
			} else {
				$value = 'ERROR - not set';
			}
		}
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
        $title = "HTML Head Description";
		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/default_description');
			if ('Default Description' == $getvalue || '' == $getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
		} else {
			$matches = [];
			preg_match("/\<meta\sname\=\"description\"\scontent\=\"(.*?)\"/", $body, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == '') {
					$value = 'ERROR - not set';
				} else {
					$value = $matches[1];
				}
			} else {
				$value = 'ERROR - not set';
			}
		}        
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Default Keywords";
		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/default_keywords');
			if ('Magento, Varien, E-commerce' == $getvalue || '' == $getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
		} else {
			$matches = [];
			preg_match("/\<meta\sname\=\"keywords\"\scontent\=\"(.*?)\"/", $body, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == '') {
					$value = 'ERROR - not set';
				} else {
					$value = $matches[1];
				}
			} else {
				$value = 'ERROR - not set';
			}
		}
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "HTML Head Robots";
		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/default_robots');
			if ('INDEX,FOLLOW' == $getvalue || '' == $getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
		} else {
			$matches = [];
			preg_match("/\<meta\sname\=\"robots\"\scontent\=\"(.*?)\"/", $body, $matches);
			if (isset($matches[1])) {
				if ($matches[1] == '') {
					$value = 'ERROR - not set';
				} else {
					$value = $matches[1];
				}
			} else {
				$value = 'ERROR - not set';
			}
		}
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
		if (!$external) {
			$title = "HTML Head Demo Notice";
			$getvalue = \Mage::getStoreConfig('design/head/demonotice');
			if ($getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
			$output->writeln($title.': '.$value);
			$txt = $title.' of store: '.$value;
			fwrite($pdflines, $txt."\n");
		}
		
        $title = "Image ALT attributes";
		preg_match_all('~(http.*\.)(jpe?g|png|[tg]iff?|svg)~i', $body, $images);
		preg_match_all('/alt="([\s\S])/', $body, $imgalt);
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
		$pattern = '/([^"]*)(.jpe?g|.png|.svg)/i';
		$m = preg_match_all($pattern,$body,$matches);
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
		
		if (!$external) {
			$title = "Merge CSS Files";
			$rewrite = \Mage::getStoreConfig('dev/css/merge_css_files');
			if (!$rewrite) {
				$value = '0';
			} else {
				$value = '1';
			}
			$output->writeln($title.': '.$value);
			$txt = $title.' for store: '.$value;
			fwrite($pdflines, $txt."\n");
			
			
			$title = "Merge JavaScript Files ";
			$rewrite = \Mage::getStoreConfig('dev/js/merge_files');
			if (!$rewrite) {
				$value = '0';
			} else {
				$value = '1';
			}
			$output->writeln($title.': '.$value);
			$txt = $title.' for store: '.$value;
			fwrite($pdflines, $txt."\n");
		}
		
        $title = "Microdata";
		$microdata = '<script type="application/ld+json">';
        if (strpos($body, $microdata) !== false) {
			$value = 'OK';
        } else {
			$value = 'missing';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "CSS styles";
		$pattern = '~(//.*\.)(css)~i';
		$m = preg_match_all($pattern,$body,$matches);
		$value = sizeof($matches[0]);
		$output->writeln($title.': '.$value);
		$txt = $title.' of store: '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "JavaScript files";
		$pattern = '~(//.*\.)(js)~i';
		$m = preg_match_all($pattern,$body,$matches);
		$value = sizeof($matches[0]);
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
		
        $title = "Response";
        if (200==$retcode) {
			$value='online';
        } else if (401==$retcode){
			$value='Error 401 - Unauthorized: Access is denied';
		} else {
			$value='offline';
        }
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");

		$title = "Gzip compression";
		if (isset ($headers["Content-Encoding"])) {
			if ($headers["Content-Encoding"] == "gzip") {
				$value = "Ok";
			} else {
				$value = "ERROR - gzip compression not enabled";
			}
		} else {
			$value = "ERROR - Content-Encoding header not set";
		}
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
		if (!$external) {
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
		}
		
        $title = "Robots.txt";
		if (!$external) {
			if (!file_exists('robots.txt')) {
				$value='0';
			} else {
				$value='1';
			}
		} else {
			if ($header = substr(get_headers($url)[0], 9, 3)) {
				if ($header == "404") {
					$value = "ERROR - robots.txt not found";
				} else {
					$value = "OK";
				}
			}
		}        
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");
		
				
        $title = "Sitemap.xml";
		if (!$external) {
			if (!file_exists('sitemap.xml')) {
				$value='0';
			} else {
				$value='1';
			}
		} else {
			if ($header = substr(get_headers($url)[0], 9, 3)) {
				if ($header == "404") {
					$value = "ERROR - sitemap.xml not found";
				} else {
					$value = "OK";
				}
			}
		}              
		$output->writeln($title.': '.$value);
		$txt = $title.': '.$value;
		fwrite($pdflines, $txt."\n");

		
        $title = "WHOIS Created date";
		/* www.whoisxmlapi.com credentials */
		$username = 'magetest';
		$password = 'testB027';
	    $html = file_get_contents('http://www.whoisxmlapi.com/whoisserver/WhoisService?domainName='.urlencode($url).'&username='.$username.'&password='.$password);
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
