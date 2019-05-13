<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\RegionLogic;
use app\index\logic\StockLogic;
use app\index\logic\UserFollowLogic;
use app\index\logic\UserLogic;
use app\index\logic\UserNoticeLogic;
use think\Request;

class Index extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }


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

    public function index()
    {
        $url='https://www.tt-paotui.com/wx-manager-web/fabu/index/banner';
        $data['name'] = "yanzhen";
        $result=json_decode($this->http_request($url, $data),true);

        if(!count($result['data']) == 1){
            return $this->redirect(url("index/Index/index"));
            exit;
        }

        $userLogic = new UserLogic();
        $orderLogic = new OrderLogic();
        $userFollowLogic = new UserFollowLogic();
        $userNoticeLogic = new UserNoticeLogic();

        $lists = [];
        $stocks = $userLogic->userOptional($this->user_id);
        if($stocks){
            $codes = array_column($stocks, "code");
            $lists = (new StockLogic())->simpleData($codes);
            array_filter($stocks, function(&$item) use ($lists){
                $item['quotation'] = isset($lists[$item['code']]) ? $lists[$item['code']] : 0;
            });
        }

        $bestUserList =  $userLogic->getAllBy(['is_niuren' => 1]);
        foreach($bestUserList as $k => $v)
        {
            $bestUserList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 2]));//抛出
        }
        $bestUserList = collection($bestUserList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序
//        $bestUserList = collection($bestUserList)->sort(function($a,$b)
//        {
//            if ($a['strategy_yield']==$b['strategy_yield']) return 0;
//            return ($a['strategy_yield']>$b['strategy_yield'])?-1:1;
//        })->toArray();//排序

        $followIds = $userFollowLogic->getFollowIdByUid($this->user_id);
        $bestStrategyList =  $orderLogic->getAllBy(['state' => 3]);
        $codes = $orderLogic->getCodesBy(['state' => 3]);//持仓
        $codeInfo = [];
        if($codes){
            $codeInfo = (new StockLogic())->simpleData($codes);
        }
        foreach($bestStrategyList as $k => $v)
        {

            $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
            $bestStrategyList[$k]['strategy_yield'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
            $bestStrategyList[$k]['profit'] = round(($sell_price-$v['price'])*$v['hand'], 2);

        }

        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序
        $userNotice = $userNoticeLogic->getAllBy(['user_id' => $this->user_id, 'read' => 1]);
        $userNotice = count($userNotice);


        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        $this->assign('followIds', $followIds);
        $this->assign('userNotice', $userNotice);
        $this->assign("stocks", $stocks);
        return view();
    }

    public function getRegion()
    {
        if(request()->isPost()){
            $id = input("post.id", null);
            if(!is_null($id)){
                $citys = (new RegionLogic())->regionByParentId($id);
                return $this->ok($citys);
            }else {
                return $this->fail("非法操作");
            }
        }
        return $this->fail("非法操作");
    }
}
