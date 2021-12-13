# Cisco WLC External Captive Portal

The captive portal web server can be setup using the instructions given [here](https://gist.github.com/nasirhafeez/4e1c2c5536d313db96e2b4ce4b3b269e). The following actions are required to use the code given in this repo:
 
Copy the `.env.example` file to the upper level folder and change its name to `.env`. Set the values of the given project-wide environment variables in it.

*Install Composer*

Then run `php composer.phar install` to install the packages given in `composer.json`.

This code is based on the following repository (refer to its README for further details):

https://github.com/stuartst/cisco-wlc-captive-portal
