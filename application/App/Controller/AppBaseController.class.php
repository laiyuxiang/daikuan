<?php
namespace App\Controller;
use Think\Controller;

class AppbaseController extends Controller {




    /**
     * 检查用户token
     */
    protected function checkToken(){
        $token = I('post.token');
        if (!$token ) {
            ereturn('未传入token或token无效','-1');
        }

        $where           =  array();
        $where['token']  =  $token;

        $token  = M('token')->where($where)->find();

        //判断有效性
        if(!$token){
            ereturn('未传入token或token无效','-1');
        }
        if($token['expiretime']<time()){
            ereturn('未传入token或token无效','-1');
        }
        $where = array();

        $where['id'] = $token['userid'];

        $userinfo  = M('Users')->where($where)->find();

        return $userinfo;

    }
    /**
     * 获取用户信息
     */
    protected  function getUserinfo($userid){

    }

    /**
     * 检查用户状态
     */
    protected function  check_user(){
        $user_status=M('Users')->where(array("id"=>sp_get_current_userid()))->getField("user_status");
        if($user_status==2){
            $this->error('您还没有激活账号，请激活后再使用！',U("user/login/active"));
        }

        if($user_status==0){
            $this->error('此账号已经被禁止使用，请联系管理员！',__ROOT__."/");
        }
    }


}