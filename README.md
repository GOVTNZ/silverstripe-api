# silverstripe-api

A facade pattern API implementation for SilverStripe using interfaces and optionally Swagger.

[![Build Status](http://img.shields.io/travis/govtnz/silverstripe-api.svg?style=flat-square)](http://travis-ci.org/govtnz/silverstripe-api)
[![Version](http://img.shields.io/packagist/v/govtnz/silverstripe-api.svg?style=flat-square)](https://packagist.org/packages/govtnz/silverstripe-api)
[![License](http://img.shields.io/packagist/l/govtnz/silverstripe-api.svg?style=flat-square)](LICENSE.md)

## Introduction

This is an opinionated package that implements a SilverStripe API with the 
following features:

* Each API node is described in a PHP interface file.
* The API is abstracted from your SilverStripe data structure. 

You can structure your API in whatever way makes the most logical sense to its 
consumers: this is the facade pattern. 

* Optional integration with a Swagger UI instance.
* An optional stub file implementation of each interface for testing.
* The real world implementation of each interface can be assigned to any (one) class.

In many cases the class implementing an interface could be an existing Page 
Controller, but you're entirely free to have a separate class purely for 
implementing the API interface.

This package is being developed progressively by the Govt.nz team, and features 
are being added as they're required for our own project. 

**This means that some desirable features have not yet been implemented.** 

OAuth and permissions checking, for example, will only be added when we need 
them ourselves.

## Quick start

```
composer require govtnz/silverstripe-api
```

The */resources/data_dir* subdirectory within this module contains interface samples.

1. Copy the entire subdirectory to a suitable location (see **API data** below).
1. Run *dev/build*. 
1. Run *dev/tasks/ApiRebuildDefinitionsTask*
1. In a web browser, type *[WEBROOT]/api/v1/section/list* ... you should see a 
correctly formatted response.

Use Swagger-UI or manually browse the *assets/api/v1/swagger.json* file to 
learn the other available API requests.


## Configuration

```
GovtNZ\SilverStripe\Api\ApiManager:
    api:
        v1:
            definition: 'path/to/base.txt'
            interfaces:
                - ApiInterfaceOne
            stubs
                - ApiInterfaceStubOne
        v2:
            definition: 'path/to/base.txt'
            interfaces:
                - ApiInterfaceOne
            stubs
                - ApiInterfaceStubOne

```

## API definitions

*silverstripe-api* allows you to break your API definition into blocks 
distributed across multiple files.

It's not mandatory to split up your API definition: if you wish, you can write 
it as a single block. But splitting it up improves maintainability, and the 
dev task *API: Rebuild definitions* will still generate a single *swagger.json* 
file for each API version.

However you structure your API definition, it needs to be

1. standard JSON,
1. aligned with the Swagger 2.0 specification, and
1. contained in mulit-line comment blocks:
   ```
   /* 
   
     (json here) 
     
   */
   ``` 

Unlike a regular JSON file, you can include // comments in your API definitions
 (but don't use /\* \*/ comments). These // comments will be ignored by the dev 
 task that generates the *swagger.json* output. The dev task will assume that 
 each chunk of JSON is a top-level element within the Swagger definition: the 
 provided examples demonstrate this.

## Swagger JSON file

The dev task *API: Rebuild definitions* takes the JSON fragments from each 
interface and builds them into a single *swagger.json* file.

By default the resulting *swagger.json* file is saved in */assets/api*, but you 
can change that with a .yml config setting:

```
Swagger:
  data-dir: [PATH]
```

And if you're integrating *govtnz/swagger-ui* with this API module, this path 
must be externally accessible.

## Definitions File

Each API directory must have a file which defines properties that are common 
across all the interface nodes.

There are two useful variables available within this text file which can make 
your API definitions more portable between dev, test and production servers:

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
It's recommended that you copy and modify the existing *resources/base.txt* 
file to kick-start your own API development.

## Tests

Automated tests can be written to exercise each interface and its stub file.
These can be stored in the */tests* subdirectory.

Each file in the *stubs* directory implements an API interface using static 
data. It is invoked in one of two circumstances:

1. When a *test* parameter is added to an API request, for example `&test=true`.
1. When there is no other implementation of an API interface.

Stub files are not mandatory, but they're useful for testing as their responses 
never change.


### API Documentation

There are several useful functions in the `ApiController`:

* **caseCamel($field)** ensures *$field* is in camelCase.
* **caseRequest($field)** ensures *$field* is in the case specified in the request (default camelCase). 
Simple, one word field names are the same in camelCase and snake_case, but more complex field names can be passed through this function when generating your output.
* **caseSnake($field)** ensures *$field* is in snake_case.
* **date3339toDB($input)** converts an RFC3339 timestamp (2015-06-28T00:00:00+12:00) to MySQL format (2015-06-28 00:00:00).
* **dateDBto3339($input)** converts a MySQL timestamp (2015-06-28 00:00:00) to RFC3339 format (2015-06-28T00:00:00+12:00).
* **formatOutput()** returns an array containing two nodes: the query total, count and offset; the output data previously assigned to *output*.
* **logAdd($text)** populates the controller's internal log; sometimes useful for debugging.
* **logGet()** returns the controller's internal log as an array; sometimes useful for debugging.
* **param($name)** returns either the request parameter *$name*, or an empty string if this doesn't exist.
* **setError($params)** takes an array containing a *status* value and one or more error messages. 
It changes the controller's status and sets the error messages to be returned.
* **xmlAdd($key, $parent, $label)** registers an XML label for a given key|parent combination.
An asterisk * can be used to denote a numeric value.
Registering labels is only necessary for exceptions to the general rule, which is that XML label plurals will be created by adding an "s".
However, one important use case is one-dimensional arrays, which will have a numeric key. 
The default behaviour is to convert any numeric key to "item" to prevent the XML breaking, but it's nicer to have a context-specific label.
 
### Implementation

Your implementation code can:

* Rely on field names being in camelCase.
* Retrieve request parameters from the controller's *param()* function.
* Convert request and response dates using *date3339toDB()* and *dateDBto3339()*.
* Register labels for XML nodes with *xmlAdd()*.
* Call the controller's *setError()* function if you are unable to process the request.

Your implementation must:

* Call *caseRequest()* for any output field that is more than a simple one word name.
* Assign generated data to the controller's *output* property as an array.
* Set the controller's *pronoun* property if the type of the output data is not the same as the base type of the API node.

For example, the API method *organisation/sector* returns a list of organisation 
sectors, not a list of organisations. In this instance set *pronoun* to *sector* 
so the output is appropriately described.

## Swagger integration

There is a companion package, [govtnz/swagger-ui](https://github.com/govtnz/swagger-ui.git), 
which forks Swagger UI and makes it easy to include in a SilverSripe project.

See the documentation within this [companion Swagger package](https://github.com/govtnz/swagger-ui.git) for more details.
