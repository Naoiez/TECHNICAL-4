<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller {
 public function registerForm(){return view('auth.register');}
 public function register(Request $request){$data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','confirmed',Password::defaults()]]);$user=User::create($data);Auth::login($user);$request->session()->regenerate();return redirect()->route('tasks.index');}
 public function loginForm(){return view('auth.login');}
 public function login(Request $request){$data=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);if(!Auth::attempt($data,$request->boolean('remember')))throw \Illuminate\Validation\ValidationException::withMessages(['email'=>'These credentials do not match our records.']);$request->session()->regenerate();return redirect()->intended(route('tasks.index'));}
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('welcome');}
}
