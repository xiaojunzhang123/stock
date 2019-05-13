<?php
error_reporting(0);
header("Content-type: text/html; charset=utf-8");
$pay_memberid = "10065";   //商户ID
$pay_orderid = date("YmdHis").rand(100000,999999);    //订单号
$pay_amount = $_REQUEST['money']?$_REQUEST['money']:"10.00";    //交易金额
$pay_applydate = date("Y-m-d H:i:s");  //订单时间
$pay_notifyurl = "http://x/server.php";   //服务端返回地址
$pay_callbackurl = "http://x/page.php";  //页面跳转返回地址
$Md5key = "vQeqHShTeaopN63hdCbjOwjwgogfZx";   //密钥
$tjurl = "http://www.djkjcc.top/Pay_Index.html";   //提交地址


$llpay = array(
    "pay_memberid" => $pay_memberid,
    "pay_orderid" => $pay_orderid,
    "pay_amount" => $pay_amount,
    "pay_applydate" => $pay_applydate,
    "pay_notifyurl" => $pay_notifyurl,
    "pay_callbackurl" => $pay_callbackurl,
);
ksort($llpay);
$md5str = "";
foreach ($llpay as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
//echo($md5str . "key=" . $Md5key);
$sign = strtoupper(md5($md5str . "key=" . $Md5key));
$llpay["pay_md5sign"] = $sign;
//$llpay["pay_tongdao"] = 'LLPay';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>支付Demo</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <div class="row" style="margin:15px;0;">
        <div class="col-md-12">
            <form class="form-inline" method="post" action="<?php echo $tjurl; ?>" enctype="multipart/form-data">
                <?php
                foreach ($llpay as $key => $val) {
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '">';
                }
                ?>
                     <dt>商品名称：</dt>
                    <dd>
                        <span class="null-star">*</span>
                        <input size="30" name="name_goods" value="羽毛球"/>
                        <span>
</span>
                    </dd>
                     <dt>订单描述：</dt>
                    <dd>
                        <span class="null-star">*</span>
                        <input size="30" name="info_order" value="用户13958069593购买羽毛球3桶"/>
                        <span></span>
                    </dd>                   
                       <dt>姓名：</dt>
                    <dd>
                        <span class="null-star">*</span>
                        <input size="30" name="acct_name" value="xxx"/>
                        <span>
</span>
                    </dd>
                      <dt>身份证号：</dt>
                    <dd>
                        <span class="null-star">*</span>
                        <input size="30" name="id_no" value="511324199007170487"/>
                        <span>
</span>
                    </dd>
                    <dd>
                        <span class="new-btn-login-sp">
                            <button type="submit" class="btn btn-success btn-lg">支付(金额：<?php echo $pay_amount; ?>元)</button>
                        </span>
                    </dd>
                </dl>

                
            </form>
        </div>
    </div>
	
    </div>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
</div>
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
</body>
</html>
