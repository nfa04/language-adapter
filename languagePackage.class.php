<?php
error_reporting (E_ALL ^ E_NOTICE);
class languagePackage {
	
	private $packageDir;
	private $idList;
	private $reader;
	
	function __construct($dir, $reader) {
		$this->packageDir = $dir;
		$this->reader = $reader;
		$this->read();
	}
	
	function read() {
		// Scan directory
		
		$scan = scandir($this->packageDir) or die('Could not read package directory');
		
		$list = array();
		
		foreach($scan AS $result) {
			if($result != '.' AND $result != '..') $list[] = str_replace('.txt','',$result);
		}
		
		$this->idList = $list;
		
	}
	
	function getStringRessourcePath($stringId) {
		
		if(in_array($stringId, $this->idList)) {
			// Perfect. This string exists so return path
			$pdir = $this->packageDir;
			return $pdir.'/'.$stringId.'.txt';
		}
		else {
			// This string doesn't exist return false
			return false;
		}
	}
	
	function getString($stringId) {
		// Read this ressource if it exists
		$resPath = $this->getStringRessourcePath($stringId);
		if($resPath !== false) return $this->renderString(file_get_contents($resPath));
		else return false;
	}
	
	function setString($stringId, $value, $override = true) {
		if($override) {
			// Override is enabled so write data anyway
			file_put_contents($this->packageDir.'/'.$stringId.'.txt', $value);
		}
		else {
			// Override is disabled so check if data exist
			if($this->getStringRessourcePath($stringId) === false) {
				// No data exist. Write them
				file_put_contents($this->packageDir.'/'.$stringId.'.txt', $value);
			}
			else {
				// Data exist. Don't write anything
				return false;
			}
		}
	}
	
	function printString($stringId) {
		echo $this->getString($stringId);
	}
	
	private function renderString($string) {
		$words = explode(' ', $string);
		for ($i = 0; $i <= count($words); $i++) {
			if(substr($words[$i],0,1) == '@') {
				// This is a command
				$command = explode(':', $words[$i]);
				// $command[0] contains the command
				// $command[1] contains the parameters
				if($command[0] == '@link') {
					$data = explode('/', $command[1]);
					// $data[0] contains the package
					// $data[1] contains the stringId
					
					// Get , or . at the end of the string and remove all of them from stringId
					$ending = substr($data[1], -1, 1);
					$add = '';
					if($ending == ',' OR $ending == '.') {
						$add = $ending;
					}
					$data[1] = str_replace(',', '', $data[1]);
					$data[1] = str_replace('.', '', $data[1]);
					
					if($data[0] == 'this') {
						$words[$i] = $this->getString($data[1]).$add;
					}
					else $words[$i] = $this->reader->getLanguagePackage($data[0])->getString($data[1]).$add;
				}
			} else continue;
		}
		$str = implode(' ', $words);
		if($this->reader->getLinebreakReplace()) $str = nl2br($str);
		return $str;
	}

}
?>