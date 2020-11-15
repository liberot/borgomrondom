class SurveyNet extends Controller {

     constructor(queue){
          super(queue);
          // events
          this.register(new Subscription(        'save::panel', this.savePanel));
          this.register(new Subscription(          'save::toc', this.saveToc));
          // controls 
          this.register(new Subscription(       'init::thread', this.initThread));
          this.register(new Subscription('download::fieldings', this.downloadFieldings));
          this.register(new Subscription(     'select::thread', this.loadThread));
          this.register(new Subscription(   'download::assets', this.downloadAssets));
          this.register(new Subscription(      'upload::asset', this.uploadAsset));
          this.register(new Subscription(        'load::panel', this.loadPanel));
     }

     downloadFieldings(){
          let ref = this;
          let data = { 'action': 'exec_get_initial_thread' }
          let cb = function(e){ ref.notify(new Message('fieldings::downloaded', { e })); }
          this.postData(data, cb);
     }

     loadPanel(msg){
          let ref = this;
          let data = {
               'action': 'exec_get_panel_by_ref',
               'section_id': msg.model.sectionId,
               'panel_ref': msg.model.panelRef
          }
          let cb = function(e){
               ref.notify(new Message('panel::loaded', { e }));
          }
          this.postData(data, cb);
     }

     uploadAsset(msg){
          let ref = this;
          let data = {
               'action': 'exec_init_asset_by_panel_ref',
               'thread_id': msg.model.threadId,
               'panel_id': msg.model.panelId,
               'panel_ref': msg.model.panelRef,
               'layout_code': msg.model.image.layoutCode,
               'image': msg.model.image.post_content
          }
          let cb = function(e){
               ref.notify(new Message('asset::uploaded', { e }));
          }
          this.postData(data, cb);
     }

     loadThread(msg){
          let ref = this;
          let data = {
               'action': 'exec_get_thread_by_id',
               'thread_id': msg.model.arguments[1]
          }
          let cb = function(e){
               ref.notify(new Message('thread::loaded', { e }));
          }
          this.postData(data, cb);
     }

     initThread(msg){
          let ref = this;
          let data = { 'action': 'exec_init_thread' }
          let cb = function(e){ ref.notify(new Message('thread::inited', { e })); }
          this.postData(data, cb);
     }

     savePanel(msg){
          let ref = this;
          let data = {
               action: 'exec_init_panel',
               panel_id: msg.model.panel.ID,
               panel: msg.model.panel.post_content,
               ref: msg.model.panel.post_content.ref,
               thread_id: msg.model.thread.ID
          }
          let cb = function(e){
               ref.notify(new Message('panel::saved', { e }));
          }
          this.postData(data, cb);
     }

     saveToc(msg){
          let ref = this;
          let data = {
               action: 'exec_save_toc',
               thread_id: msg.model.thread.ID,
               toc_id: msg.model.toc.ID,
               toc: msg.model.toc.post_content
          }
          let cb = function(e){
               ref.notify(new Message('toc::saved', { e }));
          }
          this.postData(data, cb);
     }

     postData(data, suc, err){
          let ref = this;
          jQuery('.survey-messages').html(__survey.__('wait'));
          jQuery.post(SurveyConfig.serviceURL, data, function(e){
               e = jQuery.parseJSON(e);
               switch(e.res){
                    case 'success':
                         jQuery('.survey-messages').html(e.message);
                         console.log(e);
                         null != suc ? suc(e) : false;
                         break;
                    case 'failed':
                    default:
                         jQuery('.survey-messages').html(e.message);
                         console.log(e);
                         null != err ? err() : false;
                         break;
               }
          });
     }

     downloadAssets(msg){
          let ref = this;
          let data = {
               action: 'exec_get_assets_by_panel_ref',
               thread_id: msg.model.thread.ID,
               panel_id: msg.model.panel.ID,
               panel_ref: msg.model.panel.post_excerpt
          }
          let cb = function(e){
               ref.notify(new Message('assets::downloaded', { e }));
          }
          this.postData(data, cb);
     }

     /*
     uploadAssets(msg){
          let ref = this;
          jQuery('.survey-messages').html(__survey.__('wait'));
          jQuery.post({
               url: SurveyConfig.serviceURL,
               data: msg.model.form,
               async: true,
               cache: false,
               contentType: false,
               processData: false,
               success: function(e){
                    e = jQuery.parseJSON(e);
                    console.log(e);
                    jQuery('.survey-messages').html(e.message);
                    ref.notify(new Message('assets::uploaded', { e }));
               },
               error: function(e){
                    console.log(e);
               }
          });
     }
     */
}
