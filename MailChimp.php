<?php

/***
* MailChimp class
* @author zhzhussupovkz@gmail.com
*/

class MailChimp {

	//api key
	private $api_key;

	//api endpoint
	private $api_endpoint = 'https://<dc>.api.mailchimp.com/2.0/';

	//constructor
	public function __contstruct($api_key = null) {
		$this->api_key = $api_key;
		list(, $portion) = explode('-', $this->api_key);
		$this->api_endpoint = str_replace('<dc>', $portion, $this->api_endpoint);
	}

	//send POST request to server
	private function sendRequest($method, $params = array()) {
		$auth = array('apikey' => $this->api_key);
		$params = array_merge($auth, $params);
		$data = json_encode($params);

		$options = array(
			CURLOPT_URL => $this->api_endpoint.''.$method.'.json',
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data),
			);
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		if ($result == false)
			throw new Exception(curl_error($ch));
		curl_close($ch);
		$final = json_decode($result, TRUE);
		if (isset($final['status']))
			if ($final['status'] == 'error')
				throw new Exception($final['name']);
		if (!$final)
			throw new Exception('Получены неверные данные, пожалуйста, убедитесь, что запрашиваемый метод API существует');
		return $final;
	}

	/*********** CAMPAIGNS METHODS ************/

	/**
	* Get the content (both html and text) for a campaign either 
	* as it would appear in the campaign archive or as the raw, original content 
	*/
	public function campaignsContent($cid = null, $optional = array()) {
		$required = array('cid' => $cid);
		$params = array_merge($required, $optional);
		return $this->sendRequest('campaigns/content', $params);
	}

	/**
	* Create a new draft campaign to send. 
	* You can not have more than 32,000 campaigns in your account.
	*/
	public function campaignsCreate($type = null, $options = array(), $content = array(), 
	$segment_opts = array(), $type_opts = array()) {
		$required = array('type' => $type);
		$optional = array(
			'options' => $options,
			'content' => $content,
			'segment_opts' => $segment_opts,
			'type_opts' => $type_opts
			);
		$params = array_merge($required, $optional);
		return $this->sendRequest('campaigns/create', $params);
	}

	/**
	* Delete a campaign. 
	* Seriously, "poof, gone!" - be careful! 
	* Seriously, no one can undelete these.
	*/
	public function campaignsDelete($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/delete', $params);
	}

	/**
	* Get the list of campaigns and their details matching the specified filters
	*/
	public function campaignsList($filters = array(), $start = null, $limit = null, $sort_field = null, $sort_dir = null) {
		$params = array_merge($filters, array(
			'start' => $start,
			'limit' => $limit,
			'sort_field' => $sort_field,
			'sort_dir' => $sort_dir,
			));
		return $this->sendRequest('campaigns/list', $params);
	}

	/**
	* Pause an AutoResponder or RSS campaign from sending
	*/
	public function campaignsPause($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/pause', $params);
	}

	/**
	* Returns information on whether a campaign is ready 
	* to send and possible issues we may have 
	* detected with it - very similar to the confirmation step in the app.
	*/
	public function campaignsReady($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/ready', $params);
	}

	/**
	* Replicate a campaign.
	*/
	public function campaignsReplicate($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/replicate', $params);
	}

	/**
	* Resume sending an AutoResponder or RSS campaign
	*/
	public function campaignsResume($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/resume', $params);
	}

	/**
	* Schedule a campaign to be sent in batches sometime in the future. 
	* Only valid for "regular" campaigns
	*/
	public function campaignsScheduleBatch($cid = null, $schedule_time = null, $num_batches = null, $stagger_mins = null) {
		$required = array('cid' => $cid, 'schedule_time' => $schedule_time);
		$optional = array('num_batches' => $num_batches, 'stagger_mins' => $stagger_mins);
		$params = array_merge($required, $optional);
		return $this->sendRequest('campaigns/schedule-batch', $params);
	}

	/**
	* Schedule a campaign to be sent in the future
	*/
	public function campaignsSchedule($cid = null, $schedule_time = null, $schedule_time_b = null) {
		$required = array('cid' => $cid, 'schedule_time' => $schedule_time);
		$optional = array('schedule_time_b' => $schedule_time_b);
		$params = array_merge($required, $optional);
		return $this->sendRequest('campaigns/schedule', $params);
	}

	/**
	* Allows one to test their segmentation 
	* rules before creating a campaign using them
	*/
	public function campaignsSegmentTest($list_id = null, $options = array()) {
		$required = array('list_id' => $list_id);
		$params = array_merge($required, $options);
		return $this->sendRequest('campaigns/segment-test', $params);
	}

	/**
	* Send a given campaign immediately.
	* For RSS campaigns, this will "start" them.
	*/
	public function campaignsSend($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/send', $params);
	}

	/**
	* Send a test of this campaign to the provided email addresses
	*/
	public function campaignsSendTest($cid = null, $test_emails = array(), $send_type = null) {
		$required = array('cid' => $cid);
		$optional = array('test_emails' => $test_emails, 'send_type' => $send_type);
		$params = array($required, $optional);
		return $this->sendRequest('campaigns/send-test', $params);
	}

	/**
	* Get the HTML template content sections for a campaign. 
	* Note that this will return very jagged, 
	* non-standard results based on the template a 
	* campaign is using. You only want to use this 
	* if you want to allow editing template sections in your application.
	*/
	public function campaignsTemplateContent($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/template-content', $params);
	}

	/**
	* Unschedule a campaign that is scheduled to be sent in the future
	*/
	public function campaignsUnschedule($cid = null) {
		$params = array('cid' => $cid);
		return $this->sendRequest('campaigns/unschedule', $params);
	}

	/**
	* Update just about any setting besides type for a campaign that has not been sent
	*/
	public function campaignsUpdate($cid = null, $name = null, $value = array()) {
		$params = array('cid' => $cid, 'name' => $name, 'value' => $value);
		return $this->sendRequest('campaigns/update', $params);
	}

	/******************* ECOMM METHODS *********************

	/**
	* Import Ecommerce Order Information to be used for Segmentation. 
	* This will generally be used by ecommerce package 
	* plugins provided by us or by 3rd part system developers.
	*/
	public function ecommOrderAdd($order = array()) {
		$params = array('order' => $order);
		return $this->sendRequest('ecomm/order-add', $params);
	}

	/**
	* Delete Ecommerce Order Information used for segmentation. 
	* This will generally be used by ecommerce package plugins 
	* that we provide or by 3rd part system developers.
	*/
	public function ecommmOrderDel($store_id = null, $order_id = null) {
		$params = array('store_id' => $store_id, 'order_id' => $order_id);
		return $this->sendRequest('ecomm/order-del', $params);
	}

	/**
	* Retrieve the Ecommerce Orders for an account
	*/
	public function ecommOrders($cid = null, $start = null, $limit = null, $since = null) {
		$params = array('cid' => $cid, 'start' => $start, 'limit' => $limit, 'since' => $since);
		return $this->sendRequest('ecomm/oreders', $params);
	}

	/****************** FOLDERS METHODS **************/

	/**
	* Add a new folder to file campaigns, autoresponders, or templates in
	*/
	public function foldersAdd($name = null, $type = null) {
		$params = array('name' => $name, 'type' => $type);
		return $this->sendRequest('folders/add', $params);
	}

	/**
	* Delete a campaign, autoresponder, or template folder. 
	* Note that this will simply make whatever was in the 
	* folder appear unfiled, no other data is removed
	*/
	public function foldersDel($fid = null, $type = null) {
		$params = array('fid' => $fid, 'type' => $type);
		return $this->sendRequest('folders/del', $params);
	}

	/**
	* List all the folders of a certain type
	*/
	public function foldersList($type = null) {
		$params = array('type' => $type);
		return $this->sendRequest('folders/list', $params);
	}

	/**
	* Update the name of a folder for campaigns, autoresponders, or templates
	*/
	public function foldersUpdate($fid = null, $name = null, $type = null) {
		$params = array('fid' => $fid, 'name' => $name, 'type' => $type);
		return $this->sendRequest('folders/update', $params);
	}

	/***************** GALLERY METHODS **************/

	/**
	* Return a section of the image gallery
	*/
	public function galleryList($opts = array()) {
		$params = array('opts' => $opts);
		return $this->sendRequest('gallery/list', $params);
	}
}