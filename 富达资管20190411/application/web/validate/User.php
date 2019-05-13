<?php
namespace app\web\validate;

use app\web\logic\SmsLogic;
use app\web\logic\UserLogic;
use think\Validate;
use app\web\model\Admin;

class User extends Validate
{
    protected $rule = [
        'oldPassword' => "require|checkOldPassword",
        'username'  => 'require',
        'nickname'  => 'require',
        'parent_id' => 'number|checkParentId',
        'orgCode'   => 'require|checkOrgCode',
        'mobile'    => 'require|regex:/^[1][3,4,5,7,8][0-9]{9}$/|checkMobile',
        'password'	=> 'require|length:6,16',
        'rePassword' => 'confirm:password',
        'code'      => 'require|checkCode',
        'institution' => 'require|checkInstitution',
        'newPassword'	=> 'require|length:6,16',
        'reNewPassword' => 'confirm:newPassword',
        'act'       => "checkAct"
    ];

    protected $message = [
        'oldPassword.require' => '旧密码不能为空！',
        'oldPassword.checkOldPassword' => '旧密码输入错误！',
        'parent_id.number'  => '系统提示：非法操作！',
        'parent_id.checkParentId' => '系统提示：非法操作！',
        'orgCode.require'   => '机构编码不能为空！',
        'orgCode.checkOrgCode' => '机构编码填写错误！',
        'mobile.require'    => '手机号码不能为空！',
        'mobile.regex'      => '手机号码格式错误！',
        'mobile.checkMobile' => '手机号码已注册！',
        'mobile.checkMobileExist' => '手机号码不存在！',
        'code.require'      => '短信验证码不能为空！',
        'code.checkCode'    => '短信验证码错误！',
        'code.checkForgetCode'    => '短信验证码错误！',
        'password.require'  => '密码不能为空！',
        'password.length'   => '密码为6-16位字符！',
        'rePassword.confirm' => '俩次输入密码不一致！',
        'act.checkAct'      => '系统提示：非法操作！',
        'username.require'  => '用户名不能为空！',
        'nickname.require' => '系统提示:昵称不能为空',
        'institution.require' => '请选择机构！',
        'institution.checkInstitution' => '机构不正确！',
        'newPassword.require'   => '新密码不能为空！',
        'newPassword.length'    => '新密码为6-16位字符！',
        'reNewPassword.confirm' => '俩次输入密码不一致！',
    ];

    protected $scene = [
        'register'  => ['orgCode', 'mobile', 'password', 'rePassword', 'code'],
        'captcha'   => [
            'mobile' => 'require|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'act',
        ],
        'login'     => ['username', 'password', 'institution'],
        'password'  => ['oldPassword', 'newPassword', 'reNewPassword'],
        'forget'    => [
            'mobile' => 'require|regex:/^[1][3,4,5,7,8][0-9]{9}$/|checkMobileExist',
            'code'   => 'require|checkForgetCode',
            'password',
            'institution',
        ],
        'update_nick' => ['nickname'],
    ];

    protected function checkOrgCode($value)
    {
        $_where = [
            "code"  => $value,
            "role"  => Admin::RING_ROLE_ID
        ];
        $admin = Admin::where($_where)->find();
        return $admin ? true : false;
    }

    protected function checkMobile($value, $rule, $data)
    {
        $ring = Admin::where(["code" => $data['orgCode']])->find();
        $ringAdminIds = Admin::where(["pid" => $ring['pid']])->column("admin_id");
        array_push($ringAdminIds, $ring['pid']);
        $user = \app\web\model\User::where(["username" => $value, "admin_id" => ["IN", $ringAdminIds]])->find();
        return $user ? false : true;
    }

    protected function checkMobileExist($value, $rule, $data)
    {
        $ringAdminIds = Admin::where(["pid" => $data['institution']])->column("admin_id");
        array_push($ringAdminIds, $data['institution']);
        $user = \app\web\model\User::where(["mobile" => $value, "admin_id" => ["IN", $ringAdminIds]])->find();
        return $user ? true : false;
    }

    protected function checkCode($value, $rule, $data)
    {
        $mobile = $data['mobile'];
        return (new SmsLogic())->verify($mobile, $value, "register");
    }

    protected function checkForgetCode($value, $rule, $data)
    {
        $mobile = $data['mobile'];
        return (new SmsLogic())->verify($mobile, $value, "forget");
    }

    protected function checkInstitution($value)
    {
        $_where = [
            "admin_id" => $value,
            "role"  => Admin::MEMBER_ROLE_ID
        ];
        $admin = Admin::where($_where)->find();
        return $admin ? true : false;
    }

    protected function checkOldPassword($value)
    {
        return spComparePassword($value, uInfo()['password']);
    }

    protected function checkAct($value)
    {
        $_array = ['register', 'forget', 'withdraw', 'manager'];
        return in_array($value, $_array);
    }

    protected function checkParentId($value)
    {
        if($value){
            $parent = (new UserLogic())->userById($value);
            if($parent && $parent['is_manager'] == 1){
                return true;
            }
            return false;
        }
        return true;
    }
}