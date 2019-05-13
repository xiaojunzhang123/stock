<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Queue;
use app\index\logic\OrderLogic;
use app\index\logic\UserLogic;
class Cron extends Controller
{
    // 抓取板块行情指数
    public function grabPlateIndex()
    {
        set_time_limit(0);
        // if(checkStockTradeTime()){  //这里判断交易时间，没啥意义，取接口返回的最后值就是对的
        $jsonArray = [];
        $jsonPath = "./plate.json";
        $url = 'http://hq.sinajs.cn/rn=1520407404627&list=s_sh000001,s_sz399001,s_sz399006';
        $html = file_get_contents($url);
        $html = str_replace(["\r\n", "\n", "\r", " "], "", $html);
        $plates = explode(';', $html);
        if($plates){
            foreach ($plates as $plate){
                if($plate){
                    $plate = iconv("GB2312", "UTF-8", $plate);
                    preg_match('/^varhq_str_s_([sh|sz]{2})(\d{6})="(.*)"/i', $plate, $match);
                    if($match[3]){
                        $_data = explode(",", $match[3]);
                        $jsonArray[] = [
                            "plate_name" => $_data[0],
                            "last_px"   => $_data[1],
                            "px_change" => $_data[2],
                            "px_change_rate" => $_data[3]
                        ];
                    }
                }
            }
        }
        if($jsonArray){
            file_put_contents($jsonPath, json_encode($jsonArray, JSON_UNESCAPED_UNICODE));
            echo "ok";
        }
        //}
    }


