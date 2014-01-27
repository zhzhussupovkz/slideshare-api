<?php

/**
* @author zhzhussupovkz@gmail.com
*/
class SlideShareAPI {

	//api key
	private $apiKey = 'Your API key';

	//secret
	private $secret = 'Your secret word';

	//slideshare api url
	private $url = 'https://www.slideshare.net/api/2/';

	//username
	private $username = 'Username';

	//password
	private $password = 'Password';

	//the format for the returned data
	private $format = 'array';

	/*
	API uploads are limited to 100 per day per API_Key.
	*/
	private $interval = 1800;

	//path to the file
	private $file = '';


	/**
	* All requests made using the SlideShare API must have the following parameters:
	* api_key,
	* ts,
	* hash
	* @param string $method api method to use
	* @param array $params params of the api method
	* @param boolean $auth is the authorization required?
	* @return $this->getSlides()
	*/
	private function getData($method, $params = array(), $auth = false) {
		$ts = time();
		$hash = sha1($this->secret.$ts);
		$user = array('username' => $this->username, 'password' => $this->password);
		if ($auth) {
			$params = array_merge($user, $params);
		}
		$api = array('api_key' => $this->apiKey, 'ts' => $ts, 'hash' => $hash);
		$params = array_merge($api, $params);
		$params = http_build_query($params);

		$fileURL = $this->url . $method. '/?'.$params;

		if ($this->getFile()) {
			$tf = filemtime($this->file);
			if (($ts - $tf) >= $this->interval) {
				$options = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => 0,
				);
				$ch = curl_init();
				curl_setopt_array($ch, $options);
				$result = curl_exec($ch);
				if ($result == false)
					throw new Exception(curl_error($ch));
				file_put_contents($this->file, $result);
				curl_close($ch);
				$final = file_get_contents($this->file);
			} else {
				$final = file_get_contents($this->file);
				if ($result == false)
					throw new Exception("Invalid data format");
			}
		}
		return $this->getSlides($final);
	}

	/**
	* @return file name if exists
	*/
	private function getFile() {
		if (file_exists($this->file)) {
			return $this->file;
		} else {
			return false;
		}
	}

	/**
	* @param string $result data from api method
	* @return SimpleXMLObject or array
	* For work with SimpleXML
	* see http://www.php.net/manual/en/book.simplexml.php
	*/
	private function getSlides($result) {
		switch ($this->format) {

			//return array
			case 'array':
				$xml = simplexml_load_string($result);
				$json = json_encode($xml);
				$final = json_decode($json, TRUE);
				break;

			//return SimpleXMLObject
			case 'object':
				$final = new SimpleXMLElement($result);
				break;
		}
		if (!$final)
			throw new Exception("Invalid data format");
		return $final;
	}

	/**
	* @param string $code is code of error
	* @return Exception with error description
	* get errors by code
	*/
	private function getError($code) {
		$errors = array(
			'0' => 'No API Key Provided',
			'1' => 'Failed API validation',
			'2' => 'Failed User authentication',
			'3' => 'Missing title',
			'4' => 'Missing file for upload',
			'5' => 'Blank title',
			'6' => 'Slideshow file is not a source object',
			'7' => 'Invalid extension',
			'8' => 'File size too big',
			'9' => 'SlideShow Not Found',
			'10' => 'User Not Found',
			'11' => 'Group Not Found',
			'12' => 'No Tag Provided',
			'13' => 'Tag Not Found',
			'14' => 'Required Parameter Missing',
			'15' => 'Search query cannot be blank',
			'16' => 'Insufficient permissions',
			'17' => 'Incorrect parameters',
			'70' => 'Account already linked',
			'71' => 'No linked account found',
			'72' => 'User not created',
			'73' => 'Invalid Application ID',
			'74' => 'Login already exists',
			'75' => 'EMail already exists',
			'99' => 'Account Exceeded Daily Limit',
			'100' => 'Your Account has been blocked',
			);
		return new Exception($this->error[$code]);
	}

	/**
	* function for set format
	* @param string $format set output data format
	*/
	public function setDataFormat($format) {
		$this->format = $format;
	}

	/**
	* get slideshow
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: slideshow_id, slideshow_url
	* $params = array('slideshow_id' => 'ID', 'slideshow_url' => 'http://...', ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshow
	*/
	public function getSlideshow($params) {
		if (!array_key_exists('slideshow', $params) || !array_key_exists('slideshow_url', $params))
			return 'Not set the required params';
		return $this->getData('get_slideshow', $params);
	}

	/**
	* get slideshows_by_tag
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: tag_name
	* $params = array('tag_name' => 'Tag', 'limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_tag
	*/
	public function getSsByTag($params) {
		if (!array_key_exists('tag_name', $params))
			return 'Not set the required params';
		return $this->getData('get_slideshows_by_tag', $params);
	}

	/**
	* get slideshows_by_group
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: group_name
	* $params = array('group_name' => 'Group', 'limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_group
	*/
	public function getSsByGroup($params) {
		if (!array_key_exists('group_name', $params))
			return 'Not set the required params';
		return $this->getData('get_slideshows_by_group', $params);
	}

	/**
	* get slideshows_by_user
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username_for
	* $params = array('limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_user
	*/
	public function getSsByUser($params = array()) {
		$user = array('username_for' => $this->username);
		$params = array_merge($user, $params);
		return $this->getData('get_slideshows_by_user', $params);
	}

	/**
	* search slideshows
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: q
	* $params = array('q' => 'query string', ...);
	* all params http://www.slideshare.net/developers/documentation#search_slideshows
	*/
	public function searchSlideshows($params) {
		if (!array_key_exists('q', $params))
			return 'Not set the required params';
		return $this->getData('search_slideshows', $params);
	}

	/**
	* get_user_groups
	* @return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserGroups() {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_groups', $params);
	}

	/**
	* get_user_favorites
	* @return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserFavorites() {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_favorites', $params);
	}

	/**
	* get_user_contacts
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserContacts($params) {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_contacts', $params);
	}

	// ------------------For all of this methods AUTHORIZATION REQUIRED --------------------
	/**
	* get_user_tags
	* @return SimpleXMLObject
	* required params: username, password
	*/
	public function getUserTags() {
		return $this->getData('get_user_tags', $params = array(), true);
	}

	/**
	* edit_slideshow
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $params = array('slideshow_id' => 'ID', 'slideshow_title' => 'Hello world!'...);
	* all params http://www.slideshare.net/developers/documentation#edit_slideshow
	*/
	public function editSlideshow($params) {
		if (!array_key_exists('slideshow_id', $params))
			return 'Not set the required params';
		return $this->getData('edit_slideshow', $params, true);
	}

	/**
	* delete_slideshow
	* @param integer $id slideshow's id
	* @return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function deleteSlideshow($id) {
		if (!array_key_exists('slideshow_id', $params))
			return 'Not set the required params';
		$params = array('slideshow_id' => $id);
		return $this->getData('delete_slideshow', $params, true);
	}

	/**
	* upload_slideshow
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username, password, slideshow_title, upload_url
	* $params = array('slideshow_title' => 'Title', 'upload_url' => 'http://domain.tld/directory/my_power_point.ppt'...);
	* all params http://www.slideshare.net/developers/documentation#upload_slideshow
	*/
	public function uploadSlideshow($params) {
		if (!array_key_exists('slideshow_title', $params) || !array_key_exists('upload_url', $params))
			return 'Not set the required params';
		return $this->getData('upload_slideshow', $params, true);
	}

	/**
	* add_favorite
	* @param integer $id slideshow's id
	* @return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function addFavoriteSlideshow($id) {
		if (!array_key_exists('slideshow_id', $params))
			return 'Not set the required params';
		$params = array('slideshow_id' => $id);
		return $this->getData('add_favorite', $params, true);
	}

	/**
	* check_favorite
	* @param integer $id slideshow's id
	* @return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function checkFavoriteSlideshow($id) {
		if (!array_key_exists('slideshow_id', $params))
			return 'Not set the required params';
		$params = array('slideshow_id' => $id);
		return $this->getData('check_favorite', $params, true);
	}

	/**
	* get_user_campaigns
	* @return SimpleXMLObject
	* required params: username, password
	*/
	public function getUserCampaigns() {
		return $this->getData('get_user_campaigns', $params = array(), true);
	}

	/**
	* get_user_leads
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username, password
	* $params = array('begin' => 'YYYYMMDDHHMM', 'end' => 'YYYYMMDDHHMM');
	* all params http://www.slideshare.net/developers/documentation#get_user_leads
	*/
	public function getUserLeads($params) {
		return $this->getData('get_user_leads', $params, true);
	}

	/**
	* get_user_campaign_leads
	* @param array $params params of the api method
	* @return SimpleXMLObject
	* required params: username, password, campaign_id
	* $params = array('campaign_id' => 'ID', 'begin' => 'YYYYMMDDHHMM', 'end' => 'YYYYMMDDHHMM');
	* all params http://www.slideshare.net/developers/documentation#
	*/
	public function getUserCampaignLeads($params) {
		if (!array_key_exists('campaign_id', $params))
			return 'Not set the required params';
		return $this->getData('get_user_campaign_leads', $params, true);
	}

}