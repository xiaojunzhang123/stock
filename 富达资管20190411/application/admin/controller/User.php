<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\UserGiveLogic;
use app\admin\logic\UserLogic;
use app\admin\logic\UserWithdrawLogic;
use think\Db;
use think\Request;
class User extends Base
{
    public $userLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->userLogic = new UserLogic();
    }

    public function lists()
    {
        $_res = $this->userLogic->pageUserLists(input(''));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function modify()
    {
        if(request()->isPost()) {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                if($this->userLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $id = input('user_id');
        $data = $this->userLogic->getOne($id);
        $this->assign('data', $data);
        $this->assign('id', $id);
        return view();
    }

    public function modifyPwd()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify_pwd')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
    }

    public function giveLists()
    {
        $_res = $this->userLogic->pageUserLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }
    public function giveAccount()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->setInc(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        };

    }

    public function giveLog()
    {
        $_res = (new UserGiveLogic())->pageUserGiveLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }

    public function withdrawLists()
    {
        $_res = (new UserWithdrawLogic())->pageUserWithdrawLists(input(''));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function withdrawDetail($id = null)
    {
        $withdraw = (new UserWithdrawLogic())->getWithdrawById($id);
        if($withdraw){
            $state = [0=>"待审核", 1=>"审核通过",-1=>"审核拒绝"];
            $withdraw['remark'] = json_decode($withdraw['remark'], true);
            $withdraw['state_text'] = $state[$withdraw['state']];
            $this->assign("withdraw", $withdraw);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function withdraw()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('UserWithDraw');
            if(!$validate->scene('user_withdraw')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input('post.id/d');
                $state = input('post.state/d');
                list($res, $msg) = (new UserWithdrawLogic())->doWithdraw($id, $state);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail($msg);
                }
            }
        }
    }
	
	public function cert()
    {
	//微会员下微圈
    $userid=$this->adminId;//dump($userid);die;
	if($userid=='1'){
		$wheretemp='1=1';
	}else{
		$wheretemp='s.admin_id='.$userid;
	}
	//$userid='207';
	$mencert=Db::field('s.username,a.*')//截取表s的name列 和表a的全部
    ->table(['stock_user_cert'=>'a','stock_user'=>'s'])
    ->where('a.userid=s.user_id')//查询条件语句
	->where($wheretemp)//查询条件语句
    ->paginate(10);
	
	$mencertcount=Db::field('s.username,a.*')//截取表s的name列 和表a的全部
    ->table(['stock_user_cert'=>'a','stock_user'=>'s'])
    ->where('a.userid=s.user_id')//查询条件语句
	->where($wheretemp)//查询条件语句
    ->select();
	
	// 获取分页显示
	$count = count($mencertcount);
	$this->assign('count', $count);
	$this->assign('mencert', $mencert);
	$this->assign('page', $mencert->render()); 
        return view();
    }
	
	 public function certupdate()
    {
        if(request()->isPost())
        {
			 $id = input('user_id');
            //$validate = \think\Loader::validate('User_cert');
             $status=Db::table('stock_user_cert')->where('userid', $id)->update(['status' => $_POST['status']]);

                if($status){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            
        }
	    $id = input('id');
        $datatemp=Db::table('stock_user_cert')
		->where([
			'id'    =>  $id
		])
		->select();
        $this->assign('data', $datatemp[0]);
        $this->assign('id', $id);
        return view();
    }
}