<?php
namespace App\Controller;


class LoginController extends AppbaseController {
	
	public function send_code(){

		$moblie =I('post.mobile');
		if(!$moblie){
			ereturn('请输入手机号');
		}
		//查询账号是否已经存在 存在就无需发送
		$user = M('Users');
		$where  = array();
		$where['mobile'] =$moblie;
		$exisit = $user->where($where)->find();
		if($exisit){
			ereturn('账号已经存在，无需注册');
		}else{
			//验证码入库
			$data = array();
			$data['code'] = rand(1000,9999);
			$data['mobile'] = $moblie;
			$data['type'] = 1;
			$data['expiretime'] = time()+180;
			$message = M('Message');
			//删除无效数据
			$where = array();
			$where['mobile'] = $moblie;
			$where['type'] = 1;
			$message->where($where)->delete();
			$res = $message->add($data);

			if($res){
				$rr = send_message($moblie,$data['code']);
				if($rr){
					sreturn('短信发送成功');
				}
			}
			//
		}

	}




	// 前台用户手机注册
	public function register(){
		$mobile = I('post.mobile');
		$code = I('post.code');
		$password=I('post.password');
		//验证码是否过期
		$message = M('Message');
		$where = array();
		$where['mobile'] = $mobile;
		$data = $message->where($where)->order('id desc')->find();
		if(!$data){
			ereturn('验证码无效');
		}

		if( $data['expiretime']<time() || $data['code'] !=$code ){
			ereturn('验证码无效');
		}


        $rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('mobile', 'require', '手机号不能为空！', 1 ),
            array('mobile','','手机号已被注册！！',0,'unique',3),
            array('password','require','密码不能为空！',1),
            array('password','5,20',"密码长度至少5位，最多20位！",1,'length',3),
        );

	    $users_model=M("Users");
	     
	    if($users_model->validate($rules)->create()===false){
			$return = $users_model->getError();
			ereturn($return);
	    }

	    $users_model=M("Users");
	    $data=array(
	        'user_login' => '',
	        'user_email' => '',
	        'mobile' =>$mobile,
	        'user_nicename' =>'',
	        'user_pass' => sp_password($password),
	        'last_login_ip' => get_client_ip(0,true),
	        'create_time' => date("Y-m-d H:i:s"),
	        'last_login_time' => date("Y-m-d H:i:s"),
	        'user_status' => 1,
	        "user_type"=>2,//会员
	    );
	    
