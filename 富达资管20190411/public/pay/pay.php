<?php
ini_set('display_errors', 'on');
error_reporting(E_ALL);
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('Asia/Shanghai');
require_once dirname(__FILE__) . '/lib/PayUtils.php';
require_once dirname(__FILE__) . '/lib/phpqrcode/phpqrcode.php';
$_POST=$_GET;
    $pay_data = array();
    $pay_data['out_trade_no'] = $_POST['payno'];//订单号
    $pay_data['total_fee'] = $_POST['money'].'.00';//订单金额
    $pay_data['pay_id'] = $_POST['typ'];//支付方式
    $pay_data['bank_code'] = 'ICBC';//银行编码
    $Pay = new PayUtils();
    $result = $Pay->paySubmit($pay_data);
    if ($result['data']['resp_code'] != '00') {
        echo $result['data']['resp_desc'];
        exit;
    }
    if ($pay_data['pay_id'] == 'QWJ_ALIH5' || $pay_data['pay_id'] == 'QWJ_ALIPC') {
        var_dump($result['data']);
    } else{
        $url= $result['data']['payment'];
		echo $url;
		header("Location:$url");
    }

?>