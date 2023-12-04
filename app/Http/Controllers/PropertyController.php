<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use DB;

class PropertyController extends BaseController
{
    public function metric()
    {
        try {
            $statistics = DB::select("SELECT sum(units) as totalProperties,
                (SELECT sum(b.units) FROM properties b WHERE b.type LIKE '%completed%' AND b.deleted_at IS NULL) as total_completed,
                (SELECT sum(s.units) FROM properties s WHERE s.type LIKE '%lands and infastructure%' AND s.deleted_at IS NULL) as total_land_infra,
                (SELECT sum(a.units) FROM properties a WHERE a.type LIKE '%off-plan%' AND a.deleted_at IS NULL) as total_off_plan,
                (SELECT sum(c.units) FROM properties c WHERE c.deleted_at IS NULL AND c.status = 'approved') as total_approved,
                (SELECT count(id) FROM mortgaged_properties WHERE status = 'approved') as total_mortgaging,
                (SELECT count(id) FROM orders) as total_sold 
                FROM properties WHERE deleted_at IS NULL");
                //FROM properties WHERE deleted_at IS NULL AND status = 'approved'");

            return $this->sendResponse($statistics, 'Returned proterties analytics');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }

    public function mortgageApplication()
    {
        
    }
}