	    $result = $users_model->add($data);
	    if($result){
			sreturn('注册成功');
	    }else{
			ereturn('注册失败');
		}
	}


	/**
	 * 返回加密串
	 */
	private function setToken(){
		return md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));
	}

	public function send_code_login(){
		$moblie =I('post.mobile');
		if(!$moblie){
			ereturn('请输入手机号');
		}
		//查询账号是否已经存在 存在就无需发送
		$user = M('Users');
		$where  = array();
		$where['mobile'] =$moblie;
		$exisit = $user->where($where)->find();
		if(!$exisit){
			ereturn('账号不存在，请注册');
		}else{
			//验证码入库
			$data = array();
			$data['code'] = rand(1000,9999);
			$data['mobile'] = $moblie;
			$data['type'] = 3;
			$data['expiretime'] = time()+180;
			$message = M('Message');
			//删除无效数据
			$where = array();
			$where['mobile'] = $moblie;
			$where['type'] = 3;
			$message->where($where)->delete();
			$res = $message->add($data);

			if($res){
				$rr = send_message($moblie,$data['code']);
				if($rr){
					sreturn('短信发送成功');
				}
			}
			//
		}
	}
	public function dologin_formcode(){
		$mobile = I('post.mobile');
		$users_model=M('Users');

		$where = array("user_status"=>1);
		$where['mobile']=I('post.mobile');
		$code=I('post.code');
		$result = $users_model->where($where)->find();

		if(!empty($result)){
			$message = M('Message');
			$where = array();
			$where['mobile'] = $mobile;
			$data = $message->where($where)->order('id desc')->find();
			if(!$data){
				ereturn('验证码无效');
			}

			if( $data['expiretime']<time() || $data['code'] !=$code ){
				ereturn('验证码无效');
			}

				$info = array();
				$info['token'] =  $this->setToken();
				$info['userid'] = $result["id"];
				$info['expiretime'] = time()+3600*24;
				$token_model = M('token');
				$where = array();
				$where['mobile'] = $mobile;
				$token_model->where($where)->delete();
				$add = $token_model->add($info);
				if($add){
					$data = array(
						'last_login_time' => date("Y-m-d H:i:s"),
						'last_login_ip' => get_client_ip(0,true),
					);
					$users_model->where(array('id'=>$result["id"]))->save($data);
					$userinfo = array();
					$userinfo['user_nicename'] = $result['user_nicename'];
					$userinfo['user_email'] = $result['user_email'];
					$userinfo['sex'] = $result['sex'];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
					$return = array();
					$return['token'] = $info['token'];
					$return['userinfo'] = $userinfo;
					sreturn('登录成功',$return);
				}else{
					ereturn('登录失败！');
				}


		}else{
			ereturn("用户名不存在或已被拉黑！");
		}

	}
	// 处理前台用户手机登录
	public function dologin(){
		$mobile = I('post.mobile');
		$users_model=M('Users');
		$where = array("user_status"=>1);
		$where['mobile']=I('post.mobile');
		$password=I('post.password');
		$result = $users_model->where($where)->find();

		if(!empty($result)){
			if(sp_compare_password($password, $result['user_pass'])){
				$info = array();
				$info['token'] =  $this->setToken();
				$info['userid'] = $result["id"];
				$info['expiretime'] = time()+3600*24;
				$token_model = M('token');
				$where = array();
				$where['mobile'] = $mobile;
				$token_model->where($where)->delete();
				$add = $token_model->add($info);
				if($add){
					$data = array(
						'last_login_time' => date("Y-m-d H:i:s"),
						'last_login_ip' => get_client_ip(0,true),
					);
					$users_model->where(array('id'=>$result["id"]))->save($data);
					$userinfo = array();
					$userinfo['user_nicename'] = $result['user_nicename'];
					$userinfo['user_email'] = $result['user_email'];
					$userinfo['sex'] = $result['sex'];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
//					$userinfo['user_nicename'] = $result[''];
					$return = array();
					$return['token'] = $info['token'];
					$return['userinfo'] = $userinfo;
					sreturn('登录成功',$return);
				}else{
					ereturn('登录失败！');
				}

			}else{
				ereturn('密码错误！');
			}
		}else{
			ereturn("用户名不存在或已被拉黑！");
		}
	}



	public function send_code_forget(){

		$mobile = I('post.mobile');
		//验证码入库
		$data = array();
		$data['code'] = rand(1000,9999);
		$data['mobile'] = $mobile;
		$data['type'] = 2;
		$data['expiretime'] = time()+180;
		$message = M('Message');
		//删除无效数据
		$where = array();
		$where['mobile'] = $mobile;
		$where['type'] = 2;
		$message->where($where)->delete();
		$res = $message->add($data);

		if($res){
			$rr = send_message($mobile,$data['code']);
			if($rr){
				sreturn('验证码发送成功');
			}
		}


	}

	// 前台用户忘记密码提交(手机方式找回)
	public function do_mobile_forgot_password(){
		$password = I('post.password');
		$code = I('post.code');
		$mobile = I('post.mobile');


		$data = array();
		$data['mobile'] =$mobile;
		$data['password'] = $password;
		$rules = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('mobile', 'require', '手机号不能为空！', 1 ),
			array('password','require','密码不能为空！',1),
			array('password','5,20',"密码长度至少5位，最多20位！",1,'length',3),
		);

		$users_model=M("Users");

		if($users_model->validate($rules)->create($data)===false){
			ereturn($users_model->getError());
		}
		$where = array();
		$where['mobile'] =  $mobile;
		$where['type'] = 2;
		$codeInfo = M('Message')->where($where)->find();

		if(!$codeInfo){
			ereturn('验证码错误');
		}
		if($code!=$codeInfo['code'] || $codeInfo['expiretime']<time()){
			ereturn('验证码错误或失效');
		}
		$where = array();
		$where['mobile']=$mobile;

		$users_model=M("Users");
		$result = $users_model->where($where)->count();
		if($result){
			$result=$users_model->where($where)->save(array('user_pass' => sp_password($password)));
			if($result!==false){
				sreturn('密码重置成功');
			}else{
				ereturn('密码重置失败');
			}
		}else{
			ereturn('该手机号未注册');
		}

	}
	

	
	
}