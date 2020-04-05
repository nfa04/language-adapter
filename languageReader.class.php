<?php
class languageReader {

	private $langPackages = array();
	
	private function is_complex($lang) {
		if(strpos($lang, '-') === false) return false;
		else return true;
	}
	
	function __construct($langPackDir, $fallbackLanguage = 'en', $use_downgrade_fallbacks = true) {
        define('langPackDir', $langPackDir);
        define('fallbackLanguage', $fallbackLanguage);
        define('use_downgrade_fallbacks', $use_downgrade_fallbacks);

		$this->scan();
    }
	
	function scan() {
		$langPackageScan = scandir(langPackDir) or die('ERROR: Could not find language Packages in folder '.langPackDir);
		foreach($langPackageScan AS $result) {
			if ($result === '.' or $result === '..') continue;

			if (is_dir(langPackDir . '/' . $result)) {
				// Found package
				$this->langPackages[] = $result;
			}
		}
	}
	
	
	function getFallbackPackage() {
		return new languagePackage(langPackDir.'/'.fallbackLanguage, $this);
	}
	
	function getDowngradeLanguageName($lang) {
		return substr($lang, 0, strpos($lang, '-'));
	}
	
	function getDowngradeLanguagePackage($lang) {
		return new languagePackage(langPackDir.'/'.$this->getDowngradeLanguageName($lang), $this);
	}
	
	function getLanguagePackage($lang, $use_fallback = true) {
		if(in_array($lang, $this->langPackages)) {
			// Perfect. This package exists
			return new languagePackage(langPackDir.'/'.$lang, $this);
		}
		else if(use_downgrade_fallbacks AND $this->is_complex($lang)) {
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
		return new languagePackage(langPackDir.'/intranslatable', $this);
	}
	
	function getPackageList() {
		return $this->langPackages;
	}
	
	function getPackages() {
		$packages = array();
		foreach($this->langPackages AS $package) {
			$packages[] = new languagePackage(langPackDir.'/'.$package, $this);
		}
		return $packages;
	}
}