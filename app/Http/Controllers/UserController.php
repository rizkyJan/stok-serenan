<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class UserController extends Controller {
    public function index(){return view('users.index',['users'=>User::orderBy('name')->paginate(20)]);}
    public function create(){return view('users.form',['user'=>new User]);}
    public function edit(User $user){return view('users.form',compact('user'));}
    private function validated(Request $r,?User $user=null):array{
        $v=$r->validate([
            'name'=>'required|string|max:255','email'=>['required','email','max:255',Rule::unique('users','email')->ignore($user?->id)],
            'password'=>[$user?'nullable':'required','string','min:10'],
            'role'=>['required',Rule::in(['admin','petugas'])], 'is_active'=>'required|boolean',
        ]);
        if($user && $user->isAdmin() && (!$v['is_active']||$v['role']!=='admin') && User::where('role','admin')->where('is_active',true)->count()<=1){
            throw ValidationException::withMessages(['role'=>'Minimal satu akun admin aktif harus dipertahankan.']);
        }
        if($user && $user->id===auth()->id() && !$v['is_active']){
            throw ValidationException::withMessages(['is_active'=>'Tidak dapat menonaktifkan akun sendiri.']);
        }
        if(empty($v['password']))unset($v['password']);
        return $v;
    }
    public function store(Request $r){User::create($this->validated($r));return redirect()->route('users.index')->with('success','Akun berhasil dibuat.');}
    public function update(Request $r,User $user){$user->update($this->validated($r,$user));return redirect()->route('users.index')->with('success','Akun diperbarui.');}
}
