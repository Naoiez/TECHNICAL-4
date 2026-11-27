<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ProfileController extends Controller {
 public function show(){return view('profile');}
 public function update(Request $request){$data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users,email,'.$request->user()->id]]);$request->user()->update($data);return back()->with('message','Profile updated.');}
}
