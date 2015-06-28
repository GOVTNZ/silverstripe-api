<?php

class ApiResponseSerialiser_Xml
{

    public function execute($controller) {
        $controller->getResponse()->addHeader('Content-Type', 'application/xml');
        return $this->xml_format($controller);
    }

    // function definition to convert array to xml
    private function array_to_xml($data, &$xml, $itemname) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild((substr($key, -1) === 's' || $key === "query") ? $key : $key."s");
                    $this->array_to_xml($value, $subnode, ((substr($key, -1) === 's') ? substr($key, 0, -1) : $key));
                }
                else {
                    $subnode = $xml->addChild($itemname); //.sprintf("%02d", $key + 1));
                    $this->array_to_xml($value, $subnode, "item");
                }
            }
            else {
                if (is_numeric($key))
                    //$xml->addChild("item".sprintf("%02d", $key + 1), htmlspecialchars("$value"));
                    $xml->addChild($itemname, htmlspecialchars("$value"));
                else
                    $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    private function xml_format($controller){
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><response></response>");
        $this->array_to_xml($controller->formatOutput(), $xml, $controller->noun);
        return $xml->asXML();
    }

}