<?php 
/*
Copyright 2010 Google Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

require_once("ui.inc");
require_once("utils.inc");

$gTitle = "Downloads";

function listFiles($hFiles) {
	$sHtml = "";
	$aKeys = array_keys($hFiles);
	sort($aKeys, SORT_NUMERIC);
	foreach( array_reverse($aKeys) as $epoch ) {
		$sHtml .= "  <li> " . date("M j, Y", $epoch) . ": ";
		$hFilenames = $hFiles[$epoch];
		if ( array_key_exists('desktop', $hFilenames) ) {
			$filename = $hFilenames['desktop'];
			$filesize = filesize($filename);
			$size = ( $filesize > 1024*1024 ? round($filesize/(1024*1024)) . " MB" : round($filesize/(1024)) . " kB" );
			$sHtml .= "<a href='$filename'>IE</a> ($size)";
		}
		if ( array_key_exists('mobile', $hFilenames) ) {
			$filename = $hFilenames['mobile'];
			$filesize = filesize($filename);
			$size = ( $filesize > 1024*1024 ? round($filesize/(1024*1024)) . " MB" : round($filesize/(1024)) . " kB" );
			$sHtml .= ( array_key_exists('desktop', $hFilenames) ? ", " : "" ) . "<a href='$filename'>iPhone</a> ($size)\n";
		}
	}

	return $sHtml;
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $gTitle ?></title>
<meta charset="UTF-8">

<?php echo headfirst() ?>
<link type="text/css" rel="stylesheet" href="style.css" />
</head>

<body>

<?php echo uiHeader($gTitle); ?>
<h1>Downloads</h1>

<?php
// hash of files
//   - the key is epoch time from the filename (eg "Oct 15 2011")
//   - the value is a hash with desktop: and mobile: keys
$hFiles = array();
foreach ( glob("downloads/httparchive_*.gz") as $filename ) {
	$epoch = dumpfileEpochTime($filename);
	if ( $epoch ) {
		if ( ! array_key_exists($epoch, $hFiles) ) {
			$hFiles[$epoch] = array();
		}
		$hFiles[$epoch][ FALSE === strpos($filename, "httparchive_mobile_") ? 'desktop' : 'mobile'] = $filename;
	}
}
?>

<style>
.indent LI { margin-bottom: 2px; }
</style>

<p>
All of the downloads are gzipped mysqldumps.
</p>

<p style="margin-bottom: 1em;">
There's a download containing the schema for the tables referenced in the data dumps:
</p>
<ul class=indent>
  <li> <a href="downloads/httparchive_schema.sql">schema</a>
</ul>


<p style="margin-bottom: 1em;">
There's a download containing the aggregated stats for <em>all</em> runs:
</p>
<ul class=indent>
  <li> <a href="downloads/httparchive_stats.gz">stats</a>
</ul>

<p style="margin-bottom: 1em;">
There's a download file for each run for desktop ("IE") and mobile ("iPhone"):
</p>
<ul class=indent>
<?php echo listFiles($hFiles) ?>
</ul>

<p>
The downloaded file was generated by <a href="http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html">mysqldump</a> and then gzipped.
The mysqldump file does <em>not</em> contain the commands to create the MySQL database and tables.
To restore these mysqldumps:
</p>
  <li> Import the <a href="downloads/httparchive_schema.sql">schema</a> dump to create the tables.
  <li> Ungzip the downloaded mysqldump data file.
  <li> Import the mysqldump file using this command:<br><code>mysql -v -u MYSQL_USERNAME -pMYSQL_PASSWORD -h MYSQL_HOSTNAME MYSQL_DB < MYSQLDUMP_FILE</code>
</ol>

<?php echo uiFooter() ?>

</body>

</html>

