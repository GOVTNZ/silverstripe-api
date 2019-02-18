<?php

class ApiResponseSerialiser_Xml
{

    public function execute($controller) {
        $controller->getResponse()->addHeader('Content-Type', 'application/xml');
        return $this->xml_format($controller);
    }

    // function definition to convert array to xml
    private function array_to_xml($data, &$xml, $parent, $controller) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if (is_numeric($key)){
                    $subnode = $xml->addChild($controller->xmlLabel($key, $parent));
                    $this->array_to_xml($value, $subnode, $key, $controller);
                }
                else {
                    $subnode = $xml->addChild($controller->xmlLabel($key, $parent));
                    $this->array_to_xml($value, $subnode, $key, $controller);
                }
            }
            else {
                if (is_numeric($key))
                    $xml->addChild($controller->xmlLabel($key, $parent), htmlspecialchars("$value"));
                else
                    $xml->addChild($controller->xmlLabel($key, $parent), htmlspecialchars("$value"));
            }
        }
    }

    private function xml_format($controller){
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><response></response>");
        //$pronoun = ($controller->pronoun === '') ? $controller->noun : $controller->pronoun;
        $this->array_to_xml($controller->formatOutput(), $xml, 'response', $controller);
        return $xml->asXML();
    }

}