SEO Checkup
=========
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

Installation
------------
From the n98-magerun/modules directory (see [Where can modules be placed?](https://github.com/netz98/n98-magerun/wiki/Modules#where-can-modules-be-placed)):

###With composer:

`composer require accoladefi-seo-checkup`

###With git:

`git clone https://github.com/AccoladeFi/SEO_Checkup`

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
