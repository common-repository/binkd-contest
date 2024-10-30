<?php

class binkd_api_client {
	
	
	var $apiKey;
	
	function api_client( $apiKey = '') {
		$this->apiKey = $apiKey;
	}
		

     /**
	 * @param $service name of JSON service to call
	 * @return string url in string
	 */
	function request_Url( $service )
	{

		return 'https://api.binkd.com/api.svc'.'/'.$service.'?apikey='.get_option('binkd_apiKey');
		
	}

	/**
	 * 
	 * @return array|false Contest List or false on failure
	 */
	function get_contestList() {

		$jsonStr = json_decode(file_get_contents($this->request_Url('ContestGetList')), TRUE);

		return $jsonStr;
	}

}
?>