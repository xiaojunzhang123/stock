<?php
header('Content-Type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");
 
$data=$_GET;//var_dump($data);die;
$uid=$data['uid'];
$amount=$data['amount'];
$type=$data['types'];
//快钱支付
if($type=='3'){
//人民币网关账号，该账号为11位人民币网关商户编号+01,该参数必填。
	$merchantAcctId = "1021023319001";
	//编码方式，1代表 UTF-8; 2 代表 GBK; 3代表 GB2312 默认为1,该参数必填。
	$inputCharset = "1";
	//接收支付结果的页面地址，该参数一般置为空即可。
	$pageUrl = "http://www.syzg888.com/user/home.html";
	//服务器接收支付结果的后台地址，该参数务必填写，不能为空。
	$bgUrl = "http://www.syzg888.com/pay/notifykq.php";
	//网关版本，固定值：v2.0,该参数必填。
	$version =  "v2.0";
	//语言种类，1代表中文显示，2代表英文显示。默认为1,该参数必填。
	$language =  "1";
	//签名类型,该值为4，代表PKI加密方式,该参数必填。
	$signType =  "4";
	//支付人姓名,可以为空。
	$payerName= ""; 
	//支付人联系类型，1 代表电子邮件方式；2 代表手机联系方式。可以为空。
	$payerContactType =  "";
	//支付人联系方式，与payerContactType设置对应，payerContactType为1，则填写邮箱地址；payerContactType为2，则填写手机号码。可以为空。
	$payerContact =  "";
	//商户订单号，以下采用时间来定义订单号，商户可以根据自己订单号的定义规则来定义该值，不能为空。
	$orderId = date("YmdHis");
	//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试。该参数必填。
	$orderAmount = $_GET['amount']*100;
	//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
	$orderTime = date("YmdHis");
	//商品名称，可以为空。
	$productName= ""; 
	//商品数量，可以为空。
	$productNum = "";
	//商品代码，可以为空。
	$productId = "";
	//商品描述，可以为空。
	$productDesc = "";
	//扩展字段1，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext1 = "";
	//扩展自段2，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext2 = "";
	//支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10，必填。
	$payType = "00";
	//银行代码，如果payType为00，该值可以为空；如果payType为10，该值必须填写，具体请参考银行列表。
	$bankId = "";
	//同一订单禁止重复提交标志，实物购物车填1，虚拟产品用0。1代表只能提交一次，0代表在支付不成功情况下可以再提交。可为空。
	$redoFlag = "";
	//快钱合作伙伴的帐户号，即商户编号，可为空。
	$pid = "";
	// signMsg 签名字符串 不可空，生成加密签名串
	function kq_ck_null($kq_va,$kq_na){if($kq_va == ""){$kq_va="";}else{return $kq_va=$kq_na.'='.$kq_va.'&';}}

	$kq_all_para=kq_ck_null($inputCharset,'inputCharset');
	$kq_all_para.=kq_ck_null($pageUrl,"pageUrl");
	$kq_all_para.=kq_ck_null($bgUrl,'bgUrl');
	$kq_all_para.=kq_ck_null($version,'version');
	$kq_all_para.=kq_ck_null($language,'language');
	$kq_all_para.=kq_ck_null($signType,'signType');
	$kq_all_para.=kq_ck_null($merchantAcctId,'merchantAcctId');
	$kq_all_para.=kq_ck_null($payerName,'payerName');
	$kq_all_para.=kq_ck_null($payerContactType,'payerContactType');
	$kq_all_para.=kq_ck_null($payerContact,'payerContact');
	$kq_all_para.=kq_ck_null($orderId,'orderId');
	$kq_all_para.=kq_ck_null($orderAmount,'orderAmount');
	$kq_all_para.=kq_ck_null($orderTime,'orderTime');
	$kq_all_para.=kq_ck_null($productName,'productName');
	$kq_all_para.=kq_ck_null($productNum,'productNum');
	$kq_all_para.=kq_ck_null($productId,'productId');
	$kq_all_para.=kq_ck_null($productDesc,'productDesc');
	$kq_all_para.=kq_ck_null($ext1,'ext1');
	$kq_all_para.=kq_ck_null($ext2,'ext2');
	$kq_all_para.=kq_ck_null($payType,'payType');
	$kq_all_para.=kq_ck_null($bankId,'bankId');
	$kq_all_para.=kq_ck_null($redoFlag,'redoFlag');
	$kq_all_para.=kq_ck_null($pid,'pid');
	
	$kq_all_para=substr($kq_all_para,0,strlen($kq_all_para)-1);
	/////////////  RSA 签名计算 ///////// 开始 //
	$fp = fopen("./99bill-rsa.pem", "r");
	$priv_key = fread($fp, 123456);
	fclose($fp);
	$pkeyid = openssl_get_privatekey($priv_key);

	// compute signature
	openssl_sign($kq_all_para, $signMsg, $pkeyid,OPENSSL_ALGO_SHA1);

	// free the key from memory
	openssl_free_key($pkeyid);
	$signMsg = base64_encode($signMsg);
	header("Content-type: text/html; charset=utf-8");
    $url='https://www.99bill.com/gateway/recvMerchantInfoAction.htm';
    $data_gf['inputCharset']=$inputCharset;
    $data_gf['pageUrl']=$pageUrl;
    $data_gf['bgUrl']=$bgUrl;
    $data_gf['version']=$version;
    $data_gf['language']=$language;
    $data_gf['signType']=$signType;
    $data_gf['signMsg']=$signMsg;
    $data_gf['merchantAcctId']=$merchantAcctId;
    $data_gf['payerName']=$payerName;
    $data_gf['payerContactType']=$payerContactType;
    $data_gf['orderId']=$orderId;
    $data_gf['orderAmount']=$orderAmount;
    $data_gf['orderTime']=$orderTime;
    $data_gf['productName']=$productName;
    $data_gf['productNum']=$productNum;
    $data_gf['productId']=$productId;
    $data_gf['productDesc']=$productDesc;
    $data_gf['ext1']=$ext1;
    $data_gf['ext2']=$ext2;
    $data_gf['payType']=$payType;
    $data_gf['bankId']=$bankId;
    $data_gf['redoFlag']=$redoFlag;
    $data_gf['pid']=$pid;//var_dump($data);die;
    //$back=curl_post_https($url, $data);

    $orderid='c'.date("YmdHis");
$conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
$time=time();
$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state,create_at,update_at) VALUES ('{$uid}', '{$orderid}', '{$amount}','0','0','{$time}','{$time}')";
mysqli_query($conn, $sql);
    echo form2($url,$data_gf);
}


