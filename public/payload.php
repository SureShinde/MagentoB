<?php
/**
 * User: Akbar
 * Date: 6/8/2015
 * Time: 7:56 PM
 */
//echo 'ok';
//die('ok1');


if (!function_exists('getallheaders')) {
	function getallheaders()
	{
		$headers = '';
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}
$headers = getallheaders();

if($headers['X-GitHub-Delivery']=='5edb4f80-0dde-11e5-9a4c-29eb54310df5'
&& $headers['X-Github-Event']=='release'
)die('401: Not Auth');

$body = urldecode(file_get_contents('php://input'));

$json = json_decode($body);

if($json){
	$token = '33cda1eb1f402b1ff44923c317b5c993e2bc9ab3';

	// mkdir ../$json->release->tag_name

	file_put_contents(__DIR__.'/../logs/payload_'.time().rand(100,999).'.log',$body);
	echo 'ok'."\n";

	if($json->action=='published' && !empty($json->release->prerelease) && !empty($json->release->zipball_url)){


		require_once '../loader/CurlDownloader.php';
		$dl = new Bilna\Libraries\CurlDownloader(
			$json->release->zipball_url.'?access_token='.$token
		);
		$dl->folder = '../logs/';
		set_time_limit (24 * 60 * 60);

		printf("start downloading ".$json->release->zipball_url.'?access_token='.$token."\n");
		$size = $dl->download();
		printf("Downloaded %u bytes to %s\n", $size, $dl->getFileName());

		$fullpath = $dl->getFileName();

		try {
			$version = $json->release->tag_name;
			$versions = [];
			if(preg_match("@^v?([0-9]+\.?[0-9]*)@",$version, $versions))$version='v'.$versions[1];

			$destination = realpath('..').'/'.$version;

			mkdir($destination,true);
			$old = umask(0);
			@chmod($destination, 0777);
			umask($old);


			$zip = new ZipArchive;
			if ($zip->open($fullpath) === TRUE) {
				$zip->extractTo($destination.'/');
				$zip->close();

//				$fileinfo = pathinfo($fullpath);
				$files = scandir($destination,1);
				var_dump([$destination.'/'.$files[0],$destination,rmove($destination.'/'.$files[0],$destination)]);

				echo 'ok';
			} else {
				echo 'failed';
			}
		} catch (Exception $e) {
			// handle errors
			die($e->getMessage());
		}

		@unlink($fullpath);

	}
}

function rmove($src, $dest){

	// If source is not a directory stop processing
	if(!is_dir($src)) {
		var_dump("fail in src $src");
		return false;
	}

	// If the destination directory does not exist create it
	if(!is_dir($dest)) {
		if(!mkdir($dest)) {
			var_dump("fail in dst $dest");
			// If the destination directory could not be created stop processing
			return false;
		}
	}

	// Open the source directory to read in files
	$i = new DirectoryIterator($src);
	foreach($i as $f) {
		if($f->isFile()) {
			rename($f->getRealPath(), "$dest/" . $f->getFilename());
		} else if(!$f->isDot() && $f->isDir()) {
			rmove($f->getRealPath(), "$dest/$f");
			unlink($f->getRealPath());
		}
	}
//	var_dump("success move $src, $dest");
	@unlink($src);

	return @rmdir($src);
}


function download($url,$destination_folder = '../logs/'){
	// maximum execution time in seconds
	set_time_limit (24 * 60 * 60);

	//if (!isset($_POST['submit'])) die();


//	$url = $_POST['url'];
	$newfname = $destination_folder . basename($url);

	$file = fopen ($url, "rb");
	if ($file) {
		$newf = fopen ($newfname, "wb");

		if ($newf)
			while(!feof($file)) {
				fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
			}
	}

	if ($file) {
		fclose($file);
	}

	if ($newf) {
		fclose($newf);
	}

	return $newfname;
}

