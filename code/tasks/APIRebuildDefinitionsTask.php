<?php

class APIRebuildDefinitionsTask extends BuildTask {

    protected $title = 'API: Rebuild definitions';
    protected $description = 'Parse the API interface definitions and rebuild the output JSON file';

    public function run($request) {
        $starttime = time();

        // TODO Here's where it's done

        $elapsed = date('i \m\i\n s \s\e\c', time() - $starttime);
        echo "<br /><strong>Task completed in $elapsed</strong>";
    }

    /**
     * Format progress to stdout
     * @param $text
     */
    private function out($text){
        echo ' &nbsp; &middot; &nbsp;'.$text.'<br />';
    }

}