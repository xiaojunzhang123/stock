<?php
$data=$_GET;

?>
<!DOCTYPE html>
<!-- saved from url=(0095)http://www.17sucai.com/preview/31419/2016-09-01/%E7%99%BB%E5%BD%95%E7%95%8C%E9%9D%A2/index.html -->
<html lang="zh-cn"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>快捷支付</title>
<meta name="Keywords" content="www.021news.cn">
<meta name="Description" content="www.021news.cn">

<!-- Bootstrap -->
<link href="./index_files/bootstrap.min.css" rel="stylesheet">
<link href="./index_files/main.css" rel="stylesheet">
<link href="./index_files/enter.css" rel="stylesheet">
<script src="./index_files/jquery.min.js.下载"></script>
<script src="./index_files/bootstrap.min.js.下载"></script>
<script src="./index_files/jquery.particleground.min.js.下载"></script>
</head>
<body>
<div id="particles"><canvas class="pg-canvas" width="604" height="697" style="display: block;"></canvas>
  <canvas class="pg-canvas" width="1920" height="911" style="display: block;"></canvas>
  <div class="intro" style="margin-top: -256.5px;">
    <div class="container">
      <div class="row" style="padding:30px 0;">
        
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-md-3 col-sm-8 col-centered">
          <form method="get" id="register-form" autocomplete="off" action="../../pay/index.php" class="nice-validator n-default" novalidate="">
            <input type='hidden' name='types' value="<?=$data['type']?>">
            <input type='hidden' name='uid' value="<?=$data['uid']?>">
            <input type='hidden' name='amount' value="<?=$data['amount']?>">

            <div class="form-group">
              <input type="text" class="form-control" id="account" name="bankcode" placeholder="银行卡号" autocomplete="off" aria-required="true" data-tip="银行卡号">
            </div>
            <div class="form-group">
              <input type="text" class="form-control" id="password" name="name" placeholder="开户名" aria-required="true" data-tip="请设置您的密码（6-16个字符）">
            </div>
            <div class="form-group">
              <input type="text" class="form-control" id="repeat_password" name="idNo" placeholder="身份证" aria-required="true" data-tip="开户身份证号">
            </div>
			<div class="form-group">
              <input type="text" class="form-control" id="repeat_password" name="phoneNo" placeholder="手机号" aria-required="true" data-tip="开户手机号">
            </div>
            <div id="validator-tips" class="validator-tips"></div>
          
            <div class="form-center-button">
              <button type="submit" id="submit_button" class="btn btn-primary btn-current">提交</button>
              <a href="#" class="btn btn-default">返回</a> </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" style="text-align: left" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            <h4 class="modal-title">用户协议</h4>
          </div>
          <div class="modal-body" id="agreementContent"></div>
        </div>
      </div>
    </div>
    <link rel="stylesheet" href="./index_files/jquery.validator.css">
    <script src="./index_files/zh-CN.js.下载"></script><script src="./index_files/jquery.validator.min.js.下载"></script> 
  </div>
</div>
<script>
    $(document).ready(function () {
        var intro = $('.intro');
        $('#particles').particleground({
            dotColor: 'rgba(52, 152, 219, 0.36)',
            lineColor: 'rgba(52, 152, 219, 0.86)',
            density: 130000,
            proximity: 500,
            lineWidth: 0.2
        });
        intro.css({
            'margin-top': -(intro.height() / 2 + 100)
        });
    });
</script>

</body></html>

