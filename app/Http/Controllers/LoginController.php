<?php 

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use Auth;

class LoginController extends Controller
{
	public function login(){
		
		return "hello";
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
	       $user = Auth::user();
	       $success['token'] = $user->createToken('MyApp')->accessToken;

	      return redirect()->route('home');
	    }else {
	       return response()->json(['error' => 'Unauthorised'], 401);
	    }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return "gfdfgdfg";
    }
}