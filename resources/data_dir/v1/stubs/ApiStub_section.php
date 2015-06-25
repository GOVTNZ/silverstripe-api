<?php

class ApiStub_section extends DataModel implements ApiInterface_section_01 {

    private $sections = array(
            array(
                "id" => 1,
                "name" => 'strings',
                "url" => 'https://en.wikipedia.org/wiki/String_section'
            ),
            array(
                "id" => 2,
                "name" => 'woodwind',
                "url" => 'https://en.wikipedia.org/wiki/Woodwind_section'
            ),
            array(
                "id" => 3,
                "name" => 'brass',
                "url" => 'https://en.wikipedia.org/wiki/Brass_section'
            ),
            array(
                "id" => 4,
                "name" => 'percussion',
                "url" => 'https://en.wikipedia.org/wiki/Percussion_section'
            )
    );

    public function getSectionById($controller){
        $id = intval($controller->action);
        foreach ($this->sections as $section){
            if ($section['id'] === $id){
                $controller->output[] = $section;
                return;
            }
        }
    }

    public function getSections($controller){
        $controller->output = $this->sections;
    }

}