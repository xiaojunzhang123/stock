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

		
	$logs = fopen("pay1.log", "a+");
fwrite($logs, "\r\n" . date("Y-m-d H:i:s") . "  回调信息：" . json_encode($_REQUEST) . " \r\n");
fclose($logs);
	$_POST=$_REQUEST;
    if($_POST['resp_code']=='00'){
        $status=$Pay->makeNotifySign($_POST,'pay_return');
      
		$conn=mysqli_connect('127.0.0.1','root','yt891001','syzg');
					$payno=$_POST['out_trade_no'];
					$money=$_POST['amount']; 
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
				
                    $sql="update stock_user_recharge set state='1' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
                    mysqli_close($conn);
                    echo 'success';exit;
               }else{
					  print_r($row);
				  }	
			
           // echo '验签成功,处理内部业务<br>';
  
    }else{
        echo '返回的状态：'.$_POST['resp_code'].'，返回的错误信息：'.$_POST['resp_desc'];
    }
    exit;



		
?>