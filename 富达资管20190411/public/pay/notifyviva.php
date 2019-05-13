<?php

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

    ini_set('display_errors','on');
    error_reporting(E_ALL);
    header("Content-type: text/html; charset=utf-8");
    date_default_timezone_set('Asia/Shanghai');
    require_once dirname ( __FILE__ ).'/lib/PayUtils.php';
    //$Pay=new PayUtils();

    //$Pay->addLog(json_encode($_REQUEST,true));
    //$Pay->addLog(json_encode($_POST,true));
    //$Pay->addLog($_POST['resp_code']);
	//VIVO支付回调
	//{"amount":"0.147493","orderNo":"VIVA1554099836179506423","exchangeRate":"1.0","poundage":"0.001475","sign":"380EDE4789419669F2C6B441DB9012C582ED7FB1","merchantOrderNo":"20190401142356","merchantNo":"635612171","timestamp":"1554100304"} 


 	
 $dataviva['p2'] = '635612171';
 $dataviva['key']='FFCF80C04D9DC9269A98A14E6D87339F';
 $dataviva['p3'] = date("YmdHis");


 $datavivaprice['p1'] = '635612171';
 $datavivaprice['timestamp'] = time();
 $strtempprice= $_POST['amount'].'&'.$_POST['exchangeRate'].'&'.$_POST['poundage'].'&'.$_POST['merchantNo'].'&'.$_POST['merchantOrderNo'].'&'.$_POST['orderNo'].'&'.$_POST['timestamp'];
 $datavivasign=strtoupper(getSignature($strtempprice, $dataviva['key']));
   
	$logs = fopen("payviva.log", "a+");
    fwrite($logs, "\r\n" . date("Y-m-d H:i:s") . "  回调信息：" . json_encode($_REQUEST) . " \r\n");
    fclose($logs);
	 
	if ($datavivasign == $_POST['sign']) {
                                		
						//此处做商户逻辑处理 
					            $conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
								$payno=$_REQUEST['merchantOrderNo'];
								//$money=$_REQUEST['orderAmount']/100; 
								$sql="select * from stock_user_recharge where trade_no='$payno'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row); 
								$uid = $row['user_id'];
								$money = $row['amount'];
								$zt=$row['state'];
								$timenow=time();
							    
							  if($zt=='0'){
							 
								$sql="select * from stock_user where user_id='$uid'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row); 
								//echo $row['account'];
								
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
								exit($datya);
						   }else{
								  print_r($row);
							  }	
							  
						break;
				 
		 

	}else{
						$rtnOK=0;
						//以下是我们快钱设置的show页面，商户需要自己定义该页面。
						$rtnUrl="";
							
	} 
    exit;
 	
?>