<?php 

	/////////////////////////////////////////
	// API CACHE
	/////////////////////////////////////////
	
	// Settings
	
	$default_cache = "1 HOUR";
		
	/* ************************************
	
	// Notes
	
	- A simple PHP script with database backend to cache API responses
	- Slot in your own database handler
	
	// Usage
	
	$data = GetDataCacheOrLive("URLHERE","1 HOUR");		// set cache time
	$data = GetDataCacheOrLive("URLHERE"); 				// uses $default_cache
	
	// Database Structure

	CREATE TABLE `data_dump` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`url` longtext NOT NULL,
	`data` longtext NOT NULL,
	`updated` datetime NOT NULL,
	)
	
	************************************ */
	
	// What we call when we want the data
	
	function GetDataCacheOrLive($url,$cache_time='$default_cache') {

		// Try grab it from the db cache
	
		$query = "SELECT data FROM data_dump 
					WHERE url='$url' AND updated>DATE_SUB(now(), INTERVAL $cache_time) ";
					
		$result = db_query($query) or die(db_error($query));

		if (db_num_rows($result) > 0) {
					
			// we have cached data
			
			while ($row = db_fetch_array($result)) {
				$data = $row['data'];
			} 
									
			return ($data);
			
		} else {
						
			// Need to do live call
			// It'll store it in the cache after it gets it
			
			$data = MakeLiveCall($url);
						
			return ($data);
			
		}
		
	}
	
	// Live Call
	
	function MakeLiveCall($url) {
		
		// Make server call 
		    	
		$ch = curl_init();
	    $timeout = 0;
	    curl_setopt ($ch, CURLOPT_URL, $url);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,TRUE);
		curl_setopt ($ch, CURLOPT_MAXREDIRS,2); //only 2 redirects
	    curl_setopt ($ch, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
	    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	    $rawdata = curl_exec($ch);
	    curl_close($ch);
		
		// Store response
		
		$stored = StoreResponse($url,$rawdata);
		
		// Return
		
		return $rawdata;
		
	}
	
	// Store response
	
	function StoreResponse($thisurl,$thisdata) {

		// Try grab it from the db
	
		$query = "SELECT data FROM data_dump 
					WHERE url='$thisurl' ";
		
		$result = db_query($query) or die(db_error($query));
		
		if (db_num_rows($result) > 0) {
			
			// Need to UPDATE
			
			$update_data = array();
			$update_data['data'] = $thisdata;
			$update_data['updated'] = 'NOW()';
					
			if (db_update($update_data, 'data_dump', "url='$thisurl'")) {
				return 1;
			} else {
				return 0;
			}
			
		} else {
			
			// Need to INSERT
			
			$insert_data = array();
			$insert_data['url'] = $thisurl;
			$insert_data['data'] = $thisdata;
			$insert_data['updated'] = 'NOW()';
					
			if (db_insert($insert_data, 'data_dump')) {
				return 1;
			} else {
				return 0;
			}
			
		}
			
		return 0;
		
	}
	
?>