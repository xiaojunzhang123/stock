<?php
header('Content-Type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");
$logs = fopen("pay213.log", "a+");
fwrite($logs, "\r\n" . date("Y-m-d H:i:s") . "  回调信息：" . json_encode($_REQUEST) . " \r\n");
fclose($logs);


file_put_contents("success.txt",$_REQUEST."\n", FILE_APPEND);
   $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
 	    //"transaction_id" =>$_REQUEST["transaction_id"], // 支付流水号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "returncode" => $_REQUEST["returncode"]
        );
      
        $Md5key = "vQeqHShTeaopN63hdCbjOwjwgogfZx";
        //////////////////////////////////////////
	ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key)); 
	//////////////////////////////////////////////////////
        if ($sign == $_REQUEST["sign"]) {
			
            if ($_REQUEST["returncode"] == "00") {
					$payno=$_REQUEST["orderid"];
					$money=$_REQUEST["amount"]; 
					
					$conn=mysqli_connect('127.0.0.1','www_syzg88_cn','3FftHzNzaQB4jYt5','www_syzg88_cn');
                    $sql="select * from stock_user_recharge where trade_no='$payno'";
                    $row=mysqli_query($conn,$sql);
                    $row=mysqli_fetch_assoc($row); 
                    $uid = $row['user_id'];
                    $zt=$row['state'];
					
                
				 
                    $sql="select * from stock_user where user_id='$uid'";
                    $row=mysqli_query($conn,$sql);
                    $row=mysqli_fetch_assoc($row); 
					echo $row['account'];
					
                    $sql="update stock_user set account=account+'$money' where user_id='$uid'";
                    mysqli_query($conn,$sql);

                    $sql="select * from stock_user where user_id='$uid'";
                    $row=mysqli_query($conn,$sql);
                    $row=mysqli_fetch_assoc($row); 
					
				    $sql="update stock_user_recharge set type='2' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
				
                    $sql="update stock_user_recharge set state='1' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
					
					$sql="update stock_user_recharge set actual='$money' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
					
                    mysqli_close($conn);

				   exit("ok");
            }
        }else{
            echo "签名错误";
        }


					
             

?>