<?php
namespace App\Controller;


class UserController extends AppbaseController {

	private $userinfo;
	public function __construct(){
		parent::__construct();
		//  检测token
		$this->userinfo   =   $this->checkToken();
	}

	

	
	
}