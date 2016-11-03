<?php
//
// Description
// ===========
// This method will return all the information about an item image.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business the item image is attached to.
// itemimage_id:          The ID of the item image to get the details for.
//
// Returns
// -------
//
function ciniki_jiji_itemImageGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        'itemimage_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item Image'),
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
    $rc = ciniki_jiji_checkAccess($ciniki, $args['business_id'], 'ciniki.jiji.itemImageGet');
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
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    //
    // Return default for new Item Image
    //
    if( $args['itemimage_id'] == 0 ) {
        $itemimage = array('id'=>0,
            'item_id'=>'',
            'title'=>'',
            'permalink'=>'',
            'flags'=>'0',
            'image_id'=>'',
            'description'=>'',
        );
    }

    //
    // Get the details for an existing Item Image
    //
    else {
        $strsql = "SELECT ciniki_jiji_item_images.id, "
            . "ciniki_jiji_item_images.item_id, "
            . "ciniki_jiji_item_images.title, "
            . "ciniki_jiji_item_images.permalink, "
            . "ciniki_jiji_item_images.flags, "
            . "ciniki_jiji_item_images.image_id, "
            . "ciniki_jiji_item_images.description "
            . "FROM ciniki_jiji_item_images "
            . "WHERE ciniki_jiji_item_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_jiji_item_images.id = '" . ciniki_core_dbQuote($ciniki, $args['itemimage_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.jiji', 'itemimage');
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.jiji.12', 'msg'=>'Item Image not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['itemimage']) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.jiji.13', 'msg'=>'Unable to find Item Image'));
        }
        $itemimage = $rc['itemimage'];
    }

    return array('stat'=>'ok', 'itemimage'=>$itemimage);
}
?>
