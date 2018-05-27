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
namespace app\common\model;
use think\Model;
use think\Db;
use think\Cache;
use think\db\Query;
class Base extends Query
{
  private static $queryObj;
  // protected $prefix;
  // public function __construct($data = []){
    // $this->prefix = config('database.prefix');
    // parent::__construct($data);
    
  // }
  private $initCache=true;
  public function getdb($table){
    return $this->table(config('database.prefix').$table);
  }
 

  /**
     * [getone 获得一条数据]
     * @return [type] [description]
     */
    public function getone($table,$param=[]){
      $param['functionName'] = __FUNCTION__;
        if($cache = $this->existCache($table,$param)){
            return $cache;
          }else{
            $compile = $this->compileParam($table,$param);##编辑参数
            $query = $compile['query'];
            // show($compile);
            return $this->setCache($table,$param,$query->find());
          }
       
        }
   /**
    * [getpages 分页数据]
    * @Author   Jerry
    * @DateTime 2017-10-26T16:20:45+0800
    * @Example  eg:
    * @param    [type]                   $table [description]
    * @param    array                    $param [description]
    * @return   [type]                          [description]
    */
  public function getpages($table,$param=[]){
    $param['functionName'] = __FUNCTION__;
    $param['query'] = request()->param();##分页的缓存处理。不加会影响缓存名
    if($cache = $this->existCache($table,$param)){
      return $cache;
    }else{
      $compile = $this->compileParam($table,$param);##编辑参数
      $query = $compile['query'];
      return $this->setCache($table,$param,$query->paginate($compile['param']['list_rows'],false,['query' =>request()->param()]));
    }

  }
  /**
   * [getall 查出所有]
   * @return [type] [description]
   */
     public function getall($table,$param=[])
    {
      $param['functionName'] = __FUNCTION__;
      if($cache = $this->existCache($table,$param)){
        return $cache;
      }else{
        $compile = $this->compileParam($table,$param);##编辑参数
        $query = $compile['query'];
        return $this->setCache($table,$param,$query->select());
      }
    }
     /**
      * [getall count 所有]
      * @param  [type] $table [description]
      * @param  array  $param [description]
      * @return [type]        [description]
      */
      public function getcount($table,$param=[])
    {
      $param['functionName'] = __FUNCTION__;
      if($cache = $this->existCache($table,$param)){
        return $cache;
      }else{
        $compile = $this->compileParam($table,$param);##编辑参数
        $query = $compile['query'];
        return $this->setCache($table,$param,$query->count());
      }

      
    }
    /**
     * [getquery 原生查询]
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    public function getquery($sql){
      return Db::query($sql);
    }
    /**
     * [getadd 添加数据]
     * @param  [type] $table [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function getadd($table,$data){
      if(empty($table)){
        die('表不能为空');
      }
      if($req = $this->table(config('database.prefix').$table)->insertGetId($data)){
          $this->removeCache($table);
      }
      return  $req;
    }
    /**
     * [getaddAll 插入多条]
     * @Author   Jerry
     * @DateTime 2017-08-04T21:43:15+0800
     * @Example  eg:
     * @param    [type]                   $table [description]
     * @param    [type]                   $data  [description]
     * @return   [type]                          [description]
     */
    // public function getaddAll($table,$data){
    //   if(empty($table)){
    //     die('表不能为空');
    //   }
    //   return  $this->table(config('database.prefix').$table)->insertAll($data);
    // }

