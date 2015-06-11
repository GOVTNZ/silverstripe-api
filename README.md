# silverstripe-api
A facade pattern API implementation for Silverstripe using interfaces and Swagger

&nbsp;
## Introduction
This is an opinionated package that implements a Silverstripe API with the following features:
* Each API node is described in a PHP interface file.
* Each interface function definition includes formatted comments which are used to populate a Swagger .json file when a *dev task* is run.
* A stub file implements each interface for testing.
* The real world implementation of each interface can be assigned to any (one) class.

&nbsp;
## Documentation
This file provides an

&nbsp;
## Installation directory
This package installs by default into */vendor/govtnz/silverstripe-api*.

&nbsp;
## Swagger
[Swagger](http://swagger.io/) is optional but recommended. 
It provides interactive documentation for your API, and can be easily integrated with any Silverstripe page template.
Govt.nz maintains [a fork of the Swagger UI](https://github.com/govtnz/swagger-ui.git) whose only change is inclusion of a *composer.json* file.
This makes it straightforward for us to maintain and for you to include. 
But you can pull in the original package by any means you wish - even just by copying the contents of the */dist* directory.

### Installation and visibility
Once you have it installed, the location of swagger-ui's */dist* directory has to be set in a yml config file.
```
API:
  swagger_dist_dir: '[PATH]'
```
There are files under this directory that need to be visible from the web:
1. /dist/swagger-ui.js
1. /dist/lib/\*
1. /dist/images/\*
1. /dist/css/\* if you want to use the original Swagger stylesheets
1. /dist/fonts/\* if you want to use the Swagger fonts

If swagger-ui is in the default location you'll need to modify your site's *.htacess* file accordingly:
```
RedirectMatch 404 /vendor(?!/govtnz/swagger-ui/dist/lib|/govtnz/swagger-ui/dist/images|/govtnz/swagger-ui/dist/swagger-ui\.js)
```
Regardless of where your swagger-ui package is, it's desirable to prevent access to those assets which are not required; the above *.htaccess* rule can be adapted to suit your environment.

### Customisation
If you want to modify the appearance of the Swagger UI, you could copy the CSS files to another directory within your site and customise them.
Alternatively the *silverstripe-api* package includes a SASS file which was extracted from the original CSS using [css2scss](http://sebastianpontow.de/css2compass/);
it's in the */examples* folder. 
If you use Compass, this could be a useful shortcut.

### Integration
Displaying the Swagger UI requires a page type whose controller includes the Swagger UI javascript files, and a corresponding page template with the appropriate HTML.
The code you need is in the */examples* folder.

&nbsp;
## Data directory
You need a folder to store your interface definitions, stub files for testing, .json API definition etc.
You may want to make this a root directory module and put your Swagger page class and template files here, along with any modified CSS files etc.
You may prefer to store these with files of the same type within your website - how you structure this is up to you.

However you choose to structure your data and associated files, the paths to these resources need to be set in a .yml config file.
```
API:
  data_dir: '[PATH]'
  swagger_dist_dir: '[PATH]'
```

