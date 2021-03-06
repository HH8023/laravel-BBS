<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
//use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;
use App\Http\Requests\Api\UserRequest;

class UsersController extends Controller
{
    //
    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);

        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }

        //对比验证码是否与缓存中一致，其中使用hash_equals()函数，此函数可防止时序攻击的字符串比较。
        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        //清楚验证码缓存
        \Cache::forget($request->verification_key);

        return $this->response->created();
    }
}
