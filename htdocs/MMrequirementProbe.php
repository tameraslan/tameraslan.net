<?php
/**
 * Check Server Requirements For Installs
 *
 * Check the server to see if it meets the requirements for a given script. In this script,
 * we will make sure that passthru works and we will check requirements for:
 * - PHP Version
 * - PHP Functions
 * - PHP Extensions
 * - PHP INI Settings
 * - Database Versions
 * - Database Types
 * - OS
 * - disk space
 *
 * The json decoded requirements may contain the following:
 * 
 * $requirements = array(
 * 	'php' => array(
 * 		'version' => array(
 * 			'min' => $_POST['php_version_min'],
 * 			'max' => $_POST['php_version_max']
 * 		),
 * 		'functions' => array(
 * 			'list', 'of', 'functions',
 * 		),
 * 		'extensions' => array(
 * 			'extension',
 * 			'list',
 * 			'with',
 * 			'OR' => array(
 * 				'will',
 * 				'check',
 * 				'for',
 * 				'one',
 * 				'of',
 * 				'these'
 * 			),
 * 		),
 * 		'ini' => array(
 * 			'setting_name' => array(
 * 				'min' => 'VAL',
 * 				'max' => 'VAL',
 * 			),
 * 			'setting_name' => array(
 * 				'bool' => '[true,false,on,off,1,0]',
 * 			),
 * 			'setting_name' => array(
 * 				'value' => 'some value to check',
 * 			),
 * 		),
 * 	),
 * 	'database' => array(
 * 		'mysql' => array(
 * 			'version' => array(
 * 				'min' => 'VAL',
 * 			 	'max' => 'VAL',
 * 	    	),
 *      ),
 *  ),
 * 	'os' => array(
 * 			'list', 'of', 'valid', 'oses',
 * 		),
 * 	'diskspace' => 'some size can be human readable (i.e. 256m, 256MB, 1 G, etc.)',
 * 	'webserver' => array(
 * 		'valid', 'web', 'servers',
 * 	),
 * );
 * 	
 */

error_reporting(0);
@ini_set('cgi.fix_pathinfo', 1);
$requirements = urldecode($_POST['requirements']);
$requirements = preg_replace('%\\\"%', '"', $requirements);
$requirements = unserialize($requirements);

$status = array();

// check php version
if (isset($requirements['php']['version']) && !empty($requirements['php']['version'])) {
	$results = checkVersion($requirements['php']['version']);
	if (!empty($results)) {
		if ($results['min']) {
			$status[] = 'The script requires a minimum PHP version of ' . $results['min'] . ' and your server is reporting ' . $results['actual'];
		}
		if ($results['max']) {
			$status[] = 'The script can have a maximum PHP version of ' . $results['max']	. ' and your server is reporting ' . $results['actual'];
		}
	}
}

// check php functions
if (isset($requirements['php']['functions']) && !empty($requirements['php']['functions'])) {
	$results = checkFunctions($requirements['php']['functions']);
	if (!empty($results)) {
		$status[] = 'The script requires the following missing functions: (' . implode(',', $results) . ') ';
	}
}

// check php extensions
if (isset($requirements['php']['extensions']) && !empty($requirements['php']['extensions'])) {
	$results = checkExtensions($requirements['php']['extensions']);
	if (!empty($results)) {
		$status[] = 'The script requires the following missing extensions: (' . implode(',', $results) . ') ';
	}
}

// check php ini
if (isset($requirements['php']['ini']) && !empty($requirements['php']['ini'])) {
	$results = checkIni($requirements['php']['ini']);
	if (!empty($results)) {
		foreach ($results as $key => $value) {
			$status[] = $value;
		}
	}
}

// check database requirements
if (isset($requirements['database']) && !empty($requirements['database'])) {
	if (isset($requirements['database']['mysql']) && !empty($requirements['database']['mysql'])) {
		$results = checkVersion($requirements['database']['mysql']['version'], getMysqlVersion());
		if (!empty($results)) {
			if ($results['min']) {
				$status[] = 'The script requires a minimum mysql version of ' . $results['min'] . ' and your server is reporting ' . $results['actual'];
			}
			if ($results['max']) {
				$status[] = 'The script can have a maximum mysql version of ' . $results['max']	. ' and your server is reporting ' . $results['actual'];
			}
		}
	}
	// add other databases as needed here
}

