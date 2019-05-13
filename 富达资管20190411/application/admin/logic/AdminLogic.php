<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\Role;

class AdminLogic
{
    public function pageRoleLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Role::paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function allEnableRoles()
    {
        $lists = Role::where(['show' => 1])->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function allTeamRoles()
    {
        $adminRoles = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        return Role::where(["id" => ["IN", $adminRoles]])->column("name", "id");
    }

    // 不包括组织架构的角色列表
    public function allAdminRoles()
    {
        $adminRoles = [
            Admin::ADMIN_ROLE_ID,
            Admin::SERVICE_ROLE_ID,
            Admin::FINANCE_ROLE_ID,
        ];
        $where = [
            "id"    => ["IN", $adminRoles],
            "show"  => 1
        ];
        $lists = Role::where($where)->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function allRoles()
    {
        $lists = Role::select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function roleCreate($data)
    {
        $res = Role::create($data);
        return $res ? $res->id : 0;
    }

    public function roleDelete($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Role::destroy($ids);
    }

    public function roleById($id)
    {
        $role = Role::find($id);
        return $role ? $role->toArray() : [];
    }

    public function roleUpdate($data)
    {
        return Role::update($data);
    }

    public function pageAdminLists($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = ["LIKE", "%{$filter['username']}%"];
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $where["nickname"] = ["LIKE", "%{$filter['nickname']}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $where["mobile"] = $filter['mobile'];
        }
        // 所属角色
        if(isset($filter['role']) && !empty($filter['role'])){
            $where["role"] = $filter['role'];
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with("hasOneRole")->where($where)->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    // 不包括组织架构的后台用户列表
    public function pageAdmins($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = ["LIKE", "%{$filter['username']}%"];
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $where["nickname"] = ["LIKE", "%{$filter['nickname']}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $where["mobile"] = $filter['mobile'];
        }
        // 所属角色
        if(isset($filter['role']) && !empty($filter['role'])){
            $where["role"] = $filter['role'];
        }else{
            $adminRoles = [
                Admin::ADMIN_ROLE_ID,
                Admin::SERVICE_ROLE_ID,
                Admin::FINANCE_ROLE_ID,
            ];
            $where['role'] = ["IN", $adminRoles];
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with("hasOneRole")->where($where)->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function getAdminPid()
    {
        if(manager()['admin_id'] == Admin::ADMINISTRATOR_ID){
            return 0;
        }else{
            if(in_array(manager()['role'], [Admin::SERVICE_ROLE_ID, Admin::FINANCE_ROLE_ID])){
                return 0;
            }else{
                return manager()['admin_id'];
            }
        }
    }

    public function getAdminCode($roleId = null)
    {
        $allCodes = Admin::column("code");
        while (true){
            if($roleId == Admin::RING_ROLE_ID){
                // 微圈
                // $length = rand(4, 6);
                $length = 6;
                $code = randomString($length, true);
            }else{
                $code = randomString("8");
            }
            if(!in_array($code, $allCodes)){
                break;
            }
        }
        return $code;
    }

    public function adminCreate($data)
    {
        $res = Admin::create($data);
        $pk = model("Admin")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function adminById($id)
    {
        $admin = Admin::find($id);
        return $admin ? $admin->toArray() : [];
    }

    public function adminUpdate($data)
    {
        return Admin::update($data);
    }

    public function adminDelete($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Admin::destroy($ids);
    }

    //是否代理商
    public function isProxy($roleId){
        $proxyRoleIds = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        return in_array($roleId, $proxyRoleIds);
    }

    public function pageTeamLists($role = "settle", $filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        switch ($role){
            case "settle": //结算中心
                $where['role'] = Admin::SETTLE_ROLE_ID;
                break;
            case "operate": //运营中心
                $where['role'] = Admin::OPERATE_ROLE_ID;
                break;
            case "member": //微会员
                $where['role'] = Admin::MEMBER_ROLE_ID;
                break;
            case "ring": //微圈
                $where['role'] = Admin::RING_ROLE_ID;
                break;
        }
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = ["LIKE", "%{$filter['username']}%"];
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $where["nickname"] = ["LIKE", "%{$filter['nickname']}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $where["mobile"] = trim($filter['mobile']);
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        // 上级结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['settle']}%"],
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        // 上级运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['operate']}%"],
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        // 上级微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['member']}%"],
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with(
                    [
                        "hasOneParent" => function($query){
                            $query->field("password", true);
                        }
                    ])
                    ->field("password", true)
                    ->where($where)
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function teamAdminById($id, $role="settle")
    {
        $where['admin_id'] = $id;
        switch ($role){
            case "settle": //结算中心
                $where['role'] = Admin::SETTLE_ROLE_ID;
                break;
            case "operate": //运营中心
                $where['role'] = Admin::OPERATE_ROLE_ID;
                break;
            case "member": //微会员
                $where['role'] = Admin::MEMBER_ROLE_ID;
                break;
            case "ring": //微圈
                $where['role'] = Admin::RING_ROLE_ID;
                break;
        }
        $admin = Admin::where($where)->find();
        return $admin ? $admin->toArray() : [];
    }

    public function teamAdminsByRole($role="settle")
    {
        $where = Admin::manager();
        $where['status'] = 0;
        switch ($role){
            case "settle": //结算中心
                $where['role'] = Admin::SETTLE_ROLE_ID;
                break;
            case "operate": //运营中心
                $where['role'] = Admin::OPERATE_ROLE_ID;
                break;
            case "member": //微会员
                $where['role'] = Admin::MEMBER_ROLE_ID;
                break;
            case "ring": //微圈
                $where['role'] = Admin::RING_ROLE_ID;
                break;
        }
        $admins = Admin::where($where)->select();
        return $admins ? collection($admins)->toArray() : [];
    }

    public function memberWechat($id)
    {
        $wechat = Admin::with("hasOneWechat")->field("password", true)->find($id);
        return $wechat ? $wechat->toArray() : [];
    }

    public function saveRingWechat($adminId, $data)
    {
        $admin = Admin::get($adminId);
        if($admin->hasOneWechat){
            return $admin->hasOneWechat->save($data);
        }else{
            return $admin->hasOneWechat()->save($data);
        }
    }

    public function depositRecharge($admin_id, $money)
    {
        return Admin::where(['admin_id' => $admin_id])->setInc('deposit', $money);
    }
}