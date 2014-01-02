<?php
namespace Rackem;

class Php extends Cgi
{
	public $php_exec, $options;

	public function __construct($app, $public_folder, $options = array())
	{
		parent::__construct($app, $public_folder);
		$defaults = array(
			'php' => 'php-cgi'
		);
		$this->options = array_merge($defaults, $options);
		$this->php_exec = $this->options['php'];
	}

	public function is_valid($path)
	{
		list($script, $info) = $this->path_parts($this->public_folder.$path);
		if(!preg_match('/\.php$/',$script) && !is_dir($script)) return false;
		if(strpos($script, $this->public_folder) === false) return false;
		return true;
	}

//private
	protected function run($env, $path)
	{
		if(strpos($path, '?') !== false) list($path, $query_string) = explode('?', $path, 2);
		list($script, $info) = $this->path_parts($path);
		if(is_dir($script))
		{
			if(substr($env['PATH_INFO'], -1, 1) !== '/')
				return array(301, array('Location'=>$env['PATH_INFO'].'/'), array());
			$script = rtrim($script,'/').'/index.php';
			$path = rtrim($path, '/').'/';
			$info = '/';
		}
		$env['REMOTE_ADDR'] = '127.0.0.1';
		$env['PATH_INFO'] = $info;

		$env['SCRIPT_FILENAME'] = $script;
		$env['SCRIPT_NAME'] = str_replace($this->public_folder, '', $script);
		$env['REQUEST_URI'] = $info;
		if(isset($env['QUERY_STRING']) && $env['QUERY_STRING'])
			$env['REQUEST_URI'] .= "?".$env['QUERY_STRING'];
		putenv("REDIRECT_STATUS=0");
		return parent::run($env, $this->php_exec);
	}

	protected function path_parts($path)
	{
		if(strpos($path, '.php') === false) return array($path, null);
		list($script, $info) = explode('.php', $path, 2);
		return array($script.'.php', $info);
	}
}
