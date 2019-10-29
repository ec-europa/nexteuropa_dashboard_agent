# NextEuropa Dashboard Agent
## Description
Agent used from the NextEuropa Dashboard. 
Provides an overview of all the used modules and a way to get a one time login link for user id 1.

## Usage
After installation check the admin page under /admin/config/system/nexteuropa-dashboard.
* You will find your siteUUID (format = [siteUUID]-[encryption-token].
* You can enable encryption.
* You can set a range of allowed IP's.

Go to:
* /admin/reports/nexteuropa-dashboard/ to retrieve the list of used modules.
* and /admin/reports/user-login/ to get a one time user login link for user id 1.
For both request, a parameter called NETOKEN must be added in HTTP Request header with as value the [siteUUID].
~~~~
~~~~
#### Disclaimer
This module is a fork of contrib module System Status. See https://www.drupal.org/project/system_status
Some changes were done to fit EC requirements and new features were added.