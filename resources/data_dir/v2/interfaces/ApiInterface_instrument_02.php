<?php

interface ApiInterface_instrument_02{

    // When "API: Build definitions" is called, everything between /* */ tags will be merged recursively to create the api.json file.
    // Structure within this file is purely for readability: all the JSON comment could be in a single block if you prefer.
    // If you want to comment your interface files, use //
    // For the full Swagger specification, see http://swagger.io/specification/

    // This first, stand-alone, section defines tags and definitions common to this interface

    /*
        {
            "tags": [
                {
                    "name": "instrument",
                    "description": "Section instrs",
                    "externalDocs": {
                        "description": "Find out more",
                        "url": "https://github.com/GOVTNZ/silverstripe-api/blob/master/README.md"
                    }
                }
            ],
            "definitions": {
                "Instrument": {
                    "type": "object",
                    "properties": {
                            "id": {
                                "type": "integer",
                            "format": "int64"
                        },
                        "name": {
                            "type": "string"
                        },
                        "section": {
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

    public function findInstrumentBySection($worker){
        /*
            {
              "paths": {
                "/instrument/findBySection": {
                  "get": {
                    "summary": "Finds instruments by orchestra section",
                    "tags": [
                      "instrument"
                    ],
                    "description": "Multiple sections can be provided with comma seperated strings",
                    "operationId": "findInstrumentsBySection",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [
                      {
                        "name": "section",
                        "in": "query",
                        "description": "Section values that need to be considered for filter",
                        "required": true,
                        "type": "array",
                        "items": {
                          "type": "string",
                          "enum": [
                            "brass",
                            "strings",
                            "tympani",
                            "woodwind"
                          ],
                          "default": "strings"
                        },
                        "collectionFormat": "csv"
                      }
                    ],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "type": "array",
                          "items": {
                            "$ref": "#/definitions/Instruments"
                          }
                        }
                      },
                      "400": {
                        "description": "Invalid section value"
                      }
                    },
                    "security": [
                      {
                        "api_key": []
                      }
                    ]
                  }
                }
              }
            }
        */
    }

    public function getInstrumentById($worker){
        /*
            {
              "paths": {
                "/instrument/{instrumentId}": {
                  "get": {
                    "tags": [
                      "instrument"
                    ],
                    "summary": "Find instrument by ID",
                    "description": "Returns a single instrument",
                    "operationId": "getInstrumentById",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [
                      {
                        "name": "instrumentId",
                        "in": "path",
                        "description": "ID of instrument to return",
                        "required": true,
                        "type": "integer",
                        "format": "int64"
                      }
                    ],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "$ref": "#/definitions/Instrument"
                        }
                      },
                      "400": {
                        "description": "Invalid ID supplied"
                      },
                      "404": {
                        "description": "Instrument not found"
                      }
                    },
                    "security": [
                      {
                        "api_key": []
                      }
                    ]
                  }
                }
              }
            }
        */
    }
}