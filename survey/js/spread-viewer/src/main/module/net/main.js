class Net extends Controller {

     constructor(queue){
          super(queue);
          // controls (wirks) (from the queue)
          this.register(new Subscription(            'init::book', this.initBook));
          this.register(new Subscription(          'save::layout', this.saveLayout));
          this.register(new Subscription(     'load::layoutgroup', this.loadLayoutGroup));
          this.register(new Subscription(   'load::layoutpresets', this.loadLayouts));
          this.register(new Subscription(       'import::layouts', this.importLayouts));
          // events
          this.register(new Subscription(      'prints::gathered', this.exportPrint));
          this.register(new Subscription(  'delmockbtn::released', this.deleteMockData));
          this.register(new Subscription('writemockbtn::released', this.writeMockSurvey));
          this.register(new Subscription(       'image::selected', this.uploadImage));
     }

     importLayouts(msg){
          let ref = this;
          let data = {
               action: 'exec_import_layouts'
          }
          let cb = function(e){
               ref.notify(new Message('layouts::imported', e.coll));
          }
          this.postData(data, cb);
     }

     loadLayouts(msg){
          let ref = this;
          let data = {
               action: 'exec_get_layout_by_group_and_rule',
               group: msg.model.group,
               rule: msg.model.rule 
          }
          let cb = function(e){
               ref.notify(new Message('layoutpresets::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     loadLayoutGroup(msg){
          let ref = this;
          let data = {
               action: 'exec_get_layouts_by_group',
               group: 'default' 
          }
          let cb = function(e){
               ref.notify(new Message('layoutgroup::loaded', e.coll));
          }
          this.postData(data, cb);
     }

     saveLayout(msg){
          let ref = this;
          let data = {
               action: 'exec_init_layout',
               doc: msg.model.doc,
               group: msg.model.group,
               rule: msg.model.rule,
               tags: [
                    'this', 'is', 'a', 'test', 'and', 'therefore', 'of', 'no', 'sense'
               ]
          }
          this.postData(data);
     }

     initBook(msg){
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

     loadBook(msg){
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

     exportPrint(msg){
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

     exportSeps(msg){
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

     postData(data, suc, err){
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

     uploadImage(msg){
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
}
