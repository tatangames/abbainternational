<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(){
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function indexLogin(){
        return view('frontend.login.vistalogin');
    }
}
