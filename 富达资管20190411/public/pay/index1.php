<?php
header('Content-Type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");

$data=$_GET;
$uid=$data['uid'];
$amount=$data['amount'];
$type=$data['types'];
$orderid='c'.date("YmdHis");
$conn=mysqli_connect('127.0.0.1','www_syzg88_cn','3FftHzNzaQB4jYt5','www_syzg88_cn');
if (!$conn) {exit(mysqli_error());}
$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state) VALUES ('{$uid}','{$orderid}','{$amount}','0','0')";
mysqli_query($conn, $sql);
//echo $sql;die;
$key="a056b871b30446679cde9f94b6a8649e";
$parter='1876';

$callbackurl='http://www.syzg88.cn/pay/notify.php';
$hrefbackurl='http://'.$_SERVER['HTTP_HOST'].'/user/home.html';

$attach=$orderid;
$value=$amount;
$sign=md5('parter='.$parter.'&type='.$type.'&value='.$value.'&orderid='.$orderid.'&callbackurl='.$callbackurl.$key);
$payerIp=$_SERVER["REMOTE_ADDR"];

$url="http://pay.149297.cn/Pay/GateWay?";
$url=$url.'parter='.$parter.'&type='.$type.'&value='.$value.'&orderid='.$orderid.'&callbackurl='.$callbackurl.'&hrefbackurl='.$hrefbackurl.'&payerIp='.$payerIp.'&attach='.$attach.'&sign='.$sign;
echo $url;
header('Location:'.$url);


/*
$url="http://www.syzg88.cn/pay/pay.php?&payno=$orderid&typ=$type&money=$amount".'&bankcode='.$data['bankcode'];
header("Location:$url");*/
?>