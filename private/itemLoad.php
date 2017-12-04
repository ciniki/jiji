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
// tnid:         The ID of the tenant the item is attached to.
// item_id:             The ID of the item to get the details for.
//
// Returns
// -------
//
function ciniki_jiji_itemLoad($ciniki, $tnid, $item_id, $images='all') {
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    //
    // Get the details for an existing Item
    //
    $strsql = "SELECT ciniki_jiji_items.id, "
        . "ciniki_jiji_items.title, "
        . "ciniki_jiji_items.permalink, "
        . "ciniki_jiji_items.primary_image_id, "
        . "DATE_FORMAT(ciniki_jiji_items.listing_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS listing_date, "
        . "ciniki_jiji_items.synopsis, "
        . "ciniki_jiji_items.description "
        . "FROM ciniki_jiji_items "
        . "WHERE ciniki_jiji_items.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( !is_numeric($item_id) ) {
        $strsql .= "AND ciniki_jiji_items.permalink = '" . ciniki_core_dbQuote($ciniki, $item_id) . "' ";
    } else {
        $strsql .= "AND ciniki_jiji_items.id = '" . ciniki_core_dbQuote($ciniki, $item_id) . "' ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.jiji', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.jiji.5', 'msg'=>'Item not found', 'err'=>$rc['err']));
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.jiji.6', 'msg'=>'Unable to find Item'));
    }
    $item = $rc['item'];

    //
    // Get the images
    //
    if( $images != '' ) {
        $strsql = "SELECT id, title, permalink, flags, image_id, description "
            . "FROM ciniki_jiji_item_images "
            . "WHERE item_id = '" . ciniki_core_dbQuote($ciniki, $item['id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        if( $images == 'visible' ) {
            $strsql .= "AND (flags&0x01) = 0x01 ";
        }
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.jiji', array(
            array('container'=>'images', 'fname'=>'id', 'fields'=>array('id', 'title', 'permalink', 'flags', 'image_id', 'description')),
        ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['images']) ) {
            $item['images'] = $rc['images'];
        } else {
            $item['images'] = array();
        }
    }

    return array('stat'=>'ok', 'item'=>$item);
}
?>
