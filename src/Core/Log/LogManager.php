<?php
/**
+----------------------------------------------------------------------
| swoolefy framework bases on swoole extension development, we can use it easily!
+----------------------------------------------------------------------
| Licensed ( https://opensource.org/licenses/MIT )
+----------------------------------------------------------------------
| @see https://github.com/bingcool/swoolefy
+----------------------------------------------------------------------
 */

namespace Swoolefy\Core\Log;

use \Swoolefy\Util\Log;

/**
 * Class LogManager
 * @see \Swoolefy\Util\Log
 * @mixin \Swoolefy\Util\Log
 */
class LogManager {

    use \Swoolefy\Core\SingletonTrait;

    /**
     * @var Log
     */
    protected $logger;

    /**
     * __construct
     * @param mixed $log
     */
    private function __construct() {}

    /**
     * registerLogger
     * @param  string|null $channel    
     * @param  string|null $logFilePath
     * @param  string|null $output     
     * @param  string|null $dateformat 
     * @return void                  
     */
    public function registerLogger(
        string $channel = null,
        string $logFilePath = null,
        string $output = null,
        string $dateformat = null
    ) {
        if($channel && $logFilePath) {
            $this->logger = new \Swoolefy\Util\Log($channel, $logFilePath, $output, $dateformat);
        }
    }

    /**
     * registerLoggerByClosure
     * @param  \Closure  $func
     * @param  string $log_name
     * @return mixed
     */
    public function registerLoggerByClosure(\Closure $func, string $log_name = null) {
        $this->logger = call_user_func($func, $log_name);
    }

    /**
     * getLogger
     * @return Log
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param $action
     * @param $args
     */
    public function __call($action, $args) {
        $this->logger->$action(...$args);
    }

}