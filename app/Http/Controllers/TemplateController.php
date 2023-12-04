<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

class TemplateController extends BaseController
{
    public function templateFunction()
    {
        try {
            return $this->sendResponse([], 'Success message');
        } catch (\Exception $e) {
            return $this->sendError('Error.', ['error' => $e->getMessage()], 400);
        }
    }
    
}
