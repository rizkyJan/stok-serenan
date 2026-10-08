<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class CreateAdmin extends Command {
    protected $signature='apotek:admin';
    protected $description='Buat atau perbarui admin Apotek Serenan secara interaktif';
    public function handle():int {
        $name=trim((string)$this->ask('Nama administrator', 'Admin Apotek'));
        $email=strtolower(trim((string)$this->ask('Email admin')));
        $password=(string)$this->secret('Password (minimal 10 karakter)');
        $v=Validator::make(compact('name','email','password'),[
            'name'=>'required|string|max:255','email'=>'required|email|max:255','password'=>'required|string|min:10',
        ]);
        if($v->fails()){foreach($v->errors()->all() as $e)$this->error($e);return self::FAILURE;}
        $user=User::updateOrCreate(['email'=>$email],['name'=>$name,'role'=>'admin','is_active'=>true,'password'=>Hash::make($password)]);
        $this->info('Admin siap digunakan: '.$user->email);
        return self::SUCCESS;
    }
}
