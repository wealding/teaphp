<?php

class uri
{
    public $_config;
    public $_uri = null;
    public $_parts = [];
    public $cap;

    public function __construct($uriconfig)
    {
        $this->_config = $uriconfig;
        $this->cap = $this->router();
    }

    public function makeurl($cap = [], $new_params = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        if (!empty($new_params)) {
            foreach ($new_params as $k=>$v) {
                $cap['params'][$k] = $v;
            }
        }

        return $this->_mkurl($cap);
    }

    public function _mkurl($cap = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        switch ($this->_config['type']) {
            case 'default':
                return $this->_mkurl_default($cap);
                break;
            case 'pathinfo':
                return $this->_mkurl_pathinfo($cap);
                break;
            case 'rewrite':
                return $this->_mkurl_rewrite($cap);
                break;
            case 'tea':
                return $this->_mkurl_tea($cap);
                break;
            default:
                return $this->_mkurl_default($cap);
        }
    }

    public function _mkurl_default($cap = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        if (!empty($cap['controller']) && $cap['controller'] != $this->_config['default_controller']) {
            $url .= '&'.$this->_config['para_controller'].'='.$cap['controller'];
        }
        if (!empty($cap['action']) && $cap['action'] != $this->_config['default_action']) {
            $url .= '&'.$this->_config['para_action'].'='.$cap['action'];
        }
        foreach ($cap['params'] as $key=>$val) {
            $url .= '&'.$key.'='.$val;
        }
        $urlstr = substr($url, 1, strlen($url) - 1);

        return $this->_parts['basepath'].(empty($urlstr) ? '' : '?'.$urlstr);
    }

    public function _mkurl_pathinfo($cap = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        if (!empty($cap['controller'])) {
            $url .= '/'.$cap['controller'];
        } else {
            $url .= '/'.$this->_config['default_controller'];
        }
        if (!empty($cap['action'])) {
            $url .= '/'.$cap['action'];
        } else {
            $url .= '/'.$this->_config['default_action'];
        }
        foreach ($cap['params'] as $key=>$val) {
            $url .= '/'.$key.'/'.$val;
        }
        $urlstr = substr($url, 1, strlen($url) - 1);
        $madeurl = $this->_parts['basepath'].(empty($urlstr) ? '' : 'index.php/'.$urlstr);
        if (!empty($this->_config['suffix'])) {
            return $madeurl.$this->_config['suffix'];
        } else {
            return $madeurl;
        }
    }

    public function _mkurl_rewrite($cap = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        if (!empty($cap['controller'])) {
            $url .= '/'.$cap['controller'];
        } else {
            $url .= '/'.$this->_config['default_controller'];
        }
        if (!empty($cap['action'])) {
            $url .= '/'.$cap['action'];
        } else {
            $url .= '/'.$this->_config['default_action'];
        }
        foreach ($cap['params'] as $key=>$val) {
            $url .= '/'.$key.'/'.$val;
        }
        $urlstr = substr($url, 1, strlen($url) - 1);
        $madeurl = $this->_parts['basepath'].$urlstr;
        if (!empty($this->_config['suffix'])) {
            return $madeurl.$this->_config['suffix'];
        } else {
            return $madeurl;
        }
    }

    public function _mkurl_tea($cap = [])
    {
        $cap = !empty($cap) ? $cap : $this->router();
        if (!empty($cap['controller'])) {
            $url .= '/'.$cap['controller'];
        } else {
            $url .= '/'.$this->_config['default_controller'];
        }
        if (!empty($cap['action'])) {
            $url .= '-'.$cap['action'];
        } else {
            $url .= '-'.$this->_config['default_action'];
        }
        foreach ($cap['params'] as $key=>$val) {
            $url .= '-'.$key.'-'.$val;
        }
        $urlstr = substr($url, 1, strlen($url) - 1);
        $madeurl = $this->_parts['basepath'].$urlstr;
        if (!empty($this->_config['suffix'])) {
            return $madeurl.$this->_config['suffix'];
        } else {
            return $madeurl;
        }
    }

    public function router()
    {
        switch ($this->_config['type']) {
            case 'default':
                return $this->parse_default();
                break;
            case 'pathinfo':
                return $this->parse_pathinfo();
                break;
            case 'rewrite':
                return $this->parse_rewrite();
                break;
            case 'tea':
                return $this->parse_tea();
                break;
            default:
                return $this->parse_default();
        }
    }

