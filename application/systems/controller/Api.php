<?php
/*
+--------------------------------------------------------------------------
|   thinkask [#开源系统#]
|   ========================================
|   http://www.thinkask.cn
|   ========================================
|   如果有兴趣可以加群{开发交流群} 485114585
|   ========================================
|   更改插件记得先备份，先备份，先备份，先备份
|   ========================================
+---------------------------------------------------------------------------
 */

namespace app\systems\controller;
use app\common\controller\ApiBase;
class Api extends ApiBase
{

    // public function _initialize() {
           
    // }
    /**
     * [tpost 公共POST处理]
     * @Author   Jerry
     * @DateTime 2017-04-30
     * @Example  eg:
     * @return   [type]     [description]
     */
    public function tpost(){
        if (!$this->request->isPost()) return ;
        $data = $this->request->param();
        if($data['validate']) $this->validate($data['validate'],'',$data,'api');
        if($data['encode']){
           $wheres = $data['where']?explode("=", decode($data['where'])):"";
           if($wheres) $where[$wheres[0]] =$wheres[1]; 
           $table = decode($data['table']);
        }else{
            $where = $data['where']?explode("=", $data['where']):"";
            $table = $data['table'];
        }
        unset($data['where']);
        unset($data['table']);
        $data['update_date'] = date('Y-m-d H:i:s',time());
        //有WHERE条件为修改，没有为新加
        if($where){
            $this->getbase->getedit($table,['where'=>$where],$data);
        }else{
            $this->getbase->getadd($table,$data);
        }
         return returnJson(0,'处理成功');
    }

     /**
   * [tmkedit 公共修改和新加  后台框架封装方法]
   * @return [type] [description]
   */
    public function tmkedit(){
        if($this->request->isPost()){
            $data = $this->request->param();
             if($data['validate']) $this->validate($data['validate'],'',$data,'api');
            if($data[$data['field']]){
                $where = "{$data['field']}='{$data[$data['field']]}'";
                unset($data[$data['field']]);
                $data['update_time'] = date('Y-m-d H:i:s',time());
                model('Base')->getedit($data['table'],['where'=>$where],$data);
                return returnJson(0,'更新成功',$data['gourl']);
           }else{
            $data['create_time'] = date('Y-m-d H:i:s',time());
            unset($data[$data['field']]);
            if(model('Base')->getadd($data['table'],$data)){
                 return returnJson(0,'',$data['gourl']);
              }else{
                return returnJson(1);
              }
           }
    
      }
    }
    /**
     * [delete 删除单个]
     * @return [type] [description]
     */
    public function tdel(){
          if (!$this->request->isPost()) return ;
          // if(!parent::getUid()) return returnJson(1001,'请先登陆',);
            $data = $this->request->param();
            if($data['encode']){
               $wheres = explode("=", decode($data['where']));
               $where[$wheres[0]] =$wheres[1];   
               $table = decode($data['table']);
            }else{
                $where = explode("=", $data['where']);
                $table = $data['table'];
            }
            model('Base')->getdel($table,['where'=>$where]);
            return returnJson(0,'删除成功');
        
    }



}