// check the os
if (isset($requirements['os']) && !empty($requirements['os'])) {
	$results = checkOs($requirements['os']);
	if (!empty($results)) {
		foreach ($results as $key => $value) {
			$status[] = $value;
		}
	}
}

// check diskspace
if (isset($requirements['diskspace']) && !empty($requirements['diskspace'])) {
	$results = checkDiskSpace($requirements['diskspace']);
	if (!empty($results)) {
		foreach ($results as $key => $value) {
			$status[] = $value;
		}
	}
}

// webserver
if (isset($requirements['webserver']) && !empty($requirements['webserver'])) {
	$results = checkWebServer($requirements['webserver']);
	if (!empty($results)) {
		foreach ($results as $key => $value) {
			$status[] = $value;
		}
	}
}

if (empty($status)) {
	$status = 1;
}

/**
 * getMysqlVersion
 *
 * Query the phpinfo to get the Client API Version of the database. This may not necessisarily match
 * the actual version of MySQL. However, there is no other way currently to get the MySQL version
 * without actually accessing the database. Given the face that we do not have the credentials to
 * access the database, this is the best method we currently have to get the version information.
 *
 * @return string The MySQL Client API Version number returned by phpinfo.
 **/
function getMysqlVersion() {
	ob_start();
	phpinfo(INFO_MODULES);
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr($info, 'Client API version');
	preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match);
	return $match[0];
}

/**
 * checkDiskSpace
 *
 * @param string $space The minimum space required for the install of the script.
 * @return bool
 **/
function checkDiskSpace($space = null) {
	$results = array();
	if (!$space) {
		return $results;
	}
	if (!ctype_digit($space)) {
		$space = fromReadableSize($space);
	}
	$freespace = disk_free_space(__DIR__);
	if ($freespace >= $space) {
		// ok
	} else {
		$results[] = 'Not enough disk space. This script requires ' . toReadableSize($space) . ' but there is only ' . toReadableSize($freespace) . ' available.';
	}
	return $results;
}

/**
 * checkWebServer
 *
 * Check to see if the webserver running is valid compared to what is required. The webserver list can
 * be passed as a string or an array. It will loop through each element and check to see if the local
 * running web server matches any in the required list. If it does, it will pass. Otherwise it fails.
 *
 * @param array $wenServerList A list of valid webservers for the given script.
 * @return bool
 **/
function checkWebServer($webServerList = array()) {
	$results = array();
	if (empty($webServerList)) {
		return $results;
	}
	$info = explode('/', $_SERVER['SERVER_SOFTWARE']);
	$thisServer = strtolower($info[0]);
	// This can be inconsistent across releases and configurations, apparently.
	if ( $thisServer == 'microsoft-iis' ) {
		$thisServer = 'iis';
	}
	if (!is_array($webServerList)) {
		if (strtolower($webServerList) !== $thisServer) {
			$results[] = 'The script requires one of the following: (' . $webServerList . ') and your server is running: ' . $thisServer;
		}
	} else {
		$match = false;
		foreach ($webServerList as $webServer) {
			if (strtolower($webServer) == $thisServer) {
				$match = true;
				break;
			}
		}
		if ($match == false) {
			$results[] = 'The script requires one of the following: (' . implode(',', $webServerList) . ') and your server is running: ' . $thisServer;
		}
	}
	return $results;
}

/**
 * checkOs
 *
 * Pass an array of valid operating systems allowed for the given script. If the OS of
 * the server matches one of them, then the operating system will pass and allow install.
 *
 * As part of the process, we lowercase the system OS and the osList items to ensure we
 * are making a verified match. We use PHP_OS as the value to check. PHP uses uname for this
 * value. Keep in mind this is also the name of the system PHP was compiled on.
 *
 * Example uname list as returned by PHP_OS:
 * CYGWIN_NT-5.1
 * Darwin
 * FreeBSD
 * HP-UX
 * IRIX64
 * Linux
 * NetBSD
 * OpenBSD
 * SunOS
 * Unix
 * WIN32
 * WINNT
 * Windows
 *
 * @param array $osList The operating system values that are acceptible.
 * @return bool
 **/
