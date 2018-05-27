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

namespace app\model\controller;
use app\common\controller\Base;
use think\Cache;
use think\Db;
use think\helper\Hash;
class Ajax extends Base
{
  /**
     * [index 处理字段格式数据]
     * @Author   Jerry
     * @DateTime 2017-04-27T16:39:24+0800
     * @Example  eg:
     * @return   [type]                   [description]
     */
    public function editFormat()
    {
        if($this->request->isPost()){
            $data = $this->request->param();
            $insert['forms'] = json_encode($data);
            $insert['table'] = $data['table'];
             if($data[$data['field']]){
                $data['update_date'] = date('Y-m-d H:i:s',time());
                 if($this->getbase->getedit("first_form",['where'=>[$data['field']=>$data[$data['field']]]],$insert)!==false){
                    $this->success('修改成功',$data['gourl']?$data['gourl']:"");  
                }else{
                    $this->error('修改成功失败');
                }
                 
            }else{
                $data['create_date'] = date('Y-m-d H:i:s',time());
                  if($this->getbase->getadd('first_form',$insert)){
                    $this->success('添加成功',$data['gourl']?$data['gourl']:"");
                }else{
                    $this->error('添加失败');
                }
            }
        }
       
  
    }
    /**
     * [tpost 表单数据处理]
     * @Author   Jerry
     * @DateTime 2017-05-07
     * @Example  eg:
     * @return   [type]     [description]
     */
     public function tpost(){
        $data = $this->request->param();
        if($data['encode']){
            //解密TABLE和WHERE
            $data['table'] = decode($data['table']); 
        }
        $table = $data['table'];
        $data['uid'] = parent::getUid();
        unset($data['table']);
        if($data[$data['field']]){

            $data['update_date'] = date('Y-m-d H:i:s',time());
            $this->getbase->getedit($table,['where'=>[$data['field']=>$data[$data['field']]]],$data);
           
             $this->success('修改成功',$data['gourl']?$data['gourl']:"");  
        }else{
            $data['create_date'] = date('Y-m-d H:i:s',time());
          if($this->getbase->getadd($table,$data)){
                $this->success('添加成功',$data['gourl']?$data['gourl']:"");
            } 
        }
        

    }
    
