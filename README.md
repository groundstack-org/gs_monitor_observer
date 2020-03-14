# under heavy development
## current Informations:

# gs_monitor_observer
gs_monitor_observer is a TYPO3 extension.
A TYPO3 extension used to monitor updates for TYPO3 and all installed extensions.
In combination with: [https://github.com/groundstack-org/gs_monitor_provider](gs_monitor_provider "Monitor Provider")

### Installation
... like any other TYPO3 extension [extensions.typo3.org](https://extensions.typo3.org/ "TYPO3 Extension Repository")
- create entry with full(!) URL to your TYPO3 installation e. g. https://domain.tld and an API-key
- then you can see this entry in the list- overview tab - there you can show and copy the public-key for gs_monitor_provider
- after you have added all configurations for this extension and gs_monitor_provider, you can disable the hidden field.


### Features
- API-Key is send as HTTP-Header
- Usage of JWT
- Content / information is encrypted by private public key

![example picture from backend](.github/images/preview.jpg?raw=true "Title")

### Erros (especially Windows)
If the authorization header does not arrive / is not displayed in the request, the following line in Apache httpd.conf might help: - SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

If PHP cannot create a private public key (openssl_pkey_new($options)), then perhaps the path to openssl.cnf is incorrect or not set.
This can be solved by setting the opensslCnf setting in the extension Config / Options. For example:
- D:/MAMP/bin/apache/conf/openssl.cnf

Another solution or workaround (or if the above doesn't work), copy the openssl.cnf into the folder shown to you under php.info with the parameter "Openssl default config" (if there are no such folders, just create them).

##### Copyright notice

This repository is part of the TYPO3 project. The TYPO3 project is
free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

The GNU General Public License can be found at
http://www.gnu.org/copyleft/gpl.html.

This repository is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This copyright notice MUST APPEAR in all copies of the repository!

##### License
----
GNU GENERAL PUBLIC LICENSE Version 3
