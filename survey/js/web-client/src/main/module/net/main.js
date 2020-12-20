class SurveyNet extends Controller {

     constructor(queue){
          super(queue);
          // events
          this.register(new Subscription(         'save::panel', this.savePanel));
          this.register(new Subscription(           'save::toc', this.saveToc));
          this.register(new Subscription(        'save::thread', this.saveThread));
          // controls 
          this.register(new Subscription(        'init::thread', this.initThread));
          this.register(new Subscription(      'select::thread', this.loadThread));
          this.register(new Subscription(    'download::assets', this.downloadAssets));
          this.register(new Subscription(       'upload::asset', this.uploadAsset));
          this.register(new Subscription(         'load::panel', this.loadPanel));
          this.register(new Subscription(   'load::nextsection', this.loadNextSection));
     }

     loadNextSection(msg){
          let ref = this;
          let data = { 
               'action': 'exec_get_next_section'
          }
          let cb = function(e){ 
               ref.notify(new Message('nextsection::loaded', { e })); 
          }
          this.postData(data, cb);
     }

     loadPanel(msg){
          let ref = this;
          let data = {
               'action': 'exec_get_panel_by_ref',
               'thread_id': msg.model.thread.ID,
               'section_ref': msg.model.requestedSection,
               'panel_ref': msg.model.requestedPanel
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
               'section_id': msg.model.sectionId,
               'panel_ref': msg.model.panel_ref,
               'layout_code': msg.model.layoutCode,
               'base': msg.model.base
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
               thread_id: msg.model.thread.ID,
               section_id: msg.model.section.ID,
               panel_ref: msg.model.panel.post_content.ref,
               question: msg.model.panel.post_content.question,
               answer: msg.model.panel.post_content.answer
          }
          let cb = function(e){
               ref.notify(new Message('panel::saved', { e }));
          }
          this.postData(data, cb);
     }

     saveThread(msg){
          let ref = this;

// --------------------------------------------------------------------------------
// fixdiss :: history and book getz elephant size :: send less ysfck
          let book = JSON.stringify(msg.model.thread.post_content.book);
              book = SurveyUtil.pigpack(book);

          let history = JSON.stringify(msg.model.thread.post_content.history);
              history = SurveyUtil.pigpack(history);

          let conditions = JSON.stringify(msg.model.thread.post_content.conditions);
              conditions = SurveyUtil.pigpack(conditions);

          let hiddenFields = JSON.stringify(msg.model.thread.post_content.hidden_fields);
              hiddenFields = SurveyUtil.pigpack(hiddenFields);
// ---------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------

          let data = {
               action: 'exec_save_thread',
               thread_id: msg.model.thread.ID,
               book: book, 
               history: history,
               conditions: conditions,
               hidden_fields: hiddenFields
          }
          let cb = function(e){
               ref.notify(new Message('thread::saved', { e }));
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
                         console.log('postData(): success: ', e);
                         jQuery('.survey-messages').html(e.message);
                         null != suc ? suc(e) : false;
                         break;

                    case 'failed':
                    default:
                         console.log('postData(): failed: ', e);
                         jQuery('.survey-messages').html(e.message);
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
}
