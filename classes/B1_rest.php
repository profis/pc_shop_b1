<?php 

class B1_rest {
	
	//protected $_template_group = 'checkout_user';
	
	private $url = null;
	private $responseBody = null;
	private $responseInfo = null;
	
	const privateKey = 'donttellanyone'; //private key
	const apiKey = '2724e301bd61f5f61d943a104057525c';	//public key
	
	private $_private_key;
	private $_api_key;
	
	public function __construct($private_key = '', $api_key = '') {
		$this->_private_key = $private_key;
		$this->_api_key = $api_key;
	}
	
	public function getResponseBody() {
		return $this->responseBody;
	}

	public function getResponseInfo() {
		return $this->responseInfo;
	}
		
	public function rest($what, $id=null, $lid=null, $mdate=null, $addparams=array()) {	
		$rez = null;
		switch ($what) {
			case 'pardavimas':
				$what = 'eshopsale';
			break;
			default;
				if ($id) $addparams['id'] = $id;
				if ($lid) $addparams['lid'] = $lid;
				if ($mdate)	$addparams['lmod'] = $mdate;
			break;
		}
		$what .='/';	
		
		//$this->url = 'http://av.ebuh.dev/api/'.$what; //lokalus testavimui
		$this->url = 'https://www.b1.lt/api/'.$what; //globalus
		
		$this->executeRequest($addparams, ($what == 'eshopsale')); //isimtis pardavimu atidavimui jie vykdomi per post
		
		if ($this->responseInfo['http_code']==200) {
			if ($this->responseInfo["content_type"]=="application/pdf") {
				$rez = $this->responseBody;
			} else {
				$rez = json_decode($this->responseBody);
			}
		} else {
			return null;
		}
		return $rez;
	}
	
	/*
	siunciam requesta
	*/
	protected function executeRequest($data, $doPost=false) {	
		$ch = curl_init();		
		$this->doExecute($ch, $data, $doPost);	
	}
	
	protected function doExecute(&$curlHandle, $postdata=null, $doPost=false) {
		$this->setCurlOpts($curlHandle, $postdata, $doPost);
		$this->responseBody = curl_exec($curlHandle);
		$this->responseInfo	= curl_getinfo($curlHandle);
		
		curl_close($curlHandle);
	}

	/*
	pasirasom requesta uzhashindami visus paramterus kurie siuciami
	*/	
	protected function signRequest($params) {
		
		return hash_hmac('ripemd160', http_build_query($params), $this->_private_key); 
	}
	
	protected function setCurlOpts(&$curlHandle, $params=array(), $post=false) {
	
		$params['time'] = time(); //timestamp kad isvengti request replay bandymu
		$params['apiKey'] = $this->_api_key; //public key
		$params['signature'] = $this->signRequest($params); //requesto parasas

		if ($post) {
			curl_setopt($curlHandle,CURLOPT_HEADER, false);
			curl_setopt($curlHandle, CURLOPT_POST, count($params));
			curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $params); 
		} else {
			$this->url .= '?'.http_build_query($params);
		}
		
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
		curl_setopt($curlHandle, CURLOPT_URL, $this->url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array ('Accept: text/html'));	
	}
	
}