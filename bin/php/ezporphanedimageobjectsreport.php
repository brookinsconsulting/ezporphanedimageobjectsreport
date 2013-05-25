#!/usr/bin/env php
<?php
/**
 * File containing the ezporphanedimageobjectsreport.php bin script
 *
 * @copyright Copyright (C) 1999 - 2014 Brookins Consulting. All rights reserved.
 * @copyright Copyright (C) 2013 - 2014 Think Creative. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2 (or later)
 * @version 0.10.9
 * @package ezporphanedimageobjectsreport
 */

require 'autoload.php';

/** Script startup and initialization **/

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "eZ Publish Orphaned Image Nodes CSV Report Script\n" .
                                                        "\n" .
                                                        "ezporphanedimageobjectsreport.php --storage-dir=export --hostname=www.example.com --nodeid=43" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true,
                                     'user' => true ) );

$script->startup();

$options = $script->getOptions( "[storage-dir:][hostname:][nodeid:]",
                                "[node]",
                                array( 'storage-dir' => 'Directory to place exported files in',
                                       'hostname' => 'Website hostname to match url searches for',
                                       'nodeid' => 'Content tree NodeID containing image nodes' ),
                                false,
                                array( 'user' => true ) );
$script->initialize();

/** Script default values **/

$openedFPs = array();

$orphanedCsvReportFileName = 'ezpOrphanedImageObjectsReport';

$csvHeader = array( 'NodeID', 'Estimate Node As Orphaned', 'Reverse Related Objects', 'Reverse Linked Object Count', 'Reverse Embedded Object Count', 'Linked Content Object Count', 'Url in content', 'Related Object Count', 'Embedded Content Object Count', 'Url', 'Name','Image','Image Caption','Text Line One','Text Line Two' );

$siteNodeUrlPrefix = "http://";

/** Test for required script arguments **/

if ( $options['nodeid'] )
{
    $nodeID = $options['nodeid'];
}
else
{
    $cli->error( 'NodeID is required. Specify a content treee node for the site report' );
    $script->shutdown( 1 );
}

if ( !is_numeric( $nodeID ) )
{
    $cli->error( 'Specify a numeric node ID' );
    $script->shutdown( 2 );
}

if ( $options['storage-dir'] )
{
    $storageDir = $options['storage-dir'];
}
else
{
    $storageDir = '';
}

if ( $options['hostname'] )
{
    $siteNodeUrlHostname = $options['hostname'];
}
else
{
    $cli->error( 'Hostname is required. Specify a website hostname for the site report url matching' );
    $script->shutdown( 2 );
}

/** Fetch starting node from content tree **/

$node = eZContentObjectTreeNode::fetch( $nodeID );

if ( !$node )
{
    $cli->error( "No node with ID: $nodeID" );
    $script->shutdown( 3 );
}

/** Fetch nodes under starting node in content tree **/

$subTree = $node->subTree( array( 'ClassFilterType' => 'include',
                                  'ClassFilterArray' => array( 'image' ) ) );
$subTreeCount = $node->subTreeCount( array( 'ClassFilterType' => 'include',
                                            'ClassFilterArray' => array( 'image' ) ) );

/** Alert user of report generation process starting **/

$cli->output( "Searching through content subtree from node $nodeID to find orphaned nodes ...\n" );

/** Setup script iteration details **/

$script->setIterationData( '.', '.' );
$script->resetIteration( $subTreeCount );

/** Open report file for writting **/

if ( !isset( $openedFPs[$orphanedCsvReportFileName] ) )
{
    $tempFP = @fopen( $storageDir . '/' . $orphanedCsvReportFileName . '.csv', "w" );

    if ( $tempFP )
    {
        $openedFPs[$orphanedCsvReportFileName] = $tempFP;
    }
    else
    {
        $cli->error( "Can not open output file for $storageDir/$orphanedCsvReportFileName file" );
        $script->shutdown( 4 );
    }
}
else
{
   if ( !$openedFPs[$orphanedCsvReportFileName] )
   {
        $cli->error( "Can not open output file for $storageDir/$orphanedCsvReportFileName file" );
        $script->shutdown( 4 );
   }
}

/** Define report file pointer **/

$fp = $openedFPs[$orphanedCsvReportFileName];

