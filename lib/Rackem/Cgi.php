<?php
namespace Rackem;

class Cgi
{
	public $app, $public_folder;

	public function __construct($app = null, $public_folder = null)
	{
		$this->app = $app;
		$this->public_folder = is_null($public_folder) ? getcwd() : $public_folder;
	}

	public function call($env)
	{
		if($this->is_valid($env['PATH_INFO']))
			return $this->run($env, realpath($this->public_folder.$env['PATH_INFO']));
		else
			return $this->app->call($env);
	}

	public function is_valid($path)
	{
		$path = realpath($this->public_folder.$path);
		if(strpos($path, $this->public_folder) !== false && is_file($path) && is_executable($path)) return true;
		return false;
	}

//private
	protected function run($env, $path)
	{
		$spec = array(
			0 => array("pipe", "rb"),
			1 => array("pipe", "wb"),
			2 => STDERR
		);

		$env['DOCUMENT_ROOT'] = $this->public_folder;
		$env['SERVER_SOFTWARE'] = 'Rack\'em';
		foreach($env as $k=>$v) if(is_string($v) || is_numeric($v)) putenv("$k=$v");

		$body = str_replace("\0\z","",stream_get_contents($env['rack.input']));
		if(($length = strlen($body))) putenv("CONTENT_LENGTH={$length}");

		$proc = proc_open($path, $spec, $pipes);
		if(!is_resource($proc)) return array(500, array('Content-Type'=>'text/html'), array('<h1>Internal Server Error</h1>'));
		if(isset($body)) fwrite($pipes[0], $body);
		$raw = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($proc);

		$headers = array();
		$status = 200;
		list($raw_header, $body) = preg_split('/\r?\n\r?\n/', $raw, 2);

		$raw_header = preg_split('/\r?\n/', $raw_header);
		foreach($raw_header as $line)
		{
			list($key, $value) = preg_split('/\s*\:\s*/', $line, 2);
			if($key == 'Content-type') $key = 'Content-Type';
			if(isset($headers[$key]))
				$headers[$key] .= "\n$value";
			else
				$headers[$key] = $value;
		}
		if(isset($headers['Status']))
		{
			preg_match('/(\d{3})/', $headers['Status'], $m);
			$status = $m[0];
			unset($headers['Status']);
		}
		return array($status, $headers, array($body));
	}
}
