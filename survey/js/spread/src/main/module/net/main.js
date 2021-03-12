let Net = function(controller){

     this.controller = controller;

     this.fillTemplate = function(template, model){
         return this.controller.fillTemplate(template, model);
     }

     this.register = function(subscription){
          this.controller.register(subscription);
     }

     this.notify = function(message){
          this.controller.notify(message);
     }

     this.loadCurrentDocument = function(msg){
          let ref = this;
          let data = {
               action: 'bb_load_current_document'
          }
          let cb = function(e){
               ref.notify(new Message('currentdoc::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     this.importLayouts = function(msg){
          let ref = this;
          let data = {
               action: 'bb_import_layouts'
          }
          let cb = function(e){
               ref.notify(new Message('layouts::imported', e.coll));
          }
          this.postData(data, cb);
     }

     this.loadLayouts = function(msg){
          let ref = this;
          let data = {
               action: 'bb_get_layouts_by_group_and_code',
               group_id: msg.model.groupId,
               code: msg.model.rule 
          }
          let cb = function(e){
               ref.notify(new Message('layoutpresets::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     this.loadLayoutGroup = function(msg){
          let ref = this;
          let data = {
               action: 'bb_get_layouts_by_group',
               group: 'default' 
          }
          let cb = function(e){
               ref.notify(new Message('layoutgroup::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     this.saveLayout = function(msg){
          let ref = this;
          let data = {
               action: 'bb_save_layout',
               doc: msg.model.doc,
               group: msg.model.group,
               rule: msg.model.rule,
               tags: [
                    'this', 'is', 'a', 'test', 'and', 'therefore', 'of', 'no', 'sense'
               ]
          }
          this.postData(data);
     }

     this.initBook = function(msg){
         let ref = this;
         let data = {
              'action': 'exec_init_book_by_thread_id',
              'thread_id': msg.model.arguments[1].threadId
         }
         let cb = function(e){
              ref.notify(new Message('book::loaded', e.coll));
         }
         this.postData(data, cb);
     }

     this.loadBook = function(msg){
          let ref = this;
          let data = {
               action: 'exec_get_book_by_id',
               book_id: msg.model.arguments[1]
          }
          let cb = function(e){
               ref.notify(new Message('book::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     this.exportPrint = function(msg){
          let ref = this;
          for(let idx in msg.model.prints){
               let data = {
                    action: 'exec_export_prints',
                    ppi: msg.model.ppi,
                    width: msg.model.width,
                    height: msg.model.height,
                    svg: encodeURI(msg.model.prints[idx])
               };
               let cb = function(e){
                    jQuery('.layout-messages').html(e.coll.pdf);
                    let message = {
                         model: {
                              pdf: e.coll.pdf,
                              ppi: msg.model.ppi 
                         }
                    }
                    ref.exportSeps(message);
               }
               this.postData(data, cb);
          }
     }

     this.exportSeps = function(msg){
          let ref = this;
          let data = {
               'action': 'exec_export_separations',
               'input_pdf': msg.model.pdf,
               'ppi': msg.model.ppi
          };
          let cb = function(e){
          }
          this.postData(data, cb);
     }

     this.loadLayoutGroups = function(){
          let ref = this;
          let data = {
               'action': 'bb_get_layoutgroups',
          };
          let cb = function(e){
               ref.notify(new Message('layoutgroups::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     this.postData = function(data, suc, err){
          let ref = this;
          jQuery('.layout-messages').html('wait...');
          jQuery.post(SpreadViewerConfig.serviceURL, data, function(e){
               e = jQuery.parseJSON(e);
               switch(e.res){
                    case 'success':
                         jQuery('.layout-messages').html(e.message);
console.log('postData(): success e: ', e);
                         null != suc ? suc(e) : false;
                         break;
                    case 'failed':
                    default:
                         jQuery('.layout-messages').html(e.message);
console.log('postData(): error e: ', e);
                         null != err ? err() : false;
                         break;
               }
          });
     }

     this.uploadImage = function(msg){
          let ref = this;
          jQuery('.layout-messages').html('wait...');
          jQuery.post({
               type: 'post',
               url: SpreadViewerConfig.serviceURL,
               data: msg.model.form,
               async: true,
               cache: false,
               contentType: false,
               processData: false,
               success: function(e){
                    e = jQuery.parseJSON(e);
                    for(let idx in e.coll){
                         let asset = {
                              "indx": "indx:" +parseInt(Math.random() *1000),
                              "type": "image",
                              "src": "data:image/png;base64,"+e.coll[idx].data,
                              "conf": {
                                   "unit": "px",
                                   "xpos": "0",
                                   "ypos": "0",
                                   "opacity": "1.0",
                                   "width": parseInt(e.coll[idx].info['0']),
                                   "height": parseInt(e.coll[idx].info['1'])
                              }
                         }
                         ref.notify(new Message('image::loaded', asset));
                    }
                    jQuery('.layout-messages').html(e.message);
               },
               error: function(e){
console.log('postData(): error e: ', e);
               }
          });
     }

     this.initLoads = function(msg){

          switch(SpreadViewerConfig.mode){

               case SpreadViewerConfig.WEB_CLIENT:
                    this.loadCurrentDocument();
                    break;

               case SpreadViewerConfig.ADMIN_CLIENT:
                    this.loadLayoutGroups();
                    break;
          }
     }
     
     // controls
     this.register(new Subscription(            'init::book', 'initBook', this));
     this.register(new Subscription(          'save::layout', 'saveLayout', this));
     this.register(new Subscription(     'load::layoutgroup', 'loadLayoutGroup', this));
     this.register(new Subscription(   'load::layoutpresets', 'loadLayouts', this));
     this.register(new Subscription(       'import::layouts', 'importLayouts', this));
     // events
     this.register(new Subscription(      'prints::gathered', 'exportPrint', this));
     this.register(new Subscription(  'delmockbtn::released', 'deleteMockData', this));
     this.register(new Subscription('writemockbtn::released', 'writeMockSurvey', this));
     this.register(new Subscription(       'image::selected', 'uploadImage', this));
     this.register(new Subscription( 'spread-viewer::inited', 'initLoads', this));

}
