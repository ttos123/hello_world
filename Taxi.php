<?php


namespace app\wwx\controller;

use app\admin\controller\Base;
use think\Console;

class Taxi extends Base
{
    public function index(){
        return view();
    }
    public function user(){
        if (request()->isAjax()) {

            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $key = $get['key'] ?? '';
            $adress_p = $get['adress_p'] ?? '';
            $key_adress = $get['key_adress'] ?? '';

            $where = 'u_id!=0';
            if ($key) {
                $where .= " and (u_tname like '%" . $key . "%'";
                $where .= " or u_name like '%" . $key . "%')";
            }
            if ($adress_p) {
                $where .= " and (u_location like '%" . $adress_p . "%') ";
            }
            if ($key_adress) {
                $where .= " and (u_location like '%" . $key_adress . "%') ";
            }

            $list = db('user')->alias('u')
                ->where($where)
                ->order('u_id,u_tname')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);

        }
        else {
            $list_p = db('province')
                ->Distinct(true)
                ->field('province')
                ->select();
            $this->assign('list_p', $list_p);
            return view();
        }
    }
    public function userForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['u_id'] == null) {
                $user1 = db('user')->where(['u_tname' => $data['u_tname']])->find();
                $user2 = db('sys_user')->where(['username' => $data['u_tname']])->find();
                if ($user1) {
                    $this->error('用户名已经存在！');
                }
                if ($user2) {
                    $this->error('用户名已经存在！');
                }
                $data['intaxi'] = 0;
                $data['u_status'] = 0;
                $data['u_password'] = md5($data['u_password']);
                db('user')->insert($data);
                $sys_data['role_id'] = 11;
                $sys_data['username'] = $data['u_tname'];
                $sys_data['password'] = $data['u_password'];
                db('sys_user')->insert($sys_data);

                return $this->success('用户添加成功！');
            } else {
                $user = db('user')
                    ->where('u_tname', $data['u_tname'])
                    ->where('u_id', '<>', $data['u_id'])
                    ->find();
                if ($user) {
                    $this->error('用户名已经存在！');
                }
                db('user')->update($data);
                return $this->success('用户编辑成功！');
            }
        } else {
            //添加此代码的目的为防止用户编辑时显示用户密码
            $this->assign('new', input('param.u_id'));
            $list_p = db('province')
                ->Distinct(true)
                ->field('province')
                ->select();
            $this->assign('list_p', $list_p);
            return view('user_form');
        }
    }

    public function taxiForm()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ($data['t_id'] == null) {
                $user1 = db('taxi')->where(['t_tname' => $data['t_tname']])->find();
                $user2 = db('sys_user')->where(['username' => $data['t_tname']])->find();
                if ($user1) {
                    $this->error('用户名已经存在！');
                }
                if ($user2) {
                    $this->error('用户名已经存在！');
                }
                $data['t_password'] = md5($data['t_password']);
                $data['t_status'] = 0;
                $data['intaxi'] = 0;
                db('taxi')->insert($data);
                $sys_data['role_id'] = 12;
                $sys_data['username'] = $data['t_tname'];
                $sys_data['password'] = $data['t_password'];
                db('sys_user')->insert($sys_data);
                return $this->success('用户添加成功！');
            } else {
                $user = db('taxi')
                    ->where('t_tname', $data['t_tname'])
                    ->where('t_id', '<>', $data['t_id'])
                    ->find();
                if ($user) {
                    $this->error('用户名已经存在！');
                }
                db('taxi')->update($data);
                return $this->success('用户编辑成功！');
            }
        } else {
            //添加此代码的目的为防止用户编辑时显示用户密码
            $this->assign('new', input('param.t_id'));
            $list = db('taxi')->select();
            $this->assign('list', $list);
            return view('taxi_form');
        }
    }
    public function userDel()
    {
        $username = input('post.u_id/a');
        //var_dump($username);
        //参数后加/a是因为前面批量删除时会传来数组，如[1,2]
        foreach ($username as $i) {
            $temp = db('user')->where('u_id', $i)->find();
            db('sys_user')->where('username',$temp['u_tname'])->delete();
        }
        db('user')->delete($username);
        return $this->success('删除成功!');
    }

    public function taxiDel()
    {
        $username = input('post.t_id/a');
        //var_dump($username);
        //参数后加/a是因为前面批量删除时会传来数组，如[1,2]
        foreach ($username as $i) {
            $temp = db('taxi')->where('t_id', $i)->find();
            db('sys_user')->where('username',$temp['t_tname'])->delete();
        }
        db('taxi')->delete($username);
        return $this->success('删除成功!');
    }

    public function taxi(){
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $key = $get['key'] ?? '';
            $adress_p = $get['adress_p'] ?? '';
            $key_adress = $get['key_adress'] ?? '';

            $where = 't_id!=0';
            if ($key) {
                $where .= " and (t_tname like '%" . $key . "%'";
                $where .= " or t_name like '%" . $key . "%')";
            }
            if ($adress_p) {
                $where .= " and (t_location like '%" . $adress_p . "%') ";
            }
            if ($key_adress) {
                $where .= " and (t_location like '%" . $key_adress . "%') ";
            }
            $list = db('taxi')->alias('u')
                ->where($where)
                ->order('t_id,u.t_tname')
                ->paginate($limit)
                ->toArray();
            return $this->showList($list);

        }
        else {
            $where='';
            $where .="and ( c.adress_p = 0 ) ";
            $list_p = db('province')
                ->Distinct(true)
                ->field('province')
                ->select();
            $this->assign('list_p', $list_p);
            return view();
        }
    }
    public function user_taxi()
    {
        if (request()->isAjax()) {
            $get = $this->request->get();
            $limit = $get['limit'] ?? 10;
            $key = $get['key'] ?? '';
            $key_adress = $get['key_adress'] ?? '';
            $key_min = $get['key_min'] ?? 0;
            $key_max = $get['key_max'] ?? 0;
            $where = "id!=''";
            if ($key) {
                $where .= " and(id like '%" . $key . "%'";
                $where .= " or u.u_name like '%" . $key . "%'";
                $where .= " or t.t_name like '%" . $key . "%')";
            }
            if ($key_adress){
                $where .= " and(start like '%" . $key_adress . "%'";
                $where .= " or end like '%" . $key_adress . "%')";
            }
            if($key_min)$where .= " and(cost >= " . $key_min . ")";
            if($key_max)$where .= " and(cost <= " . $key_max . ")";

            $list = db('user_taxi b')   //b表示book表的别名
                ->join('user u','u.u_id=b.u_id','left')
                    ->join('taxi t','t.t_id=b.t_id','left')
                        ->where($where)
                            ->paginate($limit) //分页
                                ->toArray();  //转换为数组
            return $this->showList($list);
        } else {
            return view();
        }
    }
    public function utPay()
    {
        $pay = input('post.pay');
        if (db('user_taxi')->where('id', input('post.id'))->update(['pay' => $pay]) !== false) {
            return $this->success('设置成功!');
        } else {
            return $this->error('设置失败!');
        }
    }
    public function userResetPwd()
    {
        db('user')->where('u_id', input('post.u_id'))->update(['u_password' => md5("1")]);
        return $this->success('重置密码成功，新密码为1!');
    }
    public function taxiResetPwd()
    {
        db('taxi')->where('t_id', input('post.t_id'))->update(['t_password' => md5("1")]);
        return $this->success('重置密码成功，新密码为1!');
    }
    public function taxiStatus()
    {
        $t_status = input('post.t_status');
        if (db('taxi')->where('t_id', input('post.t_id'))->update(['t_status' => $t_status]) !== false) {
            $temp=db('taxi')->where('t_id', input('post.t_id'))->find();
            db('sys_user')->where('username', $temp['t_tname'])->update(['status' => $t_status]);
            return $this->success('设置成功!');
        } else {
            return $this->error('设置失败!');
        }
    }
    public function userStatus()
    {
        $u_status = input('post.u_status');
        if (db('user')->where('u_id', input('post.u_id'))->update(['u_status' => $u_status]) !== false) {
            $temp=db('user')->where('u_id', input('post.u_id'))->find();
            db('sys_user')->where('username', $temp['u_tname'])->update(['status' => $u_status]);
            return $this->success('设置成功!');
        } else {
            return $this->error('设置失败!');
        }
    }
}