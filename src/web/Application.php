<?php
/**
 * briabear
 * User: lushuncheng<admin@lushuncheng.com>
 * Date: 2017/3/1
 * Time: 18:17
 * @link https://github.com/lscgzwd
 * @copyright Copyright (c) 2017 Lu Shun Cheng (https://github.com/lscgzwd)
 * @licence http://www.apache.org/licenses/LICENSE-2.0
 * @author Lu Shun Cheng (lscgzwd@gmail.com)
 */
/**
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiswoole\web;

use yii\base\ExitException;

class Application extends \yii\web\Application
{
    protected static $controllerInstances = [];
    public function run()
    {
        try {

            $this->state = self::STATE_BEFORE_REQUEST;
            $this->trigger(self::EVENT_BEFORE_REQUEST);

            $this->state = self::STATE_HANDLING_REQUEST;
            /**
             * @var Response $response
             */
            $response = $this->handleRequest($this->getRequest());

            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);

            $this->state = self::STATE_SENDING_RESPONSE;
            $result      = $response->send();

            $this->state = self::STATE_END;

            return $result;

        } catch (ExitException $e) {
            return $this->end($e->statusCode, isset($response) ? $response : null);
        }
    }
    /**
     * Terminates the application.
     * This method replaces the `exit()` function by ensuring the application life cycle is completed
     * before terminating the application.
     * @param int $status the exit status (value 0 means normal exit while other values mean abnormal exit).
     * @param Response $response the response to be sent. If not set, the default application [[response]] component will be used.
     * @throws ExitException if the application is in testing mode
     */
    public function end($status = 0, $response = null)
    {
        if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
            $this->state = self::STATE_AFTER_REQUEST;
            $this->trigger(self::EVENT_AFTER_REQUEST);
        }

        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
            $this->state = self::STATE_END;
            $response    = $response ?: $this->getResponse();
            return $response->send();
        }
    }

    /**
     * @param string $route
     * @return array|bool
     */
    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }
        if (!isset(static::$controllerInstances[$route])) {
            $controller = parent::createController($route); // TODO: Change the autogenerated stub
            if (false !== $controller) {
                static::$controllerInstances[$route] = $controller;
            }
        }
        if (isset(static::$controllerInstances[$route])) {
            return [
                clone static::$controllerInstances[$route][0],
                static::$controllerInstances[$route][1],
            ];
        } else {
            return false;
        }
    }
}
