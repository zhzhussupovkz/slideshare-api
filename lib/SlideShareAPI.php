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

	/*
	All requests made using the SlideShare API must have the following parameters:
	api_key,
	ts,
	hash
	*/
	private function getData($method, $params = array(), $auth = false) {
		$ts = time();
		$hash = sha1($this->secret.$ts);
		$user = array('username' => $this->username, 'password' => $this->password);
		if ($auth) {
			$params = array_merge($user, $params);
		}
		$params = http_build_query($params);

		$fileURL = $this->url . $method. '/?api_key='. $this->apiKey 
				.'&ts='. $ts 
				.'&hash='. $hash.'&'
				.$params;

		if ($this->getFile()) {
			$tf = filemtime($this->file());
			if (($ts - $tf) >= $this->interval) {
				try {
					file_put_contents($this->file, file_get_contents($fileURL));
					$result = file_get_contents($this->file);
				} catch (Exception $e) {
					//do something
				}
			} else {
				try {
					$result = file_get_contents($this->file);
				} catch (Exception $e) {
					//do something
				}
			}
		}
		return $this->getSlides($result);
	}

	//File for writing data
	private function getFile() {
		if (file_exists($this->file)) {
			return $this->file;
		} else {
			return false;
		}
	}

	/*
	* return SimpleXMLObject or array
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
		return $final;
	}

	//function for set format
	public function setDataFormat($format) {
		$this->format = $format;
	}

	/*
	* get slideshow
	* return SimpleXMLObject
	* required params: slideshow_id, slideshow_url
	* $params = array('slideshow_id' => 'ID', 'slideshow_url' => 'http://...', ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshow
	*/
	public function getSlideshow($params) {
		return $this->getData('get_slideshow', $params);
	}

	/*
	* get slideshows_by_tag
	* return SimpleXMLObject
	* required params: tag_name
	* $params = array('tag_name' => 'Tag', 'limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_tag
	*/
	public function getSsByTag($params) {
		return $this->getData('get_slideshows_by_tag', $params);
	}

	/*
	* get slideshows_by_group
	* return SimpleXMLObject
	* required params: group_name
	* $params = array('group_name' => 'Group', 'limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_group
	*/
	public function getSsByGroup($params) {
		return $this->getData('get_slideshows_by_group', $params);
	}

	/*
	* get slideshows_by_user
	* return SimpleXMLObject
	* required params: username_for
	* $params = array('limit' => 10, 'offset' => 12, 'detailed' => 1 ...);
	* all params http://www.slideshare.net/developers/documentation#get_slideshows_by_user
	*/
	public function getSsByUser($params = array()) {
		$user = array('username_for' => $this->username);
		$params = array_merge($user, $params);
		return $this->getData('get_slideshows_by_user', $params);
	}

	/*
	* search slideshows
	* return SimpleXMLObject
	* required params: q
	* $params = array('q' => 'query string', ...);
	* all params http://www.slideshare.net/developers/documentation#search_slideshows
	*/
	public function searchSlideshows($params) {
		return $this->getData('search_slideshows', $params);
	}

	/*
	* get_user_groups
	* return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserGroups() {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_groups', $params);
	}

	/*
	* get_user_favorites
	* return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserFavorites() {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_favorites', $params);
	}

	/*
	* get_user_contacts
	* return SimpleXMLObject
	* required params: username_for
	*/
	public function getUserContacts($params) {
		$params = array('username_for' => $this->username);
		return $this->getData('get_user_contacts', $params);
	}

	// ------------------For all of this methods AUTHORIZATION REQUIRED --------------------
	/*
	* get_user_tags
	* return SimpleXMLObject
	* required params: username, password
	*/
	public function getUserTags() {
		return $this->getData('get_user_tags', $params = array(), true);
	}

	/*
	* edit_slideshow
	* return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $params = array('slideshow_id' => 'ID', 'slideshow_title' => 'Hello world!'...);
	* all params http://www.slideshare.net/developers/documentation#edit_slideshow
	*/
	public function editSlideshow($params) {
		return $this->getData('edit_slideshow', $params, true);
	}

	/*
	* delete_slideshow
	* return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function deleteSlideshow($id) {
		$params = array('slideshow_id' => $id);
		return $this->getData('delete_slideshow', $params, true);
	}

	/*
	* upload_slideshow
	* return SimpleXMLObject
	* required params: username, password, slideshow_title, upload_url
	* $params = array('slideshow_title' => 'Title', 'upload_url' => 'http://domain.tld/directory/my_power_point.ppt'...);
	* all params http://www.slideshare.net/developers/documentation#upload_slideshow
	*/
	public function uploadSlideshow($params) {
		return $this->getData('upload_slideshow', $params, true);
	}

	/*
	* add_favorite
	* return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function addFavoriteSlideshow($id) {
		$params = array('slideshow_id' => $id);
		return $this->getData('add_favorite', $params, true);
	}

	/*
	* check_favorite
	* return SimpleXMLObject
	* required params: username, password, slideshow_id
	* $id = ss-26156460;
	*/
	public function checkFavoriteSlideshow($id) {
		$params = array('slideshow_id' => $id);
		return $this->getData('check_favorite', $params, true);
	}

	/*
	* get_user_campaigns
	* return SimpleXMLObject
	* required params: username, password
	*/
	public function getUserCampaigns() {
		return $this->getData('get_user_campaigns', $params = array(), true);
	}

	/*
	* get_user_leads
	* return SimpleXMLObject
	* required params: username, password
	* $params = array('begin' => 'YYYYMMDDHHMM', 'end' => 'YYYYMMDDHHMM');
	* all params http://www.slideshare.net/developers/documentation#get_user_leads
	*/
	public function getUserLeads($params) {
		return $this->getData('get_user_leads', $params, true);
	}

	/*
	* get_user_campaign_leads
	* return SimpleXMLObject
	* required params: username, password, campaign_id
	* $params = array('campaign_id' => 'ID', 'begin' => 'YYYYMMDDHHMM', 'end' => 'YYYYMMDDHHMM');
	* all params http://www.slideshare.net/developers/documentation#
	*/
	public function addUserCampaignLeads($params) {
		return $this->getData('get_user_campaign_leads', $params, true);
	}

}