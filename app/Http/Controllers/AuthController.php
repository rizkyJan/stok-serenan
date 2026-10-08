<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
    public function create(){return view('auth.login');}
    public function store(Request $request){
        $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
        $data['is_active']=true;
        if(!Auth::attempt($data)){return back()->withErrors(['email'=>'Email, kata sandi, atau status akun tidak sesuai.'])->onlyInput('email');}
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }
    public function destroy(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login');}
}
