<?php

namespace App\Http\Controllers\Api\Sistema;

use App\Http\Controllers\Controller;
use App\Models\RefranLogin;
use Illuminate\Http\Request;

class ApiSistemaController extends Controller
{

    public function refranLogin()
    {

        $infoRefran = RefranLogin::where('id', 1)->first();

        return ['success' => 1,
            'refran' => $infoRefran->refran,
            'salmo' => $infoRefran->salmo
            ];

    }


}
