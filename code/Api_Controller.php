<?php

class Api_Controller extends Page_Controller {

    private static $allowed_actions = array(
        "index"
    );

    private static $url_handlers = array(
        '$Version/$Noun//$Action' => 'index'
    );

    private
        $log = null;

    public
        $action = '',
        $case = 'camel',
        $error = null,
        $fields = null,
        $filter = '',
        $format = 'json',
        $implementer = null,
        $limit = null,
        $method = '',
        $noun = '',
        $output = null,
        $params = null,
        $sort = null,
        $status = 200,
        $swagger = null,
        $test = FALSE,
        $total = 0,
        $verb = '',
        $version = 0;

    public function index(){
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
                }
                catch(Exception $except) {
                    $this->setError(array(
                        "status" => 500,
                        "dev" => "Error processing request: please check your syntax against the request definition",
                        "user" => "This request could not be processed"
                    ));
                }
            }
            else if (Director::isDev())
                $this->testOutput();
        }
        else
            $this->populateErrorResponse();

        // Deliver
        $this->setStandardHeaders();
        $ApiResponse = $this->getResponseSerialiser();
        return $ApiResponse->execute($this);
    }

    /**
     * A utility function that simplistically converts standard MySQL timestamps (2015-06-28 00:00:00) to RFC3339 format (2015-06-28T00:00:00+12:00)
     * @param $input
     * @return string
     */
    public function dateDBto3339($input){
        return str_replace(' ', 'T', $input).'+12:00';
    }

    public function formatOutput(){
        $out = array(
            "query" => array(
                "offset" => (is_null($this->limit)) ? 0 : $this->limit['offset'],
                "count" => (is_null($this->limit)) ? 'all' : min($this->limit['count'], $this->total),
                "total" => $this->total
            ),
            $this->noun."s" => $this->output
        );
        return $out;
    }

    public function loadSwagger($version){
        // Find the location of the swagger.json file with the right version
        $dir = Config::inst()->get('Swagger', 'data_dir');
        $dir = Director::baseFolder().((isset($dir)) ? $dir : "/assets/api");
        $swagger = "$dir/$version/swagger.json";

        if (!file_exists($swagger)){
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

    public function logAdd($text){
        // Create the log if/when it's first needed
        if (is_null($this->log))
            $this->log = array();
        $this->log[] = $text;
    }

    public function setError($params){
        // Create the error array if/when it's first needed
        if (is_null($this->error))
            $this->error = array();
        foreach ($params as $key=>$value){
            if ($key === "status")
                $this->status = $value;
            else
                $this->error[$key] = $value;
        }
    }

    public function logGet(){
        return $this->log;
    }

    // ------------------------------------------------------------------------

    private function getImplementerClass(){
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
        while ($pos < count($implementers) && count($implementers) > 1){
            if (strpos(strtolower($implementers[$pos]), 'apistub_') === 0){
                if ($this->test)
                    return $implementers[$pos];
                else
                    unset($implementers[$pos]);
            }
            else
                $pos++;
        }

        // Ensure we only have one "real" implementation
        if (count($implementers) > 1) {
            $this->setError(array(
                "status" => 500,
                "dev" => "There is more than one implementation for $this->noun in API v$this->version",
                "user" => "The server is not able to fulfill this request"
            ));
            return null;
        } else
            return $implementers[0];
    }

    private function getResponseSerialiser(){
        $class = "ApiResponseSerialiser_".ucfirst($this->format);
        $formatter = new $class();
        return $formatter;
    }

    private function populateErrorResponse(){
        $this->output = $this->error;
    }

    private function setStandardHeaders(){
        $this->getResponse()->setStatusCode($this->status);
        $this->getResponse()->addHeader("access-control-allow-origin", "*");
        $this->getResponse()->addHeader("access-control-allow-methods", "GET, POST, DELETE, PUT");
        $this->getResponse()->addHeader("access-control-allow-headers", "api_key, Authorization");
        $this->getResponse()->addHeader("connection", "close");
    }

    private function testOutput(){
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