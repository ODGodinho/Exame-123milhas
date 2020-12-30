<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request;

class FlightResource extends JsonResource
{
    public function __construct($resource, $groups = [])
    {
        // Ensure we call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->groups = collect($groups);
    }

    public function toArray($request)
    {
        $firstGroup = $this->groups->first();
        return [
            "flights" => $this->resource,
            "groups" => collect($this->groups),
            "totalGroups" => $this->groups->count(),
            "totalFlights" => count($this->resource),
            "cheapestPrice" => $firstGroup['totalPrice'],
            "cheapestGroup" => $firstGroup['uniqueId']
        ];
    }
}
