# apicache
A simple PHP script with database backend to cache API responses.

# Notes
	
- A simple PHP script with database backend to cache API responses
- Slot in your own database handler
	
#  Usage
	
`$data = GetDataCacheOrLive("URLHERE","1 HOUR"); // set cache time`

`$data = GetDataCacheOrLive("URLHERE"); // uses $default_cache`
	
# Database Structure

	CREATE TABLE `data_dump` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`url` longtext NOT NULL,
	`data` longtext NOT NULL,
	`updated` datetime NOT NULL,
	)
