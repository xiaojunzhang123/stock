<?php
/**
 */
require_once "WunPay.Exception.php";
require_once "WunPay.Config.php";
require_once "WunPay.Data.php";

class WunPayApi
{
	
	
	/**
	 * 
	 * 统一下单，
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public static function unifiedOrder($inputObj, $timeOut = 30)
	{
		$url = "http://39.104.111.172:93/pay/frontGateway";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WunPayException("缺少统一支付接口必填参数out_trade_no！");
		}else if(!$inputObj->IsBodySet()){
			throw new WunPayException("缺少统一支付接口必填参数body！");
		}else if(!$inputObj->IsTotal_feeSet()) {
			throw new WunPayException("缺少统一支付接口必填参数total_fee！");
		}else if(!$inputObj->IsAppidSet()) {
			throw new WunPayException("缺少统一支付接口必填参数appid！");
		}else if(!$inputObj->IsMch_idSet()) {
			throw new WunPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new WunPayException("缺少统一支付接口必填参数method！");
        }
		
				

		$inputObj->SetVersion(WunPayConfig::VERSION);
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		//签名
		$inputObj->SetSign();
		
		
		$data = $inputObj->GetValues();


        self::setHtml($url,$data);
	}
	
	public static function setHtml($tjurl, $arraystr)
    {
        $str = '<form id="Form1" name="Form1" method="post" action="' . $tjurl . '">';
        foreach ($arraystr as $key => $val) {
            $str .= '<input type="hidden" name="' . $key . '" value="' . $val . '">';
        }
        $str .= '</form>';
        $str .= '<script>';
        $str .= 'document.Form1.submit();';
        $str .= '</script>';
        exit($str);
    }
	/**
	 * 以post方式提交xml到对应的接口url
	 *
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WunPayException
	 */
	public static function postFormCurl($data, $url,$second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
	
		//如果有配置代理这里就设置代理
		if(WunPayConfig::CURL_PROXY_HOST != "0.0.0.0"
				&& WunPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WunPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WunPayConfig::CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);//TRUE
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//2严格校验
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
// 		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new TransferPayException("curl出错，错误码:$error");
		}
	}
	
	
	/**niy
	 *
	 * 查询订单，
	 * @param
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($inputObj, $timeOut = 30)
	{
		$url = "http://39.104.111.172:93/pay/gateway";
        if(!$inputObj->IsAppidSet()) {
            throw new WunPayException("缺少统一支付接口必填参数appid！");
        }else if(!$inputObj->IsMch_idSet()) {
            throw new WunPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new WunPayException("缺少统一支付接口必填参数method！");
        }
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() ) {
			throw new WunPayException("订单查询接口中，out_trade_no不能为空！");
		}

		$inputObj->SetVersion(WunPayConfig::VERSION);
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
	
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
	
		$result = self::postXmlCurl($xml, $url, false, $timeOut);
	
		return $result;
	}
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	
	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}
	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WunPayException
	 */
	public static function postXmlCurl($xml, $url,$second = 30)
	{		
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		
		//如果有配置代理这里就设置代理
		if(WunPayConfig::CURL_PROXY_HOST != "0.0.0.0"
			&& WunPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, WunPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, WunPayConfig::CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);//TRUE
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//2严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new TransferPayException("curl出错，错误码:$error");
		}
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}

