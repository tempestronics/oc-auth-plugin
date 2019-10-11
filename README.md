# [Auth Plugin](https://github.com/SaifurRahmanMohsin/oc-auth-plugin) #
Auth Provider for OctoberCMS

## Introduction ##

This plugin provides authentication to the RESTful plugin nodes. This means that you can now secure the API nodes written using the Rest plugin using this plugin. You will need to install other plugins for specific auth mechanisms. For example, [OAuth2](https://github.com/SaifurRahmanMohsin/oc-oauth2-plugin).

## Configuration ##
You need to configure the API from the OctoberCMS backend in the Settings page (System > API Configuration).

## Limitation ##
* For now, this plugin is locked on to work with OctoberCMS's Backend users only. Will extend it to all kinds of models in the future.

## Coming Soon ##
* RainLab.User plugin support for better token management as well as support for using username than email for authentication.
* Auth configuration tab in Settings to manage the auth configuration.
* Mobile plugin support to allow instance-level token issue, singleton tokens, and other cool features.