<?php
//
// Description
// -----------
// This function will return the list of options for the module that can be set for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get events for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_jiji_hooks_webOptions(&$ciniki, $business_id, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['business']['modules']['ciniki.jiji']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'3572', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'business_id', $business_id, 'ciniki.web', 'settings', 'page-jiji');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }


    $options = array();
    $options[] = array(
        'label'=>'Page Title',
        'setting'=>'page-jiji-name', 
        'type'=>'text',
        'value'=>(isset($settings['page-jiji-name'])?$settings['page-jiji-name']:'cilist'),
        );

    $pages['ciniki.jiji'] = array('name'=>'Buy/Sell', 'options'=>$options);

    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
