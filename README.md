# silverstripe-api
A facade pattern API implementation for Silverstripe using interfaces and optionally Swagger

&nbsp;

&nbsp;
## Introduction
This is an opinionated package that implements a Silverstripe API with the following features:
* Each API node is described in a PHP interface file.
* The API is abstracted from your Silverstripe data structure. 
You can structure your API in whatever way makes the most logical sense to its consumers: this is the facade pattern. 
* Optional integration with a Swagger UI instance.
* An optional stub file implementation of each interface for testing.
* The real world implementation of each interface can be assigned to any (one) class.
In many cases the class implementing an interface could be an existing Page Controller, but you're entirely free to have a separate class purely for implementing the API interface.

This package is being developed progressively by the Govt.nz team, and features are being added as they're required for our own project. 
**This means that some desirable features have not yet been implemented.** 
OAuth and permissions checking, for example, will only be added when we need them ourselves.
We don't have the resources to add features until they're on our own development roadmap, so we'd be delighted if others extended this package to provide some of the functionality we've not yet included.

### Quick start
The */resources/data_dir* subdirectory within this module contains interface samples.

1. Copy the entire subdirectory to a suitable location (see **API data** below).
Ensure you don't copy the *_manifest_exclude* file, which is outside this subdirectory.
1. Run *dev/build*. 
1. Open *dev/tasks* and run *API: Rebuild definitions*.
1. In a web browser, type *[WEBROOT]/api/v1/section/list* ... you should see a correctly formatted response.
Use Swagger-UI or manually browse the *assets/api/v1/swagger.json* file to learn the other available API requests.

&nbsp;

&nbsp;
## File structure

This module installs into */silverstripe-api*.

### API data
You need another directory to store your interface definitions and stub files for testing.
This is called the **API data directory**, and it can be anywhere you wish: our own implementation has this as a root directory, but that's not mandatory.

The path to the API data directory needs to be set in a .yml config file. No default is provided.
```
API:
  data_dir: '[PATH]'
```
The path must be preceded by a forward slash. For example:
```
API:
  data_dir: '/api/data'
```

Within the *API data directory* the following structure must be adhered to.
```
data_dir
  |---v1
  |   |---interfaces 
  |   |---stubs
  |   |---tests  
  |
  |---v2
      ... etc   
```

### API definitions
*silverstripe-api* allows you to break your API definition into blocks distributed across multiple files.
It's not mandatory to split up your API definition: if you wish, you can write it as a single block.
But splitting it up improves maintainability, and the dev task *API: Rebuild definitions* will still generate a single *swagger.json* file for each API version.

All the interface definition files are kept in an */interfaces* subdirectory. 

However you structure your API definition, it needs to be

1. standard JSON,
1. aligned with the Swagger 2.0 specification, and
1. contained in mulit-line comment blocks:
   ```
   /* 
     (json here) 
   */
   ``` 

Unlike a regular JSON file, you can include // comments in your API definitions (but don't use /\* \*/ comments).
These // comments will be ignored by the dev task that generates the *swagger.json* output.
The dev task will assume that each chunk of JSON is a top-level element within the Swagger definition: the provided examples demonstrate this.

### Swagger JSON file
The dev task *API: Rebuild definitions* takes the JSON fragments from each */interfaces* directory and builds them into a single *swagger.json* file.
By default the resulting *swagger.json* file is saved in */assets/api*, but you can change that with a .yml config setting:
```
Swagger:
  data-dir: [PATH]
```
The path must be preceded by a forward slash.
And if you're integrating *govtnz/swagger-ui* with this API module, this path must be externally accessible.

### base.txt
Each */interface* directory must have a file called *base.txt*.
This file defines properties that are common across all the interface nodes.
There are two useful variables available within *base.txt* which can make your API definitions more portable between dev, test and production servers:

You can use *getHost* to automatically populate the "host" key:
```
"host": "<% getHost %>",
```

You can use *getProtocol* to automatically populate the "schemes" array:
```
"schemes": [
    "<% getProtocol %>"
],
```
It's recommended that you copy and modify the existing *base.txt* file to kick-start your own API development.

### ApiInterface PHP files
File names for the interface definition files include the API version and the name of the API node.
For example, version two of the interface definition for *cake* would be in a file named *ApiInterface_cake_02.php* and headed:
```
<?php
interface ApiInterface_cake_02 {
    // function definitions here
}
```
The examples put common definitions at the top of each file, and function-specific definitions after each function declaration.
While this can make the API definition easier to maintain, it's not mandatory.

What is mandatory is the structure of each PHP function definition.

1. The function name is defined in the corresponding *"path"* section of the JSON fragment as the *operationID*.
2. Each function takes only one parameter: the *Api_Controller* instance handling that API request.
The controller exposes all the parameters the function will need to fulfill the API request, and in turn the function will populate the controller's *output* property with the response.

&nbsp;

&nbsp;
##Other files
### Stub files
Each file in the *stubs* directory implements an API interface using static data. It is invoked in one of two circumstances:

1. When a *test* parameter is added to an API request, for example `&test=true`.
1. When there is no other implementation of an API interface.

A stub file's name must start *ApiStub_*.

Stub files are not mandatory, but they're useful for testing as their responses never change.

### Tests
Automated tests can be written to exercise each interface and its stub file.
These can be stored in the */tests* subdirectory.

&nbsp;

&nbsp;
## How *silverstripe-api* works
For each incoming request, an instance of *Api_Controller* is created, and this controller then manages the following steps:

1. *ApiRequestSerialiser* is invoked to parse the request.
1. The controller loads the *swagger.json* file to determine which interface and function should handle the request.
1. *ApiAuthenticator* applies OAuth and permissions checking (note that this is currently a stub - see the *Introduction* above).
1. The controller invokes the implementation class for the required interface and calls the indicated function.
1. The implementation class retrieves the requested data.
1. One of the *ApiResponseSerialiser* classes is invoked to return the data in the requested format.   
 
&nbsp;

&nbsp;
## Swagger integration
There is a companion package, [govtnz/swagger-ui](https://github.com/govtnz/swagger-ui.git), which forks Swagger UI and makes it easy to include in a Silverstripe project.
See the documentation within this [companion Swagger package](https://github.com/govtnz/swagger-ui.git) for more details.

&nbsp;

&nbsp;

&nbsp;

&nbsp;