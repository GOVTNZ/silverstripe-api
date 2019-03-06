<?php

namespace GovtNZ\SilverStripe\Api\Task;

use SilverStripe\Dev\BuildTask;
use GovtNZ\SilverStripe\Api\ApiManager;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use DirectoryIterator;

class ApiRebuildDefinitionsTask extends BuildTask
{
    protected $title = 'API: Rebuild definitions';

    protected $description = 'Parse the API interface definitions and rebuild the output JSON file';

    protected $enabled = true;

    private static $segment = 'ApiRebuildDefinitionsTask';

    private $baseUrl = '';

    private $swaggerDir = '';

    public function run($request)
    {
        $this->baseUrl = Director::absoluteBaseURL();

        $starttime = time();
        $apis = Config::inst()->get(ApiManager::class, 'api');

        foreach ($apis as $key => $settings) {
            if (!isset($settings['definition']) || !isset($settings['interfaces'])) {
                $this->out('Invalid API missing definition file or interfaces', 'err');
            } else {
                $this->buildSwaggerDefinition($key, $settings);
            }
        }

        $elapsed = date('i \m\i\n s \s\e\c', time() - $starttime);
        $this->out("<strong>Task completed in $elapsed</strong>");
    }

    public function buildSwaggerDefinition($key, $settings)
    {
        $definition = $settings['definition'];
        $interfaces = [];

        foreach ($settings['interfaces'] as $interface) {
            if (is_file($interface)) {
                $interfaces[] = $interface;
            } elseif (is_dir($interface)) {
                foreach (glob("$interface/*.php") as $file) {
                    $interfaces[] = $file;
                }
            }
        }

        $this->buildSwagger($definition, $interfaces, $settings);
    }

    /**
     * @param string $definitionFile
     * @param array  $interfacePaths
     * @param array  $context
     *
     * @return void
     */
    protected function buildSwagger($definitionFile, array $interfacePaths, array $context)
    {
        $swagger = array();
        $swagger = $this->mergeJsonFromFile($swagger, $definitionFile);

        foreach ($interfacePaths as $file) {
            $swagger = $this->mergeJsonFromFile($swagger, $file);
        }

        // Save output
        $output = json_encode($swagger);
        $writeTo = $this->getSwaggerBaseDir();

        $versionPath = Controller::join_links($writeTo, $context['version']);

        if (file_exists($versionPath)) {
            $this->emptyDir($versionPath);
        } else {
            mkdir($versionPath, 0755, true);
        }

        file_put_contents(
            Controller::join_links($versionPath, "swagger.json"),
            $output
        );

        $this->out("Created swagger.json in $writeTo");
    }

    protected function emptyDir($dir)
    {
        $childDirs = array_diff(scandir($dir), array('.', '..'));

        foreach ($childDirs as $childDir) {
            $path = Controller::join_links($dir, $childDir);

            if (is_dir($path)) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    protected function getSwaggerBaseDir()
    {
        if ($this->swaggerDir === '') {
            $this->swaggerDir = Config::inst()->get('Swagger', 'data_dir');

            if (!$this->swaggerDir) {
                $this->swaggerDir = Controller::join_links(ASSETS_PATH, 'api');
            }

            if (file_exists($this->swaggerDir)) {
                $this->emptyDir($this->swaggerDir);
            } else {
                mkdir($this->swaggerDir, 0755, true);
            }
        }

        return $this->swaggerDir;
    }

    protected function mergeJsonBlock($swagger, $block)
    {
        $json = json_decode($block, true);

        if (is_null($swagger) || empty($swagger)) {
            return $json;
        }

        if (is_null($json)) {
            $this->out('Block beginning '.str_replace("\n", "", substr($block, 0, 80)).' is not valid JSON', 'err');
            return $swagger;
        }

        return array_merge_recursive($swagger, $json);
    }

    protected function mergeJsonFromFile($swagger, $file)
    {
        if (!file_exists($file)) {
            $file = Controller::join_links(BASE_PATH, $file);
        }

        $handle = fopen($file, "r");

        if ($handle) {
            $this->out("File $file opened for reading.");

            $block = '';
            $json = false;
            while (($line = fgets($handle)) !== false) {
                if (strpos(trim($line), '//') !== 0 && strpos($line, '*/') !== false) {
                    $json = false;
                    $swagger = $this->mergeJsonBlock($swagger, $block);
                    $block = '';
                } elseif ($json && strpos(trim($line), '//') !== 0) {
                    $line = $this->parseJson($line);
                    $block .= $line;
                } elseif (strpos(trim($line), '//') !== 0 && strpos($line, '/*') !== false) {
                    $json = true;
                }
            }
            fclose($handle);
        } else {
            $this->out("File <em>$file</em> cannot be opened for reading", 'err');
        }
        return $swagger;
    }

    /**
     * Format progress to stdout
     * @param $text
     */
    protected function out($text, $type = 'std')
    {
        if (Director::is_cli()) {
            if ($type === 'err') {
                echo "\033[31m [WARNING] ". $text ."\033[0m". PHP_EOL;
            } else {
                echo " [*] \033[31m". $text ."\033[0m" .PHP_EOL;
            }
        } else {
            if ($type === 'err') {
                echo ' &nbsp; &middot; &nbsp;<span style="color:#cc0000">'.$text.'</span><br />';
            } else {
                echo ' &nbsp; &middot; &nbsp;'.$text.'<br />';
            }
        }
    }

    /**
     * @param string
     *
     * @return string
     */
    protected function parseJson($line)
    {
        $cmdStart = strpos($line, '<%');
        $cmdEnd = strpos($line, '%>');
        if ($cmdStart !== false && $cmdEnd !== false) {
            $cmd = trim(substr($line, $cmdStart + 2, $cmdEnd - ($cmdStart + 2)));
            switch ($cmd) {
                case 'getHost':
                    $value = $this->urlGetHost();
                    break;
                case 'getProtocol':
                    $value = $this->urlGetProtocol();
                    break;
                default:
                    $value = '';
            }
            if ($value !== '') {
                $line = substr($line, 0, $cmdStart).$value.substr($line, $cmdEnd + 2);
            }
        }
        return $line;
    }

    private function urlGetHost()
    {
        return substr($this->baseUrl, strpos($this->baseUrl, '://') + 3)."api";
    }

    private function urlGetProtocol()
    {
        return substr($this->baseUrl, 0, strpos($this->baseUrl, '://'));
    }
}
