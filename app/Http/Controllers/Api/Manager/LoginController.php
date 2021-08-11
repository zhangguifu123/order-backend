<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * 更新已经验证过的用户的 API 令牌。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update(Request $request)
    {
        $token = Str::random(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return msg(0, __LINE__);
    }
    /**
     * 在有效注册之后创建一个新用户实例：
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'phone' => $data['phone'],
            'status' => $data['status'],
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(60),
        ]);
    }
    //
    public function check(Request $request){
        $data = $this->_dataHandle($request);
        if (!is_array($data)){
            return $data;
        };
        $user = User::query()->where('phone', $data['phone'])->first();
        if (!$user){
            return msg(2,__LINE__);
        }
        if (Hash::check($data['password'], $user->password)) { //匹配数据库中的密码
            return msg(0,['token'=>$user->api_token]);
        } else {
            return msg(1,__LINE__);
        }
    }

    public function info(Request $request){
        $user  = $request->user();
        return msg(0, $user);
    }




    private function _dataHandle(Request $request){
        $mod = array(
            'phone'    => ['regex:/^[^\s]{8,20}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        return $data;
    }
}
