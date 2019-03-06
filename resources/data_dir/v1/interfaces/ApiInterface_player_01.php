<?php

interface ApiInterface_player_01
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
                    "name": "player",
                    "description": "Orchestra personnel",
                    "externalDocs": {
                        "description": "Find out more",
                        "url": "https://github.com/GOVTNZ/silverstripe-api/blob/master/README.md"
                    }
                }
            ],
            "definitions": {
                "Player": {
                    "type": "object",
                    "properties": {
                        "id": {
                            "type": "integer",
                            "format": "int64"
                        },
                        "firstname": {
                            "type": "string"
                        },
                        "lastname": {
                            "type": "string"
                        },
                        "instrument": {
                            "type": "array",
                            "items": {
                                "type": "integer"
                            }
                        }
                    }
                }
            }
        }
    */

    public function findPlayersByInstrument($controller);
        /*
            {
              "paths": {
                "/player/findByInstrument": {
                  "get": {
                    "summary": "Finds player by instrument",
                    "tags": [
                      "player"
                    ],
                    "description": "Enter the instrument ID to find corresponding players",
                    "operationId": "findPlayersByInstrument",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [
                      {
                        "name": "instrumentID",
                        "in": "query",
                        "description": "The ID of the instrument to find players for",
                        "required": true,
                        "type": "integer",
                        "format": "int64"
                      }
                    ],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "type": "array",
                          "items": {
                            "$ref": "#/definitions/Player"
                          }
                        }
                      },
                      "400": {
                        "description": "Invalid instrumentID value"
                      },
                      "404": {
                        "decsription": "No players found for this instrument"
                      }
                    }
                  }
                }
              }
            }
        */

    public function getPlayerById($controller);
        /*
            {
              "paths": {
                "/player/{playerId}": {
                  "get": {
                    "tags": [
                      "player"
                    ],
                    "summary": "Find player by ID",
                    "description": "Returns a single player",
                    "operationId": "getPlayerById",
                    "produces": [
                      "application/xml",
                      "application/json"
                    ],
                    "parameters": [
                      {
                        "name": "playerId",
                        "in": "path",
                        "description": "ID of player to return",
                        "required": true,
                        "type": "integer",
                        "format": "int64"
                      }
                    ],
                    "responses": {
                      "200": {
                        "description": "successful operation",
                        "schema": {
                          "$ref": "#/definitions/Player"
                        }
                      },
                      "400": {
                        "description": "Invalid ID supplied"
                      },
                      "404": {
                        "description": "Player not found"
                      }
                    }
                  }
                }
              }
            }
        */
}
