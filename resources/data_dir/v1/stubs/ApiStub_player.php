<?php

class ApiStub_player extends DataModel implements ApiInterface_player_01
{

    private $players = array(
        array(
            "id" => 1,
            "firstname" => 'Desi',
            "lastname" => 'Phipps',
            "instrument" => array(1,2)
        ),
        array(
            "id" => 2,
            "firstname" => 'Rita Marie',
            "lastname" => 'Fingus',
            "instrument" => array(1,25)
        ),
        array(
            "id" => 3,
            "firstname" => 'Donny',
            "lastname" => 'Osmosis',
            "instrument" => array(1)
        ),
        array(
            "id" => 4,
            "firstname" => 'Freda',
            "lastname" => 'Flinistoni',
            "instrument" => array(2,1)
        ),
        array(
            "id" => 5,
            "firstname" => 'Andy',
            "lastname" => 'Passims',
            "instrument" => array(2)
        ),
        array(
            "id" => 6,
            "firstname" => 'Creo',
            "lastname" => 'Sote',
            "instrument" => array(3)
        ),
        array(
            "id" => 7,
            "firstname" => 'Benny',
            "lastname" => 'Anderjets',
            "instrument" => array(4,3)
        ),
        array(
            "id" => 8,
            "firstname" => 'Dottie',
            "lastname" => 'Kimcom',
            "instrument" => array(6)
        ),
        array(
            "id" => 9,
            "firstname" => 'Hailie',
            "lastname" => 'Selassie',
            "instrument" => array(6,5,25)
        ),
        array(
            "id" => 10,
            "firstname" => 'Trevor',
            "lastname" => 'Puget',
            "instrument" => array(7)
        ),
        array(
            "id" => 11,
            "firstname" => 'Media',
            "lastname" => 'Works',
            "instrument" => array(8)
        ),
        array(
            "id" => 12,
            "firstname" => 'Vivienne',
            "lastname" => 'Vance',
            "instrument" => array(9)
        ),
        array(
            "id" => 13,
            "firstname" => 'Ming',
            "lastname" => 'Dynasty',
            "instrument" => array(10,11)
        ),
        array(
            "id" => 14,
            "firstname" => 'Rita',
            "lastname" => 'Ure',
            "instrument" => array(11,10)
        ),
        array(
            "id" => 15,
            "firstname" => 'Shane',
            "lastname" => 'Tisane',
            "instrument" => array(12)
        ),
        array(
            "id" => 16,
            "firstname" => 'Max',
            "lastname" => 'Million',
            "instrument" => array(13)
        ),
        array(
            "id" => 17,
            "firstname" => 'Noel',
            "lastname" => 'Bludger',
            "instrument" => array(14,16,17,18,19,20,21)
        ),
        array(
            "id" => 18,
            "firstname" => 'Rack',
            "lastname" => 'Maninoff',
            "instrument" => array(15,22,23,24)
        ),
        array(
            "id" => 19,
            "firstname" => 'Arnie',
            "lastname" => 'Navy',
            "instrument" => array(25)
        )
    );

    public function findPlayersByInstrument($controller)
    {
        $instr = $controller->params['instrumentID'];
        if (!isset($instr)) {
            $controller->setError(array(
                "status" => 400,
                "dev" => "An instrumentID must be supplied as a query parameter (eg ?instrumentID=6)",
                "user" => "No instrumentID supplied"
            ));
            return;
        }
        foreach ($this->players as $player) {
            if (in_array($instr, $player['instrument'])) {
                $controller->output[] = $player;
            }
        }
    }

    public function getPlayerById($controller)
    {
        $id = intval($controller->action);
        foreach ($this->players as $player) {
            if ($player['id'] === $id) {
                $controller->output[] = $player;
                return;
            }
        }
    }
}