//viva支付
if($type=='4'){ 
 
 $dataviva['p2'] = '635612171';
 $dataviva['key']='FFCF80C04D9DC9269A98A14E6D87339F';
 $dataviva['p3'] = 'c'.date("YmdHis");
 $dingdantime=time();

 $datavivaprice['p1'] = '635612171';
 $datavivaprice['timestamp'] = $dingdantime;
 $strtempprice= $datavivaprice['p1'].'&'.$datavivaprice['timestamp'];
 $datavivaprice['sign']=strtoupper(getSignature($strtempprice, $dataviva['key']));
 $headerprice=array('Content-Type:application/json','charset:utf-8','access_key:'.$datavivaprice['sign'],'app_id:91982467','timestamp:'.$datavivaprice['timestamp']); 
 $urlprice='http://merchant-api.vivapay.io/api/recharge/convert/v1?p1='.$datavivaprice['p1'].'&timestamp='.$datavivaprice['timestamp'];
 $outputprice=curl_get($urlprice,$headerprice);
 $outputprice = json_decode($outputprice, true);
 $amountfl=$outputprice['data']['price'];
  
 $dataviva['p1'] = $_GET['amount']/$amountfl;

//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
$dataviva['timestamp'] = $dingdantime;
//p1&p2&p3&timestamp 
$strtemp= $dataviva['p1'].'&'.$dataviva['p2'].'&'.$dataviva['p3'].'&'.$dataviva['timestamp'];
$dataviva['sign']=strtoupper(getSignature($strtemp, $dataviva['key'])); 
header("Content-type: text/html; charset=utf-8");
$url_ru='http://merchant-api.vivapay.io/api/recharge/check/v2?p1='.$dataviva['p1'].'&p2='.$dataviva['p2'].'&p3='.$dataviva['p3'].'&timestamp='.$dataviva['timestamp'];
$header=array('Content-Type:application/json','charset:utf-8','access_key:'.$dataviva['sign'],'app_id:91982467','timestamp:'.$dataviva['timestamp']);
//print_r($url);

//var_dump($data);die;
//$back=curl_post_https($url, $data);

$orderid=$dataviva['p3'];
$output=curl_get($url_ru,$header);
$output = json_decode($output, true);//var_dump($output);die;

$conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
$time=time();
$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state,create_at,update_at) VALUES ('{$uid}', '{$orderid}', '{$amount}','4','0','{$time}','{$time}')";
mysqli_query($conn, $sql);
 
