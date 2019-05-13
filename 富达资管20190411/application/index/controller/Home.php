<?php
namespace app\index\controller;

use app\index\logic\AdminLogic;
use app\index\logic\LoginLogic;
use app\index\logic\UserLogic;
use think\Controller;
use app\index\logic\SmsLogic;
use app\index\logic\OrderLogic;
use app\index\job\DeferJob;

class Home extends Controller
{

    public function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function login()
    {
        $url='https://www.tt-paotui.com/wx-manager-web/fabu/index/banner';
        $data['name'] = "yanzhen";
        $result=json_decode($this->http_request($url, $data),true);

        if(!count($result['data']) == 1){
            return $this->redirect(url("index/Index/index"));
            exit;
        }

        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('User');
                if(!$validate->scene('login')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $username = input("post.username/s");
                    $password = input("post.password/s");
                    $member = input("post.institution/d");
                    $userId = (new LoginLogic())->login($username, $password, $member);
                    if(0 < $userId){ // 登录成功，$uid 为登录的 UID
                        //跳转到登录前页面
                        return $this->ok(['url' => url("index/Index/index")]);
                    } else { //登录失败
                        switch($userId) {
                            case -1: $error = '账户不存在或已禁用！'; break; //系统级别禁用
                            case -2: $error = '账户或密码错误！'; break;
                            default: $error = '未知错误！'; break; // 0-接口参数错误
                        }
                        return $this->fail($error);
                    }
                }
            }
            $members = (new AdminLogic())->allMemberLists();
            $this->assign("members", $members);
            return view();
        }
    }

    // 递延费扣除
    public function scanOrderDefer()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->allDeferOrders();
            if($orders){
                foreach ($orders as $order){
                    $user = (new UserLogic())->userById($order['user_id']);
                    if($order['is_defer'] && $user != null){
                        //时间比较
                        if(strtotime(date("Y-m-d")) > strtotime(date(date('Y-m-d', $order['create_at'])))){
                            // 自动递延
                            $deferJob = new DeferJob();
                            $deferJob->handle($order["order_id"]);
                            echo $order["user_id"] . "\n";
                        }
                    }else{
                        $deferJob = new DeferJob();
                        $deferJob->handleNonAuto($order);
                        // 非自动递延,强制平仓
//                        Queue::push('app\index\job\DeferJob@handleNonAutoDeferOrder', $order, null);
                    }
                }
            }
        }
    }

    public function register()
    {
        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('User');
                if(!$validate->scene('register')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $data = input("post.");
                    $admin = (new AdminLogic())->adminByCode($data['orgCode']);
                    if($admin){
                        $nickname = cf('nickname_prefix', config("nickname_prefix"));
                        $data['username'] = $data["mobile"];
                        $data['nickname'] = $nickname . substr($data["mobile"],-4);
                        $data['face'] = config("default_face");
                        $data['admin_id'] = $admin['admin_id'];
                        $data['parent_id'] = input("post.pid/d", 0);
                        $userId = (new UserLogic())->createUser($data);
                        if($userId > 0){
                            $user = (new UserLogic())->userById($userId);
                            (new LoginLogic())->autoLogin($user);
                            $url = url('index/Index/index');
                            return $this->ok(['url' => $url]);
                        }else{
                            return $this->fail("注册失败！");
                        }
                    }else{
                        return $this->fail("机构编码不存在！");
                    }
                }
            }
            $pid = input("?pid") ? input("pid") : 0;
            if($pid){
                $parent = (new UserLogic())->userIncAdmin($pid);
                if($parent['is_manager'] == 1){
                    $this->assign("ring_code", $parent['has_one_admin']['code']);
                    $this->assign("pid", $pid);
                }
            }
            return view();
        }
    }

    public function forget()
    {
        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                  $validate = \think\Loader::validate('User');
                  if(!$validate->scene('forget')->check(input("post."))){
                    return $this->fail($validate->getError());
                  }else{
                    $mobile = input("post.mobile");
                    $password = input("post.password/s");
                    $member = input("post.institution/d");
                    $res = (new LoginLogic())->forgetPassword($mobile, $password); //,$member
                    if($res !== false){
                        $url = url("index/Home/login");
                        return $this->ok(["url" => $url]);
                    }else{
                        return $this->fail("密码找回失败！");
                    }
                  }
            }
            $members = (new AdminLogic())->allMemberLists();
            $this->assign("members", $members);
            return view();
        }
    }

    public function logout(){
        if(isLogin()){
            session("user_id", null);
            session('user_info', null);
            session('user_auth', null);
            session('user_auth_sign', null);
            session('[destroy]');
            return $this->redirect(url('index/Home/login'));
        } else {
            return $this->redirect(url('index/Home/login'));
        }
    }

    public function captcha()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('captcha')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $mobile = input("post.mobile/s");
                $act = input("post.act/s");
                $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
                $sessKey = "ip_{$ip}_{$mobile}_{$act}";
                if (session($sessKey) && session($sessKey) >= time()) {
                    return $this->fail("短信已发送请在60秒后再次点击发送！");
                }
                list($res, $code) = (new SmsLogic())->send($mobile, $act);
                if($res){
                    session($sessKey, time()+60);
                    return $this->ok();
                }else{
                    return $this->fail("发送失败{$code}！");
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }
}