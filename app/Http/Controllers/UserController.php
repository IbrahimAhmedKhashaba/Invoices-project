<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:قائمة المستخدمين', ['only' => ['index']]);
        $this->middleware('permission:اضافة مستخدم', ['only' => ['create' , 'store']]);
        $this->middleware('permission:تعديل مستخدم', ['only' => ['edit' , 'update']]);
        $this->middleware('permission:حذف مستخدم', ['only' => ['destroy']]);
    }

    public function index(Request $request){
        $data = User::orderBy('id','DESC')->paginate(5);
        return view('users.show_users',compact('data'))
        ->with('i', ($request->input('page', 1) - 1) * 5);
    }



    public function create(){
        $roles = Role::pluck('name','name')->all();
        return view('users.Add_user',compact('roles'));

    }

    public function store(Request $request){
        $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|confirmed',
        'roles_name' => 'required'
        ]);

        $input = $request->all();


        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles_name'));
        session()->flash('Add' , 'تم اضافة المستخدم بنجاح');
        return redirect()->route('users.index');
    }


    public function show($id){
        $user = User::find($id);
        return view('users.show',compact('user'));
    }

    public function edit($id){
    $user = User::find($id);
    $roles = Role::pluck('name','name')->all();
    $userRole = $user->roles->pluck('name','name')->all();
    return view('users.edit',compact('user','roles','userRole'));
    }

    public function update(Request $request, $id){
        $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email|unique:users,email,'.$id,
        'roles' => 'required',
        'status' =>'required',
        ]);
        $input = $request->all();

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();
        $user->assignRole($request->input('roles'));
        session()->flash('edit' , 'تم تحديث معلومات المستخدم بنجاح');
        return redirect()->route('users.index');
    }

    public function destroy(Request $request){
        User::find($request->user_id)->delete();
        session()->flash('delete' , 'تم حذف المستخدم بنجاح');
        return redirect()->route('users.index');
    }

}
