<?php

/**
* YiiSlideShare - Yii extension for work with SlideShare API v2.0
*
* @author zhzhussupovkz@gmail.com
*
* Use:
* 
* 1: cd ../protected/extensions
* 
* 2: git clone -b yii-ext https://github.com/zhzhussupovkz/slideshare-api.git
* 
* 3: add this lines to your protected/config/main.php:
* 
* ...
* 
* // application components
* 	'components'=>array(
* 		'user'=>array(
* 			// enable cookie-based authentication
* 			'allowAutoLogin'=>true,
* 		),
* ...
* ...
* 		'yiiSlideShare' => array(
* 			'class' => 'ext.slideshare-api.YiiSlideShare',
* 		),
* ...
* ...
*
* 4. set params in  ext.slideshare-api.SlideShareAPI class :
* 
* 	//api key
* 	private $apiKey = 'Your API key';
*
* 	//secret
* 	private $secret = 'Your secret word';
*
* 	//slideshare api url
* 	private $url = 'https://www.slideshare.net/api/2/';
*
* 	//username
* 	private $username = 'Username';
*
* 	//password
* 	private $password = 'Password';
* 
* 	//API uploads are limited to 100 per day per API_Key.
* 	private $interval = 1800;
* 
* 	//path to the file
* 	private $file = '';
* 
* 
* 5.use like this:
* 
* 	$ssObj = Yii::app()->yiiSlideShare->load();
* 	$params = array('limit' => 10, 'offset' => 12, 'detailed' => 1);
* 	
* 	
* 	//return SimpleXMLObject. See http://www.php.net/manual/en/book.simplexml.php
* 	$slides = $ssObj->getSSByUser();
* 
*/
class YiiSlideShare extends CApplicationComponent {

	public function init() {
		parent::init();
		$directory = dirname(__FILE__);
		$alias = md5($directory);
		Yii::setPathOfAlias($alias, $directory);
		Yii::import($alias.'./lib/SlideShareAPI');
	}

	public function load() {
		return new SlideShareAPI;
	}
}

