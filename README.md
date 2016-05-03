SEO Checkup
=========
## Release - Version 0.1

This is an extension for the [Netz98 Magerun CLI Tool](https://github.com/netz98/n98-magerun). It allows you to check the search engine optimization for any site. The current stable build performs the following checks:

* Meta tags
* Sitemap.xml
* Check Robots.txt 
* Various ranking stats such as Google page rank, Alexa rank, etc..
* Domain age with WHOIS info
* Web Server Rewrites
* JavaScript merged
* CSS merged
* Google Analytics found
* CSS minification
* Javascript minification
* ALT attributes
* Page size XXXXX KB
* Image(s) size XXXXX KB
* Gzip enabled

Installation
------------
From the n98-magerun/modules directory (see [Where can modules be placed?](https://github.com/netz98/n98-magerun/wiki/Modules#where-can-modules-be-placed)):

###With composer:

`composer require accoladefi-seo-checkup`

###With git:

`git clone https://github.com/Accolades/SEO_Checkup.git`

Commands
------------
`magerun seo:check`

Performs an internal check on an existing Magento installation, allowing for a more complete scan due to database/file system access.

 `magerun seo:check <URL>`

Performs an external check on any given URL. This method doesn't provide as much information due to limited access.

Contributing
--------------
All contributions are welcome! In order to streamline development, please fork the dev branch and make a pull request when you are ready to submit your code. When making contributions, we ask that you please consider the following:

* Commits should be frequent and small
* Commits must have detailed messages about what is being changed
* Pull requests should address only one issue or new feature

Roadmap
----------
`seo:check`

* Storeviews with hreflang
* H1 Check - Not missing / Only one H1 on page (whats the value in there)
* How many internal / external links on page
* URLs work with HTTP or HTTPS, but not both.
* Site uses https / http2
* Meta Tags
  - Title
    * Length: 31 characters
    * Max length: 60 characters
  - Keywords
    * Count: 4 keywords
    * Length: 39 characters
  - Description
    * Length: 160 characters
* Magento caches enabled
* Varnish enabled
* PageSpeed enabled
* Plagiarism / Duplicate Content check
* Options:
  - Generate report as PDF file
  - Website screen preview
* [Domain IP blacklist checker](https://developers.google.com/speed/docs/insights/v2/reference/pagespeedapi/runpagespeed#parameters )

 
###Advertising Feed
`ad:feed`

* Generate Google Feed

###SEO Crawl 

`seo:crawl`

* crawl pages from sitemap.xml 
* give overall report, summary on screen and report in PDF
* Options 
  - crawl X amount of pages
  - crawl certain types of pages (product, category),
  - have regex filtering of what named pages...
* Microdata
* Analytics Scripts

###SEO Monitor
`seo:monitor`

* can be put to cron to alert about change like robotx.txt changes and alerts
* Offline / Online checker
* Changes in robots.txt
* HTTP Responses, 404, 503, 301 etc.
* Domain canonical setup – www vs non-www
* Sub-domain auto discovery
* Malware status alerts
* Domain expiry
* disallow
* noindex
* sitespeed

###Social stats checker
`some:check`

###Security Monitor
`security:check`

`security:monitor`

* admin URL changed
* downloader URL protected
* file permissions correct

###Performance Check / Monitor
`perf:check`

`perf:monitor`

* jMeter
* [Google PageSpeed] (https://developers.google.com/speed/docs/insights/v2/reference/pagespeedapi/runpagespeed#parameters)
* [WebPageTest] (https://sites.google.com/a/webpagetest.org/docs/advanced-features/webpagetest-restful-apis)
