<?php
namespace app\index\logic;

use app\index\model\Admin;
use app\index\model\User;
use app\index\model\UserCert;

class LoginLogic
{
    public function login($username, $password, $memberAdminId)
    {
        $ringAdminIds = Admin::where(["pid" => $memberAdminId])->column("admin_id");
        array_push($ringAdminIds, $memberAdminId);
        $map = [];
        $map['username'] = $username;
        $map['state'] = 0;
        //$map['admin_id'] = ["IN", $ringAdminIds];

        /* 获取用户数据 */
        $user = User::where($map)->find();
        if($user){

            /* 验证用户密码 */
            $user = $user->toArray();
            $map2['userid'] = $user['user_id'];

            
            if(spComparePassword($password, $user['password'])){
                //登录成功
                $userId = $user['user_id'];
                // 更新登录信息
                $this->autoLogin($user);
                return $userId ; //登录成功，返回用户UID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在
        }
    }

    public function autoLogin($user)
    {
        $auth = [
            'user_id'  => $user['user_id'],
            'username' => $user['username'],
            'type' => $user['type'],
        ];
        session("user_id", $user['user_id']);
        session("type", $user['type']);
        session('user_info', $user);
        session('user_auth', $auth);
        session('user_auth_sign', dataAuthSign($auth));
    }

    public function forgetPassword($mobile, $password, $memberAdminId)
    {
        $ringAdminIds = Admin::where(["pid" => $memberAdminId])->column("admin_id");
        array_push($ringAdminIds, $memberAdminId);
        $map = [];
        $map['mobile'] = $mobile;
        $map['admin_id'] = ["IN", $ringAdminIds];
        return User::where($map)->update(["password" => spPassword($password)]);
    }
}