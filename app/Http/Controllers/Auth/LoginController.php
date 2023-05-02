<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Models\User;
use Auth;

class LoginController extends Controller
{
    //

    public function login(Request $request){
        //request->student_id exists
        if($request->student_id){
            //Check if user exists
            $user = User::where('student_id', $request->student_id)->first();
            if($user){
                //Check if password matches
                if(Hash::check($request->password, $user->password)){
                    //Login user
                    Auth::login($user);
                    return redirect()->route('dashboard');
                }else{
                    return redirect()->back()->with('error', 'Invalid login details');
                }
            }else{
                return redirect()->route('welcome', ['new' => $request->student_id]);
            }
        }
        else{
            //return to welcome page with $new = $request->student_id
            return redirect()->back()->with('error', 'Invalid login details');
        }        
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('welcome');
    }
}
