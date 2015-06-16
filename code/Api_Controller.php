<?php

class Api_Controller extends Page_Controller {

    // We don't define allowed_actions as we want to receive all incoming API calls
    private static $allowed_actions = array();

    public function index(){
        $out = array(
            "request" => $this->request->getURL(),
            "response" => "Nowt as yet"
        );
        echo json_encode($out);
    }
}