<?php
header('Content-Type:text/html;charset=utf8');
date_default_timezone_set("Asia/Shanghai");
$logs = fopen("pay.log", "a+");
fwrite($logs, "\r\n" . date("Y-m-d H:i:s") . "  回调信息：" . json_encode($_REQUEST) . " \r\n");
fclose($logs);
$orderid=$_REQUEST['orderid'];
$opstate=$_REQUEST['opstate'];
$ovalue=$_REQUEST['ovalue'];
$sign=$_REQUEST['sign'];
$key="a056b871b30446679cde9f94b6a8649e";
$md5=md5('orderid='.$orderid.'&opstate='.$opstate.'&ovalue='.$ovalue.$key);
if ($opstate==0 && $sign== $md5) {


					$payno=$orderid;
					$money=$ovalue; 
					
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
					
				     $sql="update stock_user_recharge set actual='$money' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
				
                    $sql="update stock_user_recharge set state='1' where trade_no='$payno'";
                    mysqli_query($conn,$sql);
                    mysqli_close($conn);
                    exit("ok");
             

   
}else{
	echo 'sign';
}

?>