    public function parse_default()
    {
        $this->parse_url($this->geturi());
        //get params
        $this->_parts['query'] = isset($this->_parts['query']) ? $this->_parts['query'] : '';
        $uri = explode('&', $this->_parts['query']);
        if (!empty($uri) && is_array($uri)) {
            foreach ($uri as $param) {
                if (!empty($param)) {
                    $p = explode('=', $param);
                    $params[$p[0]] = $p[1];
                }
            }
        }
        $params = !empty($params) ? $params : '';

        return $this->getcap($params);
    }

    public function parse_pathinfo()
    {
        $this->parse_url($this->geturi());
        $path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        //remove suffix
        if (!empty($this->_config['suffix'])) {
            $path_info = str_replace($this->_config['suffix'], '', $path_info);
        }
        if (!empty($path_info)) {
            $uri = explode('/', $path_info);
        }
        if (empty($this->_config['para_controller'])) {
            $this->_config['para_controller'] = 'c';
        }

        if (empty($this->_config['para_action'])) {
            $this->_config['para_action'] = 'a';
        }

        $params[$this->_config['para_controller']] = $uri[1];
        $params[$this->_config['para_action']] = $uri[2];
        unset($uri[0]);
        unset($uri[1]);
        unset($uri[2]);
        if (!empty($uri) && is_array($uri)) {
            foreach ($uri as $key=>$param) {
                if (($key % 2) == 1) {
                    $params[$param] = $uri[$key + 1];
                }
            }
        }

        return $this->getcap($params);
    }

    public function parse_rewrite()
    {
        $this->parse_url($this->geturi());
        //remove basepath
        $path_info = str_replace($this->_parts['basepath'], '', $this->_parts['path']);
        //remove suffix
        if (!empty($this->_config['suffix'])) {
            $path_info = str_replace($this->_config['suffix'], '', $path_info);
        }

        $uri = explode('/', $path_info);

        if (empty($this->_config['para_controller'])) {
            $this->_config['para_controller'] = 'c';
        }

        if (empty($this->_config['para_action'])) {
            $this->_config['para_action'] = 'a';
        }

        $params[$this->_config['para_controller']] = $uri[0];
        $params[$this->_config['para_action']] = $uri[1];
        unset($uri[0]);
        unset($uri[1]);
        if (!empty($uri) && is_array($uri)) {
            foreach ($uri as $key=>$param) {
                if (($key % 2) == 1) {
                    $params[$param] = $uri[$key + 1];
                }
            }
        }

        return $this->getcap($params);
    }

    public function parse_tea()
    {
        $this->parse_url($this->geturi());
        //remove basepath
        $path_info = str_replace($this->_parts['basepath'], '', $this->_parts['path']);
        //remove suffix
        if (!empty($this->_config['suffix'])) {
            $path_info = str_replace($this->_config['suffix'], '', $path_info);
        }

        $uri = explode('-', $path_info);

        if (empty($this->_config['para_controller'])) {
            $this->_config['para_controller'] = 'c';
        }

        if (empty($this->_config['para_action'])) {
            $this->_config['para_action'] = 'a';
        }

        $params[$this->_config['para_controller']] = $uri[0];
        $params[$this->_config['para_action']] = $uri[1];
        unset($uri[0]);
        unset($uri[1]);
        if (!empty($uri) && is_array($uri)) {
            foreach ($uri as $key=>$param) {
                if (($key % 2) == 1) {
                    $params[$param] = $uri[$key + 1];
                }
            }
        }

        return $this->getcap($params);
    }

    public function getcap($params)
    {
        if (empty($this->_config['para_controller'])) {
            $this->_config['para_controller'] = 'c';
        }

        if (empty($this->_config['para_action'])) {
            $this->_config['para_action'] = 'a';
        }

        if (!empty($params)) {
            foreach ($params as $key=>$val) {
                if ($key == $this->_config['para_controller']) {
                    $controller = $val;
                    unset($params[$key]);
                }
                if ($key == $this->_config['para_action']) {
                    $action = $val;
                    unset($params[$key]);
                }
            }
        }

        if (empty($controller)) {
            if (empty($this->_config['default_controller'])) {
                $controller = 'main';
                $this->_config['default_controller'] = 'main';
            } else {
                $controller = $this->_config['default_controller'];
            }
        }
        if (empty($action)) {
            if (empty($this->_config['default_action'])) {
                $action = 'index';
                $this->_config['default_action'] = 'index';
            } else {
                $action = $this->_config['default_action'];
            }
        }

        return ['controller'=>$controller, 'action'=>$action, 'params'=>$params];
    }

