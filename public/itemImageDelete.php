<?php
//
// Description
// -----------
// This method will delete an item image.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:            The ID of the tenant the item image is attached to.
// itemimage_id:            The ID of the item image to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_jiji_itemImageDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'itemimage_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Item Image'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'checkAccess');
    $rc = ciniki_jiji_checkAccess($ciniki, $args['tnid'], 'ciniki.jiji.itemImageDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the current settings for the item image
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_jiji_item_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['itemimage_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.jiji', 'itemimage');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['itemimage']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.jiji.11', 'msg'=>'Item Image does not exist.'));
    }
    $itemimage = $rc['itemimage'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.jiji');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Remove the itemimage
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.jiji.itemimage',
        $args['itemimage_id'], $itemimage['uuid'], 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.jiji');
        return $rc;
    }

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.jiji');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'jiji');

    return array('stat'=>'ok');
}
?>
