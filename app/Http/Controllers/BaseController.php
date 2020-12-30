<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlightResource;
use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BaseController extends Controller
{

    public function index(Request $request)
    {
        // conecta a API e recupera alista de itens
        $flights = Flight::getFlightAPI();

        // faz agrupamento dos itens por fare - tipo_ida_volta - e valor
        $flightsGroupPerFare = Flight::groupFlightPerFare($flights);

        // gera o grupos para resposta
        $response = Flight::createGroup($flightsGroupPerFare);

        return (new FlightResource($flights, $response))->toArray($request);
    }
}
