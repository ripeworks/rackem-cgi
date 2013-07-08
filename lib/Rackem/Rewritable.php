<?php
namespace Rackem;

class Rewritable extends Php
{
	public function call($env)
	{
		$path = realpath($this->public_folder.$env['PATH_INFO']);
		if(!$this->is_valid($env['PATH_INFO']))
		{
			if(is_file($path))
			{
				$file = new \Rackem\File($this->public_folder);
				return $file->call($env);
			}
			$path = $this->public_folder.'/index.php'.$env['PATH_INFO'];
		}
		return $this->run($env, $path);
	}
}