    public static function redirect($location, $exit = true, $code = 302, $headerBefore = null, $headerAfter = null)
    {
        if ($headerBefore != null) {
            for ($i = 0; $i < count($headerBefore); $i++) {
                header($headerBefore[$i]);
            }
        }
        header("Location: $location", true, $code);
        if ($headerAfter != null) {
            for ($i = 0; $i < count($headerBefore); $i++) {
                header($headerBefore[$i]);
            }
        }
        if ($exit) {
            exit;
        }
    }

    public function geturi($uri = '')
    {
        if (empty($this->_uri)) {
            if ($uri == '') {
                if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                    $http = 'https://';
                } else {
                    $http = 'http://';
                }
                if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
                    $theURI = $http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                } else {
                    $theURI = 'http'.$https.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
                    if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                        $theURI .= '?'.$_SERVER['QUERY_STRING'];
                    }
                }
            } else {
                $theURI = $uri;
            }
            $this->_uri = $theURI;
        }

        return $this->_uri;
    }

    public function parse_url($url)
    {
        $result = [];
        $entities = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'];
        $replacements = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '$', ',', '/', '?', '%', '#', '[', ']'];
        $encodedURL = str_replace($entities, $replacements, urlencode($url));
        $encodedParts = parse_url($encodedURL);
        foreach ($encodedParts as $key => $value) {
            $this->_parts[$key] = urldecode($value);
        }
        $check = $this->_isip($this->_parts['host']);
        if ($check == false) {
            if (isset($this->_parts['host']) && $this->_parts['host'] != '') {
                $domain = $this->_getdomain($this->_parts['host']);
            } else {
                $domain = $this->_getdomain($url);
            }
            if (!empty($domain)) {
                $this->_parts['domain'] = $domain;
            }
        } else {
            if (!empty($result['host'])) {
                $this->_parts['domain'] = $result['host'];
            }
        }

        //获取base path
        if (!empty($this->_parts['path'])) {
            $this->_parts['basepath'] = $this->_get_basepath($this->_parts['path']);
        } else {
            $this->_parts['basepath'] = '/';
        }

        return $this->_parts;
    }

    public function _get_basepath($path)
    {
        $pwd = str_replace('\\', '/', str_replace('../', '/', APP_PATH));
        $pwd = preg_replace("/\/+/", '/', $pwd);
        $pwd = explode('/', $pwd);
        $urlpath = str_replace('\\', '/', str_replace('../', '/', $path));
        $urlpath = preg_replace("/\/+/", '/', $urlpath);
        if ($urlpath != '/') {
            $urlpath = explode('/', $urlpath);
        } else {
            $urlpath = [''];
        }
        $basepath = array_intersect($urlpath, $pwd);
        $bp = implode('/', $basepath);
        if (!empty($bp)) {
            return '/'.$bp.'/';
        } else {
            return '/';
        }
    }

    private function _isip($ip_addr)
    {
        if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip_addr)) {
            $parts = explode('.', $ip_addr);
            foreach ($parts as $ip_parts) {
                if (intval($ip_parts) > 255 || intval($ip_parts) < 0) {
                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    private function _getdomain($domainb)
    {
        $bits = explode('/', $domainb);
        if ($bits[0] == 'http:' || $bits[0] == 'https:') {
            $domainb = $bits[2];
        } else {
            $domainb = $bits[0];
        }
        $bits = explode('.', $domainb);
        $idz = count($bits);
        $idz -= 3;
        if (strlen($bits[($idz + 2)]) == 2) {
            if (in_array($bits[($idz + 1)], ['com', 'net', 'org', 'gov', 'mil', 'co', 'biz', 'me', 'idv'])) {
                $url = $bits[$idz].'.'.$bits[($idz + 1)].'.'.$bits[($idz + 2)];
            } else {
                $url = $bits[($idz + 1)].'.'.$bits[($idz + 2)];
            }
            //todo:判断zj.cn等省级域名。
        } elseif (strlen($bits[($idz + 2)]) == 0) {
            $url = $bits[($idz)].'.'.$bits[($idz + 1)];
        } else {
            $url = $bits[($idz + 1)].'.'.$bits[($idz + 2)];
        }

        return $url;
    }
}