if($output['code']=='200'){
	$url=$output['data']['url'];
	header("Location:{$url}");die;
}
 
}

//支付宝网关支付接口
if($type=='5'){ 
 include_once("./citic/WunPay.Data.php");
 include_once("./citic/WunPay.Api.php");
 $mch_config = array (
		'mch_id' => 'cm2019040310000259',
		'mch_appid' => 'ca2019040310000259',
		'mch_key' => '7b26369b80789271128d9d1272c46fb4',
		'version' => '2.0.0' 
);
 
    $nonce_str='c'.date("YmdHis");
	$total_fee=$data['amount']*100;
	$body=isset($_POST['body']) ? $_POST['body'] : "";
	$notify_url="http://www.syzg888.com/pay/notifyzfbwg.php";


	$scene='bar_code';
    //appid=ca2018111910000151&mch_id=cm2018111910000151&method=pay.alipay.wu
//n&notify_url=http://192.168.1.123/demo.php&out_trade_no=1543201739885&total
//_fee=1&version=2.0.0
    $signn='appid='.$mch_config['mch_appid'].'&mch_id='.$mch_config['mch_id'].'&method=pay.alipay.wun&notify_url='.$notify_url.'&out_trade_no='.$nonce_str.'&total_fee='.$total_fee.'&version=2.0.0';
	$sign=md5($signn.$mch_config['mch_key']);
 
	$input = new WunPayUnifiedOrder($mch_config['mch_key']);
	$input->SetAppid($mch_config['mch_appid']);   //支付平台分配的应用id
	$input->SetMch_id($mch_config['mch_id']);	  //支付平台分配的商户id
	$input->SetVersion($mch_config['version']);   //版本号
	$input->SetMethod("pay.alipay.wun");   //接口地址
	$input->SetNonce_str($nonce_str);			  //随机字符串
	$input->SetBody($body);                        //商品名称
	$input->SetSign($sign);						  //签名
	$input->SetOut_trade_no($nonce_str);       //商户订单号
	$input->SetTotal_fee($total_fee);			  //金额
	$input->SetNotify_url($notify_url);			  //金额



	$conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
	$time=time();
	$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state,create_at,update_at) VALUES ('{$uid}', '{$nonce_str}', '{$data['amount']}','5','0','{$time}','{$time}')";
	mysqli_query($conn, $sql);
	//$reqXml = $input->ToXml();
	$order = WunPayApi::unifiedOrder( $input );
  
 
}



if($type=='2'){
	//echo $sql; die;
  	if(!isset($data['bankcode'])){
      $url='http://www.syzg888.com/pay/shou/index.php?type='.$type.'&uid='.$uid.'&amount='.$amount;
    	header("Location:{$url}");die;
    }
	//pay($orderid,$type,$amount);

}

function form2($url,$data,$method='post')
    { 
        $form="<body onLoad='document.pay.submit()'><form name='pay' action='$url' method='$method' hidden='hidden'>";
        foreach ($data as $key => $value) {
            $form.="<input name='$key' value='$value'>";
        }
        $form.="</form></body>";
        return $form;
		die;
    }




$orderid='c'.date("YmdHis");
$conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
$time=time();
$sql = "INSERT INTO stock_user_recharge (user_id, trade_no, amount,type,state,create_at,update_at) VALUES ('{$uid}', '{$orderid}', '{$amount}','0','0','{$time}','{$time}')";
mysqli_query($conn, $sql);
if($type=='2'){
	//echo $sql;die;
	newpay($orderid,$data,$type);die;
}

