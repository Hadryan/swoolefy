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

namespace Swoolefy\Core;

use Swoolefy\Core\Model\BModel;

trait AppTrait {

    /**
     * $request 当前请求的对象
     * @var \Swoole\Http\Request
     */
    public $request = null;

    /**
     * $response 当前请求的响应对象
     * @var \Swoole\Http\Response
     */
    public $response = null;

	/**
	 * $previousUrl,记录url
	 * @var array
	 */
	public $previousUrl = [];

	/**
	 * $selfModel 控制器对应的自身model
	 * @var array
	 */
	public $selfModel = [];

    /**
     * @var array
     */
	protected $requestParams = [];

    /**
     * @var array
     */
	protected $postParams = [];

    /**
     * @param string $action
     * @return bool
     */
	public function _beforeAction($action) {
		return true;
	}

    /**
     * @param string $action
     * @return bool
     */
	public function _afterAction($action) {
		return true;
	}

    /**
     * 提前结束请求，在_beforeAction中调用
     * @param int $ret
     * @param string $msg
     * @param string $data
     * @param string $formatter
     * @return boolean
     */
    public function beforeEnd($ret = 0, string $msg = '', $data = '', string $formatter = 'json') {
    	if(is_object(Application::getApp())) {
    		Application::getApp()->setEnd();
    	}
    	$responseData = Application::buildResponseData($ret, $msg, $data);
        $this->jsonSerialize($responseData, $formatter);
        $this->response->end();
        return true;
    }

	/**
	 * isGet
	 * @return boolean
	 */
	public function isGet() {
		return ($this->request->server['REQUEST_METHOD'] == 'GET') ? true :false;
	}

	/**
	 * isPost
	 * @return boolean
	 */
	public function isPost() {
		return ($this->request->server['REQUEST_METHOD'] == 'POST') ? true :false;
	}

	/**
	 * isPut
	 * @return boolean
	 */
	public function isPut() {
		return ($this->request->server['REQUEST_METHOD'] == 'PUT') ? true :false;
	}

	/**
	 * isDelete
	 * @return boolean
	 */
	public function isDelete() {
		return ($this->request->server['REQUEST_METHOD'] == 'DELETE') ? true :false;
	}

	/**
	 * isAjax
	 * @return boolean
	 */
	public function isAjax() {
		return (isset($this->request->header['x-requested-with']) && strtolower($this->request->header['x-requested-with']) == 'xmlhttprequest') ? true : false;
	}

	/**
	 * isSsl
	 * @return   boolean
	 */
	public function isSsl() {
	    if(isset($this->request->server['HTTPS']) && ('1' == $this->request->server['HTTPS'] || 'on' == strtolower($this->request->server['HTTPS']))){
	        return true;
	    }elseif(isset($this->request->server['SERVER_PORT']) && ('443' == $this->request->server['SERVER_PORT'] )) {
	        return true;
	    }
	    return false;
	}

