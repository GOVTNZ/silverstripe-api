<?php

namespace GovtNZ\SilverStripe\Api;

use SilverStripe\Core\Config\Config;

/**
 * The ApiRequestSerialiser breaks the incoming request into components which
 * are used to construct the output.
 */

class ApiRequestSerialiser
{

    private
        $formats = null,
        $responses = null,
        $vars = null;


    /**
     * Entry function.
     * @param $controller
     */
    public static function execute($controller)
    {
        $serialiser = new ApiRequestSerialiser;

        $serialiser->parseRequestCase($controller);

        if (!$serialiser->parseRequestParams($controller)) {
            return;
        }

        if (!$serialiser->parseRequestVars($controller)) {
            return;
        }

        $serialiser->parseRequestFields($controller);
        $serialiser->parseRequestFormat($controller);
        $serialiser->parseRequestTest($controller);

        // Filters, sorting and pagination only apply if we're returning more than one record
        if (isset($serialiser->responses->{"200"}->schema->type) && $serialiser->responses->{"200"}->schema->type === 'array') {
            $serialiser->parseRequestSort($controller);
            $serialiser->parseRequestLimit($controller);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * @param $format
     * @return boolean
     * Checks whether the requested format is supported by the method that will respond to this request
     * Requires that isActionValid() be run first, as this saves the Swagger format section
     */
    private function formatSupported($format)
    {
        if (isset($this->formats)) {
            if (is_array($this->formats)) {
                foreach ($this->formats as $support) {
                    if (stripos($support, $format) !== false) {
                        return true;
                    }
                }
            } else {
                return (stripos($this->formats, $format) !== false);
            }
        }
        return false;
    }



    /**
     * @return array|scalar
     * Returns either the instance's configured pagination limit or, if that isn't set, the package's pagination limit
     */
    private function getPaginationLimit()
    {
        $limit = Config::inst()->get('API', 'pagination');
        if (!isset($limit)) {
            $limit = Config::inst()->get('API', 'default_pagination');
        }
        return $limit;
    }



    /**
     * @param $controller
     * @param $noun
     * @param $action - can be a name (eg "getList") or an ID (eg 1711)
     * @return boolean
     * Checks that the combination of /noun/action exists as a path
     * If it does, the matching operationId and any parameters are saved
     */
    private function isActionValid($controller, $noun, $action)
    {
        // Variable parameters are wrapped in curly brackets in the Swagger definition, so we can match against the opening bracket
        $param = (is_numeric($action)) ? '{' : strtolower($action);

        // Iterate through the paths in the Swagger file
        foreach ($controller->swagger->paths as $key => $value) {
            // If we find a key that matches the path ...
            if (strpos(strtolower($key), strtolower("/$noun/$param")) !== false) {
                // ... and the value contains a property matching the verb (get/put/post/delete) ...
                if (property_exists($value, $controller->verb)) {
                    // ... and the value contains an operationId ...
                    if (property_exists($value->{$controller->verb}, 'operationId')) {
                        $controller->method = $value->{$controller->verb}->operationId;
                        // (if we get here retrieve the parameters and responses for later parsing)
                        if (property_exists($value->{$controller->verb}, "parameters")) {
                            $this->vars = $value->{$controller->verb}->parameters;
                        }
                        if (property_exists($value->{$controller->verb}, "responses")) {
                            $this->responses = $value->{$controller->verb}->responses;
                        }
                        if (property_exists($value->{$controller->verb}, "produces")) {
                            $this->formats = $value->{$controller->verb}->produces;
                        }
                        // ... we have a match!
                        return true;
                    }
                }
            }
        }

        // If there's no match return an error message
        $controller->setError(array(
            "status" => 400,
            "dev" => "The combination '$noun/$action' is not supported by this API",
            "user" => "This request cannot be fulfilled"
        ));
        return false;
    }



    /**
     * @param $controller
     * @param $noun
     * @return boolean
     * Checks whether the noun exists as the first element of a path
     */
    private function isNounValid($controller, $noun)
    {
        // If the Swagger definition isn't correctly formed, return an error
        if (!property_exists($controller->swagger, 'paths')) {
            $controller->setError(array(
                "status" => 500,
                "dev" => "The Swagger API definition does not define any paths",
                "user" => "There is a server error which prevents this request from being processed"
            ));
            return false;
        }

        // Iterate through the paths looking for a match
        foreach ($controller->swagger->paths as $key => $value) {
            if (strpos($key, "/$noun/") === 0) {
                return true;
            }
        }

        // If there's no match, return an error message
        $controller->setError(array(
            "status" => 400,
            "dev" => "The element \"$noun\" is not supported by this API",
            "user" => "This request contains the invalid element \"$noun\""
        ));
        return false;
    }


    /**
     * Checks that the type of a submitted parameter value is correct
     * @param $param
     * @param $value
     * @return boolean
     */
    private function paramTypeMatch($param, $value)
    {
        switch ($param->type) {
            case 'array':
                // This could be split with a switch statement on items->type
                $out = true;
                if ($param->items->type === 'integer' && $param->collectionFormat === 'csv') {
                    $values = explode(',', $value);
                    $pos = 0;
                    while ($out && $pos < count($values)) {
                        $out = is_numeric($values[$pos]);
                        $pos++;
                    }
                }
                return $out;
            case 'integer':
                return is_numeric($value);
            case 'dateTime':
            case 'string':
                // This could be split with a switch statement on format
                $out = true;
                if ($param->type !== 'string' || (isset($param->format) && $param->format === 'dateTime')) {
                    // 2015-08-01T00:00:00+12:00 || 2015-08-01T00:00:00Z
                    $core = substr($value, 0, 19);
                    $out = strlen($core) === 19 && preg_match('@^\d{4}(-)((0[1-9])|(1[0-2]))(-)((0[1-9])|([1-2][0-9])|(3[0-1]))(T)(([0-1][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])$@', $core);
                    if ($out) {
                        $zone = substr($value, 19);
                        // If the dateTime is not encoded, a + at the start of the timezone becomes a space - we should allow this for convenience
                        $out = ($zone === 'Z') || preg_match('@^(\+|-|\s)(([0-1][0-9])|(2[0-3])):([0-5][0-9])$@', $zone);
                    }
                }
                return $out;
            // Additional checks can be added here
            default:
                return true;
        }
    }



    /**
     * @param $controller
     * Checks for a case var in the request and updates the controller's case property
     */
    private function parseRequestCase($controller)
    {
        $case = $controller->request->requestVar('case');
        if (isset($case)) {
            $controller->case = strtolower($case);
        }
    }



    /**
     * @param $controller
     * Checks for a fields var in the request and populates the controller's fields array if it exists
     */
    private function parseRequestFields($controller)
    {
        $fields = $controller->request->requestVar('fields');
        if (isset($fields)) {
            $controller->fields = array();
            $fieldlist = explode(",", $fields);
            foreach ($fieldlist as $field) {
                $controller->fields[] = $controller->caseCamel($field);
            }
        }
    }



    /**
     * @param $controller
     * Checks for a supported extension (.json, .xml etc) in the request or header and sets the controller's format property
     */
    private function parseRequestFormat($controller)
    {
        $format = strtolower($controller->request->getExtension());
        if (isset($format) && $this->formatSupported($format)) {
            $controller->format = $format;
        } else {
            $formatstr = strtolower($controller->request->getHeader('Accept'));
            if (isset($formatstr)) {
                $formatarr = explode('/', $formatstr);
                if (count($formatarr) > 1 && $this->formatSupported($formatarr[1])) {
                    $controller->format = $formatarr[1];
                }
            }
        }
    }



    /**
     * @param $controller
     * @return boolean
     * Checks for limit vars in the request and populates the controller's limit array using default values if necessary
     */
    private function parseRequestLimit($controller)
    {
        $limit = $controller->request->requestVar('limit');

        // If the user wants all records, we don't set limit values ...
        if (isset($limit) && strtolower($limit) === 'all') {
            return;
        }

        // ... otherwise initialise the limit property and add count and offset
        $controller->limit = array();
        $controller->limit["count"] = (isset($limit)) ? $limit : $this->getPaginationLimit();

        $offset = $controller->request->requestVar('offset');
        $controller->limit["offset"] = (isset($offset)) ? $offset : 0;
    }



    /**
     * @param $controller
     * Parses the URL elements, populating the controller's verb, version, noun and action properties
     */
    private function parseRequestParams($controller)
    {
        $controller->verb = $this->parseRequestVerb($controller);
        if ($controller->status !== 200) {
            return false;
        }

        $version = $controller->request->param("Version");
        $controller->version = (isset($version) && strtolower($version[0]) === 'v') ? substr($version, 1) : 0;
        if ($controller->status !== 200) {
            return false;
        }

        $controller->loadSwagger($version);
        if ($controller->status !== 200) {
            return false;
        }

        $noun = strtolower($controller->request->param("Noun"));
        $controller->noun = (isset($noun) && $this->isNounValid($controller, $noun)) ? $noun : '';
        if ($controller->status !== 200) {
            return false;
        }

        $action = $controller->caseCamel(strtolower($controller->request->param("Action")));
        if (isset($action)) {
            $controller->action = (isset($action) && $this->isActionValid($controller, $noun, $action)) ? $action : '';
        }

        return ($controller->status === 200);
    }



    /**
     * @param $controller
     * Checks for a sort var in the request and populates the controller's sort array if it exists
     */
    private function parseRequestSort($controller)
    {
        // Sort fields
        $sortby = $controller->request->requestVar('sort');
        if (isset($sortby)) {
            // If we have one or more defined sort fields, initialise the controller array to hold the value(s)
            $controller->sort = array();
            $sortfields = explode(",", $sortby);

            foreach ($sortfields as $field) {
                $order = ($field[0] === '-') ? 'DESC' : 'ASC';
                $sort = ($field[0] === '-' || $field[0] === '+') ? substr($field, 1) : $field;
                $controller->sort[$sort] = $order;
            }
        }
    }



    /**
     * @param $controller
     * Checks for a test var in the request (test=[anything]) and sets the controller's test value if it exists
     */
    private function parseRequestTest($controller)
    {
        $value = $controller->request->requestVar('test');
        if (isset($value)) {
            $controller->test = true;
        }
    }



    /**
     * @param $controller
     * @return boolean
     * Iterates through the required vars for the requested path, populating the controller's params array
     */
    private function parseRequestVars($controller)
    {
        foreach ($this->vars as $param) {
            // Ensure mandatory properties are present
            $missing = '';
            if (!property_exists($param, 'name')) {
                $missing .= 'name, ';
            }
            if (!property_exists($param, 'in')) {
                $missing .= 'in, ';
            }
            if ($missing !== '') {
                $controller->setError(array(
                    "status" => 500,
                    "dev" => "At least one of the API parameters defined for this method is missing mandatory properties ".substr($missing, -2).".",
                    "user" => "There is a server error which prevents this request from being fulfilled."
                ));
                return false;
            }

            // Only one parameter can be passed in the path, and this has already been harvested
            if ($param->in === 'path') {
                $controller->params[$param->name] = $controller->action;
            }
            // Any other parameters must be query vars
            else {
                $value = $controller->request->requestVar($controller->caseRequest($param->name));
                if (!isset($value) && $param->required === true) {
                    $controller->setError(array(
                        "status" => 400,
                        "dev" => "The required var $param->name was not found in the URL.",
                        "user" => "The request is incomplete."
                    ));
                    return false;
                } elseif (isset($value)) {
                    if ($this->paramTypeMatch($param, $value)) {
                        $controller->params[$param->name] = addslashes($value);
                    } else {
                        $type = $param->type;
                        $type .= ($type === 'array') ? " of ".$param->items->type : '';
                        $type .= ($type === 'dateTime') ? " (RFC3339)" : '';
                        $type .= ($type === 'string' && $type->format === 'dateTime') ? " (RFC3339 dateTime)" : '';
                        $controller->setError(array(
                            "status" => 400,
                            "dev" => "The parameter '$param->name' is defined as $type. '$value' is the wrong type or an invalid value.",
                            "user" => "Your request is badly formed."
                        ));
                        return false;
                    }
                }
            }
        }
        return true;
    }



    /**
     * @param $controller
     * @return string
     * Returns a lower-case string containing the type of request
     */
    private function parseRequestVerb($controller)
    {
        switch (true) {
            case $controller->request->isDELETE():
                return 'delete';
            case $controller->request->isGET():
                return 'get';
            case $controller->request->isPOST():
                return 'post';
            case $controller->request->isPUT():
                return 'put';
            default:
                $controller->setError(array(
                    "status" => 400,
                    "dev" => "The protocol for this request does not appear to be GET, PUT, POST or DELETE.",
                    "user" => "This request is not properly formed and cannot be fulfilled."
                ));
                return '';
        }
    }
}
