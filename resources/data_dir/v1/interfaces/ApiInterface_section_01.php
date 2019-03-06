<?php

interface ApiInterface_section_01
{

    // When "API: Build definitions" is called, everything between /* */ tags will be merged recursively to create the api.json file.
    // Structure within this file is purely for readability: all the JSON comment could be in a single block if you prefer.
    // If you want to comment your interface files, use //
    // For the full Swagger specification, see http://swagger.io/specification/

    // This first, stand-alone, section defines tags and definitions common to this interface

    /*
        {
            "tags": [
                {
                    "name": "section",
                    "description": "Orchestra section",
                    "externalDocs": {
                        "description": "Find out more about the sections of our orchestra",
                        "url": "https://github.com/GOVTNZ/silverstripe-api/blob/master/README.md"
                    }
                }
            ],
            "definitions": {
                "Section": {
                    "type": "object",
                    "properties": {
                        "id": {
                            "type": "integer",
                            "format": "int64"
                        },
                        "name": {
                            "type": "string"
                        },
                        "url": {
                            "type": "string"
                        }
                    }
                }
            }
        }
     */

    public function getSectionById($controller);
        /*
            {
              "paths": {
                "/section/{sectionId}": {
                  "get": {
                    "tags": [
                      "section"
                    ],
                    "summary": "Find section by ID",
                    "description": "Returns a single section of the orchestra",
                    "operationId": "getSectionById",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [
                      {
                        "name": "sectionID",
                        "in": "path",
                        "description": "ID of section to return",
                        "required": true,
                        "type": "integer",
                        "format": "int64"
                      }
                    ],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "$ref": "#/definitions/Section"
                        }
                      },
                      "400": {
                        "description": "Invalid ID supplied"
                      },
                      "404": {
                        "description": "Section not found"
                      }
                    }
                  }
                }
              }
            }
        */

    public function getSections($controller);
        /*
            {
              "paths": {
                "/section/list": {
                  "get": {
                    "tags": [
                      "section"
                    ],
                    "summary": "Returns a list of sections",
                    "description": "Returns a list of sections",
                    "operationId": "getSections",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "type": "array",
                          "items": {
                            "$ref": "#/definitions/Section"
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
         */
}
