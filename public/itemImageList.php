<?php
//
// Description
// -----------
// This method will return the list of Item Images for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Item Image for.
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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'checkAccess');
    $rc = ciniki_jiji_checkAccess($ciniki, $args['tnid'], 'ciniki.jiji.itemImageList');
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
        . "WHERE ciniki_jiji_item_images.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
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
