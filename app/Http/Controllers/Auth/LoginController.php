<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use Auth;
use Crypt;
use Illuminate\Http\Request;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function customLogin()
    {
        $user =  User::where('email',request('email'))->first();
        if(request('password') == $user->password){

            $decrypt = Crypt::decrypt($user->pwd);

            if (Auth::attempt(['email' => request('email'), 'password' => $decrypt])) {
              return redirect()->route('home');
            }else {
               return response()->json(['error' => 'Unauthorised'], 401);
            }
        }else{

            return redirect('login');
        }
    }

    public function logout(Request $request)
    {

        $this->guard()->logout();

        $request->session()->invalidate();

        return "sdasd";
    }
}
