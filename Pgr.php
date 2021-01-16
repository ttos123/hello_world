<?php


namespace app\wwx\controller;

use app\admin\controller\Base;
use think\Console;

class Pgr  extends Base
{
    public function take()
    {

        return view('take');
    }
    public function drv()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $u_id=db('user')->where('u_tname',$username)->find();
            $where = " u_id = " . $u_id['u_id'] . " and status = 1 and pay = 1";
            $list = db('user_taxi')->alias('u')
                ->where($where)
                //->order('role_id,u.user_id')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);
        } else {
            return view('drv');
        }
    }

    public function being()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $temp=db('user')->where('u_tname',$username)->find();
            $u_id=$temp['u_id'];
            $where = " u_id = " . $u_id . " and (status = 0 or pay = 0)";
            $list = db('user_taxi')->alias('u')
                ->join('taxi t', 'u.t_id=t.t_id', 'left')
                ->where($where)
                //->order('role_id,u.user_id')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);
        } else {
            return view('being');
        }
    }

    public function temp()
    {
        if (request()->isAjax()) {

            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $temp=db('user')->where('u_tname',$username)->find();
            $u_id=$temp['u_id'];
            $where = " u_id = " . $u_id . " and reserve = 0";
            $list = db('temp')->alias('u')
                ->where($where)
                //->order('role_id,u.user_id')
                ->paginate($limit)
                ->toArray();

            return $this->showList($list);

        } else {
            return view('temp');
        }
    }

    public function drvForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['id'] == null) {
            } else {
                db('user_taxi')->update($data);
                return $this->success('留言编辑成功！');
            }
        } else {
            //添加此代码的目的为防止用户编辑时显示用户密码
            $this->assign('new', input('param.t_id'));
            $list = db('user_taxi')->select();
            $this->assign('list', $list);
            return view('drv_form');
        }
    }

    public function beingForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['id'] == null) {
            } else {
                $data['status'] = 1;
                db('user_taxi')->update($data);
                return $this->success('支付成功！');
            }
        } else {
            //添加此代码的目的为防止用户编辑时显示用户密码
            $this->assign('new', input('param.t_id'));
            $list = db('user_taxi')->select();
            $this->assign('list', $list);
            return view('being_form');
        }
    }

    public function tempDel()
    {
        //参数后加/a是因为前面批量删除时会传来数组，如[1,2]
        db('temp')->delete(input('post.num'));
        return $this->success('删除成功!');
    }

    public function takeForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $username = session('username');
            $intaxi = db('user')->where(['u_tname' => $username])->find();
            if ($intaxi['intaxi'] === 1) return $this->error('订单未结束！');
            $data['u_id'] = $intaxi['u_id'];
            $data['reserve'] = 0;
            db('temp')->strict(false)->insert($data);
            db('user')->where('u_id=' . $intaxi['u_id'] . '')->update(['intaxi' => 1]);
            return $this->success('订单已发出！');
        } else {
            return view('take');
        }
    }

    public function userInfo()
    {
        if (request()->isPost()) {
            $data = input('post.');
            db('user')->strict(false)->update($data);
            return $this->success('用户信息更新成功！');
        } else {
            $username=session('username');
            $data1 = db('user')->where('u_tname' , $username)->find();
            $this->assign('data1', json_encode($data1, true));
            return view('info');
        }
    }
    public function pgrPwd()
    {
//        if (request()->isPost()) {
//            $data = input('post.');
//            db('sys_user')->update($data);
//            return $this->success('用户信息更新成功！');
//
//        } else {
            return view('pgr_pwd');
//        }
    }
    public function modiPwd(){
        $data=input("post.");
        $user = db('sys_user')->where('user_id', session("user_id"))->find();
        if($user['password'] != md5($data['oldPsw'])){
            return $this->error('原密码错误!');
        }else{
            $data['user_id']=(string)(session("user_id"));
            $data['password']=md5($data['newPsw']);

            $data2=session("username");
            //注意要删除前台确认新密码的name属性和删除数据中的oldPassword的值，
            //因为加了的话会传到后台来而数据库没有此字段，会造成更新失败。
            /*unset($data['oldPsw']);
            unset($data['newPsw']);
            if (db('sys_user')->update($data)!==false) {*/

            //strict  关闭严格检查字段名  https://www.kancloud.cn/manual/thinkphp5/162902
            if (db('sys_user')->strict(false)->update($data)!==false){  //
                db('user')->where('u_tname',$data2)->update(['u_password' => $data['password']]);
                return $this->success('密码修改成功！');
            } else {
                return $this->error('密码修改失败!');
            }
        }
    }

}