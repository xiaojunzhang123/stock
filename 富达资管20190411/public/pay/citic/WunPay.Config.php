<?php
/**
 * Modify on 2016/07/07
 */
/**
* 	配置账号信息
*/

class WunPayConfig
{
	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	
	const NOTIFY_URL = D_ALI_NOTIFY_URL;

	const GATEWAY_URL = "http://xxxxxxxxxxxx/pay/frontGateway";
	
	const TEST_MCH_ID = "cmxxxxxxxxxxxxxxxxx";  //测试商户号
	const TEST_MCH_APPID = "caxxxxxxxxxxxxxxx";  //测试商户appid
	const TEST_MCH_KEY = "xxxxxxxxxxxxxxxxxxx";  //测试商户key
	
	const VERSION = "2.0.0";  //支付宝网关接口版本号
	const notify_url="http://www.baidu.com";
}
