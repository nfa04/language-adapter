<?php
class languageReader {

	private $langPackages = array();
	private $langPackDir;
	private $fallbackLanguage;
	private $use_downgrade_fallbacks;
	private $replace_linebreaks;
	private $listen_on_get;
	
	
	private function is_complex($lang) {
		if(strpos($lang, '-') === false) return false;
		else return true;
	}
	
	
	function __construct($langPackDir, $fallbackLanguage = 'en', $use_downgrade_fallbacks = true, $replace_linebreaks = true, $l_o_g = false) {

		@session_start();
		
		$this->langPackDir = __DIR__.DIRECTORY_SEPARATOR.$langPackDir;
		$this->fallbackLanguage = $fallbackLanguage;
		$this->use_downgrade_fallbacks = $use_downgrade_fallbacks;
		$this->replace_linebreaks = $replace_linebreaks;
		$this->listen_on_get = $l_o_g;

		$this->scan();
    }
	
	function scan() {
		$langPackageScan = scandir($this->langPackDir) or die('ERROR: Could not find language Packages in folder '.$this->langPackDir);
		foreach($langPackageScan AS $result) {
			if ($result === '.' or $result === '..') continue;
			if($result == 'languagePackages.conf') {
				// Found the configuration file
				$this->readConfig($this->langPackDir.'/languagePackages.conf');
			}
			if (is_dir($this->langPackDir . '/' . $result)) {
				// Found package
				$this->langPackages[] = $result;
			}
		}
	}
	
	function readConfig($path) {
		// Read in the file
		$file = file_get_contents($path);
		$conf = explode(';', $file);
		foreach($conf AS $preference) {
			$data = explode('=', trim($preference));
			$property = $data[0];
			$this->$property = $data[1];
		}
	}
	
	function getLinebreakReplace() {
		return $this->replace_linebreaks;
	}
	
	function getFallbackPackage() {
		return new languagePackage($this->langPackDir.'/'.$this->fallbackLanguage, $this);
	}
	
	function getDowngradeLanguageName($lang) {
		return substr($lang, 0, strpos($lang, '-'));
	}
	
	function getDowngradeLanguagePackage($lang) {
		return new languagePackage($this->langPackDir.'/'.$this->getDowngradeLanguageName($lang), $this);
	}
	
	function getLanguagePackage($lang, $use_fallback = true) {
		if(in_array($lang, $this->langPackages)) {
			// Perfect. This package exists
			return new languagePackage($this->langPackDir.'/'.$lang, $this);
		}
		else if($this->use_downgrade_fallbacks AND $this->is_complex($lang)) {
			// This package doesn't exist so check if downgrade is available
			return $this->getDowngradeLanguagePackage($lang);
		}
		else if($use_fallback) {
			// This package doesn't exist and there isn't a downgrade available so return the fallback package
			return $this->getFallbackPackage();
		}
		else return false;
	}
	
	function getAutodetectedLanguagePackage() {
		if(isset($_SESSION['la-selected-lang']) AND !isset($_GET[$this->get_param])) return $this->getLanguagePackage($_SESSION['la-selected-lang']);
		if(($this->listen_on_get AND isset($_GET[$this->get_param]))) {
			// Chosen language detected in get params
			$lp = $this->getLanguagePackage($_GET[$this->get_param]);
			if($lp !== false) {
				$_SESSION['la-selected-lang'] = $lp->getPackageName();
				return $lp;
			}
			else {
				$dlp = $this->getDowngradeLanguagePackage($_GET[$this->get_param]);
				if($dlp !== false) {
					$_SESSION['la-selected-lang'] = $dlp->getPackageName();
					return $this->getDowngradeLanguagePackage($_GET[$this->get_param]);
				}
			}
		}
		$accepted = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$weights = array();
		foreach($accepted AS $lang) {
			$lang = trim($lang);
			if(strpos($lang, ';') === false) $weights[$lang] = 1;
			else {
				$explode = explode(';q=', $lang);
				$weights[$explode[0]] = $explode[1];
			}
		}
		arsort($weights);
		$priorities = array_keys($weights);
		foreach($priorities AS $lang) {
			$langPack = $this->getLanguagePackage($lang, false);
			if($langPack !== false) {
				return $langPack;
				break;
			}
		}
		return $this->getFallbackPackage();
	}
	
	function getIntranslatablePackage() {
		return new languagePackage($this->langPackDir.'/intranslatable', $this);
	}
	
	function getPackageList() {
		return $this->langPackages;
	}
	
	function getPackages() {
		$packages = array();
		foreach($this->langPackages AS $package) {
			$packages[] = new languagePackage($this->langPackDir.'/'.$package, $this);
		}
		return $packages;
	}
	
}