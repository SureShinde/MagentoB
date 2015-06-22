<?php
/**
 * User: Akbar
 * Date: 6/9/2015
 * Time: 12:43 PM
 */

namespace Bilna\Libraries;

/*
 * vim: ts=4 sw=4 fdm=marker noet tw=78
 */
class CurlDownloader
{
	private $remoteFileName = NULL;



	private $ch = NULL;
	private $headers = array();
	private $response = NULL;
	private $fp = NULL;
	private $debug = FALSE;
	private $fileSize = 0;

	private $verbose;

	public $DEFAULT_FNAME = 'bilna-api.out';
	public $folder = './';

	public function __construct($url)
	{
		$this->init($url);
	}

	public function toggleDebug()
	{
		$this->debug = !$this->debug;
	}

	public function init($url)
	{
		if( !$url )
			throw new \InvalidArgumentException("Need a URL");

		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
//		curl_setopt($this->ch, CURLOPT_HEADER, true);

		curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36');
		curl_setopt($this->ch, CURLOPT_TIMEOUT,60*60);


		$headers = array(
			'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding:gzip, deflate, sdch',
		);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

//		curl_setopt($this->ch, CURLOPT_VERBOSE, true);
//		$this->verbose = fopen('php://temp', 'rw+');
//		curl_setopt($this->ch, CURLOPT_STDERR, $this->verbose);

		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION,
			array($this, 'headerCallback'));
		curl_setopt($this->ch, CURLOPT_WRITEFUNCTION,
			array($this, 'bodyCallback'));
	}

	public function headerCallback($ch, $string)
	{
		$len = strlen($string);
		if( !strstr($string, ':') )
		{
			$this->response = trim($string);
			return $len;
		}
		list($name, $value) = explode(':', $string, 2);
		if( strcasecmp($name, 'Content-Disposition') == 0 )
		{
			$parts = explode(';', $value);
			if( count($parts) > 1 )
			{
				foreach($parts AS $crumb)
				{
					if( strstr($crumb, '=') )
					{
						list($pname, $pval) = explode('=', $crumb);
						$pname = trim($pname);
						if( strcasecmp($pname, 'filename') == 0 )
						{
							// Using basename to prevent path injection
							// in malicious headers.
							$this->remoteFileName = $this->folder.basename(
								$this->unquote(trim($pval)));
							@unlink($this->remoteFileName);
							$this->fp = fopen($this->remoteFileName, 'wb');
						}
					}
				}
			}
		}

		$this->headers[$name] = trim($value);
		return $len;
	}
	public function bodyCallback($ch, $string)
	{
		if( !$this->fp )
		{
			trigger_error("No remote filename received, trying default",
				E_USER_WARNING);
			$this->remoteFileName = $this->folder . $this->DEFAULT_FNAME;
			$this->fp = fopen($this->remoteFileName, 'wb');
			if( !$this->fp )
				throw new \RuntimeException("Can't open default filename");
		}
		$len = fwrite($this->fp, $string);
		$this->fileSize += $len;
		return $len;
	}

	public function download()
	{
		$this->folder = realpath($this->folder);
		if(!$this->folder || !is_dir($this->folder)){
			throw new \RuntimeException("Can't open folder");
		}else{
			$this->folder .= DIRECTORY_SEPARATOR;
		}
//		rewind($this->verbose);,$verboseLog
//		$verboseLog = stream_get_contents($this->verbose);

//		echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
		$retval = curl_exec($this->ch);
		if( $this->debug )
			var_dump([$retval,$this->headers,curl_getinfo($this->ch)]);
		if( $this->fp )
			fclose($this->fp);
		curl_close($this->ch);
		return $this->fileSize;
	}

	public function getFileName() { return $this->remoteFileName; }

	private function unquote($string)
	{
		return str_replace(array("'", '"'), '', $string);
	}
}

