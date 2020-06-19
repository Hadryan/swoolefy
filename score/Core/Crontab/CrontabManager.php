<?php
/**
+----------------------------------------------------------------------
| swoolefy framework bases on swoole extension development, we can use it easily!
+----------------------------------------------------------------------
| Licensed ( https://opensource.org/licenses/MIT )
+----------------------------------------------------------------------
| Author: bingcool <bingcoolhuang@gmail.com || 2437667702@qq.com>
+----------------------------------------------------------------------
*/

namespace Swoolefy\Core\Crontab;

use Cron\CronExpression;
use Swoolefy\Core\Application;
use Swoolefy\Core\Timer\TickManager;

class CrontabManager {

	use \Swoolefy\Core\SingletonTrait;

	protected $cron_tasks = [];

    /**
     * @param string $cron_name
     * @param string $expression
     * @param mixed  $func
     * @throws \Exception
     */
	public function addRule(string $cron_name, string $expression, $func) {
	    if(!class_exists('Cron\\CronExpression')) {
            throw new \Exception("If you want to use crontab, you need to install 'composer require dragonmantank/cron-expression' ");
        }

	    if(!CronExpression::isValidExpression($expression)) {
            throw new \Exception("Crontab expression format is wrong, please check it");
        }

        if(!is_callable($func)) {
            throw new \Exception("Params func must be callable");
        }

        $cron_name_key = md5($cron_name);

        if(isset($this->cron_tasks[$cron_name_key])) {
            throw new \Exception("cron_name=$cron_name has been seted, you can not set again!");
        }

        $this->cron_tasks[$cron_name_key] = [$expression, $func];

        if(is_array($func)) {
            TickManager::tickTimer(1000, $func, $expression);
        }else {
            \Swoole\Timer::tick(1000, function($timer_id, $expression) use($func) {
                try {
                    $cronInstance = new CronController();
                    $cronInstance->runCron($expression, $func);
                    if(method_exists("Swoolefy\\Core\\Application", 'removeApp')) {
                        Application::removeApp($cronInstance->coroutine_id);
                    }
                }catch (\Throwable $throwable) {
                    throw $throwable;
                }
            }, $expression);
        }

        unset($cron_name_key);
	}

    /**
     * @param string|null $cron_name
     * @return array|mixed|null
     */
	public function getCronTaskByName(string $cron_name = null) {
		if($cron_name) {
			$cron_name_key = md5($cron_name);
			if(isset($this->cron_tasks[$cron_name_key])) {
				return $this->cron_tasks[$cron_name_key];
			}
			return null;
		}
		return $this->cron_tasks;
	}

}