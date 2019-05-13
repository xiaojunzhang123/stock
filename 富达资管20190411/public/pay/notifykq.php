<?php
    ini_set('display_errors','on');
    error_reporting(E_ALL);
    header("Content-type: text/html; charset=utf-8");
    date_default_timezone_set('Asia/Shanghai');
    require_once dirname ( __FILE__ ).'/lib/PayUtils.php';
    $Pay=new PayUtils();

    //$Pay->addLog(json_encode($_REQUEST,true));
    //$Pay->addLog(json_encode($_POST,true));
    //$Pay->addLog($_POST['resp_code']);
	//快钱支付回调
	//{"merchantAcctId":"1021023319001","version":"v2.0","language":"1","signType":"4","payType":"10","bankId":"CMB","orderId":"20190328123424","orderAmount":"1","orderTime":"20190328123424","ext1":"","ext2":"","payAmount":"1","dealId":"3372086905","bankDealId":"2019032890201123426350500202509","dealTime":"20190328123531","payResult":"10","errCode":"","fee":"1","signMsg":"XlxiTOKboHpQ9LrouonGyAey8NYl9S8wZqw8dpG0S3n8Igii0MW9YYxHg51e8lVukxCj2+AUrQKGde6tw2ulA69gB+NLzodvdx3yAG46Jrtm4EOvlghqpwalVMwMlx1bEljO5rYJ8vekCuHrT5u3qTy2JxdprxWsLR35TI6jT5Ei\/IQcay8KeXDTdG9Qll5axeKFSEx5O4oEVm1GUU+TEdjPDDcJc0uQalIUYnHGi1pU\/7CIi5Gzi64tbL6KM5D+DglNHT4EdAAo0WJf+swA\/tD6hQypj7qlpPCLP\/td8Z71SBz4mXQ+K6sOGNO9HpHbKT3CnVfAHo\/uNRw32iXcmA=="} 	
	$logs = fopen("paykq.log", "a+");
    fwrite($logs, "\r\n" . date("Y-m-d H:i:s") . "  回调信息：" . json_encode($_REQUEST) . " \r\n");
    fclose($logs);
	$_POST=$_REQUEST;
	
		function kq_ck_null($kq_va,$kq_na){if($kq_va == ""){return $kq_va="";}else{return $kq_va=$kq_na.'='.$kq_va.'&';}}
	//人民币网关账号，该账号为11位人民币网关商户编号+01,该值与提交时相同。
	$kq_check_all_para=kq_ck_null($_REQUEST['merchantAcctId'],'merchantAcctId');
	//网关版本，固定值：v2.0,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['version'],'version');
	//语言种类，1代表中文显示，2代表英文显示。默认为1,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['language'],'language');
	//签名类型,该值为4，代表PKI加密方式,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['signType'],'signType');
	//支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['payType'],'payType');
	//银行代码，如果payType为00，该值为空；如果payType为10,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['bankId'],'bankId');
	//商户订单号，,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['orderId'],'orderId');
	//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101,该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['orderTime'],'orderTime');
	//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试,该值与支付时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['orderAmount'],'orderAmount');
        //已绑短卡号,信用卡快捷支付绑定卡信息后返回前六后四位信用卡号
        $kq_check_all_para.=kq_ck_null($_REQUEST['bindCard'],'bindCard');
        //已绑短手机尾号,信用卡快捷支付绑定卡信息后返回前三位后四位手机号码
        $kq_check_all_para.=kq_ck_null($_REQUEST['bindMobile'],'bindMobile');
	// 快钱交易号，商户每一笔交易都会在快钱生成一个交易号。
	$kq_check_all_para.=kq_ck_null($_REQUEST['dealId'],'dealId');
	//银行交易号 ，快钱交易在银行支付时对应的交易号，如果不是通过银行卡支付，则为空
	$kq_check_all_para.=kq_ck_null($_REQUEST['bankDealId'],'bankDealId');
	//快钱交易时间，快钱对交易进行处理的时间,格式：yyyyMMddHHmmss，如：20071117020101
	$kq_check_all_para.=kq_ck_null($_REQUEST['dealTime'],'dealTime');
	//商户实际支付金额 以分为单位。比方10元，提交时金额应为1000。该金额代表商户快钱账户最终收到的金额。
	$kq_check_all_para.=kq_ck_null($_REQUEST['payAmount'],'payAmount');
	//费用，快钱收取商户的手续费，单位为分。
	$kq_check_all_para.=kq_ck_null($_REQUEST['fee'],'fee');
	//扩展字段1，该值与提交时相同
	$kq_check_all_para.=kq_ck_null($_REQUEST['ext1'],'ext1');
	//扩展字段2，该值与提交时相同。
	$kq_check_all_para.=kq_ck_null($_REQUEST['ext2'],'ext2');
	//处理结果， 10支付成功，11 支付失败，00订单申请成功，01 订单申请失败
	$kq_check_all_para.=kq_ck_null($_REQUEST['payResult'],'payResult');
	//错误代码 ，请参照《人民币网关接口文档》最后部分的详细解释。
	$kq_check_all_para.=kq_ck_null($_REQUEST['errCode'],'errCode');

	$trans_body=substr($kq_check_all_para,0,strlen($kq_check_all_para)-1);
	$MAC=base64_decode($_REQUEST[signMsg]);

	$fp = fopen("./99bill.cert.rsa.20340630.cer", "r"); 
	$cert = fread($fp, 8192); 
	fclose($fp); 
	$pubkeyid = openssl_get_publickey($cert); 
	$ok = openssl_verify($trans_body, $MAC, $pubkeyid); 

	if ($ok == 1) { 
		switch($_REQUEST[payResult]){
				case '10':
						//此处做商户逻辑处理
						$rtnOK=1;
						//以下是我们快钱设置的show页面，商户需要自己定义该页面。
						//$rtnUrl="http://219.233.173.50:8802/futao/rmb_demo/show.php?msg=success";
						        //$status=$Pay->makeNotifySign($_REQUEST,'pay_return');
      
					            $conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
								$payno='c'.$_REQUEST['orderId'];
								$money=$_REQUEST['orderAmount']/100; 
								$sql="select * from stock_user_recharge where trade_no='$payno'";
								$row=mysqli_query($conn,$sql);
								$row=mysqli_fetch_assoc($row); 
								$uid = $row['user_id'];
								$zt=$row['state'];
								 
							 
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
								 
								 $sql="update stock_user_recharge set type='3' where trade_no='$payno'";
                                 mysqli_query($conn,$sql);
							
								$sql="update stock_user_recharge set state='1' where trade_no='$payno'";
								mysqli_query($conn,$sql);
								mysqli_close($conn);
								echo 'success';exit;
						   }else{
								  print_r($row);
							  }	
						break;
				default:
						$rtnOK=0;
						//以下是我们快钱设置的show页面，商户需要自己定义该页面。
						$rtnUrl="";
						break;	
		
		}

	}else{
						$rtnOK=0;
						//以下是我们快钱设置的show页面，商户需要自己定义该页面。
						$rtnUrl="";
							
	}
	
    /*if($_POST['resp_code']=='00'){
        
			
           // echo '验签成功,处理内部业务<br>';
  
    }else{
        echo '返回的状态：'.$_POST['resp_code'].'，返回的错误信息：'.$_POST['resp_desc'];
    }*/
    exit;



		
?>