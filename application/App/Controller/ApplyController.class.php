<?php
namespace App\Controller;
/*
 * 申请认证相关
 */

class ApplyController extends AppbaseController {

	private $userinfo;
	public function __construct(){
		parent::__construct();
		//  检测token
		$this->userinfo   =   $this->checkToken();
	}

	/*
	 * 认证进程返回
	 */
	public function process(){
		$userinfo  = $this->userinfo;

	}

	

	
	
}