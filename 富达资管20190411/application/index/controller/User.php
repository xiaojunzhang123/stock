<?php
namespace app\index\controller;

use app\common\payment\authLlpay;
use app\index\logic\OrderLogic;
use app\index\logic\RechargeLogic;
use app\index\logic\RegionLogic;
use app\index\logic\UserNoticeLogic;
use think\Request;
use app\index\logic\UserLogic;
use app\index\logic\BankLogic;
use app\index\logic\StockLogic;
use think\Db;

class User extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }

    public function index()
    {
        $userStatic = $this->_logic->userStatic($this->user_id);
        $this->assign("user", array_merge(uInfo(), $userStatic));
        return view();
    }

    public function setting()
    {
        $this->assign("user", uInfo());
        return view();
    }

    public function optional()
    {
        $stocks = $this->_logic->userOptional($this->user_id);
        if($stocks){
            $codes = array_column($stocks, "code");
            $lists = (new StockLogic())->simpleData($codes);
            array_filter($stocks, function(&$item) use ($lists){
                $item['quotation'] = $lists[$item['code']];
            });
        }
        $this->assign("stocks", $stocks);
        return view();
    }

    public function createOptional()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Optional');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $code = input("post.code/s");
                $stock = (new StockLogic())->stockByCode($code);
                $optionalId = $this->_logic->createUserOptional($this->user_id, $stock);
                if($optionalId > 0){
                    return $this->ok();
                }else{
                    return $this->fail("自选股票添加失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    public function removeOptional()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Optional');
            if(!$validate->scene('remove')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $ids = input("post.ids/a");
                $res = $this->_logic->removeUserOptional($this->user_id, $ids);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("删除失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    public function password()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('password')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "user_id"   => $this->user_id,
                    "password"  => input("post.newPassword/s")
                ];
                $res = $this->_logic->updateUser($data);
                if($res){
                    session("user_info", null);
                    $url = url("index/User/setting");
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("修改失败！");
                }
            }
        }
        return view();
    }

    public function cards()
    {
        $user = $this->_logic->userIncCard($this->user_id);
        $this->assign("user", $user);
        return view();
    }

    public function modifyCard()
    {
        if(request()->isPost()) {
            $validate = \think\Loader::validate('Card');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $res = $this->_logic->saveUserCard($this->user_id, $data);
                if($res){
                    $url = url("index/User/cards");
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("绑定银行卡失败，请稍后重试！");
                }
            }
        }
        $user = $this->_logic->userIncCard($this->user_id);
        $banks = (new BankLogic())->bankLists();
        $_regionLogic = new RegionLogic();
        if($user['has_one_card']){
            $provinces = $_regionLogic->regionByParentId();
            $citys = $_regionLogic->regionByParentId($user['has_one_card']['bank_province']);
        }else{
            $provinces = $_regionLogic->regionByParentId();
            $citys = $_regionLogic->regionByParentId($provinces[0]['id']);
        }
        $callback = input("?get.callback") ? base64_decode(input("get.callback")) : "";
        $this->assign("user", $user);
        $this->assign("banks", $banks);
        $this->assign("provinces", $provinces);
        $this->assign("citys", $citys);
        $this->assign("callback", $callback);
        return view();
    }

    public function recharge()
    {
        $rdee=Db::table('stock_user_cert')
            ->where([
                'userid'  => $this->user_id,
            ])
            ->select();
        if(!$rdee[0]){
            $url = url("/user/certification");//dump($url);die;
            //return $this->ok($url,'请绑定银行卡');
            $this->success('请先实名认证',$url);
            die;
        }
		
		
		if($rdee[0]['status'] == '2'){
            $url = url("/user/certification");
            $this->success('您的资料审核不合格，请您重新上传',$url);
            die;
        }
		
		if($rdee[0]['status'] != '1'){
            $url = url("/user/home");//dump($url);die;
            //return $this->ok($url,'请绑定银行卡');
            $this->success('请等待管理员审核',$url);
            die;
        }
		
        //dump($rdee);die;
        /*$url = url("index/User/index");
        return $this->ok(['url' => $url]);*/

        if(request()->isPost()){
            header("Content-type: text/html; charset=utf-8");
            $validate = \think\Loader::validate('Recharge');
            if(!$validate->scene('do')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $amount = input("post.amount");
                $type = input("post.type", 1);
                $way = $type == 1 ? 2 : 1; //支付通道，2-连连支付
                // 生成订单
                $orderSn = (new RechargeLogic())->createRechargeOrder($this->user_id, $amount, $way);
                if($orderSn){
                    if($way == 2){
                        // 连连支付
                        $user = $this->_logic->userIncCard($this->user_id);
                        if($user['has_one_card']){
                            // 请求支付
                            $card = $user['has_one_card'];
                            $risk = [
                                "frms_ware_category" => "2026",
                                "user_info_mercht_userno" => $this->user_id,
                                "user_info_bind_phone"  => $user['mobile'],
                                "user_info_dt_register" => date("YmdHis", $user['create_at']),
                                "goods_name"    => "58好策略余额充值",
                                "user_info_full_name" => $card['bank_user'],
                                "user_info_id_no" => $card['id_card'],
                                "user_info_identify_state" => "1",
                                "user_info_identify_type" => "1"
                            ];
                            $html = (new authLlpay())->getCode($this->user_id, $orderSn, $amount, $card, $risk);
                            echo $html;
                            exit;
                        }else{
                            return $this->fail("请先绑定银行卡！");
                        }
                    }
                }else{
                    return $this->fail("充值订单创建失败！");
                }
            }
        }
        $user = $this->_logic->userIncCard($this->user_id);
        $bind = $user['has_one_card'] ? 1 : 0;
        $callback = url("index/User/recharge");
        $redirect = url("index/User/modifyCard", ["callback" => base64_encode($callback)]);
        $this->assign("bind", $bind);
		$this->assign('uid',$this->user_id);
        $this->assign("redirect", $redirect);
        return view();
    }

    public function withdraw()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Withdraw');
            if(!$validate->scene('do')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $money = input("post.money/f");
                $user = $this->_logic->userIncCard($this->user_id);
                $remark = [
                    "bank" => $user['has_one_card']['bank_name'],
                    "card" => $user['has_one_card']['bank_card'],
                    "name" => $user['has_one_card']['bank_user'],
                    "addr" => $user['has_one_card']['bank_address'],
                ];
                $withdrawId = $this->_logic->createUserWithdraw($this->user_id, $money, $remark);
                if($withdrawId > 0){
                    $url = url("index/User/index");
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("提现申请失败！");
                }
            }
        }
        $user = $this->_logic->userIncCard($this->user_id);
        $bind = $user['has_one_card'] ? 1 : 0;
        $callback = url("index/User/withdraw");
        $redirect = url("index/User/modifyCard", ["callback" => base64_encode($callback)]);
        $this->assign("bind", $bind);
        $this->assign("user", $user);
        $this->assign("redirect", $redirect);
        return view();
    }


    public function record($type = null)
    {
        $user = uInfo();
        $record = $this->_logic->pageUserRecords($this->user_id, $type);

        if($record['data']){
            $list = $record['data'];
            $last_page = $record['last_page'];
            $current_page = $record['current_page'];
        }else{
            $list = [];
            $last_page = 1;
            $current_page = 1;
        }
        if(request()->isPost()){
            foreach ($list as $k => $v)
            {
                $list[$k]['create_at'] = date('Y-m-d H:i', $v['create_at']);
            }
            $response = ["lists" => $list, "total_page" => $last_page, "current_page" => $current_page];
            return $this->ok($response);
        }
        $this->assign("type", isset($type) ? $type : '-1');
        $this->assign("user", $user);
        $this->assign("records", $list);
        $this->assign("totalPage", $last_page);
        $this->assign("currentPage", $current_page);
        return view();
    }

    public function noticeLists()
    {
        $userNoticeLogic = new UserNoticeLogic();
        $lists = $userNoticeLogic->getAllByUid($this->user_id);
        $this->assign('lists', $lists);
        return view();
    }
    public function noticeDetail()
    {
        $id = input('id/d');

        if($id <= 0) return $this->redirect('index/User/noticeLists');
        $userNoticeLogic = new UserNoticeLogic();
        $content = $userNoticeLogic->getContentById($this->user_id);
        $userNoticeLogic->updateBy(['id' => $id, 'read' => 2]);
        $this->assign('content', $content);
        return view();
    }
    public function avatar()
    {
        if(request()->isPost()){
            $file = request()->file('avatar');
            if(empty($file)) return $this->fail('系统提示:非法操作');
            $path = './upload/avatar/';
            $res = $file->move($path, 'user_id_'.$this->user_id.'.png');
            if($res){
                $file_name = $res->getFilename();
                $path = trim($path, '.');
                $ret = $this->_logic->updateUser(['user_id' => $this->user_id, 'face' => $path.$file_name]);
                if($ret)
                {
                    return $this->ok(['avatar' => $path.$file_name]);
                }
                return $this->fail('系统提示:头像上传失败');

            }else{
                return $this->fail($file->getError());
            }
        }

    }
    public function nickEdit()
    {
        if(request()->isPost()){
            $data = input('post.');
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('update_nick')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $updateArr = ['user_id' => $this->user_id];
                isset($data['nickname']) ? $updateArr['nickname'] = $data['nickname'] : '';
                $userLogic = new UserLogic();
                if($userLogic->updateUser($updateArr))
                {
                    return $this->ok();
                }
            }

            return $this->fail('系统提示:操作失败');
        }
    }

    public function certification()
    {   
	    $rdeee=Db::table('stock_user_cert')
            ->where([
                'userid'  => $this->user_id,
            ])
            ->select();
       
		if($rdeee[0]['status'] == '0'){
            $url = url("/user/home");
            $this->success('您的资料正在审核，请耐心等待！',$url);
            die;
        }
		 
        $rdee=Db::table('stock_user_cert')
            ->where([
                'userid'  => $this->user_id,
            ])
            ->select();
        //dump($rdee);die;
        $this->assign('id', $this->user_id);
        return view();
    }
    //插表登记身份证信息
    public function cert_insert()
    { 
            $rdeee=Db::table('stock_user_cert')
            ->where([
                'userid'  => $this->user_id,
            ])
            ->select();
			$data = input("post.");
			if($rdeee[0]){
			  /*$res = Db::table('stock_user_cert')
                   ->where('id', $this->user_id)
                   ->data($data)
                   ->update();*/
			  $where['userid'] = $this->user_id;
              $res = model("User_cert")->save($data,$where);
				
			}else{
                //$res = $this->_logic->saveUserCert($this->user_id, $data);
               $res = model("User_cert")->save($data);
			}
            //$data = input("post.");
            /*$data['userid'] = 1;
            $data['frontcard'] = 1;
            $data['backcard'] = 1;
            $data['realname'] = 1;
            $data['cardnum'] = 1;*/
            //$data = input("post.");
                //$res = $this->_logic->saveUserCert($this->user_id, $data);
              // $res = model("User_cert")->save($data);
               //return $res ? model("User_cert")->getLastInsID() : 0;
                if($res){ 
                    return $this->ok(['cert' => $res]);
                }else{
                    return $this->fail("请稍后重试！");
                }
                die;
         
    }

    //上传身份证正面和反面
    public function cert_update()
    { 
        if(request()->isPost()){
            $file = request()->file('cert');
            if(empty($file)) return $this->fail('系统提示:非法操作');
            $path = './upload/card/';
            $res = $file->move($path, 'card_'.time().'.png');
            if($res){
                $file_name = $res->getFilename();
                $path = trim($path, '.');
                //$ret = $this->_logic->updateUser(['user_id' => $this->user_id, 'face' => $path.$file_name]);
                 
                    return $this->ok(['avatar' => $path.$file_name]);
                 
            }else{
                return $this->fail($file->getError());
            }
        } 
    }

}