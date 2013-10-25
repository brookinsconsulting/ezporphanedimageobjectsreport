<?php
/**
 * File containing the generatereport.php cronjob.
 *
 * @copyright Copyright (C) 1999 - 2014 Brookins Consulting. All rights reserved.
 * @copyright Copyright (C) 2013 - 2014 Think Creative. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version 0.12.0
 * @package ezporphanedimageobjectsreport
 */

$ini = eZINI::instance( 'ezporphanedimageobjectsreport.ini' );
$siteHostname = $ini->variable( 'SiteSettings', 'SiteHostname' );
$reportStoragePath = $ini->variable( 'SiteSettings', 'ReportStoragePath' );
$nodeID = $ini->variable( 'SiteSettings', 'NodeID' );

// General cronjob part options
$phpBin = '/usr/bin/php -d memory_limit=-1 ';
$generatorWorkerScript = 'extension/ezporphanedimageobjectsreport/bin/php/ezporphanedimageobjectsreport.php';
$options = '--storage-dir=' . $reportStoragePath . ' --hostname=' . $siteHostname . ' --nodeid=' . $nodeID;
$result = false;

passthru( "$phpBin ./$generatorWorkerScript $options;", $result );

print_r( $result ); echo "\n";

?>