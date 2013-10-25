<?php
/**
 * File containing the orphanedimageobjects/report module view.
 *
 * @copyright Copyright (C) 1999 - 2014 Brookins Consulting. All rights reserved.
 * @copyright Copyright (C) 2013 - 2014 Think Creative. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version 0.12.0
 * @package ezporphanedimageobjects
 */

/**
 * Default module parameters
 */
$module = $Params["Module"];

/**
* Default class instances
*/

// Parse HTTP POST variables
$http = eZHTTPTool::instance();

// Access system variables
$sys = eZSys::instance();

// Init template behaviors
$tpl = eZTemplate::factory();

// Access ini variables
$ini = eZINI::instance();
$iniOrphanedimageobjects = eZINI::instance( 'ezporphanedimageobjectsreport.ini' );

// Report file variables
$reportStoragePath = $iniOrphanedimageobjects->variable( 'SiteSettings', 'ReportStoragePath' );
$filename = 'ezpOrphanedImageObjectsReport.csv';
$file = $reportStoragePath . '/' . $filename;

/**
 * Handle download action
 */
if ( $http->hasPostVariable( 'Download' ) )
{
    if ( !eZFile::download( $file, true, $filename ) )
       $module->redirectTo( 'orphanedimageobjects/report' );
}

/**
 * Handle generate actions
 */
if ( $http->hasPostVariable( 'Generate' ) )
{
    $siteHostname = $iniOrphanedimageobjects->variable( 'SiteSettings', 'SiteHostname' );
    $nodeID = $iniOrphanedimageobjects->variable( 'SiteSettings', 'NodeID' );

    // General script options
    $phpBin = '/usr/bin/php -d memory_limit=-1 ';
    $generatorWorkerScript = 'extension/ezporphanedimageobjectsreport/bin/php/ezporphanedimageobjectsreport.php';
    $options = '--storage-dir=' . $reportStoragePath . ' --hostname=' . $siteHostname . ' --nodeid=' . $nodeID;
    $result = false;
    $output = false;
    // print_r( "$phpBin ./$generatorWorkerScript $options ");
    exec( "$phpBin ./$generatorWorkerScript $options;", $output, $result );
    sleep( 120 );
    print_r( $output );
}

/**
 * Test for generated report
 */
if ( file_exists( $file ) )
{
    $tpl->setVariable( 'fileModificationTimestamp', date("F d Y H:i:s", filemtime( $file ) ) );
    $tpl->setVariable( 'status', true );
}
else
{
    $tpl->setVariable( 'status', false );
}


/**
 * Default template include
 */
$Result = array();
$Result['content'] = $tpl->fetch( "design:orphanedimageobjects/report.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr('design/standard/orphanedimageobjects', 'Orphaned Image Objects') ),
                         array( 'url' => false,
                                'text' => ezpI18n::tr('design/standard/orphanedimageobjects', 'Report') )
                        );

$Result['left_menu'] = 'design:orphanedimageobjects/menu.tpl';

?>