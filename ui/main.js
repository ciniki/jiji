//
// This app will handle the listing, additions and deletions of jiji.  These are associated business.
//
function ciniki_jiji_main() {
    //
    // jiji panel
    //
    this.menu = new M.panel('Items', 'ciniki_jiji_main', 'menu', 'mc', 'medium', 'sectioned', 'ciniki.jiji.main.menu');
    this.menu.sections = {
        'items':{'label':'Items', 'type':'simplegrid', 'num_cols':2,
            'headerValues':null,
            'noData':'No items',
            'addTxt':'Add an item',
            'addFn':'M.ciniki_jiji_main.item.open(\'M.ciniki_jiji_main.menu.open();\',0);',
            },
        };
    this.menu.sectionData = function(s) { return this.data[s]; }
    this.menu.noData = function(s) { return this.sections[s].noData; }
    this.menu.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.title;
            case 1: return d.listing_date;
        }
    };
    this.menu.rowFn = function(s, i, d) {
        return 'M.ciniki_jiji_main.item.open(\'M.ciniki_jiji_main.menu.open();\',\'' + d.id + '\');';
    };
    this.menu.open = function(cb, cat) {
        this.data = {};
        M.api.getJSONCb('ciniki.jiji.itemList', {'business_id':M.curBusinessID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_jiji_main.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    };
    this.menu.addButton('add', 'Add', 'M.ciniki_jiji_main.item.open(\'M.ciniki_jiji_main.menu.open();\',0);');
    this.menu.addClose('Back');

    //
    // The panel for an item
    //
    this.item = new M.panel('Item', 'ciniki_jiji_main', 'item', 'mc', 'medium mediumaside', 'sectioned', 'ciniki.jiji.main.item');
    this.item.data = null;
    this.item.item_id = 0;
    this.item.sections = { 
        '_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no',
                'addDropImage':function(iid) {
                    M.ciniki_jiji_main.item.setFieldValue('primary_image_id', iid, null, null);
                    return true;
                    },
                'addDropImageRefresh':'',
                'deleteImage':function(fid) {
                        M.ciniki_jiji_main.item.setFieldValue(fid, 0, null, null);
                        return true;
                    },
                },
            }},
        'general':{'label':'General', 'aside':'yes', 'fields':{
            'title':{'label':'Title', 'type':'text'},
            'listing_date':{'label':'Date', 'type':'text'},
            }},
        '_synopsis':{'label':'Synopsis', 'fields':{
            'synopsis':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'small', 'type':'textarea'},
            }},
        '_description':{'label':'Description', 'fields':{
            'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
            }},
        'images':{'label':'Gallery', 'type':'simplethumbs'},
        '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
            'addTxt':'Add Additional Image',
            'addFn':'M.ciniki_jiji_main.item.save("M.ciniki_jiji_main.itemimage.open(\'M.ciniki_jiji_main.item.refreshImages();\',0,M.ciniki_jiji_main.item.item_id);");',
            },
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_jiji_main.item.save();'},
            'delete':{'label':'Delete', 'visible':function() { return M.ciniki_jiji_main.item.item_id > 0 ? 'yes':'no';}, 
                'fn':'M.ciniki_jiji_main.item.remove();'},
            }},
        };  
    this.item.fieldValue = function(s, i, d) { return this.data[i]; }
    this.item.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.jiji.itemHistory', 'args':{'business_id':M.curBusinessID, 'item_id':this.item_id, 'field':i}};
    }
    this.item.thumbFn = function(s, i, d) {
        return 'M.ciniki_jiji_main.itemimage.open(\'M.ciniki_jiji_main.item.refreshImages();\',\'' + d.id + '\');';
    };
    this.item.refreshImages = function() {
        if( M.ciniki_jiji_main.item.item_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.jiji.itemGet', {'business_id':M.curBusinessID, 'item_id':this.item_id, 'images':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_jiji_main.item;
                p.data.images = rsp.item.images;
                p.refreshSection('images');
                p.show();
            });
        }
    }
    this.item.open = function(cb, iid) {
        this.reset();
        if( iid != null ) { this.item_id = iid; }
        this.reset();
        this.sections._buttons.buttons.delete.visible = 'yes';
        M.api.getJSONCb('ciniki.jiji.itemGet', {'business_id':M.curBusinessID, 'item_id':this.item_id, 'images':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_jiji_main.item;
            p.data = rsp.item;
            p.refresh();
            p.show(cb);
        });
    };
    this.item.save = function(cb) {
        if( cb == null ) { cb = 'M.ciniki_jiji_main.item.close();'; }
        if( this.item_id > 0 ) {
            var c = this.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.jiji.itemUpdate', {'business_id':M.curBusinessID, 'item_id':this.item_id}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    eval(cb);
                });
            } else {
                eval(cb);
            }
        } else {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.jiji.itemAdd', {'business_id':M.curBusinessID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                eval(cb);
            });
        }
    };
    this.item.remove = function() {
        if( confirm("Are you sure you want to remove this item?") ) {
            M.api.getJSONCb('ciniki.jiji.itemDelete', {'business_id':M.curBusinessID, 'item_id':this.item_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_jiji_main.item.close();
            });
        }
    }
    this.item.addDropImage = function(iid) {
        if( this.item_id == 0 ) {
            var c = this.serializeForm('yes');
            M.api.postJSONCb('ciniki.jiji.itemAdd', {'business_id':M.curBusinessID, 'item_id':this.item_id, 'image_id':iid}, c,
                function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_jiji_main.item.item_id = rsp.id;
                    M.ciniki_jiji_main.item.refreshImages();
                });
        } else {
            M.api.getJSONCb('ciniki.jiji.itemImageAdd', {'business_id':M.curBusinessID, 'image_id':iid, 'title':'', 'item_id':this.item_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_jiji_main.item.refreshImages();
            });
        }
        return true;
    };
    this.item.addButton('save', 'Save', 'M.ciniki_jiji_main.item.save();');
    this.item.addClose('Cancel');

    //
    // The panel to display the edit form
    //
    this.itemimage = new M.panel('Edit Image', 'ciniki_jiji_main', 'itemimage', 'mc', 'medium mediumaside', 'sectioned', 'ciniki.jiji.main.itemimage');
    this.itemimage.data = {};
    this.itemimage.itemimage_id = 0;
    this.itemimage.item_id = 0;
    this.itemimage.sections = {
        '_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
            'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        'info':{'label':'Information', 'type':'simpleform', 'fields':{
            'title':{'label':'Title', 'type':'text'},
            'flags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':{'1':{'name':'Visible'}}},
            }},
        '_description':{'label':'Description', 'type':'simpleform', 'fields':{
            'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_jiji_main.itemimage.save();'},
            'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_jiji_main.itemimage.remove();'},
            }},
    };
    this.itemimage.fieldValue = function(s, i, d) { 
        if( this.data[i] != null ) {
            return this.data[i]; 
        } 
        return ''; 
    };
    this.itemimage.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.jiji.itemImageHistory', 'args':{'business_id':M.curBusinessID, 'itemimage_id':this.itemimage_id, 'field':i}};
    };
    this.itemimage.addDropImage = function(iid) {
        M.ciniki_jiji_main.itemimage.setFieldValue('image_id', iid, null, null);
        return true;
    };
    this.itemimage.open = function(cb, iid, itid) {
        if( iid != null ) { this.itemimage_id = iid; }
        if( itid != null ) { this.item_id = itid; }
        if( this.itemimage_id > 0 ) {
            this.reset();
            this.sections._buttons.buttons.delete.visible = 'yes';
            M.api.getJSONCb('ciniki.jiji.itemImageGet', {'business_id':M.curBusinessID, 'itemimage_id':this.itemimage_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_jiji_main.itemimage;
                p.data = rsp.itemimage;
                p.refresh();
                p.show(cb);
            });
        } else {
            this.reset();
            this.sections._buttons.buttons.delete.visible = 'no';
            this.data = {'flags':1};
            this.refresh();
            this.show(cb);
        }
    };
    this.itemimage.save = function() {
        if( this.itemimage_id > 0 ) {
            var c = this.serializeFormData('no');
            if( c != '' ) {
                M.api.postJSONFormData('ciniki.jiji.itemImageUpdate', {'business_id':M.curBusinessID, 
                    'itemimage_id':this.itemimage_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_jiji_main.itemimage.close();
                        }
                    });
            } else {
                this.close();
            }
        } else {
            var c = this.serializeFormData('yes');
            M.api.postJSONFormData('ciniki.jiji.itemImageAdd', {'business_id':M.curBusinessID, 'item_id':this.item_id}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } 
                M.ciniki_jiji_main.itemimage.itemimage_id = rsp.id;
                M.ciniki_jiji_main.itemimage.close();
            });
        }
    };
    this.itemimage.remove = function() {
        if( confirm('Are you sure you want to delete this image?') ) {
            M.api.getJSONCb('ciniki.jiji.itemImageDelete', {'business_id':M.curBusinessID, 
                'itemimage_id':this.itemimage_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_jiji_main.itemimage.close();
                });
        }
    };
    this.itemimage.addButton('save', 'Save', 'M.ciniki_jiji_main.itemimage.save();');
    this.itemimage.addClose('Cancel');

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_jiji_main', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        this.menu.open(cb);
    }
};
