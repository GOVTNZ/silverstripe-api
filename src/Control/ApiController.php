<?php

namespace GovtNZ\SilverStripe\Api\Control;

use PageController;

class ApiController extends PageController
{

    private static $allowed_actions = array(
        "index"
    );

    private static $url_handlers = array(
        '$Version/$Noun//$Action' => 'index'
    );

    private $log = null;

    public
        $action = '',
        $case = 'camel',
        $error = null,
        $fields = null,
        //$filter = '',
        $format = 'json',
        $implementer = null,
        $limit = null,
        $method = '',
        $noun = '',
        $output = null,
        $params = null,
        $pronoun = '',
        $sort = null,
        $status = 200,
        $swagger = null,
        $test = false,
        $total = 0,
        $verb = '',
        $version = 0,
        $xml = null;



    public function index()
    {
        // Prepare
        ApiRequestSerialiser::execute($this);
        ApiAuthenticator::execute($this);

        // Generate
        if ($this->status === 200) {
            $output = array();
            $implementerclass = $this->getImplementerClass();
            if (!is_null($implementerclass)) {
                $this->implementer = new $implementerclass();
                $method = $this->method;
                try {
                    $this->implementer->{$method}($this);
                } catch (Exception $except) {
                    if ($this->status === 200) {
                        $this->setError(array(
                            "status" => 500,
                            "dev" => "Error processing request: please check your syntax against the request definition",
                            "user" => "This request could not be processed"
                        ));
                    }
                }
            } elseif (Director::isDev()) {
                $this->testOutput();
            }
        } else {
            $this->populateErrorResponse();
        }

        // Deliver
        $this->setStandardHeaders();
        $ApiResponse = $this->getResponseSerialiser();

        // Hook to allow analytics tracking, external logging, etc
        $this->extend('updateController', $this);

        return $ApiResponse->execute($this);
    }



