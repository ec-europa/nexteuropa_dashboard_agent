# NextEuropa Dashboard Agent

## Description
Agent to be used by the NextEuropa Dashboard application.
Provides an overview of all used modules and a way to get a one time login link for UID 1.

## Usage
After installation check the admin page under `/admin/config/system/nexteuropa-dashboard`:
* You will find your siteUUID (format = [siteUUID]-[encryption-token];
* You can enable encryption;
* You can set a range of allowed IP's.

Go to:
* `/admin/reports/nexteuropa-dashboard/` to retrieve the list of used modules.
* `/admin/reports/user-login/super_admin` to get a one time user login link for UID 1.
* `/admin/reports/user-login/user_administrator` to get a one time user login link for user `user_administrator`.

For these requests, a parameter called _NETOKEN_ is expected in HTTP Request header for the authentication.

## Available drush commands

Drush command to update tokens:
```
drush ne-dashboard-agent-update-tokens
drush nedut
```

## Disclaimer
This module is a fork of the contrib module [System Status](https://www.drupal.org/project/system_status).
Some changes were done to fit EC requirements, and new features were added as well.
