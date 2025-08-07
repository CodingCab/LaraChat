<?php

namespace App\Http\Controllers\Csv;

use App\Helpers\CsvStreamedResponse;
use App\Http\Controllers\Controller;
use App\Models\OrderShipment;
use Illuminate\Http\Request;

class ReadyOrderShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderShipment::getSpatieQueryBuilder();

        return CsvStreamedResponse::fromQueryBuilder($query, request('filename', 'filename_url_param_not_specified.csv'));
    }
}
