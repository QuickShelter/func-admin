<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use DB;

class RequestController extends BaseController
{
    public function metric()
    {
        try {
            $statistics = DB::select("SELECT count(id) as all_requests,
                (SELECT count(a.id) FROM requests a WHERE a.status = 0) as pending,
                (SELECT count(b.id) FROM requests b WHERE b.status = 1) as declined,
                (SELECT count(c.id) FROM requests c WHERE c.status = 2) as approved,
                (SELECT count(d.id) FROM requests d WHERE d.type = '".config('constants.request_type.mortgage')."' 
                AND d.status = 0) as mortgage_application, 
                (SELECT count(e.id) FROM requests e WHERE e.type = '".config('constants.request_type.services')."'
                AND e.status = 0) as services,
                (SELECT count(f.id) FROM requests f WHERE f.type = '".config('constants.request_type.properties')."' 
                AND f.status = 0) as property_upload,
                (SELECT count(g.id) FROM requests g WHERE g.type = '".config('constants.request_type.price')."'
                AND g.status = 0) as price_change 
                FROM requests");

            return $this->sendResponse($statistics, 'Requests Analytics');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }
}
