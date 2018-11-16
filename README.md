# 系统架构目录
```
├── application           //应用目录
│   ├── Admin             //后台管理应用模块
│   │   ├── Controller    //后台控制器
│   │   ├── Model         //模型目录文件
│   │   └── View         //视图文件目录
│   ├── Api               //API应用模块
│   ├── Common             //通用应用模块
│   ├── Extra             //扩展配置目录
│   ├── Common.php         //通用辅助函数
│   ├── Route.php          //自定义路由配置
├── extend 
│   ├── Pool              //mysql、redis连接池目录
│   ├── Utility             //扩展辅助类目录
│   └── Config             //配置文件目录    
├── public                  //Nginx反向代理web指向目录 用于静态页面
│   ├── assets
│   │   ├── css                //CSS样式目录
│   │   ├── fonts            //字体目录
│   │   ├── img
│   │   └── js
│   └── uploads                //上传文件目录
├── runtime                    //缓存目录    
├── vendor                    //Compposer资源包位置
├── EasySwooleEvent.php       //easyswoole 全局事件配置
├── easyswoole                 //系统启动文件
├── dev.env                  //开发环境swoole配置文件
├── produce.env               //生产环境swoole配置文件
├── README.md
└── composer.json            //Composer包配置
```
# 开发建议
> * 此系统采用easyswoole3进行开发，开发文档请转移至[官网](https://www.easyswoole.com/Manual/3.x/Cn/_book/)进行查看
> * Swoole基础知识请转移至[官网](https://www.swoole.com)，或者[https://toxmc.github.io/swoole-cs.github.io/#](https://toxmc.github.io/swoole-cs.github.io/#)
> * 开发调试可以使用pp($data) 函数进行数据断点输出 ， 切不可执行exit、die等函数！！！ var_dump()、echo 等会直接输出至控制台
> * 对于get、post等请求参数，可以通过在控制器中使用$this->get()获取get参数 ，通过$this->post()获取post参数；更多参数获取方式详见App\Common\Traits\Request;
> * 对于全局变量、静态变量、单例、静态属性等等，使用完之后请自行释放，否则易引发变量污染
> * extend/Config/ 文件目录下是配置文件存放目录，这些配置文件会在系统初始化时自动加载，如需区分开发环境与生产环境配置，请参照databases.php 设置对应的dev(开发环境)、produce(生产环境)键值

# EasySwoole底层修改内容

`由于对框架底层进行了修改，如需更新某个composer包，请使用composer require xxxx/xxx 切不可直接执行composer update,若不小心执行该命令，请修改下列文件，恢复至修改后的内容`
> vendor/easyswoole/trace/src/Logger.php
>> 修改后(设置日志记录方式以年月日时进行分割，利于日志查看)
```
public function log(string $str,$category = 'default')
{
    if($this->loggerWriter instanceof LoggerWriterInterface){
        $this->loggerWriter->writeLog($str,$category,time());
    }else{
        $str = date("y-m-d H:i:s").":{$str}\n";
        $filePrefix = $category."_".date('ym');
        if (!is_dir($this->logDir.'/'.date("Y/m/d/H"))) {
            mkdir($this->logDir.'/'.date("Y/m/d/H"),0777,true);
        }
        $filePath = $this->logDir.'/'.date("Y/m/d/H")."/{$filePrefix}.log";
        file_put_contents($filePath,$str,FILE_APPEND|LOCK_EX);
    }
}
```
>> 修改前
```
public function log(string $str,$category = 'default')
{
    if($this->loggerWriter instanceof LoggerWriterInterface){
        $this->loggerWriter->writeLog($str,$category,time());
    }else{
        $str = date("y-m-d H:i:s").":{$str}\n";
        $filePrefix = $category."_".date('ym');
        $filePath = $this->logDir."/{$filePrefix}.log";
        file_put_contents($filePath,$str,FILE_APPEND|LOCK_EX);
    }
}
```
> vendor/easyswoole/http/src/Dispatcher.php
>> 修改后(修改路由指向控制器路径)
```php
private function controllerHandler(Request $request,Response $response,string $path)
    {
        $pathInfo = ltrim($path,"/");
        $list = explode("/",$pathInfo);
        $actionName = null;
        $finalClass = null;
        $controlMaxDepth = $this->maxDepth;
        $currentDepth = count($list);
        $maxDepth = $currentDepth < $controlMaxDepth ? $currentDepth : $controlMaxDepth;
        while ($maxDepth >= 0){
            $className = '';
            for ($i=0 ;$i<$maxDepth;$i++){
                $className = $className."\\".ucfirst($list[$i] ?: 'Index');//为一级控制器Index服务
                //此处为修改内容
                if ($i === 0) $className .='\\Controller';
            }
        省略....
```
>> 修改前
```php
private function controllerHandler(Request $request,Response $response,string $path)
    {
        $pathInfo = ltrim($path,"/");
        $list = explode("/",$pathInfo);
        $actionName = null;
        $finalClass = null;
        $controlMaxDepth = $this->maxDepth;
        $currentDepth = count($list);
        $maxDepth = $currentDepth < $controlMaxDepth ? $currentDepth : $controlMaxDepth;
        while ($maxDepth >= 0){
            $className = '';
            for ($i=0 ;$i<$maxDepth;$i++){
                $className = $className."\\".ucfirst($list[$i] ?: 'Index');//为一级控制器Index服务
            }
        省略....
```
