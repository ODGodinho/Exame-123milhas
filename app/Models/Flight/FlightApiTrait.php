<?php

namespace App\Models\Flight;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait FlightApiTrait
{

    /**
     * Conecta na API da 123milhas e recupera os dados em Objeto
     *
     * @return array
     */
    public static function getFlightAPI()
    {
        $client = new \GuzzleHttp\Client();
        // Tratamento de erro em App\Exceptions\Handler
        $response = $client->request('GET', 'http://prova.123milhas.net/api/flights');
        $responseValue = json_decode($response->getBody()->getContents());
        // $responseValue = json_decode(file_get_contents(base_path("app/Models/aa.json")));
        return $responseValue;
    }

    /**
     * separa a lista de voos por Fate e ida e volta
     *
     * @param array $flights
     * @return array
     */
    public static function groupFlightPerFare(array $flights)
    {
        $typesFlightToBoard = [];

        foreach ($flights as $key => $flight) {
            // Inicializa a lista se ela não existir
            $typesFlightToBoard[$flight->fare] = ($typesFlightToBoard[$flight->fare] ?? [
                "outbound" => [],
                "inbound" => []
            ]) ?: ["outbound" => [], "inbound" => []];

            if ($flight->outbound == 1) {
                $typesFlightToBoard[$flight->fare]["outbound"][$flight->price] =
                    ($typesFlightToBoard[$flight->fare]["outbound"][$flight->price] ?? []) ?: [];

                $typesFlightToBoard[$flight->fare]["outbound"][$flight->price][] = $flight;
            } elseif ($flight->inbound == 1) {
                $typesFlightToBoard[$flight->fare]["inbound"][$flight->price] =
                    ($typesFlightToBoard[$flight->fare]["inbound"][$flight->price] ?? []) ?: [];

                $typesFlightToBoard[$flight->fare]["inbound"][$flight->price][] = $flight;
            }
        }

        return $typesFlightToBoard;
    }

    public static function createGroup(array $groupFlightPerFare)
    {
        $groupList = [];
        $id = 1;

        foreach ($groupFlightPerFare as $fare => $typesFlightToBoard) {
            // recupera todos os voos de ida
            foreach ($typesFlightToBoard['outbound'] as $priceOutBound => $flightOutBound) {
                // Todas As Voltas
                foreach ($typesFlightToBoard['inbound'] as $priceInBound => $flightInBound) {
                    $currentInBoundPrice = $priceInBound;
                    $sumValue = $priceOutBound + $priceInBound;
                    static::initGroupListItem(
                        $sumValue,
                        $groupList,
                        $id,
                        $flightInBound,
                        $flightOutBound
                    );
                }
            }
        }

        usort($groupList, function ($a, $b) {
            return $a['totalPrice'] > $b['totalPrice'];
        });

        return $groupList;
    }

    private static function &initGroupListItem($sumValue, &$groupList, &$id, $flightInBound, $flightOutBound)
    {
        $currentItem = [
            "uniqueId" => $id,
            "totalPrice" => $sumValue,
            "outbound" => $flightInBound,
            "inbound" => $flightOutBound,
        ];
        $groupList[$id++] = &$currentItem;

        return $currentItem;
    }
}