    /**
     * [getedit description 修改]
     * @return [type] [description]
     */
    public function getedit($table,$param,$data){
      if(empty($table)) die('表不能为空');
      if(empty($param['where'])) die('修改文件必须传入WHERE条件');
      $req = $this->table(config('database.prefix').$table)->where($param['where'])->update($data);
      if($req!==false){
        $this->removeCache($table);
      }
      return  $req;
    }
    /**
     * [getdel description]
     * @return [type] [description]
     */
    public function getdel($table,$param){
       if(empty($table)) die('表不能为空');
       if(empty($param['where'])) die('删除必须传入WHERE条件');
       if($req = $this->table(config('database.prefix').$table)->where($param['where'])->delete()){
          $this->removeCache($table);
       }
       return  $req;
    }
    
  
  // 自增  字段
  /**
   * [getinc description]
   * @param  [type] $table [表名]
   * @param  [type] $param [条件]
   * @param  [type] $field [自增的字段，如SCORE 那么 SCORE++]
   * @return [type]        [description]
   */
  public function getinc($table,$param,$field){
    if($req = $this->table(config('database.prefix').$table)->where($param['where'])->setInc($field)){
          $this->removeCache($table);
       }
    return $req;
  }
  //// 自减  字段
  public function getdec($table,$param,$field){
     if($req = $this->table($table)->where($param['where'])->setDec($field)){
          $this->removeCache($table);
       }
    return $req;
  }
  /**
   * 数据修改
   * @return [bool] [是否成功]
   */
  public function change(){
    $data = \think\Request::instance()->post();
    if (isset($data['id']) && $data['id']) {
      return $this->save($data, array('id'=>$data['id']));
    }else{
      return $this->save($data);
    }
  }
  /**
   * [debug debug为真，当前查询不开启缓存]
   * @Author   Jerry
   * @DateTime 2017-10-26T16:16:36+0800
   * @Example  eg:
   * @param    [type]                   $status [description]
   * @return   [type]                           [description]
   */
  public function debug($status){
    if($status)$this->initCache = false;
    return $this;
  }
    /**
   * [existCache 缓存是否存在]
   * @Author   Jerry
   * @DateTime 2017-10-26T15:51:27+0800
   * @Example  eg:
   * @param    [type]                   $table [description]
   * @param    [type]                   $param [description]
   * @return   [type]                          [description]
   */
  private function existCache($table,$param){
    $cacheName = $this->getCacheName($table,$param);
    if(!$this->initCache) return false;##关闭当前的查询缓存（DEBUG调试时会用到）
    if(Cache::get($cacheName)){
      return Cache::get($cacheName);
    }else{ 
      return false;
    }

  }
  
  /**
   * [setCache 生成缓存]
   * @Author   Jerry
   * @DateTime 2017-10-26T16:10:39+0800
   * @Example  eg:
   * @param    [type]                   $table [description]
   * @param    [type]                   $param [description]
   */
  private function setCache($table,$param,$data){
    $cacheName = $this->getCacheName($table,$param);
    Cache::tag($table);
    Cache::set($cacheName,$data);
    return Cache::get($cacheName);
  }
  /**
   * [removeCache 删除缓存标签]
   * @Author   Jerry
   * @DateTime 2017-10-26T16:11:04+0800
   * @Example  eg:
   * @param    [type]                   $table [description]
   * @return   [type]                          [description]
   */
  private function removeCache($table){
    if(!$table) return;
    Cache::clear($table);
  }
  /**
   * [getCacheName 获得缓存名]
   * @Author   Jerry
   * @DateTime 2017-10-26T16:09:44+0800
   * @Example  eg:
   * @param    [type]                   $table [description]
   * @param    [type]                   $param [description]
   * @return   [type]                          [description]
   */
  private function getCacheName($table,$param){
    $cacheName = "";
    foreach ($param as $key => $v) {
      $cacheName.=is_array($v)?serialize($v):$v;
    }
    $cacheName = $table.$cacheName.base64_encode($table);##避免重名
    return $cacheName?base64_encode($cacheName):"";
  }
  /**
   * [compileParam 编译参数]
   * @Author   Jerry
   * @DateTime 2017-10-26T15:54:38+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  private function compileParam($table,$param){
    if(empty($table)) die('必须指定表名');
    ##组SQL
    $query = $this->setTable(config('database.prefix').$table);
    foreach ($param as $k=> $v) {
      ##此条件存在
     if($v&&$this->method($k)){
       ##方法是否存在
        $query = $query->$k($v);##组对象
     }
    }

    $param['list_rows'] = (int)$param['list_rows']?(int)$param['list_rows']:((int)input('list_rows')?(int)input('list_rows'):30);
    $compile['param'] = $param;
    $compile['query'] = $query;
    return $compile;
  }
  /**
   * [method 可执行的方法]
   * @Author   Jerry
   * @DateTime 2017-10-27T15:53:49+0800
   * @Example  eg:
   * @return   [type]                   [description]
   */
  private function method($method){
    $methods =['where','whereOr','whereXor','whereNull','whereNotNull','whereExists','whereNotExists','whereIn','whereNotIn','whereLike','whereNotLike','whereBetween','whereNotBetween','whereExp','parseWhereExp','limit','order','group','having','alias','join','unionALL','union','field'];
    if(in_array($method, $methods)){
      return true;
    }else{
      return false;
    }
  }
    
}