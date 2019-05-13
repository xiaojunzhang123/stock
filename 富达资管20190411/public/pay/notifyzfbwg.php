<?php
include_once("./citic/WunPay.Data.php");
include_once("./citic/WunPay.Api.php"); 
include_once("./citic/WunPay.Notify.php"); 
function xmlToArray($xml){
	//禁止引用外部xml实体
	libxml_disable_entity_loader(true);
	$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	$val = json_decode(json_encode($xmlstring),true);
	return $val;
}

 
$post_temp=file_get_contents("php://input");
//$post_temp='<xml><transaction_id><![CDATA[20190404200040011100950032800118]]></transaction_id><charset><![CDATA[UTF-8]]></charset><nonce_str><![CDATA[yfkoai89fl7l9uquzoc9rkj4307]]></nonce_str><method><![CDATA[pay.alipay.wun]]></method><openid><![CDATA[]]></openid><sign><![CDATA[BF411AF11ADF88F5E23100A490426349]]></sign><fee_type><![CDATA[CNY]]></fee_type><mch_id><![CDATA[cm2019040310000259]]></mch_id><version><![CDATA[2.0.0]]></version><out_trade_no><![CDATA[c20190404231816]]></out_trade_no><total_fee><![CDATA[1]]></total_fee><appid><![CDATA[ca2019040310000259]]></appid><result_code><![CDATA[SUCCESS]]></result_code><time_end><![CDATA[20190404231951]]></time_end><return_code><![CDATA[SUCCESS]]></return_code><sign_type><![CDATA[MD5]]></sign_type></xml>';
	 //$objectxml = simplexml_load_string($post_temp);//将文件转换成 对象
//$xmljson= json_encode($objectxml );//将对象转换个JSON
$xmlarray=xmlToArray($post_temp);//将json转换成数组
$obj = new WunPayDataBase('');
			$obj->FromXml($post_temp);
			$values = $obj->GetValues();
 

 $mch_config = array (
		'mch_id' => 'cm2019040310000259',
		'mch_appid' => 'ca2019040310000259',
		'mch_key' => '7b26369b80789271128d9d1272c46fb4',
		'version' => '2.0.0' 
);


$out_trade_no=$values['out_trade_no'];

$nonce_str=$values['nonce_str'];

$sign=$values['sign'];

$input = new WunPayOrderQuery($mch_config['mch_key']);
$input->SetAppid($mch_config['mch_appid']);   //支付平台分配的应用id
$input->SetMch_id($mch_config['mch_id']);	  //支付平台分配的商户id
$input->SetVersion($mch_config['version']);   //版本号
$input->SetMethod("pay.alipay.query");   //接口地址
$input->SetNonce_str($nonce_str);			  //随机字符串
$input->SetSign($sign);						  //签名
$input->SetOut_trade_no($out_trade_no);       //商户订单号
$reqXml = $input->ToXml();
$order = WunPayApi::orderQuery($input);

$rspArr = xmlToArray($order);
 $result = null;
//$result = "通信状态:" . $rspArr["return_code"];
if("FAIL" == $rspArr["return_code"] ){
	$result = $result . "<br>" . "错误描述:" . $rspArr["return_msg"];
}else if("SUCCESS"== $rspArr["return_code"]){
	if(isset($rspArr["sign"])){
		$isCheckSign = true;
		if(!$isCheckSign){
			$result = $result . "<br>错误描述:" . "验证签名不通过";
		}else{ 
			 
           //此处做商户逻辑处理 
					            $conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
								$payno=$rspArr['out_trade_no'];
								//$money=$_REQUEST['orderAmount']/100; 
								$sql="select * from stock_user_recharge where trade_no='$payno'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row);
								$uid = $row['user_id'];
								$money = $row['amount'];
								$zt=$row['state'];
								$timenow=time();
							    $datya='{"code":200}';
								 
							  if($zt=='0'){
							 
								$sql="select * from stock_user where user_id='$uid'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row); 
								echo $row['account'];
								
								$sql="update stock_user set account=account+'$money' where user_id='$uid'";
								mysqli_query($conn,$sql);

								$sql="select * from stock_user where user_id='$uid'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row); 
								
							     $sql="update stock_user_recharge set actual='$money' where trade_no='$payno'";
                                 mysqli_query($conn,$sql); 
								
								$sql="update stock_user_recharge set update_at='$timenow' where trade_no='$payno'";
                                 mysqli_query($conn,$sql); 
							
								$sql="update stock_user_recharge set state='1' where trade_no='$payno'";
								mysqli_query($conn,$sql);
								mysqli_close($conn);
								$datya='{"code":200}';
								
                             }
                             exit($datya);
	}

  }
} 
 
    exit;
 	
?>