   /**
    * [editmodel 模型修改和新加]
    * @Author   Jerry
    * @DateTime 2017-06-26T17:19:47+0800
    * @Example  eg:
    * @return   [type]                   [description]
    */
    public function editmodel(){
        if($this->request->isAJax()){
            $data = $this->request->param();
             if($data['validate']) $this->validate($data['validate'],'',$data,'ajax');
                if($data[$data['field']]){
                    $where = "{$data['field']}='{$data[$data['field']]}'";
                    unset($data[$data['field']]);
                    $data['update_time'] = date('Y-m-d H:i:s',time());
                    model('Base')->getedit($data['table'],['where'=>$where],$data);
                    return returnJson(0,'更新成功',$data['gourl']);
               }else{
                $data['create_date'] = date('Y-m-d H:i:s',time());
                unset($data[$data['field']]);
                if(model('base')->getcount('models',['where'=>['name'=>input('name')]])) returnJson(1,'模型名已存在!');
                //判断是表是否存在
                    if(!$this->_checktable(input('tablename'))){
                         return returnJson(1,'此表已存在，换个表名再试!');   
                    }else{
                        if($modelid = model('Base')->getadd($data['table'],$data)){
                            $this->_ctable(strtolower(input('tablename')),(int)input('isDefaultFile'),$modelid,input('description'));
                            return returnJson(0,'',$data['gourl']);
                        }else{
                           return returnJson(1); 
                        }

                     }
                }
           }
  } 
  /**
   * [delField 删除字段]
   * @Author   Jerry
   * @DateTime 2017-06-28T14:43:16+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function delField(){
    if($this->request->isAJax()){
      //查出字段信息
      $fieldinfo =model('base')->getone('models_fields',['where'=>['id'=>input('fieldid')],'field'=>'field,modelid']);
      $tableinfo =model('base')->getone('models',['where'=>['id'=>$fieldinfo['modelid']],'field'=>'tablename']);
      if(model('Base')->getdel('models_fields',['where'=>['id'=>input('fieldid')]])){
            $sql = "ALTER TABLE `".config('database.prefix').$tableinfo['tablename']."` DROP COLUMN `{$fieldinfo['field']}`;";
             Db::execute($sql);
             returnJson(0);
          }else{  
            returnJson(1);
          }
        }  
  }
  /**
   * [editfield 处理字段]
   * @Author   Jerry
   * @DateTime 2017-06-27T11:15:57+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  public function editfield(){
    if($this->request->isAJax()){
            $data = $this->request->param();
             if($data['validate']) $this->validate($data['validate'],'',$data,'ajax');
                if($data[$data['field']]){
                    $where = "{$data['field']}='{$data[$data['field']]}'";
                    unset($data[$data['field']]);
                    $data['update_time'] = date('Y-m-d H:i:s',time());
                    model('Base')->getedit($data['table'],['where'=>$where],$data);
                    return returnJson(0,'更新成功',$data['gourl']);
               }else{
                $data['create_date'] = date('Y-m-d H:i:s',time());
                unset($data[$data['field']]);
                //检查字段是否存在
                if(model('base')->getcount('models_fields',['where'=>['modelid'=>input('modelid'),'field'=>input('field')]])>0){
                    //新建字段
                    // $this->cfield('');
                     return returnJson(1,'此字段已存在!');
                    
                }else{
                    if(!input('field')) return returnJson(1,'字段不能为空');
                    if(!input('sqltype')) return returnJson(1,'字段类型不能为空');
                    $tablename = model('base')->getone('models',['where'=>['id'=>input('modelid')],'field'=>'tablename']);
                    $re = $this->_cfield(config('database.prefix').current($tablename),input('field'),input('sqltype'),input('comment'),'',''); 
                    //插入字段信息到字段表
                    $_POST['create_date'] = date('Y-m-d H:i:s');
                    model('base')->getadd('models_fields',$_POST);
                     return returnJson(0,'操作成功',$data['gourl']);
                }
           }
       }
  }
  /**
      * [creatField 添加字段 ]
      * @param  [type] $table [表]
      * @param  [type] $field [字段]
      * @return [type]        [数据类型]
      * length :字符长度
      * decimal:小数点倍数
      * 
      */
     private function _cfield($table,$field,$type="varchar",$comment,$length='',$decimal=''){
            
            $sqlfile = config("SQLTYPE.".$type);
            $length = empty($length)?$sqlfile['maxlength']:$length;
            $decimal = empty($decimal)?$sqlfile['decimal']:$decimal;
            $comment = empty($comment)?"":$comment;
            $sql_star = "ALTER TABLE `".$table."` ADD COLUMN `".$field."` ";
            $sql_comment = " COMMENT '".$comment."'";
           switch (strtolower($type)) {
            case "varchar":
                    $sql = "VARCHAR(".$length.") NULL";
                break;
               
            case "int":
                    //无法设置小数点
                    $sql = " INT(".$length.")";
                 break;

               //    case "smallint":
               //  $sql = "ALTER TABLE ".C('DB_PREFIX').$table." ADD ".$field." DECIMAL(10)";
               // break;

            case "tinyint":
                $sql = " TINYINT(".$length.")";
               break;
            case "bigint":
                $sql = " BIGINT(".$length.")";
               break;

            case "decimal":
                    $sql = " DECIMAL(".$length.",".$decimal.")";
                break;


            case "text":
                   $sql = "text NULL";
                break;
            case "longtext":
                    $sql = "longtext NULL";
                break;


            case "float":
                    $sql = $length>0?" float(".$length.",".$decimal.")":" float NULL";
             break;
            default:
                   # code...
                   break;
           }
            return model('base')-> execute($sql_star.$sql.$sql_comment);
        }
   /**
      * [_ctable 建表]
      * @param  [type] $tableName [description]
      * @param  [type] $default [是否生成默认字段]
      * @param  [type] $modelid [模型ID]
      * @return [type]            [description]
      */
     private function _ctable($tableName,$default,$modelid,$description=''){
        $sqldefault = "CREATE TABLE `@dbPrefix@@tableName@` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
              `title` varchar(255) DEFAULT NULL COMMENT '标题',
              `create_date` datetime DEFAULT NULL COMMENT '创建时间',
              `update_date` datetime DEFAULT NULL COMMENT '更新时间',
              `status` tinyint(4) DEFAULT NULL COMMENT '状态',
              `sort` tinyint(4) DEFAULT NULL COMMENT '排序',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='模型@tableName@: {$description} ';";
        $simplesql = "CREATE TABLE `@dbPrefix@@tableName@` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='模型@tableName@: {$description} ';";
        $dbPrefix = config('database.prefix');
        //表前缀，表名，替换
       $sqlresult = model('base')->execute(str_replace(array('@dbPrefix@', '@tableName@'), array($dbPrefix, $tableName), $default>0?$sqldefault:$simplesql));
        if($default>0){
          //查出当前的表
            $data[0]['field'] = "title";
            $data[0]['modelid'] = $modelid;
            $data[0]['name'] = "标题";
            $data[0]['formtype'] = "text";
            $data[0]['sqltype'] ="varchar"; 
            $data[0]['length'] ="255"; 
            $data[0]['create_date'] =date('Y-m-d H:i:s');


            $data[1]['field'] = "status";
            $data[1]['modelid'] = $modelid;
            $data[1]['name'] = "状态";
            $data[1]['formtype'] = "switch";
            $data[1]['sqltype'] ="tinyint";
            $data[1]['length'] ="4"; 
            $data[1]['create_date'] =date('Y-m-d H:i:s');



            $data[2]['field'] = "sort";
            $data[2]['modelid'] = $modelid;
            $data[2]['name'] = "排序";
            $data[2]['formtype'] = "number";
            $data[2]['sqltype'] ="tinyint";
            $data[2]['length'] ="4"; 
            $data[2]['create_date'] =date('Y-m-d H:i:s');
            foreach ($data as $k => $v) {
                $re = model('base')->getadd('models_fields',$v);
            }  
        
    }
}

  /**
     * [checktable 检查表是否存在]
     * @param  [type] $tablename [description]
     * @return [type]            [description]
     */
    protected function _checktable($tablename){
        $tablename = config('database.prefix').$tablename;
        $re = model('base')->execute("show tables like '$tablename'");
        // show("show tables like '$tablename'");
        if ($re == 0) {
            //不存在
            return true;
        } else {
            //存在
            return false;
        }

    }
     /**
     * [creatDefaultFile 建立默认的表字段]
     * @Author   Jerry
     * @DateTime 2017-06-26T16:10:43+0800
     * @Example  eg:
     * @param    [type]                   $modelid [description]
     * @return   [type]                            [description]
     */
    public function creatDefaultFile($modelid){
        if(!isset($modelid)||empty($modelid)||!$modelid){
            $this->error('没有指定模型ID');
        }else{
           
            
        }

    } 

   
  
}
