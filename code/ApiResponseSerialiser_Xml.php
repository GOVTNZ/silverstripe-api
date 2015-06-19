<?php

class ApiResponseSerialiser_Xml
{

    public function execute($controller) {
        $controller->getResponse()->setStatusCode($controller->status);
        $controller->getResponse()->addHeader('Content-Type', 'application/xml');
        return $this->xml_format($controller);
    }

    // function definition to convert array to xml
    private function array_to_xml($data, &$xml) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                }
                else {
                    $subnode = $xml->addChild("item".sprintf("%02d", $key));
                    $this->array_to_xml($value, $subnode);
                }
            }
            else {
                if (is_numeric($key))
                    $xml->addChild("item".sprintf("%02d", $key), htmlspecialchars("$value"));
                else
                    $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    private function xml_format($controller){
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><$controller->noun></$controller->noun>");
        $this->array_to_xml($controller->output, $xml);
        return $xml->asXML();
    }

}