    /**
     * Utility function that converts a fieldname to camelCase
     * @param $field
     * @return string
     */
    public function caseCamel($field)
    {
        $out = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $field))));
        return $out;
    }



    /**
     * Utility function that converts a fieldname to whatever case is specified in the request
     * @param $field
     * @return string
     */
    public function caseRequest($field)
    {
        switch ($this->case) {
            case 'snake':
                return $this->caseSnake($field);
            default:
                return $this->caseCamel($field);
        }
    }



    /**
     * Utility function that converts a fieldname to snake_case
     * @param $field
     * @return string
     */
    public function caseSnake($field)
    {
        $out = preg_replace_callback(
            '/[A-Z]/',
            create_function('$match', 'return "_" . strtolower($match[0]);'),
            $field
        );
        return $out;
    }



    /**
     * A utility function that converts an RFC3339 timestamp (2015-06-28T00:00:00+12:00) to MySQL format (2015-06-28 00:00:00)
     * @param $input
     */
    public function date3339toDB($input)
    {
        // HTTPDecode will not restore the + before the timezone offset; this causes strtotime to break, so we must replace it
        $date = strtotime(str_replace(' ', '+', $input));
        return date('Y-m-d H:i:s', $date);
    }



    /**
     * A utility function that converts a MySQL timestamp (2015-06-28 00:00:00) to RFC3339 format (2015-06-28T00:00:00+12:00)
     * @param $input
     * @return string
     */
    public function dateDBto3339($input)
    {
        $offset = date('Z');
        $zone = sprintf("%s%02d:%02d", ($offset >= 0) ? '+' : '-', abs($offset / 3600), abs($offset % 3600));
        return str_replace(' ', 'T', $input).$zone;
    }



    /**
     * Returns an array with two nodes, one containing the query offset, count and total, the other containing the request data response.
     * @return array
     */
    public function formatOutput()
    {
        if ($this->status !== 200) {
            $out = array(
                "status" => $this->status,
                "messages" => $this->error
            );
            return $out;
        }

        $pronoun = ($this->pronoun === '') ? $this->noun."s" : $this->pronoun."s";
        $out = array(
            "query" => array(
                "offset" => intval((is_null($this->limit)) ? 0 : $this->limit['offset']),
                "count" => intval((is_null($this->limit)) ? $this->total : min($this->limit['count'], $this->total)),
                "total" => intval($this->total)
            ),
            $pronoun => $this->output
        );
        return $out;
    }



    /**
     * Loads the swagger.json file matching the requested API version.
     * @param $version
     */
    public function loadSwagger($version)
    {
        // Find the location of the swagger.json file with the right version
        $dir = Config::inst()->get('Swagger', 'data_dir');
        $dir = Director::baseFolder().((isset($dir)) ? $dir : "/assets/api");
        $swagger = "$dir/$version/swagger.json";

        if (!file_exists($swagger)) {
            $this->setError(array(
                "status" => 500,
                "dev" => "The required file '$swagger' could not be found on the server",
                "user" => "There is a server error which prevents this request from being processed"
            ));
            return;
        }

        $json = file_get_contents($swagger);
        $this->swagger = json_decode($json);
    }



    /**
     * Adds text to the log.
     * For development use only.
     * @param $text
     */
    public function logAdd($text)
    {
        // Create the log if/when it's first needed
        if (is_null($this->log)) {
            $this->log = array();
        }
        $this->log[] = $text;
    }



    /**
     * Retrieves the log array.
     * For development use only.
     * @return null
     */
    public function logGet()
    {
        return $this->log;
    }



    /**
     * Returns either the request parameter $name, or an empty string if this doesn't exist.
     * @param $name
     * @return string
     */
    public function param($name)
    {
        return (isset($this->params[$name]) && trim($this->params[$name]) !== '') ? trim($this->params[$name]) : '';
    }



    /**
     * Populates the error array and changes the controller status.
     * @param $params
     */
    public function setError($params)
    {
        // Create the error array if/when it's first needed
        if (is_null($this->error)) {
            $this->error = array();
        }
        foreach ($params as $key => $value) {
            if ($key === "status") {
                $this->status = $value;
            } else {
                $this->error[$key] = $value;
            }
        }
    }


    /**
     * Registers an XML label to use for a particular key|parent combination
     * These are only required for a label which does not simply form a plural by adding an "s"
     * Use an asterisk * for any element that's numeric
     * @param $key
     * @param $parent
     * @param $label
     */
    public function xmlAdd($key, $parent, $label)
    {
        if (is_null($this->xml)) {
            $this->xml = array();
        }
        $this->xml["$key|$parent"] = $label;
    }


    /**
     * Returns an XML label for a key|parent combination
     * If no label exists, a simple plural is formed for parent nodes
     * @param $key
     * @param $parent
     * @return string
     */
    public function xmlLabel($key, $parent)
    {
        $out = null;

        // If the xml array is populated, look up this combination
        if (!is_null($this->xml)) {
            $lookup = ((is_numeric($key)) ? "*" : $key)."|".((is_numeric($parent)) ? "*" : $parent);
            if (array_key_exists($lookup, $this->xml)) {
                $out = $this->xml[$lookup];
            }
        }

        // Otherwise apply standard rules
        if (is_null($out) || $out === '') {
            if (is_numeric($key) && $parent[strlen($parent) - 1] === 's') {
                $out = substr($parent, 0, -1);
            } else {
                $out = ($key === $parent) ? $key.'s' : $key;
            }
        }
        // A catch-all to ensure we don't return a numeric key, which will break the XML
        return (is_numeric($out)) ? "item" : $out;
    }

    // ------------------------------------------------------------------------

    private function getImplementerClass()
    {
        $version = sprintf('%02d', $this->version);
        $interface = "ApiInterface_$this->noun" . "_$version";
        $implementers = ClassInfo::implementorsOf($interface);
        if (count($implementers) === 0) {
            $this->setError(array(
                "status" => 500,
                "dev" => "There is no implementation for $this->noun in API v$this->version",
                "user" => "The server is not able to fulfill this request"
            ));
            return null;
        }

        // Check for, and remove or return, any test implementation
        $pos = 0;
        while ($pos < count($implementers) && count($implementers) > 1) {
            if (strpos(strtolower($implementers[$pos]), 'apistub_') === 0) {
                if ($this->test) {
                    return $implementers[$pos];
                } else {
                    unset($implementers[$pos]);
                }
            } else {
                $pos++;
            }
        }

        // Ensure we only have one "real" implementation
        if (count($implementers) > 1) {
            $this->setError(array(
                "status" => 500,
                "dev" => "There is more than one implementation for $this->noun in API v$this->version",
                "user" => "The server is not able to fulfill this request"
            ));
            return null;
        } else {
            return $implementers[0];
        }
    }



    private function getResponseSerialiser()
    {
        $class = "ApiResponseSerialiser_".ucfirst($this->format);
        $formatter = new $class();
        return $formatter;
    }



    private function populateErrorResponse()
    {
        $this->output = $this->error;
    }



    private function setStandardHeaders()
    {
        $this->getResponse()->setStatusCode($this->status);
        $this->getResponse()->addHeader("access-control-allow-origin", "*");
        $this->getResponse()->addHeader("access-control-allow-methods", "GET, POST, DELETE, PUT");
        $this->getResponse()->addHeader("access-control-allow-headers", "api_key, Authorization");
        $this->getResponse()->addHeader("connection", "close");
    }



    private function testOutput()
    {
        $this->output = array(
            "log" => $this->logGet(),
            "action" => $this->action,
            "case" => $this->case,
            "error" => $this->error,
            "fields" => $this->fields,
            "format" => $this->format,
            "limit" => $this->limit,
            "method" => $this->method,
            "noun" => $this->noun,
            "params" => $this->params,
            "sort" => $this->sort,
            "status" => $this->status,
            "test" => $this->test,
            "verb" => $this->verb,
            "version" => $this->version
        );
    }
}