function checkOs($osList = array()) {
	$result = array();
	if (empty($osList)) {
		return $result;
	}
	$thisOs = strtolower(PHP_OS);
	if (is_array($osList)) {
		$match = false;
		foreach ($osList as $os) {
			if (strtolower($os) == $thisOs) {
				$match = true;
				break;
			}
		}
		if ($match == false) {
			$result[] = 'The script requires one of the following operating systems: (' . implode(',', $osList) . ') and your server is running: ' . $thisOs;
		}
	} else {
		if (strtolower($osList) !== $thisOs) {
			$result[] = 'The script requires one of the following operating systems: (' . $osList . ') and your server is running: ' . $thisOs;
		}
	}
	return $result;
}

/**
 * checkVersion
 *
 * This will compare two versions to see if it falls within the expected version limits. By default,
 * the $actualVersion is set to the PHP_VERSION and is used mainly to compare which version of PHP
 * is required vs which version is being run on the server. However, this can also be used to compare
 * other versions of other software as well. To use this method, the first parameter is the min/max
 * version of the requirement. The second variable is the actual version running.
 *
 * To not compare one of the versionCheck array items, set it to null.
 * `$versionCheck['max'] = null;`
 *
 * The results returned will be true if everything is ok. Otherwise, it will return the min, max, 
 * and actual server version. This can then be returned to the user for information.
 * 
 * @param array $versionCheck The min / max version to compare.
 * @param string $actualVersion The actual version to compare to.
 * @return mixed
 **/
function checkVersion($versionCheck = array(), $actualVersion = PHP_VERSION) {
	$results = array();
	if (empty($versionCheck) || !is_array($versionCheck)) {
		return $results;
	}
	if ($versionCheck['min'] && version_compare($actualVersion, $versionCheck['min']) <= 0) {
		$results = $versionCheck;
		$results['actual'] = $actualVersion;
	}
	if ($versionCheck['max'] && version_compare($actualVersion, $versionCheck['max']) >= 0) {
		$results = $versionCheck;
		$results['actual'] = $actualVersion;
	}
	return $results;
}

/**
 * checkFunctions
 *
 * Send a list of functions that must exist. If the functions do not not exist, return a list
 * of missing functions. This will indicate to the user what is missing and needs to be fixed.
 * Otherwise, return an empty array. The empty array indicates all is good and the installation
 * may proceed.
 *
 * @param array $functionList The list of functions to check.
 * @return $results array Empty or a list of functions that are missing.
 **/
function checkFunctions($functionList = array()) {
	$results = array();
	if (empty($functionList)) {
		return $results;
	}
	foreach ($functionList as $function) {
		$function = strtolower($function);
		if (!function_exists($function)) {
			$results[] = $function;
		}
	}
	return $results;
}

/**
 * checkIni
 *
 * Check the ini setting for a given variable. We can check the following:
 * - range (min / max)
 * - bool (on/off)
 * - value (a specific value)
 *
 * @return void
 **/
function checkIni($iniSettings = array()) {
	$results = array();
	if (empty($iniSettings)) {
		return $results;
	}
	foreach ($iniSettings as $iniSetting => $range) {
		$setting = ini_get($iniSetting);
		// account for a range
		if ($range['min'] || $range['max']) {
			if (!$setting) {
				$results[] = 'The required ini setting for ' . $iniSetting . ' was not found!';
			} else {
				$size = fromReadableSize($setting);
				if ($range['min'] && fromReadableSize($range['min']) > $size) {
					$results[] = 'The script requires a minimum INI setting for ' . $iniSetting . ' of ' . $range['min'] . ' and it is set to ' . $setting;
				}
				if ($range['max'] && fromReadableSize($range['max']) < $size) {
					$results[] = 'The script requires a maximum INI setting for ' . $iniSetting . ' of ' . $range['min'] . ' and it is set to ' . $setting;
				}
			}
			continue;
		}

		// account for a boolean
		if ($range['bool']) {
			$range['bool'] = standarizeBoolean($range['bool']);
			$setting = standarizeBoolean($setting);
			if ($range['bool'] !== $setting) {
				$results[] = 'The script requires the INI setting for ' . $iniSetting . ' to be set to ' . $range['bool'] . ' and it is set to ' . $setting;
			}
			continue;
		}

		// account for an actual setting
		if ($range['value']) {
			if (!$setting) {
				$results[] = 'The required ini setting for ' . $iniSetting . ' was not found!';
			} else {
				if (strtolower($range['value']) !== strtolower($setting)) {
					$results[] = 'The script requires the INI setting for ' . $iniSetting . ' to be set to ' . $range['value'] . ' and it is set to ' . $setting;
				}
			}
			continue;
		}
	}
	return $results;
}

