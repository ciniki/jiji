<?php
//
// Description
// -----------
// This function will process a web request for the jiji module.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get jiji for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_jiji_web_processRequest(&$ciniki, $settings, $business_id, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['business']['modules']['ciniki.jiji']) ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'3571', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }
    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'blocks'=>array(),
        'submenu'=>array(),
        );

    //
    // Setup titles
    //
    if( isset($settings['page-jiji-name']) && $settings['page-jiji-name'] !='' ) {
        $module_title = $settings['page-jiji-name'];
    } elseif( isset($args['page_title']) ) {
        $module_title = $args['page_title'];
    } else {
        $module_title = 'Buy/Sell';
    }
    $page['breadcrumbs'][] = array('name'=>$module_title, 'url'=>$args['base_url']);

    $ciniki['response']['head']['og']['url'] = $args['domain_base_url'];

    //
    // Setup the base url as the base url for this page. This may be altered below
    // as the uri_split is processed, but we do not want to alter the original passed in.
    //
    $base_url = $args['base_url'];

    //
    // Parse the url to determine what was requested
    //
    $display = 'list';
    $item_permalink = '';

    //
    // Check if we are to display a category
    //
    if( isset($args['uri_split'][0]) && $args['uri_split'][0] != '' ) {
        $display = 'item';
        $item_permalink = $args['uri_split'][0];
        if( isset($args['uri_split'][1]) && $args['uri_split'][1] == 'gallery' 
            && isset($args['uri_split'][2]) && $args['uri_split'][2] != '' ) {
            $image_permalink = $args['uri_split'][2];
            $display = 'itempic';
        }
    }

    //
    // Display the item or the image for an item
    //
    if( $display == 'item' || $display == 'itempic' ) {
        //
        // Load the item
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'jiji', 'private', 'itemLoad');
        $rc = ciniki_jiji_itemLoad($ciniki, $business_id, $item_permalink, 'visible');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $item = $rc['item'];
        $base_url .= '/' . $item_permalink;
        $page['breadcrumbs'][] = array('name'=>$item['title'], 'url'=>$args['base_url'] . '/' . $item_permalink);

        //
        // Setup sharing information
        //
        if( isset($event['synopsis']) && $event['synopsis'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($event['synopsis']);
        } elseif( isset($event['description']) && $event['description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($event['description']);
        }

        //
        // Reset page title to be the event name
        //
        $page['title'] .= ($page['title']!=''?' - ':'') . $item['title'];

        //
        // Setup the blocks to display the item gallery image
        //
        if( $display == 'itempic' ) {
            if( !isset($item['images']) || count($item['images']) < 1 ) {
                $page['blocks'][] = array('type'=>'message', 'section'=>'jiji-image', 'content'=>"I'm sorry, but we can't seem to find the image you requested.");
            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'galleryFindNextPrev');
                $rc = ciniki_web_galleryFindNextPrev($ciniki, $item['images'], $image_permalink);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( $rc['img'] == NULL ) {
                    $page['blocks'][] = array('type'=>'message', 'section'=>'jiji-image', 'content'=>"I'm sorry, but we can't seem to find the image you requested.");
                } else {
                    $page['breadcrumbs'][] = array('name'=>$rc['img']['title'], 'url'=>$base_url . '/gallery/' . $image_permalink);
                    if( $rc['img']['title'] != '' ) {
                        $page['title'] .= ' - ' . $rc['img']['title'];
                    }
                    $block = array('type'=>'galleryimage', 'section'=>'jiji-image', 'primary'=>'yes', 'image'=>$rc['img']);
                    if( $rc['prev'] != null ) {
                        $block['prev'] = array('url'=>$base_url . '/gallery/' . $rc['prev']['permalink'], 'image_id'=>$rc['prev']['image_id']);
                    }
                    if( $rc['next'] != null ) {
                        $block['next'] = array('url'=>$base_url . '/gallery/' . $rc['next']['permalink'], 'image_id'=>$rc['next']['image_id']);
                    }
                    $page['blocks'][] = $block;
                }
            }
        } 

        //
        // Setup the blocks to display the item
        //
        else {
            //
            // Add primary image
            //
            if( isset($item['primary_image_id']) && $item['primary_image_id'] > 0 ) {
                $page['blocks'][] = array('type'=>'asideimage', 'section'=>'primary-image', 'primary'=>'yes', 'image_id'=>$item['primary_image_id'], 'title'=>$item['title'], 'caption'=>'');
            }

            //
            // Add description
            //
            $content = '';
            if( isset($item['description']) && $item['description'] != '' ) {
                $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'', 'content'=>$item['description']);
            } elseif( isset($item['synopsis']) ) {
                $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'', 'content'=>$item['synopsis']);
            }

            //
            // Add prices, links, files, etc to the page blocks
            //
            if( !isset($settings['page-jiji-share-buttons']) || $settings['page-jiji-share-buttons'] == 'yes' ) {
                $tags = array();
                $page['blocks'][] = array('type'=>'sharebuttons', 'section'=>'share', 'pagetitle'=>$item['title'], 'tags'=>$tags);
            }
            if( isset($item['images']) && count($item['images']) > 0 ) {
                $page['blocks'][] = array('type'=>'gallery', 'section'=>'gallery', 'title'=>'Additional Images', 'base_url'=>$base_url . '/gallery', 'images'=>$item['images']);
            }
        }
    }

    elseif( $display == 'list' ) {
        $display_format = 'imagelist';
        if( isset($settings['page-jiji-display-format']) && $settings['page-jiji-display-format'] == 'cilist' ) {
            $display_format = 'cilist';
        }

        //
        // Get the jiji
        //
        $strsql = "SELECT id, title, permalink, primary_image_id AS image_id, listing_date, synopsis, 'yes' AS is_details "
            . "FROM ciniki_jiji_items "
            . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
            . "ORDER BY listing_date DESC "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.jiji', 'item');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['rows']) || count($rc['rows']) == 0 ) {
            $page['blocks'][] = array('type'=>'message', 'section'=>'jiji-list', 'content'=>"Currently no listings");
        } else {
            if( $display_format == 'imagelist' ) {
                $page['blocks'][] = array('type'=>'imagelist', 'section'=>'jiji-list', 'noimage'=>'yes', 'title'=>'', 'base_url'=>$base_url, 'list'=>$rc['rows']);
            } else {
                $page['blocks'][] = array('type'=>'cilist', 'section'=>'jiji-list', 'title'=>'', 'base_url'=>$base_url, 'categories'=>$rc['rows']);
            }
        }
    }

    return array('stat'=>'ok', 'page'=>$page);
}
?>
