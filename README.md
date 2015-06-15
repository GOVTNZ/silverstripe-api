# silverstripe-api
A facade pattern API implementation for Silverstripe using interfaces and Swagger

&nbsp;
## Introduction
This is an opinionated package that implements a Silverstripe API with the following features:
* Each API node is described in a PHP interface file.
* Optional integration with a Swagger UI instance.
* A stub file implements each interface for testing.
* The real world implementation of each interface can be assigned to any (one) class.

This package is being developed progressively by the Govt.nz team, and features are being added as they're required for our own project. 
This means that some features required by other users have not yet been implemented. OAuth and permissions checking, for example, will only be added when we need them ourselves.
While this is not ideal, we don't have the resources to add functionality until it's on our own development roadmap.
We'd be delighted if others forked and extended this package to provide some of the functionality we've not included.

&nbsp;
## Structure
This package installs into */vendor/govtnz/silverstripe-api*.

You need another directory to store your interface definitions, stub files for testing and the resulting .json API definition etc.
This is called the **API data directory**, and it can be anywhere you wish: our own implementation has this as a root directory, but that's not mandatory.
You may want to put your Swagger page class and template files here, along with any modified CSS files etc, but you don't have to:
if you prefer to store these with files of the same type within your website, that's is up to you.

The path to the API data directory needs to be set in a .yml config file. The path should be preceded by a forward slash.
```
API:
  data_dir: '[PATH]'
```

Within the API data directory the following structure must be adhered to.
```
data_dir
  |---v1
  |   |---interfaces 
  |   |---stubs
  |   |---tests  
  |   api.json
  |
  |---v2
      ... etc   
```


&nbsp;
## How *silverstripe-api* works
1. *APIWorker* invokes *APIRequestSerialiser* to parse incoming requests into 
  * a noun (eg *organisation*, *activity* etc), 
  * a verb (GET, PUT, POST, DELETE), 
  * a version (eg *v1*, *v2* etc),
  * a format (JSON and XML are initially supported) and 
  * parameters (there can be zero or more parameters).
1. *APIWorker* then invokes *APIAuthenticator* which applies OAuth and permissions checking (note that this is currently a stub - see the *Introduction* above).
1. *APIWorker* invokes the class which implements the interface corresponding to the *noun*, *verb* and *version*.
1. *APIWorker* invokes *APIResponseSerialiser* to return the resulting data in the requested format.   
 

&nbsp;
## Swagger
[Swagger](http://swagger.io/) is optional but highly recommended, as it provides interactive documentation for your API.
If you include the recommended comments in your interface files, *govtnz/silverstripe-api* will automatically generate the .json file Swagger UI requires.
There is a companion package, [govtnz/swagger-ui](https://github.com/govtnz/swagger-ui.git), which forks Swagger UI and makes it easy to include in a Silverstripe project.
See the documentation within this [companion Swagger package](https://github.com/govtnz/swagger-ui.git) for more details.

### Swagger installation testing
If you install Swagger alongside this package, you can test that the installation is correct and that these two packages 
(*govtnz/silverstripe-api* and *govtnz/swagger-ui*) will play nicely together with the following steps.

1. Install *govtnz/swagger-ui*, create a new Swagger page type and a new instance of this page type.
1. Create your **API data directory**.
1. Add the following entry to a .yml config file:
```
API:
     data_dir: '[PATH]'
```
1. Copy the contents of */vendor/govtnz/silverstripe-api/resources/data-dir* to this new directory.
1. Run *dev/build* if you haven't done so since installing *govtnz/silverstripe-api*.
1. Run the dev task *API: Build definitions*.
 
Now when you visit your new Swagger page you should be able to explore the contents of the test file using Swagger.

### How Swagger is provisioned 
* Each interface defined in *[data_dir]/interfaces* is an API node, and each interface definition includes comments that document the use of each function.
* When the dev task *API: Build definitions* is run, all interface files are parsed and an *api.json* file created in the root of the **API data directory**.
* The .yml config entry for *data_dir* tells the Swagger UI where to look for these files.


