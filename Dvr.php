<?php


namespace app\wwx\controller;

use app\admin\controller\Base;

class Dvr extends Base
{
    public function pgr()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $t_id=db('taxi')->where('t_tname',$username)->find();
            $where = " t_id = " . $t_id['t_id'] . " and status = 1 ";
            $list = db('user_taxi')
                ->where($where)
                //->order('role_id,u.user_id')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);
        }
        else {
            return view();
        }
    }
    public function being()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $t_id=db('taxi')->where('t_tname',$username)->find();
            $where = " t_id = " . $t_id['t_id'] . " and u.status = 0 and t.reserve = 1 ";
            $list = db('user_taxi')->alias('u')
                ->join('temp t','u.u_id=t.u_id','left')
                ->where($where)
                //->order('role_id,u.user_id')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);
        }
        else {
            return view();
        }
    }
    public function beingForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['id'] == null) {
            } else {
                db('user_taxi')->update($data);
                return $this->success('设置成功！');
            }
        } else {
            //添加此代码的目的为防止用户编辑时显示用户密码
            $this->assign('new', input('param.t_id'));
            $list = db('user_taxi')->select();
            $this->assign('list', $list);
            return view('being_form');
        }
    }
    public function doneOrder()
    {
        $t_id = input('post.t_id');
        $u_id = input('post.u_id');

        $temp = db('temp')->where('u_id',$u_id)->find();
        $id=$temp['num'];
        db('temp')->delete($id);

        //db('temp')->delete(input('post.u_id/a'));
        db('user_taxi')->where('id', input('post.id'))->update(['status' => 1]);
        db('user')->where('u_id', $u_id)->update(['intaxi' => 0]);
        db('taxi')->where('t_id', $t_id)->update(['intaxi' => 0]);

        return $this->success('订单已完成!');
    }
    public function res()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $username = session('username');
            $t_id=db('taxi')->where('t_tname',$username)->find();
            $where = " t_id = " . $t_id['t_id'] . " ";
            $list = db('temp te')
                ->where('reserve = 0')
                ->join('user u','u.u_id = te.u_id','left')
                    ->join('taxi t','t.t_location = u.u_location','left')
                        ->where($where)
                        //->order('role_id,u.user_id')
                            ->paginate($limit)
                                ->toArray();
            return $this->showList($list);
        }
        else {
            return view('res');
        }
    }
    public function tempAdd()        //接单
    {
        if (request()->isPost()) {
            $data = input('post.');
            $username = session('username');
            $id=db('taxi')->where('t_tname',$username)->find();
            $t_id=$id['t_id'];
            $intaxi = db('taxi')->where(['t_id' => $t_id])->find();
            if($intaxi['intaxi']===1) return $this->error('订单未结束！');
            $data['t_id'] = $t_id;
            //$data['order_number']=date('Y-m-d h:i:s', time());
            $data['order_number']=date('Ymdhis', time());
            db('temp')->where('num', input('post.num'))->update(['reserve' => 1]);
            db('user_taxi')->strict(false)->insert($data);
            db('taxi')->where('t_id='.$t_id.'')->update(['intaxi' => 1]);
            return $this->success('订单已接受！');
        } else {
            return view('res');
        }
    }
    public function dvrInfo()
    {
        if (request()->isPost()) {
            $data = input('post.');
            db('taxi')->strict(false)->update($data);
            return $this->success('用户信息更新成功！');
        } else {
            $username=session('username');
            $data1 = db('taxi')->where('t_tname' , $username)->find();
            $this->assign('data1', json_encode($data1, true));
            return view('info');
        }
    }
    public function dvrPwd()
    {
//        if (request()->isPost()) {
//            $data = input('post.');
//            db('sys_user')->update($data);
//            return $this->success('用户信息更新成功！');
//
//        } else {
        return view('dvr_pwd');
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
                db('taxi')->where('t_tname',$data2)->update(['t_password' => $data['password']]);
                return $this->success('密码修改成功！');
            } else {
                return $this->error('密码修改失败!');
            }
        }
    }
}