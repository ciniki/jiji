<?php
//
// Description
// -----------
// This method will return the list of Item Images for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:        The ID of the business to get Item Image for.
//
// Returns
// -------
//
function ciniki_jiji_itemImageList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to business_id as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'checkAccess');
    $rc = ciniki_jiji_checkAccess($ciniki, $args['business_id'], 'ciniki.jiji.itemImageList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of itemimages
    //
    $strsql = "SELECT ciniki_jiji_item_images.id, "
        . "ciniki_jiji_item_images.item_id, "
        . "ciniki_jiji_item_images.title, "
        . "ciniki_jiji_item_images.permalink, "
        . "ciniki_jiji_item_images.flags, "
        . "ciniki_jiji_item_images.image_id, "
        . "ciniki_jiji_item_images.description "
        . "FROM ciniki_jiji_item_images "
        . "WHERE ciniki_jiji_item_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.jiji', array(
        array('container'=>'itemimages', 'fname'=>'id', 
            'fields'=>array('id', 'item_id', 'title', 'permalink', 'flags', 'image_id', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['itemimages']) ) {
        $itemimages = $rc['itemimages'];
    } else {
        $itemimages = array();
    }

    return array('stat'=>'ok', 'itemimages'=>$itemimages);
}
?>
