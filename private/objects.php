<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_jiji_objects($ciniki) {
    
    $objects = array();
    $objects['item'] = array(
        'name'=>'Item',
        'o_name'=>'item',
        'o_container'=>'items',
        'sync'=>'yes',
        'table'=>'ciniki_jiji_items',
        'fields'=>array(
            'title'=>array('name'=>'Title'),
            'permalink'=>array('name'=>'Permalink'),
            'primary_image_id'=>array('name'=>'Primary Image', 'ref'=>'ciniki.images.image'),
            'listing_date'=>array('name'=>'Listing Date'),
            'synopsis'=>array('name'=>'Synopsis', 'default'=>''),
            'description'=>array('name'=>'Description', 'default'=>''),
            ),
        'history_table'=>'ciniki_jiji_history',
        );
    $objects['itemimage'] = array(
        'name'=>'Item Image',
        'o_name'=>'itemimage',
        'o_container'=>'itemimages',
        'sync'=>'yes',
        'table'=>'ciniki_jiji_item_images',
        'fields'=>array(
            'item_id'=>array('name'=>'Item', 'ref'=>'ciniki.jiji.item'),
            'title'=>array('name'=>'Title'),
            'permalink'=>array('name'=>'Permalink'),
            'flags'=>array('name'=>'Options', 'default'=>0),
            'image_id'=>array('name'=>'Image', 'ref'=>'ciniki.images.image'),
            'description'=>array('name'=>'Description', 'default'=>''),
            ),
        'history_table'=>'ciniki_jiji_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
