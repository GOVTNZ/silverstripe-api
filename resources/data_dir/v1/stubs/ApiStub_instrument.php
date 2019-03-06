<?php

class ApiStub_instrument extends DataModel implements ApiInterface_instrument_01
{

    private $instruments = array(

        // Strings

        array(
            "id" => 1,
            "name" => "violin",
            "section" => "strings",
            "url" => "https://en.wikipedia.org/wiki/Violin_family"
        ),
        array(
            "id" => 2,
            "name" => "viola",
            "section" => "strings",
            "url" => "https://en.wikipedia.org/wiki/Violin_family"
        ),
        array(
            "id" => 3,
            "name" => "cello",
            "section" => "strings",
            "url" => "https://en.wikipedia.org/wiki/Violin_family"
        ),
        array(
            "id" => 4,
            "name" => "double bass",
            "section" => "strings",
            "url" => "https://en.wikipedia.org/wiki/Violin_family"
        ),

        // Woodwind

        array(
            "id" => 5,
            "name" => "piccolo",
            "section" => "woodwind",
            "url" => "https://en.wikipedia.org/wiki/Woodwind_section"
        ),
        array(
            "id" => 6,
            "name" => "flute",
            "section" => "woodwind",
            "url" => "https://en.wikipedia.org/wiki/Woodwind_section"
        ),
        array(
            "id" => 7,
            "name" => "clarinet",
            "section" => "woodwind",
            "url" => "https://en.wikipedia.org/wiki/Woodwind_section"
        ),
        array(
            "id" => 8,
            "name" => "oboe",
            "section" => "woodwind",
            "url" => "https://en.wikipedia.org/wiki/Woodwind_section"
        ),
        array(
            "id" => 9,
            "name" => "basoon",
            "section" => "woodwind",
            "url" => "https://en.wikipedia.org/wiki/Woodwind_section"
        ),

        // Brass

        array(
            "id" => 10,
            "name" => "horn",
            "section" => "brass",
            "url" => "https://en.wikipedia.org/wiki/Brass_section"
        ),
        array(
            "id" => 11,
            "name" => "trumpet",
            "section" => "brass",
            "url" => "https://en.wikipedia.org/wiki/Brass_section"
        ),
        array(
            "id" => 12,
            "name" => "trombone",
            "section" => "brass",
            "url" => "https://en.wikipedia.org/wiki/Brass_section"
        ),
        array(
            "id" => 13,
            "name" => "tuba",
            "section" => "brass",
            "url" => "https://en.wikipedia.org/wiki/Brass_section"
        ),

        // Percussion

        array(
            "id" => 14,
            "name" => "tympani",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 15,
            "name" => "xylophone",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 16,
            "name" => "cymbals",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 17,
            "name" => "triangle",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 18,
            "name" => "snare drum",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 19,
            "name" => "bass drum",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 20,
            "name" => "tambourine",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 21,
            "name" => "maracas",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 22,
            "name" => "gong",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 23,
            "name" => "chimes",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 24,
            "name" => "celesta",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        ),
        array(
            "id" => 25,
            "name" => "piano",
            "section" => "percussion",
            "url" => "https://www.orsymphony.org/edu/instruments/percussion.aspx"
        )
    );

    public function findInstrumentsBySection(Api_Controller $controller)
    {
        $section = $controller->params['section'];
        if (!isset($section)) {
            $controller->setError(array(
                "status" => 400,
                "dev" => "No section supplied",
                "user" => "No section supplied"
            ));
            return;
        }
        foreach ($this->instruments as $instrument) {
            $sec = ','.$section.',';
            $insec = ','.$instrument['section'].',';
            if (strpos(','.strtolower($section).',', ','.strtolower($instrument['section']).',') !== false) {
                $controller->output[] = $instrument;
            }
        }
    }

    public function getInstrumentById(Api_Controller $controller)
    {
        $id = intval($controller->action);
        foreach ($this->instruments as $instrument) {
            if ($instrument['id'] === $id) {
                $controller->output[] = $instrument;
                return;
            }
        }
    }
}