	/**
	 * isMobile 
	 * @return   boolean
	 */
    public function isMobile() {
        if (isset($this->request->server['HTTP_VIA']) && stristr($this->request->server['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($this->request->server['HTTP_ACCEPT']) && strpos(strtoupper($this->request->server['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($this->request->server['HTTP_X_WAP_PROFILE']) || isset($this->request->server['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($this->request->server['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->request->server['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * getRequest 
     * @return \Swoole\Http\Request
     */
    public function getRequest() {
    	return $this->request;
    }

    /**
     * getResponse 
     * @return \Swoole\Http\Response
     */
    public function getResponse() {
    	return $this->response;
    }

    /**
     * getRequestParam  获取请求参数，包括get,post
     * @param    string   $name
     * @return   mixed
     */
    public function getRequestParams(string $name = null) {
        if(!$this->requestParams) {
            $get = isset($this->request->get) ? $this->request->get : [];
            $post = isset($this->request->post) ? $this->request->post : [];
            if(empty($post)) {
                $post = json_decode($this->request->rawContent(), true) ?? [];
            }
            $this->requestParams = array_merge($get, $post);
            unset($get, $post);
        }

    	if($name) {
    		$value = $this->requestParams[$name] ?? null;
    	}else {
            $value = $this->requestParams;

    	}
    	return $value;
    }

    /**
     * getQueryParams 获取get参数
     * @param  string|null $name
     * @return mixed
     */
    public function getQueryParams(string $name = null) {
    	$input = $this->request->get;
    	if($name) {
    		$value = $input[$name] ?? null;
    	}else {
    		$value = isset($input) ? $input : [];
    	}
    	return $value;
    }

    /**
     * getPostParams 获取Post参数
     * @param  string|null $name
     * @return mixed
     */
    public function getPostParams(string $name = null) {
        if(!$this->postParams) {
            $input = $this->request->post ?? [];
            if(!$input) {
                $input = json_decode($this->request->rawContent(), true) ?? [];
            }
            $this->postParams = $input;
        }

    	if($name) {
    		$value = $this->postParams[$name] ?? null;
    	}else {
    		$value = $this->postParams;
    	}

    	return $value;
    }

    /**
     * getCookieParam 
     * @param    string|null   $name
     * @return   mixed
     */
    public function getCookieParams(string $name = null) {
    	$cookies = $this->request->cookie;
    	if($name) {
    		$value = $cookies[$name] ?? null;
    	}else {
    		$value = $cookies ?? [];
    	}
    	return $value;
    }

    /**
     * getData 获取完整的原始Http请求报文,包括Http Header和Http Body
     * @return string
     */
    public function getData() {
    	return $this->request->getData();
    }

    /**
     * getServerParam 
     * @param    string|null   $name
     * @return   mixed
     */
    public function getServerParams(string $name = null) {
    	if($name) {
    		$value = isset($this->request->server[$name]) ? $this->request->server[$name] : null;
    		return $value;	
    	}
    	return $this->request->server;
    }

    /**
     * getHeaderParam 
     * @param    string|null   $name
     * @return   mixed
     */
    public function getHeaderParams(string $name = null) {
    	if($name) {
    		$value = isset($this->request->header[$name]) ? $this->request->header[$name] : null;
    		return $value;
    	}

    	return $this->request->header;
    }

    /**
     * getFilesParam 
     * @return mixed
     */
    public function getUploadFiles() {
    	return $this->request->files;
    }

    /**
     * getRawContent 
     * @return  mixed
     */
    public function getRawContent() {
    	return $this->request->rawContent();
    }

	/**
	 * getMethod 
	 * @return string
	 */
	public function getMethod() {
		return $this->request->server['REQUEST_METHOD'];
	}

	/**
	 * getRequestUri
	 * @return string
	 */
	public function getRequestUri() {
		return $this->request->server['PATH_INFO'];
	}

	/**
	 * getRoute
	 * @return  string
	 */
	public function getRoute() {
		return $this->request->server['ROUTE'];
	}

	/**
	 * getQueryString
	 * @return string
	 */
	public function getQueryString() {
	    if(isset($this->request->server['QUERY_STRING'])) {
            return $this->request->server['QUERY_STRING'];
        }
        return null;
	}

	/**
	 * getProtocol
	 * @return  string
	 */
	public function getProtocol() {
		return $this->request->server['SERVER_PROTOCOL'];
	}

	/**
	 * getHomeUrl 获取当前请求的url
	 * @param    $ssl
	 * @return   string
	 */
	public function getHomeUrl(bool $ssl = false) {
		$protocolVersion = $this->getProtocol();
		list($protocol, $version) = explode('/', $protocolVersion);
		$protocol = strtolower($protocol).'://';
		if($ssl) {
			$protocol = 'https://';
		}
		$queryString = $this->getQueryString();
		if($queryString) {
            $url = $protocol.$this->getHostName().$this->getRequestUri().'?'.$queryString;
        }else {
            $url = $protocol.$this->getHostName().$this->getRequestUri();
        }
		return $url;
	}

	/**
	 * rememberUrl
	 * @param  string  $name
	 * @param  string  $url
	 * @param  boolean $ssl
	 * @return   void   
	 */
	public function rememberUrl(string $name = null, string $url = null, bool $ssl = false) {
		if($url && $name) {
			$this->previousUrl[$name] = $url;
		}else {
			// 获取当前的url保存
			$this->previousUrl['home_url'] = $this->getHomeUrl($ssl);
		}
	}

	/**
	 * getPreviousUrl
	 * @param    string  $name
	 * @return   mixed
	 */
	public function getPreviousUrl(string $name = null) {
		if($name) {
			if(isset($this->previousUrl[$name])) {
				$previousUrl =  $this->previousUrl[$name];
			}
		}else {
			if(isset($this->previousUrl['home_url'])) {
                $previousUrl = $this->previousUrl['home_url'];
			}
		}
		return $previousUrl ?? null;
	} 

	/**
	 * getRoute
	 * @return array
	 */
	public function getRouteParams() {
		return $this->request->server['ROUTE_PARAMS'];
	}

	/**
	 * getModule 
	 * @return string|null
	 */
	public function getModuleId() {
		list($count, $routeParams) = $this->getRouteParams();
		if($count == 3) {
			return $routeParams[0];
		}
		return null;
	}

	/**
	 * getController
	 * @return string
	 */
	public function getControllerId() {
		list($count, $routeParams) = $this->getRouteParams();
		if($count == 3) {
			return $routeParams[1];
		}else {
			return $routeParams[0];
		}
	}

	/**
	 * getAction
	 * @return string
	 */
	public function getActionId() {
		list($count, $routeParams) = $this->getRouteParams();
		return array_pop($routeParams);
	}

	/**
	 * getModel 默认获取当前module下的控制器对应的module
	 * @param  string  $model
     * @param  string  $module
     * @throws mixed
	 * @return BModel|null
	 */
	public function getModel(string $model = '', string $module = '') {
		if(empty($module)) {
			$module = $this->getModuleId();
		}
		$controller = $this->getControllerId();
		// 如果存在module
		if(!empty($module)) {
			// model的类文件对应控制器
			if(!empty($model)) {
				$modelClass = $this->app_conf['app_namespace'].'\\'.'Module'.'\\'.$module.'\\'.'Model'.'\\'.$model;
			}else {
				$modelClass = $this->app_conf['app_namespace'].'\\'.'Module'.'\\'.$module.'\\'.'Model'.'\\'.$controller;
			}
		}else {
			// model的类文件对应控制器
			if(!empty($model)) {
				$modelClass = $this->app_conf['app_namespace'].'\\'.'Model'.'\\'.$model;
				
			}else {
				$modelClass = $this->app_conf['app_namespace'].'\\'.'Model'.'\\'.$controller;
			}
		}
		// 从内存数组中返回
		if(!isset($this->selfModel[$modelClass])) {
            try{
                /**@var BModel $modelInstance */
                $modelInstance = new $modelClass;
                $this->selfModel[$modelClass] = $modelInstance;
            }catch(\Exception $exception) {
                throw $exception;
            }
		}
        return $this->selfModel[$modelClass] ?? null;
    }

	/**
	 * getQuery
	 * @return string
	 */
	public function getQuery() {
		return $this->request->get;
	}

	/**
	 * getView
	 * @return   object
	 */
	public function getView(string $view_component_name = 'view') {
		return Application::getApp()->{$view_component_name};
	}


	/**
	 * assign
	 * @param   string  $name
	 * @param   string|array  $value
	 * @return  void   
	 */
	public function assign(string $name, $value) {
		Application::getApp()->view->assign($name,$value);
	}

	/**
	 * display
	 * @param    string  $template_file
	 * @return   void             
	 */
	public function display(string $template_file = null) {
		Application::getApp()->view->display($template_file);
	}

	/**
	 * fetch
	 * @param    string  $template_file
	 * @return   void              
	 */
	public function fetch(string $template_file = null) {
		Application::getApp()->view->display($template_file);
	}

    /**
     * @param array $data
     * @param int $ret
     * @param string $msg
     * @param string $formatter
     */
	public function returnJson(array $data = [], $ret=0, $msg='', string $formatter = 'json') {
        $responseData = Application::buildResponseData($ret, $msg, $data);
        $this->jsonSerialize($responseData, $formatter);
	}

    /**
     * jsonSerialize
     * @param array $data
     * @param string $formatter
     */
	public function jsonSerialize(array $data = [], string $formatter = 'json') {
        switch(strtoupper($formatter)) {
            case 'JSON':
                $this->response->header('Content-Type','application/json; charset=utf-8');
                $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            default:
                $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
        }

        if(strlen($jsonString) > 2 * 1024 * 1024){
            $chunks = str_split($jsonString,2 * 1024 * 1024);
            unset($jsonString);
            foreach($chunks as $k=>$chunk) {
                $this->response->write($chunk);
                unset($chunks[$k]);
            }
        }else{
            $this->response->write($jsonString);
        }
    }

	/**
	 * sendfile
	 * @param    string  $filename 
	 * @param    int     $offset   
	 * @param    int     $length
	 * @return   void          
	 */
	public function sendfile(string $filename, int $offset = 0, int $length = 0) {
		$this->response->sendfile($filename, $offset, $length);
	}

	/**
	 * parseUrl 解析URI
	 * @param    string  $url
	 * @return   array
	 */
	public function parseUrl(string $url) {
        $res = parse_url($url);
        $return['protocol'] = $res['scheme'];
        $return['host'] = $res['host'];
        $return['port'] = $res['port'];
        $return['user'] = $res['user'];
        $return['pass'] = $res['pass'];
        $return['path'] = $res['path'];
        $return['id'] = $res['fragment'];
        parse_str($res['query'], $return['params']);
        return $return;
    }

	/**
	 * redirect 重定向,使用这个函数后,要return,停止程序执行
	 * @param    string  $url
	 * @param    array   $params eg:['name'=>'ming','age'=>18]
	 * @param    int     $code default 301
	 * @return   void
	 */
	public function redirect(string $url, array $params = [], int $code = 301) {
        $queryString = '';
		trim($url);
		if(strncmp($url, '//', 2) && strpos($url, '://') === false) {
			if(strpos($url, '/') != 0) {
				$url = '/'.$url;
			}
            list($protocol, $version) = explode('/', $this->getProtocol());
            $protocol = strtolower($protocol).'://';
            $url = $protocol.$this->getHostName().$url;
		}		
		if($params) {
			if(strpos($url,'?') > 0) {
                $queryString = http_build_query($params);
			}else {
                $queryString = '?';
                $queryString .= http_build_query($params);
			}
		}
		if(is_object(Application::getApp())) {
			Application::getApp()->setEnd();
		}
        $url = $url.$queryString;
		if(version_compare(swoole_version(), '4.4.5', '>')) {
			$this->response->redirect($url, $code);
		}else {
			$this->status($code);
			$this->response->header('Location', $url);
			$this->response->end();
		}
	}

	/**
	 * dump，调试函数
	 * @param    string|array  $var
	 * @param    boolean       $echo
	 * @param    mixed         $label
	 * @param    boolean       $strict
	 * @return   string            
	 */
	public function dump($var, $echo = true, $label = null, $strict = true) {
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
	        ob_start();
	        var_dump($var);
	        // 获取终端输出
	        $output = ob_get_clean();
	        @ob_end_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if($echo) {
	    	// 调试环境这个函数使用
	        if(!IS_PRD_ENV()) @$this->response->write($output);
	        return null;
	    }else {
            return $output;
        }
	}

	/**
     * getRefererUrl 获取当前页面的上一级页面的来源url
     * @return mixed
     */
    public function getRefererUrl() {
        return $this->request->server['HTTP_REFERER'] ?? '';
    }

	/**
     * getClientIP 获取客户端ip
     * @param   int  $type 返回类型 0:返回IP地址,1:返回IPV4地址数字
     * @return  mixed
     */
    public function getClientIP(int $type = 0) {
        // 通过nginx的代理
        if(isset($this->request->server['HTTP_X_REAL_IP']) && strcasecmp($this->request->server['HTTP_X_REAL_IP'], "unknown")) {
            $ip = $this->request->server['HTTP_X_REAL_IP'];
        }
        if(isset($this->request->server['HTTP_CLIENT_IP']) && strcasecmp($this->request->server['HTTP_CLIENT_IP'], "unknown")) {
            $ip = $this->request->server["HTTP_CLIENT_IP"];
        }
        if(isset($this->request->server['HTTP_X_FORWARDED_FOR']) and strcasecmp($this->request->server['HTTP_X_FORWARDED_FOR'], "unknown")) {
            return $this->request->server['HTTP_X_FORWARDED_FOR'];
        }
        if(isset($this->request->server['REMOTE_ADDR'])) {
            //没通过代理，或者通过代理而没设置x-real-ip的 
            $ip = $this->request->server['REMOTE_ADDR'];
        }
        // IP地址合法验证 
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**
	 * getFd 获取当前请求的fd
	 * @return  int
	 */
	public function getFd() {
		return $this->request->fd;
	}

	/**
     * header,使用链式作用域
     * @param    string  $name
     * @param    string  $value
     * @return   object
     */
    public function header(string $name, $value) {
        $this->response->header($name, $value);
        return $this->response;
    }

    /**
     * setCookie 设置HTTP响应的cookie信息，与PHP的setcookie()参数一致
     * @param   string  $key   Cookie名称
     * @param   string  $value Cookie值
     * @param   int     $expire 有效时间
     * @param   string  $path 有效路径
     * @param   string  $domain 有效域名
     * @param   boolean $secure Cookie是否仅仅通过安全的HTTPS连接传给客户端
     * @param   boolean $httpOnly 设置成TRUE，Cookie仅可通过HTTP协议访问
     * @return  mixed
     */
    public function setCookie(
        string $key,
        string $value = '',
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httpOnly);
        return $this->response;
    }

    /**
	 * getHostName
	 * @return   string
	 */
	public function getHostName() {
		return $this->request->server['HTTP_HOST'];
	}

	/**
	 * getBrowser 获取浏览器
	 * @return   string
	 */
	public function getBrowser() {
        $sys = $this->request->server['HTTP_USER_AGENT'];
        if (stripos($sys, "Firefox/") > 0)
 		{
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];
        }
        elseif (stripos($sys, "Maxthon") > 0)
        {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        }
        elseif (stripos($sys, "MSIE") > 0)
        {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];
        }
        elseif (stripos($sys, "OPR") > 0)
        {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];
        }
        elseif (stripos($sys, "Edge") > 0)
        {
            preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        }
        elseif (stripos($sys, "Chrome") > 0)
        {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];
        }
        elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0)
        {
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        }
        else
        {
            $exp[0] = "Unkown";
            $exp[1] = "";
        }

        return $exp[0] . '(' . $exp[1] . ')';
    }

    /**
     * getOS 客户端操作系统信息
     * @return  string
     */
    public static function getClientOS() {
        $agent = Application::getApp()->request->server['HTTP_USER_AGENT'];

        if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
        {
            $clientOS = 'Windows 7';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
        {
            $clientOS = 'Windows 10';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
        {
            $clientOS = 'Windows 8';
        }
        elseif (preg_match('/linux/i', $agent) && preg_match('/android/i', $agent))
        {
            $clientOS = 'Android';
        }elseif(preg_match('/iPhone/i', $agent)) {
            $clientOS = 'Ios';
        }
        elseif (preg_match('/linux/i', $agent))
        {
            $clientOS = 'Linux';
        }
        elseif (preg_match('/unix/i', $agent))
        {
            $clientOS = 'Unix';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
        {
            $clientOS = 'Windows XP';
        }
        elseif(preg_match('/win/i', $agent) && strpos($agent, '95'))
        {
            $clientOS = 'Windows 95';
        }
        elseif (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
        {
            $clientOS = 'Windows ME';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
        {
            $clientOS = 'Windows 98';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
        {
            $clientOS = 'Windows Vista';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
        {
            $clientOS = 'Windows 2000';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
        {
            $clientOS = 'Windows NT';
        }
        elseif (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
        {
            $clientOS = 'Windows 32';
        }
        elseif (preg_match('/linux/i', $agent) && preg_match('/android/i', $agent))
        {
            $clientOS = 'Android';
        }elseif(preg_match('/iPhone/i', $agent)) {
            $clientOS = 'Ios';
        }
        elseif (preg_match('/linux/i', $agent))
        {
            $clientOS = 'Linux';
        }
        elseif (preg_match('/unix/i', $agent))
        {
            $clientOS = 'Unix';
        }
        elseif (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
        {
            $clientOS = 'SunOS';
        }
        elseif (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
        {
            $clientOS = 'IBM OS/2';
        }
        elseif (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
        {
            $clientOS = 'Macintosh';
        }
        elseif (preg_match('/PowerPC/i', $agent))
        {
            $clientOS = 'PowerPC';
        }
        elseif (preg_match('/AIX/i', $agent))
        {
            $clientOS = 'AIX';
        }
        elseif (preg_match('/HPUX/i', $agent))
        {
            $clientOS = 'HPUX';
        }
        elseif (preg_match('/NetBSD/i', $agent))
        {
            $clientOS = 'NetBSD';
        }
        elseif (preg_match('/BSD/i', $agent))
        {
            $clientOS = 'BSD';
        }
        elseif (preg_match('/OSF1/i', $agent))
        {
            $clientOS = 'OSF1';
        }
        elseif (preg_match('/IRIX/i', $agent))
        {
            $clientOS = 'IRIX';
        }
        elseif (preg_match('/FreeBSD/i', $agent))
        {
            $clientOS = 'FreeBSD';
        }
        elseif (preg_match('/teleport/i', $agent))
        {
            $clientOS = 'teleport';
        }
        elseif (preg_match('/flashget/i', $agent))
        {
            $clientOS = 'flashget';
        }
        elseif (preg_match('/webzip/i', $agent))
        {
            $clientOS = 'webzip';
        }
        elseif (preg_match('/offline/i', $agent))
        {
            $clientOS = 'offline';
        }
        else
        {
            $clientOS = 'Unknown';
        }

        return $clientOS;
    }

	/**
	 * sendHttpStatus
	 * @param    int  $code
	 * @return   void     
	 */
	public function status(int $code) {
		$http_status = array(
			// Informational 1xx
			100,
			101,

			// Success 2xx
			200,
			201,
			202,
			203,
			204,
			205,
			206,

			// Redirection 3xx
			300,
			301,
			302,  // 1.1
			303,
			304,
			305,
			// 306 is deprecated but reserved
			307,

			// Client Error 4xx
			400,
			401,
			402,
			403,
			404,
			405,
			406,
			407,
			408,
			409,
			410,
			411,
			412,
			413,
			414,
			415,
			416,
			417,

			// Server Error 5xx
			500,
			501,
			502,
			503,
			504,
			505,
			509
		);
		if(in_array($code, $http_status)) {
			$this->response->status($code);
		}else {
			if(!IS_PRD_ENV()) {
				$this->response->write('Error: '.$code .'is not a standard http code');
			}
		}
	}	
}