/**
 * standarizeBoolean
 *
 * This will standardize the boolean value to make sure we are comparing the values as expected. The
 * boolean will be checked and returned as a 1 or 0.
 *
 * @param string $value The boolean value to convert to a standard value.
 * @return string $value The updated value for the boolean.
 **/
function standarizeBoolean($value = null) {
	if ($value === null) {
		return null;
	}
	$value = strtolower($value);
	if ($value === true || $value === '1' || $value === 1 || $value === 'on' || $value === 'true' || $value === 'yes') {
		$value = 'On';
	}
	if ($value === false || $value === '0' || $value === 0 || $value === 'off' || $value === 'false' || $value === 'no' || $value === '') {
		$value = 'Off';
	}
	return $value;
}

/**
 * Converts filesize from human readable string to bytes
 *
 * @param string $size Size in human readable string like '5MB', '5M', '500B', '50kb' etc.
 * @param mixed $default Value to be returned when invalid size was used, for example 'Unknown type'
 * @return mixed Number of bytes as integer on success, `$default` on failure if not false
 */
function fromReadableSize($size, $default = false) {
	if (ctype_digit($size)) {
		return (int)$size;
	}
	$size = strtoupper($size);

	$l = -2;
	$i = array_search(substr($size, -2), array('KB', 'MB', 'GB', 'TB', 'PB'));
	if ($i === false) {
		$l = -1;
		$i = array_search(substr($size, -1), array('K', 'M', 'G', 'T', 'P'));
	}
	if ($i !== false) {
		$size = substr($size, 0, $l);
		return $size * pow(1024, $i + 1);
	}

	if (substr($size, -1) === 'B' && ctype_digit(substr($size, 0, -1))) {
		$size = substr($size, 0, -1);
		return (int)$size;
	}

	if ($default !== false) {
		return $default;
	}
}

/**
 * Returns a formatted-for-humans file size.
 *
 * @param integer $size Size in bytes
 * @return string Human readable size
 */
function toReadableSize($bytes, $decimals = 2) {
	$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

/**
 * checkExtensions
 *
 * Send a list of extensions that must be loaded. If the extensions are not loaded, return a list
 * of missing extensions. This will indicate to the user what is missing and needs to be fixed.
 * Otherwise, return an empty array. The empty array indicates all is good and the installation
 * may proceed.
 *
 * Some scripts expect one of many different extensions. This method will also handle the 'OR'
 * array allowing the developer to indicate a list of extensions to check. The method will check
 * each item in the 'OR' array. If one of them exists, it will pass.
 *
 * @param array $extensionList The list of extensions to check. Also accepts an 'OR' array.
 * @return $results array Empty or a list of extensions that are are not loaded.
 **/
function checkExtensions($extensionList = array()) {
	$results = array();
	if (empty($extensionList)) {
		return $results;
	}
	foreach ($extensionList as $extension) {
		if (is_array($extension)) {
			$found = false;
			foreach ($extension as $extensionCheck) {
				$extensionCheck = strtolower($extensionCheck);
				if (extension_loaded($extensionCheck)) {
					$found = true;
				}
			}
			if (!$found) {
				$results['OR'] = $extension;
			}
		} else {
			$extension = strtolower($extension);
			if (!extension_loaded($extension)) {
				$results[] = $extension;
			}
		}
	}
	return $results;
}

//Output and Cleanup
echo serialize($status);
@unlink(__FILE__);
