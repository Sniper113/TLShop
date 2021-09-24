<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AdminUserController extends Controller
{
    //
    function __construct()
    {
        $this->middleware(function ($request, $next){
            session(['module_active' => 'users']);

            return $next($request);
        });
    }


    function list(Request $request){


        //Lấy tổng só trạng thái của các user
        $status = $request->input('status');

        $list_act = [
            'delete' => 'Xoá tạm thời',
        ];
        
        if($status == 'trash'){
            $list_act = [
                'restore' => 'Khôi phục',
                'forceDelete' => 'Xoá vĩnh viễn',
            ];
            $users = User::onlyTrashed()->paginate(10);
        }else{
            $keyword = "";
            if ($request->input('keyword')) {
                $keyword = $request->input('keyword');
            }
            $users = User::where('name','LIKE',"%{$keyword}%")->paginate(10);
        }

        $count_user_active = User::count();
        $count_user_trash = User::onlyTrashed()->count();

        $count = [$count_user_active, $count_user_trash];




        // $keyword = "";
        // if($request->input('keyword')){
        //     $keyword = $request->input('keyword');

        // }
        // $users = User::where('name','LIKE',"%{$keyword}%")->paginate(10);
        //$users = User::withTrashed()->where('name','LIKE',"%{$keyword}%")->paginate(10); //Hiển cả danh sách user đang xoá tạm thời
        //nếu keyword có tồn tại thì ta đưa vào biến $keyword
        //sau đó đưa keyword vào câu lệnh truy vấn

        //dd($users);

        //return $request->input('keyword');
        return view('admin.user.list', compact('users', 'count', 'list_act'));
    }

    function add(Request $request){
        
        return view('admin.user.add');
    }

    function store(Request $request){

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'required' => ':attribute không được để trống',
                'min' => ':attribute có độ dài ít nhất :min ký tự',
                'max' => ':attribute có độ dài nhiều nhất :max ký tự',
            ],
            [
                'name' => 'Tên người dùng',
                'email' => 'Email',
                'password' => 'Mật khẩu',
            ],
        );
        //return $request->all();

        User::create(
            [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]
        );
        //dd(User::all());
        return redirect('admin/user/list')->with('status', 'Đã thêm thành viên thành công');

        //if($request->input('btn-add'))
        
        //return $request->input();
    }

    function delete($id){
        if(Auth::id()!=$id){
            $user = User::find($id);
            $user->delete();

            return redirect('admin/user/list')->with('status', 'Đã xoá thành viên thành công');

        }else{
            return redirect('admin/user/list')->with('status', 'Bạn không thể xoá chính bạn');
        }
    }

    function action(Request $request){

        $list_check = $request -> input('list_check');

        if($list_check){
            //return $request->input('list_check');
            foreach($list_check as $k => $id){
                if (Auth::id() == $id) {
                    unset($list_check[$k]);
                }
            }
            if (!empty($list_check)) {
                $act = $request->input('act');
                if($act == 'delete'){
                    User::destroy($list_check);
                    return redirect('admin/user/list')->with('status', 'Bạn đã khoá thành viên thành công');
                }

                if($act == 'restore'){
                    User::withTrashed()->whereIn('id', $list_check)->restore();                
                    return redirect('admin/user/list')->with('status', 'Bạn đã khôi phục thành viên thành công');
                }

                if($act == 'forceDelete'){
                    User::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect('admin/user/list')->with('status', 'Bạn đã xoá vĩnh viễn tài khoảng này');
                }
            }
            return redirect('admin/user/list')->with('status', 'Bạn không thể thao tác trên tài khoảng của bạn');

        }else{
            return redirect('admin/user/list')->with('status', 'Bạn cần chọn tài khoảng cần thực thi');
        }
    }

    function edit(Request $request, $id){

        $user = User::find($id);
        return view('admin.user.edit', compact('user'));
    }

    function update(Request $request, $id){

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'required' => ':attribute không được để trống',
                'min' => ':attribute có độ dài ít nhất :min ký tự',
                'max' => ':attribute có độ dài nhiều nhất :max ký tự',
            ],
            [
                'name' => 'Tên người dùng',
                'email' => 'Email',
                'password' => 'Mật khẩu',
            ],
        );

        User::where('id', $id)->update([
            'name' => $request->input('name'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect('admin/user/list')->with('status', 'Bạn đã cập nhật thành công');

    }
}