/** Write report csv header **/

if ( !fputcsv( $fp, $csvHeader, ';' ) )
{
    $cli->error( "Can not write to report file" );
    $script->shutdown( 6 );
}

/** Iterate over nodes **/

while ( list( $key, $childNode ) = each( $subTree ) )
{
    $objectData = array();
    $estimateObjectOrphaned = 0;
    $status = true;

    /** Fetch object details **/

    $object = $childNode->attribute( 'object' );
    $classIdentifier = $object->attribute( 'class_identifier' );

    /** Calculate orphaned object statistics **/

    $nodeReverseRelatedCount = $childNode->reverseRelatedCount( array( $childNode->attribute( 'node_id' ) ) );

    $embeddedContentObjectCount = $object->embeddedContentObjectCount( false );

    $reverseLinkedObjectCount = $object->reverseLinkedObjectCount( false );

    $reverseEmbeddedObjectCount = $object->reverseEmbeddedObjectCount( false );

    $linkedContentObjectCount = $object->linkedContentObjectCount( false );

    $relatedObjectCount = $object->relatedObjectCount();

    $childNodeID = $childNode->attribute('node_id');

    $nodeUrl = $childNode->attribute('url');

    $nodeFullUrl = $siteNodeUrlPrefix . $siteNodeUrlHostname . '/' . $childNode->attribute('url');

    $actualSiteNodeUrl = $siteNodeUrlPrefix . $siteNodeUrlHostname . '/' . $childNode->attribute('url');

    $urlInContent = eZURL::fetchByUrl( $actualSiteNodeUrl );

    if( $urlInContent != false )
    {
        $urlInContentID = 1;
    }
    else
    {
        $urlInContentID = 0;
    }

    /** Calculate orphaned object status **/

    if( $nodeReverseRelatedCount == 0 && $reverseLinkedObjectCount == 0 && $reverseEmbeddedObjectCount == 0 && $urlInContentID == 0 )
    {
        $estimateObjectOrphaned = 1;
    }

    /** Build report for objects of class image **/

    if( $classIdentifier == 'image' )
    {
        $objectData[] = $childNodeID;

        $objectData[] = $estimateObjectOrphaned;

        $objectData[] = $nodeReverseRelatedCount;

        $objectData[] = $reverseLinkedObjectCount;

        $objectData[] = $reverseEmbeddedObjectCount;

        $objectData[] = $linkedContentObjectCount;

        $objectData[] = $urlInContentID;

        $objectData[] = $relatedObjectCount;

        $objectData[] = $embeddedContentObjectCount;

        $objectData[] = $nodeFullUrl;

        /** Iterate over node content object attributes **/

        foreach ( $object->attribute( 'contentobject_attributes' ) as $attribute )
        {
            if( $attribute->contentClassAttributeName() != 'Caption' )
            {
                $attributeStringContent = $attribute->toString();

               if ( $attributeStringContent != '' )
               {
                   switch ( $datatypeString = $attribute->attribute( 'data_type_string' ) )
                   {
                       case 'ezimage':
                       {
                           $imagePathParts = explode( '/', $attributeStringContent );
                           $imageFile = array_pop( $imagePathParts );
                           $attributeStringContent = @explode( '|', $imageFile);
                           $objectData[] = $attributeStringContent[0];
                           $objectData[] = $attributeStringContent[1];
                       } break;

                       case 'ezbinaryfile':
                       case 'ezmedia':
                       {
                           $binaryData = explode( '|', $attributeStringContent );
                           $attributeStringContent = $binaryData[1];
                           $objectData[] = $attributeStringContent;
                       } break;

                       default:
                           $objectData[] = $attributeStringContent;
                   }
               }
            }
        }

        /** Test if report file is opened **/

        if ( !$fp )
        {
            $cli->error( "Can not open output file" );
            $script->shutdown( 5 );
        }

        /** Write report datat to file **/

        if ( !fputcsv( $fp, $objectData, ';' ) )
        {
            $cli->error( "Can not write to file" );
            $script->shutdown( 6 );
        }
    }

    $script->iterate( $cli, $status );
}

/** Close report file **/

while ( $fp = each( $openedFPs ) )
{
    fclose( $fp['value'] );
}

/** Shutdown script **/

$script->shutdown();

?>