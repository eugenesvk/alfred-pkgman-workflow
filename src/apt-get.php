<?php

/*
Apt-Get

*/

// ****************

require_once('cache.php');
require_once('workflows.php');

class Repo {
	
	private $id = 'apt-get';
	private $min_query_length = 1; // increase for slow DBs
	private $max_return = 25;
	
	private $cache;
	private $w;
	private $pkgs;
	
	function __construct() {
		
		$this->cache = new Cache();
		$this->w = new Workflows();
		
		// get DB here if not dynamic search
		//$data = (array) $this->cache->get_db($this->id);
		//$this->$pkgs = $data;
	}
	
	// return id | url | pkgstr
	function makeArg($id, $url, $version) {
		return $id . "|" . $url . "|" . $id;//"\"$id\":\"$version\",";
	}
	
	function check($pkg, $query) {
		if (!$query) { return true; }
		if (strpos($pkg["name"], $query) !== false) {
			return true;
		} else if (strpos($pkg["description"], $query) !== false) {
			return true;
		} 
	
		return false;
	}
	
	function search($query) {
		if ( count($query) < $this->min_query_length) {
			$this->w->result(
				"{$this->id}-min",
				$query,
				"Minimum query length of {$this->min_query_length} not met.",
				"",
				"icon-cache/{$this->id}.png"
			);
			return;
		}
		
		$this->pkgs = $cache->get_query_regex($this->id, $query, 'https://apps.ubuntu.com/cat/search/?q='.$query, '/<tr>([\s\S]*?)<\/tr>/i');
		
		foreach($this->pkgs as $item) {
			preg_match('/<p>(.*?)<\/p>/i', $item, $matches);
			$name = trim(strip_tags($matches[1]));
			
			preg_match('/<h3>([\s\S]*?)<\/h3>/i', $item, $matches);
			$description = trim(strip_tags($matches[1]));
		
			$this->w->result(
				$name,
				$this->makeArg($name, 'https://apps.ubuntu.com/cat/applications/'.$name, "*"),
				$name,
				$description,
				"icon-cache/{$this->id}.png"
			);
			//break;
			// only search till max return reached
			if ( count ( $this->w->results() ) == $this->max_return ) {
				break;
			}
		}
		
		if ( count( $this->w->results() ) == 0) {
			$this->w->result(
				"{$this->id}-search",
				"https://apps.ubuntu.com/cat/search/?q={$query}",
				"No components were found that matched \"{$query}\"",
				"Click to see the results for yourself",
				"icon-cache/{$this->id}.png"
			);
		}
	}
	
	function xml() {
		$this->w->result(
			"{$this->id}-www",
			"https://apps.ubuntu.com/cat/",
			"Go to the website",
			"https://apps.ubuntu.com",
			"icon-cache/{$this->id}.png"
		);
		
		return $this->w->toxml();
	}

}

// ****************

/*$query = "leaflet";
$repo = new Repo();
$repo->search($query);
echo $repo->xml();*/

?>