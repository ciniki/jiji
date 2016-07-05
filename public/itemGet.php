<?php
//
// Description
// ===========
// This method will return all the information about an item.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the item is attached to.
// item_id:          The ID of the item to get the details for.
//
// Returns
// -------
//
function ciniki_jiji_itemGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
        'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'checkAccess');
    $rc = ciniki_jiji_checkAccess($ciniki, $args['business_id'], 'ciniki.jiji.itemGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $php_date_format = ciniki_users_dateFormat($ciniki, 'php');
    $mysql_date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Return default for new Item
    //
    if( $args['item_id'] == 0 ) {
        $dt = new DateTime('now', new DateTimeZone($intl_timezone));
        $item = array('id'=>0,
            'title'=>'',
            'permalink'=>'',
            'primary_image_id'=>'',
            'listing_date'=>$dt->format($php_date_format),
            'synopsis'=>'',
            'description'=>'',
        );
    }

    //
    // Get the details for an existing Item
    //
    else {
        $strsql = "SELECT ciniki_jiji_items.id, "
            . "ciniki_jiji_items.title, "
            . "ciniki_jiji_items.permalink, "
            . "ciniki_jiji_items.primary_image_id, "
            . "DATE_FORMAT(ciniki_jiji_items.listing_date, '" . ciniki_core_dbQuote($ciniki, $mysql_date_format) . "') AS listing_date, "
            . "ciniki_jiji_items.synopsis, "
            . "ciniki_jiji_items.description "
            . "FROM ciniki_jiji_items "
            . "WHERE ciniki_jiji_items.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_jiji_items.id = '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.jiji', 'item');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3563', 'msg'=>'Item not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['item']) ) {
            return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3564', 'msg'=>'Unable to find Item'));
        }
        $item = $rc['item'];

        //
        // Get the images
        //
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql = "SELECT id, "
                . "title, "
                . "flags, "
                . "image_id, "
                . "description "
                . "FROM ciniki_jiji_item_images "
                . "WHERE item_id = '" . ciniki_core_dbQuote($ciniki, $args['item_id']) . "' "
                . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . "";
            ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
            $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.jiji', array(
                array('container'=>'images', 'fname'=>'id', 'fields'=>array('id', 'title', 'flags', 'image_id', 'description')),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['images']) ) {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
                $item['images'] = $rc['images'];
                foreach($item['images'] as $img_id => $img) {
                    if( isset($img['image_id']) && $img['image_id'] > 0 ) {
                        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $img['image_id'], 75);
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $item['images'][$img_id]['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            } else {
                $item['images'] = array();
            }
        }
    }

    return array('stat'=>'ok', 'item'=>$item);
}
?>