if(in_array($type,[992,1004,1006])){
	//echo $sql; die;

	pay($orderid,$type,$amount);

}else{
	if($data['types'] !='3' && $data['types'] !='4'){
	$url="http://www.syzg888.com/pay/pay.php?&payno=$orderid&typ=$type&money=$amount".'&bankcode='.$data['bankcode'];
	header("Location:$url");
	}
}
function newpay($orderid,$data,$type='')
{
	header("Content-type: text/html; charset=utf-8");
	$pay_memberid = "10065";   //商户ID
	$pay_orderid = $orderid;    //订单号
	$pay_amount = $data['amount'];    //交易金额
	$pay_applydate = date("Y-m-d H:i:s");  //订单时间
	$pay_notifyurl = "http://www.syzg888.com/pay/not.php";   //服务端返回地址
	$pay_callbackurl = "http://www.syzg888.com/user/home.html";  //页面跳转返回地址
	$Md5key = "vQeqHShTeaopN63hdCbjOwjwgogfZx";   //密钥
	$tjurl = "http://www.suswandd.cn/Pay_Index.html";   //提交地址

	$llpay = array(
	    "pay_memberid" => $pay_memberid,
	    "pay_orderid" => $pay_orderid,
	    "pay_amount" => $pay_amount,
	    "pay_applydate" => $pay_applydate,
	    "pay_notifyurl" => $pay_notifyurl,
	    "pay_callbackurl" => $pay_callbackurl, 
    

     // bankcode=卡号&name=开户名&repeat_password=身份证&repeat_password=手机号
	);
	ksort($llpay);
	$md5str = "";
	foreach ($llpay as $key => $val) {
	    $md5str = $md5str . $key . "=" . $val . "&";
	}
  
	//echo($md5str . "key=" . $Md5key);
	$sign = strtoupper(md5($md5str . "key=" . $Md5key));
	$llpay["pay_md5sign"] = $sign;
 		$llpay['pay_type']=$type;
  		$llpay['cardNo']=$data['bankcode'];
  		$llpay['acctName']= $data['name'];
  		$llpay['idNo']=$data['idNo'];
  		$llpay['phoneNo']=$data['phoneNo'];
  echo form($tjurl,$llpay);
}
function form($url,$data,$method='post')
    {
        $form="<body onLoad='document.pay.submit()'><form name='pay' action='$url' method='$method' hidden='hidden'>";
        foreach ($data as $key => $value) {
            $form.="<input name='$key' value='$value'>";
        }
        $form.="</form></body>";
        return $form;
    }

function pay($orderid,$type,$amount)
{
	header('Content-Type:text/html;charset=utf8');
	date_default_timezone_set("Asia/Shanghai");

	//echo $sql;die;
	$key="a056b871b30446679cde9f94b6a8649e";
	$parter='1876';

	$callbackurl='http://www.syzg888.com/pay/notify1.php';
	$hrefbackurl='http://'.$_SERVER['HTTP_HOST'].'/user/home.html';

	$attach=$orderid;
	$value=$amount;
	$sign=md5('parter='.$parter.'&type='.$type.'&value='.$value.'&orderid='.$orderid.'&callbackurl='.$callbackurl.$key);
	$payerIp=$_SERVER["REMOTE_ADDR"];

	$url="http://pay.149297.cn/Pay/GateWay?";
	$url=$url.'parter='.$parter.'&type='.$type.'&value='.$value.'&orderid='.$orderid.'&callbackurl='.$callbackurl.'&hrefbackurl='.$hrefbackurl.'&payerIp='.$payerIp.'&attach='.$attach.'&sign='.$sign;
	echo $url;
	header('Location:'.$url);
}

/**
 * @使用HMAC-SHA1算法生成oauth_signature签名值
 *
 * @param $key  密钥
 * @param $str  源串
 *
 * @return 签名值
 */
 
function getSignature($str, $key) { 
$signature = ""; 
if (function_exists('hash_hmac')) {
$signature = bin2hex(hash_hmac("sha1", $str, $key, true));
} else {
$blocksize = 64; 
$hashfunc = 'sha1'; 
if (strlen($key) > $blocksize) { 
$key = pack('H*', $hashfunc($key)); 
} 
$key = str_pad($key, $blocksize, chr(0x00)); 
$ipad = str_repeat(chr(0x36), $blocksize); 
$opad = str_repeat(chr(0x5c), $blocksize); 
$hmac = pack( 
'H*', $hashfunc( 
($key ^ $opad) . pack( 
'H*', $hashfunc( 
($key ^ $ipad) . $str 
) 
) 
) 
); 
$signature = bin2hex($hmac);
} 
return $signature; 
}

function curl_get($url,$header){
	$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
//	curl_setopt($ch, CURLOPT_POSTFIELDS,$dataviva);
// 执行后不直接打印出来
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, 0);
// 跳过证书检查
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// 不从证书中检查SSL加密算法是否存在
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

//执行并获取HTML文档内容
$output = curl_exec($ch);

//释放curl句柄
curl_close($ch);  
return $output;
}
?>