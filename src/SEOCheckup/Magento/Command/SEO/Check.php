<?php

namespace SEOCheckup\Magento\Command\SEO;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
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

		# Store info in array for parsing later
		$info = [];
		$info["URL"] = $url;

		$size = mb_strlen($body, 'UTF-8')/8000;
		$size = round($size, 2);
        if ($size != 0) {
			$info["HTML size"] = $size.' KB';
        } else {
			$info["HTML size"] = 'not available';
        }

		$html = file_get_contents('http://www.alexa.com/siteinfo/'. urlencode($url));
		$r = explode('/awis -->', $html);
		if (isset($r[1])){
			$r = explode('</strong>', $r[1]);
			$info["Alexa rank"] = trim(preg_replace('/\s\s+/', ' ', $r[0]));
		}
        if (strpos($value, '<span') !== false) {
			$info["Alexa rank"] = '0';
        }
		
		$ga = '//www.google-analytics.com/analytics.js'; //Check for Google Analytics script
        if (strpos($body, $ga) !== false) {
			$info["Google Analytics"] = 'Google Analytics script found';
        } else {
			$info["Google Analytics"] = 'Google Analytics script not found';
        }

		$frosmo = '//inpref.s3.amazonaws.com/frosmo.easy.js'; //Check for Frosmo script
        if (strpos($body, $frosmo) !== false) {
			$info["Frosmo analytics"] = 'Frosmo analytics script found';
        } else {
			$info["Frosmo analytics"] = 'Frosmo analytics script not found';
        }

		# Store settings should only be checked if it's an internal check, otherwise the title tags should be scanned

		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/default_title');
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
		$info["HTML Head Title"] = $value;

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
		$info["HTML Head Description"] = $value;

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
		$info["HTML Head Default Keywords"] = $value;

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
		$info["HTML Head Robots"] = $value;

		if (!$external) {
			$getvalue = \Mage::getStoreConfig('design/head/demonotice');
			if ($getvalue) {
				$value = 'ERROR - not changed';
			} else {
				$value = 'OK';
			}
			$info["HTML Head Demo Notice"] = $value;
		}

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
		$info["Image ALT attributes"] = $value;

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
				$value += (int) $img["Content-Length"];
			}
			catch (SomeException $e){}
		}
		$info["Image(s) size"] = round(($value/8388608),2).'MB';
		
		if (!$external) {
			$rewrite = \Mage::getStoreConfig('dev/css/merge_css_files');
			if (!$rewrite) {
				$value = '0';
			} else {
				$value = '1';
			}
			$info["Merge CSS Files"] = $value;

			$rewrite = \Mage::getStoreConfig('dev/js/merge_files');
			if (!$rewrite) {
				$value = '0';
			} else {
				$value = '1';
			}
			$info["Merge JavaScript Files "] = $value;
		}

		$microdata = '<script type="application/ld+json">';
        if (strpos($body, $microdata) !== false) {
			$value = 'OK';
        } else {
			$value = 'missing';
        }
		$info["Microdata"] = $value;

		$pattern = '~(//.*\.)(css)~i';
		preg_match_all($pattern,$body,$matches);
		$info["CSS styles"] = sizeof($matches[0]);

		$pattern = '~(//.*\.)(js)~i';
		preg_match_all($pattern,$body,$matches);
		$info["JavaScript files"] = sizeof($matches[0]);

        $info["Response"] = $retcode;

		if (isset ($headers["Content-Encoding"])) {
			if ($headers["Content-Encoding"] == "gzip") {
				$value = "Ok";
			} else {
				$value = "ERROR - gzip compression not enabled";
			}
		} else {
			$value = "ERROR - Content-Encoding header not set";
		}
		$info["Gzip compression"] = $value;

		if (!$external) {
			$rewrite = \Mage::app()->getConfig('web/seo/use_rewrite');
			if (!$rewrite) {
				$value = '0';
			} else {
				$value = '1';
			}
			$info["Using Web Server Rewrites"] = $value;
		}

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
		$info["Robots.txt"] = $value;

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
		$info["Sitemap.xml"] = $value;

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
        }
		$info["WHOIS Created date"] = $value;

		$r = explode('<expiresDate>', $html);
		if (isset($r[1])){
			$r = explode('</expiresDate>', $r[1]);
			$value = $r[0];
		}
        if (strlen($html)==131) {
			$value = 'please insert valid whoisxmlapi.com credentials to Whois.php';
        }
		$info["WHOIS Expire date"] = $value;

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
		$info["WHOIS Estimated domain age"] = $value;


		# Print out the data in a nice format:
		$table = new Table($output);
		$table->setHeaders([
			new TableCell(
				"Accolade's SEO Checkup Report",
				["colspan" => 2]
			)
		]);
		foreach ($info as $title => $value) {
			# Prevent the rows from being longer than 80 characters for nice viewing on all screen sizes
			$rowSpan = 2;
			if (strlen($value) > 42) {
				$value = chunk_split($value, 42, "\n");
				$rowSpan = substr_count($value, "\n") + 1;
			}
			$rows[] = [
				$title,
				new TableCell(
					(string) $value,
					["rowspan" => $rowSpan]
				)
			];
		}
		$table->setRows($rows);
		$table->render();
		if (true) {
			$pdflines = fopen("pdflines.txt", "a");
			foreach ($info as $title => $value) {
				fwrite($pdflines, $title . ": " . (string) $value . "\n");
			}
			fclose($pdflines);
			include('pdf.php'); //Make PDF
		}
    }
}
