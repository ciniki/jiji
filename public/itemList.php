<?php
//
// Description
// -----------
// This method will return the list of Items for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Item for.
//
// Returns
// -------
//
function ciniki_jiji_itemList($ciniki) {
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
    $rc = ciniki_jiji_checkAccess($ciniki, $args['tnid'], 'ciniki.jiji.itemList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Get the list of items
    //
    $strsql = "SELECT ciniki_jiji_items.id, "
        . "ciniki_jiji_items.title, "
        . "ciniki_jiji_items.permalink, "
        . "ciniki_jiji_items.primary_image_id, "
        . "DATE_FORMAT(ciniki_jiji_items.listing_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS listing_date, "
        . "ciniki_jiji_items.synopsis, "
        . "ciniki_jiji_items.description "
        . "FROM ciniki_jiji_items "
        . "WHERE ciniki_jiji_items.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY listing_date DESC, title "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.jiji', array(
        array('container'=>'items', 'fname'=>'id', 
            'fields'=>array('id', 'title', 'permalink', 'primary_image_id', 'listing_date', 'synopsis', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['items']) ) {
        $items = $rc['items'];
    } else {
        $items = array();
    }

    return array('stat'=>'ok', 'items'=>$items);
}
?>
