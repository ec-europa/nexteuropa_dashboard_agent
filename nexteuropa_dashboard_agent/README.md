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
* /admin/reports/nexteuropa-dashboard/[siteUUID] to retrieve the list of used modules
* and /admin/reports/user-login/[siteUUID] to get a one time user login link for user id 1.
