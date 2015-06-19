<?php

class ApiRebuildDefinitionsTask extends BuildTask {

    protected $title = 'API: Rebuild definitions';
    protected $description = 'Parse the API interface definitions and rebuild the output JSON file';
    protected $enabled = true;
    private $baseUrl = '';

    public function run($request) {
        $starttime = time();

        $api_data_dir = Director::baseFolder().Config::inst()->get('API', 'data_dir');
        if (file_exists($api_data_dir)){
            foreach (new DirectoryIterator($api_data_dir) as $subdir) {
                if($subdir->isDir() && !$subdir->isDot())
                    $this->buildSwagger($api_data_dir, $subdir->getFileName());
            }
        }

        $elapsed = date('i \m\i\n s \s\e\c', time() - $starttime);
        $this->out("<strong>Task completed in $elapsed</strong>");
    }

    // ------------------------------------------------------------------------

    private function buildSwagger($dir, $subdir){
        if (!file_exists("$dir/$subdir/interfaces")) {
            $this->out("Directory <em>$subdir</em> has no <em>interfaces</em> subdirectory", 'err');
            return;
        }
        $base = "$dir/$subdir/interfaces/base.txt";
        if (!file_exists($base)){
            $this->out("Directory <em>$subdir/interfaces</em> has no <em>base.txt</em> file", 'err');
            return;
        }

        $swagger = array();
        $swagger = $this->mergeJsonFromFile($swagger, $base);

        foreach (glob("$dir/$subdir/interfaces/ApiInterface_*.php") as $file){
            $swagger = $this->mergeJsonFromFile($swagger, $file);
        }
        // Save output
        $output = json_encode($swagger);
        if (!file_exists(Director::baseFolder()."/assets/api/$subdir"))
            mkdir(Director::baseFolder()."/assets/api/$subdir", 0755, true);
        file_put_contents(Director::baseFolder()."/assets/api/$subdir/swagger.json", $output);
        $this->out("Created swagger.json for $subdir");
    }

    private function mergeJsonBlock($swagger, $block){
        $json = json_decode($block, true);
        if (is_null($swagger) || empty($swagger))
            return $json;
        return array_merge_recursive($swagger, $json);
    }

    private function mergeJsonFromFile($swagger, $file){
        $handle = fopen($file, "r");
        if ($handle) {
            $block = '';
            $json = false;
            while (($line = fgets($handle)) !== false) {
                if (strpos(trim($line), '//') !== 0 && strpos($line, '*/') !== false) {
                    $json = false;
                    $swagger = $this->mergeJsonBlock($swagger, $block);
                    $block = '';
                }
                else if ($json){
                    $line = $this->parseJson($line);
                    $block .= $line;
                }
                else if (strpos(trim($line), '//') !== 0 && strpos($line, '/*') !== false)
                    $json = true;
            }
            fclose($handle);
        } else
            $this->out("File <em>$file</em> cannot be opened for reading", 'err');
        return $swagger;
    }

    /**
     * Format progress to stdout
     * @param $text
     */
    private function out($text, $type = 'std'){
        if ($type === 'err')
            echo ' &nbsp; &middot; &nbsp;<span style="color:#cc0000">'.$text.'</span><br />';
        else
            echo ' &nbsp; &middot; &nbsp;'.$text.'<br />';
    }

    private function parseJson($line){
        $cmdStart = strpos($line, '<%');
        $cmdEnd = strpos($line, '%>');
        if ($cmdStart !== false && $cmdEnd !== false){
            $cmd = trim(substr($line, $cmdStart + 2, $cmdEnd - ($cmdStart + 2)));
            switch($cmd){
                case 'getHost':
                    $value = $this->urlGetHost();
                    break;
                case 'getProtocol':
                    $value = $this->urlGetProtocol();
                    break;
                default:
                    $value = '';
            }
            if ($value !== '')
                $line = substr($line, 0, $cmdStart).$value.substr($line, $cmdEnd + 2);
        }
        return $line;
    }

    private function urlCheck(){
        if ($this->baseUrl === '')
            $this->baseUrl = Director::absoluteBaseURL();
    }

    private function urlGetHost(){
        $this->urlCheck();
        return substr($this->baseUrl, strpos($this->baseUrl, '://') + 3)."api";
    }

    private function urlGetProtocol(){
        $this->urlCheck();
        return substr($this->baseUrl, 0, strpos($this->baseUrl, '://'));
    }

}