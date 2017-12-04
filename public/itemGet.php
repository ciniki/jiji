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
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'item_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
        'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'checkAccess');
    $rc = ciniki_jiji_checkAccess($ciniki, $args['tnid'], 'ciniki.jiji.itemGet');
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
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Item
    //
    if( $args['item_id'] == 0 ) {
        $dt = new DateTime('now', new DateTimeZone($intl_timezone));
        $item = array('id'=>0,
            'title'=>'',
            'permalink'=>'',
            'primary_image_id'=>'',
            'listing_date'=>$dt->format($date_format),
            'synopsis'=>'',
            'description'=>'',
        );
    }

    //
    // Get the details for an existing Item
    //
    else {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'itemLoad');
        $rc = ciniki_jiji_itemLoad($ciniki, $args['tnid'], $args['item_id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $item = $rc['item'];

        //
        // Get the images
        //
        if( isset($args['images']) && $args['images'] == 'yes' && isset($item['images']) ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'hooks', 'loadThumbnail');
            foreach($item['images'] as $img_id => $img) {
                if( isset($img['image_id']) && $img['image_id'] > 0 ) {
                    $rc = ciniki_images_hooks_loadThumbnail($ciniki, $args['tnid'], array('image_id'=>$img['image_id'], 'maxlength'=>75));
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $item['images'][$img_id]['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                }
            }
        }
    }

    return array('stat'=>'ok', 'item'=>$item);
}
?>
