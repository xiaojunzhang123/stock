<?php
namespace app\common\libraries;

use chuanglan\ChuanglanSMS;

class Sms
{
    public function send($mobile, $code, $act = "register")
    {
        /*$engine = new ChuanglanSMS();
        $result = $engine->sendSMS($mobile, '【58好策略】您好，您的验证码是' . $code);
        $result = $engine->execResult($result);*/
		$content="【双盈资管】您的验证码是：".$code."，请在10分钟内输入";
		$url='https://api.mysubmail.com/message/send.json';
		$data="appid=31285&to=$mobile&content=$content&signature=7a283593bfc54e034555f3c106ac7f87";
		$result=json_decode($this->http_request($url, $data),true);		
		
        if ($result['status'] == 'success') {
            $sessKey = "{$mobile}_{$act}";
            session($sessKey, $code);
            return [true, $code];
        } else {
            return [false, $result['status']];
        }
    }
	public function http_request($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}	
}