<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:33
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Context;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Utility\File;
use Extend\Utility\Cache;
use Extend\Utility\Pool\RedisPool;
use Extend\Utility\TrackerManager;
use EasySwoole\Trace\Bean\Tracker;
use EasySwoole\Component\Di;
use EasySwoole\Component\Pool\PoolManager;
use Extend\Utility\Pool\MysqlPool;

define('DS', DIRECTORY_SEPARATOR);
defined('APP_PATH') or define('APP_PATH', 'Application' . DS);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);

class EasySwooleEvent implements Event
{
    static private $origin=[
        'http://local.cc'
    ];

    public static function initialize()
    {
        // 全局初始化
        date_default_timezone_set('Asia/Shanghai');
        //引入公共函数库
        require_once 'Application/Common.php';
        //导入自定义配置
        self::loadConf();
        //获取mysql配置信息
        $dbConf = Config::getInstance()->getConf('databases');
        //Db::setConfig($dbConf);
        //设置控制器默认命名空间
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_NAMESPACE,Config::getInstance()->getConf('HTTP_CONTROLLER_NAMESPACE') );
        //设置控制器深度
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH,Config::getInstance()->getConf('HTTP_CONTROLLER_MAX_DEPTH') );

        //调用链追踪器设置Token获取值为协程id
//        TrackerManager::getInstance()->setTokenGenerator(function () {
//            return \Swoole\Coroutine::getuid();
//        });
        //每个链结束的时候，都会执行的回调
//        TrackerManager::getInstance()->setEndTrackerHook(function ($token, Tracker $tracker) {
//            Logger::getInstance()->log((string)$tracker);//直接保存到日志
//        });
        // 注册mysql数据库连接池
        PoolManager::getInstance()->register(MysqlPool::class, $dbConf['POOL_MAX_NUM']);
        // 注册Redis连接池
        PoolManager::getInstance()->register(RedisPool::class, Config::getInstance()->getConf('redis.POOL_MAX_NUM'));

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // TODO: Implement mainServerCreate() method.
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        //跨域处理
        foreach (self::$origin as $v){
            $response->withHeader('Access-Control-Allow-Origin', $v);
        }
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS,DELETE,PUT');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');//允许携带cookie
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            $response->end();
        }
        //TrackerManager::getInstance()->getTracker()->addAttribute('workerId', ServerManager::getInstance()->getSwooleServer()->worker_id);
        //实例化Request类
        \Extend\Utility\Request::getInstance($response,$request);
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        //tracker结束
        //TrackerManager::getInstance()->closeTracker();
        //释放Request类
        \Extend\Utility\Request::getInstance()->gc();
        //释放redis数据库连接池对象
        Cache::getInstance()->gc();
    }

    public static function onReceive(\swoole_server $server, int $fd, int $reactor_id, string $data):void
    {
    }

    /**
     * 引用自定义配置文件
     * @throws \Exception
     */
    public static function loadConf()
    {
        $files = File::scanDirectory(EASYSWOOLE_ROOT . '/extend/Config');
        if (is_array($files)) {
            foreach ($files['files'] as $file) {
                $fileNameArr = explode('.', $file);
                $fileSuffix = end($fileNameArr);
                if ($fileSuffix == 'php') {
                    Config::getInstance()->loadFile($file);
                } elseif ($fileSuffix == 'env') {
                    Config::getInstance()->loadEnv($file);
                }
            }
        }
    }

}