    public function grabStockpx(){
        set_time_limit(0);
        $jsonArray = [];
        $num = 10;
        $data = [];
        $jsonPath = './px.json';
        $url = 'http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?page=1&num='.$num.'&sort=changepercent&asc=0&node=hs_a&symbol=&_s_r_a=init';
        $html = file_get_contents($url);
        //$p = '@code:"(\d{6})",name:"([^"]*)",trade:"([\d\.]*)",pricechange:"([\d\.]*)",changepercent:"([\d\.]*)"@is';
        $p = '@code:"(\d{6})",name:"([^"]*)",trade:"([\d\.]*)",pricechange:"([\d\.]*)",changepercent:"([\d\.]*)"@is';

        preg_match_all($p, $html, $m);
        $temp = [];
        if (!empty($m[1])) {
            $len = count($m[1]);
            for($i=0;$i<$len;$i++){
                $temp[]  = array('code' => $m[1][$i],
                    'name' => iconv("GB2312", "UTF-8", $m[2][$i]),
                    'trade' => $m[3][$i],
                    'changepercent' => $m[5][$i]
                );
            }
        }
        $data['zfpx'] = $temp;
        $url = 'http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?page=1&num='.$num.'&sort=changepercent&asc=1&node=hs_a&symbol=&_s_r_a=init';
        //http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?page=1&num=40&sort=changepercent&asc=1&node=hs_a&symbol=&_s_r_a=init
        $html = file_get_contents($url);
        $p = '@code:"(\d{6})",name:"([^"]*)",trade:"([\d\.]*)",pricechange:"([\-\d\.]*)",changepercent:"([\-\d\.]*)"@is';

        preg_match_all($p, $html, $m);
        $temp = [];
        if (!empty($m[1])) {
            $len = count($m[1]);
            for($i=0;$i<$len;$i++){
                $temp[]  = array('code' => $m[1][$i],
                    'name' => iconv("GB2312", "UTF-8", $m[2][$i]),
                    'trade' => $m[3][$i],
                    'changepercent' => $m[5][$i]
                );
            }
        }
        $data['dfpx'] = $temp;

        if ($data) {
            @file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE));
            echo "ok";
        }
    }
    // 半小时 股票列表
    public function grabStockLists()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $_arrays = [];
            $_jsTextIndex = 0;
            $_jsTextArrays = [];
            $_jsText = "var stocks=new Array();";
            $_jsPath = "./static/js/stock.js";
            $url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,1,80]]&callback=FDC_DC.theTableData';
            $html = file_get_contents($url);
            $json = substr($html, 70, -3);
            $array = json_decode($json, true);
            $total = $array["count"];
            $count = ceil($total / 80);
            foreach ($array['items'] as $item){
                $_arrays[] = [
                    "full_code"	=> $item[0],
                    "code"  => $item[1],
                    "name"  => str_replace(' ', '', $item[2]),
                ];
                $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $item[1] . "','" . str_replace(' ', '', $item[2]) . "','" . $item[0] . "'); ";
                $_jsTextIndex++;
            }
            for ($i = 2; $i <= $count; $i++){
                $_url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,'.$i.',80]]&callback=FDC_DC.theTableData';
                $_html = file_get_contents($_url);
                $_json = substr($_html, 70, -3);
                $_array = json_decode($_json, true);
                foreach ($_array['items'] as $_item){
                    $_arrays[] = [
                        "full_code"	=> $_item[0],
                        "code"  => $_item[1],
                        "name"  => str_replace(' ', '', $_item[2]),
                    ];
                    $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $_item[1] . "','" . str_replace(' ', '', $_item[2]) . "','" . $_item[0] . "'); ";
                    $_jsTextIndex++;
                }
            }
            $_jsText .= implode('', $_jsTextArrays);
            Db::startTrans();
            try{
                model("Lists")->query("truncate table stock_list");
                model("Lists")->saveAll($_arrays);
                @file_put_contents($_jsPath, $_jsText);
                // 提交事务
                Db::commit();
                echo "ok";
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                echo "false";
            }
        }
    }

    // 递延费扣除
    public function scanOrderDefer()
    {
//        set_time_limit(0);
//        if(!checkStockTradeTime()){
//            $orders = (new OrderLogic())->allDeferOrders();
//
//            if($orders){
//                foreach ($orders as $order){
//                    $user = (new UserLogic())->userById($order['user_id']);
//                    if($order['is_defer'] && $user != null && $user['mobile'] == "17364001377"){
//                        // 自动递延
//                        Queue::push('app\index\job\DeferJob@handleDeferOrder', $order["order_id"], 'handleDeferOrder');
//                    }else{
//                        // 非自动递延,强制平仓
//                        Queue::push('app\index\job\DeferJob@handleNonAutoDeferOrder', $order, null);
//                    }
//                }
//            }
//        }
    }

    // 爆仓、止盈、止损，交易时间段、每2秒一次
    public function scanOrderSell()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->orderByState($state = 3);
            if($orders){
                foreach ($orders as $order){
                    /*$sellData = [
                        "order_id"  => $order["order_id"], //订单ID
                        "code"      => $order["code"], // 股票code
                        "price"     => $order["price"], // 买入价
                        "hand"      => $order["hand"], // 买入手数
                        "stop_profit" => $order["stop_profit_price"], // 止盈
                        "stop_loss" => $order["stop_loss_price"], // 止损
                        "deposit"   => $order["deposit"], // 保证金
                    ];*/
                    //Queue::push('app\index\job\SellJob@handleSellOrder', $sellData, null);
                    Queue::push('app\index\job\SellJob@handleSellOrder', $order["order_id"], null);
                }
            }
        }
    }

    // 盈利牛人返点-每天停盘后的时间段 16-23点
    public function handleNiurenRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayNiurenRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    if($order['is_follow'] == 1){
                        // 跟买
                        $followData = [
                            "money" => $order["profit"], //盈利额
                            "order_id" => $order["order_id"], //订单ID
                            "follow_id" => $order["follow_id"] //跟买订单ID
                        ];
                        Queue::push('app\index\job\RebateJob@handleFollowOrder', $followData, null);
                    }
                }
            }
        }
    }

    // 盈利代理商返点-每天停盘后的时间段 16-23点
    public function handleProxyRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayProxyRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    $rebateData = [
                        "money" => $order["profit"] * $order['belongs_to_mode']['point'] / 100, //系统抽成金额
                        "order_id" => $order["order_id"], //订单ID
                        "user_id" => $order["user_id"],
                    ];
                    Queue::push('app\index\job\RebateJob@handleProxyRebate', $rebateData, null);
                }
            }
        }
    }

    // 建仓费返点-每天停盘后的时间段 16-23点
    public function handleJiancangRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayJiancangRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    $rebateData = [
                        "money" => $order["jiancang_fee"],
                        "order_id" => $order["order_id"], //订单ID
                        "user_id" => $order["user_id"]
                    ];
                    Queue::push('app\index\job\RebateJob@handleJiancangRebate', $rebateData, null);
                }
            }
        }
    }
}