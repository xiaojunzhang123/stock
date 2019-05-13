<?php
namespace app\common\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\logic\OrderLogic;
use app\index\model\Order;
use app\index\logic\StockLogic;
use app\index\logic\UserLogic;

use think\Db;
class Test extends Command
{
    protected $_logic;
    protected $_userLogic;

    // 配置定时器的信息
    protected function configure()
    {
        $this->setName('test')
            ->setDescription('Command Test');
    }


    function workTimestamp($length, $holiday = [], $time = null)
    {
        $realLength = 1;
        $time = $time ? : time();
        for($i = 1; $i <= $length;){
            $timestamp = strtotime("+{$realLength}day", $time);
            $realLength++;
            $week = date("w", $timestamp);
            $date = date("Y-m-d", $timestamp);
            if($week == 0 || $week == 6){
                // 周末
                continue;
            }
            if(in_array($date, $holiday)){
                // 节假日
                continue;
            }
            $i++;
        }
        return $timestamp;
    }

    function checkStockTradeTime()
    {

        if(date('w') == 0){
            return false;
        }
        if(date('w') == 6){
            return false;
        }
        if(date('G') < 9 || (date('G') == 9 && date('i') < 30)){
            return false;
        }
        if(((date('G') == 11 && date('i') > 30) || date('G') > 11) && date('G') < 13){
            return false;
        }
        if(date('G') >= 15){
            return false;
        }
        $holiday = explode(',', $this->cf("holiday", ""));
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }

    function createStrategySn()
    {
        return date("YmdHis") . isLogin() . randomString(6, true);
    }

    function createOrderSn()
    {
        //return date("YmdHis") . isLogin() . randomString(6, true);
        return uniqid() . randomString(4, true);
    }

    function cf($alias, $default='')
    {
        $value = model("app\common\model\System")->where(["alias" => $alias])->value("val");
        return is_null($value) ? $default : $value;
    }

    protected function execute(Input $input, Output $output)
    {
        //交易时间
        if($this->checkStockTradeTime()){
            $this->_logic = new OrderLogic();
            $this->_userLogic = new UserLogic();

            // 输出到日志文件
            $output->writeln("TestCommand:");

            $field = "order_id,user_id";
            $orderIdList =  $this->_userLogic->getAllOrderIdList(3,$field);

            foreach ($orderIdList as $key => $value) {
                    $order =$this->_userLogic->userOrderById2($orderIdList[$key]['user_id'], $orderIdList[$key]['order_id'], 3);
                    $order = reset($order);
                    if ($order) {
                        if(strtotime(date("Y-m-d")) > strtotime(date(date('Y-m-d', $order['create_at'])))){
                            $quotation = (new StockLogic())->simpleData($order['code']);
                            if (isset($quotation[$order['code']]) && !empty($quotation[$order['code']])) {
                                if (number_format($order['stop_loss_price'], 2) > number_format($quotation[$order['code']]['last_px'], 2)) {
                                    $order['last_px'] = $quotation[$order['code']]['last_px'];
                                    $data = [
                                        "order_id" => $order["order_id"],
                                        "sell_price" => $order["last_px"],
                                        "sell_hand" => $order["hand"],
                                        "sell_deposit" => $order["hand"] * $order["last_px"],
                                        "profit" => ($order["last_px"] - $order["price"]) * $order["hand"],
                                        "state" => 6,
                                        "force_type" => 2 // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                                    ];
                                    $res = Order::update($data);
                                    if ($res) {
                                        $res = $this->_logic->sellOk($order["order_id"]);
                                        if ($res) {
                                            $output->writeln($order['code'] + "强平成功");
                                        }
                                    } else {
                                        $output->writeln("平仓申请提交失败！1");
                                    }
                                } else if (number_format($order['stop_profit_price'], 2) < number_format($quotation[$order['code']]['last_px'], 2)) {
                                    $order['last_px'] = $quotation[$order['code']]['last_px'];
                                    $data = [
                                        "order_id" => $order["order_id"],
                                        "sell_price" => $order["last_px"],
                                        "sell_hand" => $order["hand"],
                                        "sell_deposit" => $order["hand"] * $order["last_px"],
                                        "profit" => ($order["last_px"] - $order["price"]) * $order["hand"],
                                        "state" => 6,
                                        "force_type" => 2 // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                                    ];
                                    $res = Order::update($data);
                                    if ($res) {
                                        $res = $this->_logic->sellOk($order["order_id"]);
                                        if ($res) {
                                            $output->writeln($order['code'] + "强平成功");
                                        }
                                    } else {
                                        $output->writeln("平仓申请提交失败！1");
                                    }
                                } else {
                                    $output->writeln("没有需要强平股票");
                                }

                            } else {
                                $output->writeln("平仓申请提交失败！2");
                            }
                        }
                    }
            }
        }else{
            $output->writeln("不在交易时间");
        }

    }
}