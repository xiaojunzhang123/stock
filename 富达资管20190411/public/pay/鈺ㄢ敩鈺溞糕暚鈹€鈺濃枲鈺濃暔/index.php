<?php
header('Content-Type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");
$data=$_GET;
$uid=$data['uid'];
$amount=$data['amount'];
$type=$data['types'];
$orderid=date("YmdHis");
$conn=mysqli_connect('127.0.0.1','root','','');

$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state) VALUES ('{$uid}', '{$orderid}', '{$amount}','0','0')";
mysqli_query($conn, $sql);
$url="http://pay1./pay/pay.php?appid=2018101394&payno=$orderid&typ=$type&money=$amount&back_url=http://www.huiyingcelue.cn";
header("Location:$url");
?>