<?php
//软件接口配置
    $key_="JHee8Nk";//接口KEY  自己修改下 软件上和这个设置一样就行
    $md5key="JHesdekjeri";//MD5加密字符串 自己修改下 软件上和这个设置一样就行
//软件接口地址 http://域名/mcbpay/apipay.php?payno=#name&tno=#tno&money=#money&sign=#sign&key=接口KEY
	
		$getkey=$_REQUEST['key'];//接收参数key
		$tno=$_REQUEST['tno'];//接收参数tno 交易号
		$payno=$_REQUEST['payno'];//接收参数payno 一般是用户名 用户ID
		$money=$_REQUEST['money'];//接收参数money 付款金额
		$sign=$_REQUEST['sign'];//接收参数sign
		$typ=(int)$_REQUEST['typ'];//接收参数typ
		if($typ==1){
			$typname='手工充值';
		}else if($typ==2){
			$typname='支付宝充值';
		}else if($typ==3){
			$typname='财付通充值';
		}else if($typ==4){
			$typname='手Q充值';
		}else if($typ==5){
			$typname='微信充值';
		}
		
		if(!$tno)exit('没有订单号');
		if(!$payno)exit('没有付款说明');
		if($getkey!=$key_)exit('KEY错误');
		if(strtoupper($sign)!=strtoupper(md5($tno.$payno.$money.$md5key)))exit('签名错误');
$conn=mysqli_connect('127.0.0.1','root','awkdbkkjnak7167','huiyingcl');
             	 
                    $sql="select * from stock_user_recharge where trade_no='$payno'";
                    $row=mysqli_query($conn,$sql);
                    $row=mysqli_fetch_assoc($row); 
                    $uid = $row['user_id'];
                    $zt=$row['state'];
                  if($zt=='0'){
                    $sql="update stock_user set account=account+'$money' where user_id='$uid'";
                    mysqli_query($conn,$sql);

                    $sql="update stock_user_recharge set state='1' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
                    mysqli_close($conn);
                    exit('1');
                  }
		
?>