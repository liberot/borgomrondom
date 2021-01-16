let SurveyNet  = function(controller){

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

     this.loadNextSection = function(msg){
          let ref = this;
          let data = { 
               'action': 'exec_get_next_section'
          }
          let cb = function(e){ 
               ref.notify(new Message('nextsection::loaded', { e: e })); 
          }
          this.postData(data, cb);
     }

     this.loadSection = function(msg){
          let ref = this;
          let data = { 
               'action': 'exec_get_section_by_survey_id',
               'survey_id': msg.model.surveyId 
          }
          let cb = function(e){ 
               ref.notify(new Message('section::loaded', { e: e })); 
          }
          this.postData(data, cb);
     }

     this.loadPanel = function(msg){
          let ref = this;
          let data = {
               'action': 'exec_get_panel_by_ref',
               'thread_id': msg.model.threadId,
               'section_id': msg.model.sectionId,
               'panel_ref': msg.model.panelRef
          }
          let cb = function(e){
               ref.notify(new Message('panel::loaded', {e: e}));
          }
          this.postData(data, cb);
     }

     this.uploadAsset = function(msg){

          let ref = this;

          let data = {

               'action': 'exec_init_asset_by_panel_ref',
               'section_id': msg.model.sectionId,
               'panel_ref': msg.model.panelRef,
               'group_ref': msg.model.groupRef,
               'layout_code': msg.model.layoutCode,
               'base': msg.model.base
          }

          let cb = function(e){
               ref.notify(new Message('asset::uploaded', { e: e }));
          }
          this.postData(data, cb);
     }

     this.loadThread = function(msg){
          let ref = this;
          let data = {
               'action': 'exec_get_thread_by_id',
               'thread_id': msg.model.arguments[1]
          }
          let cb = function(e){
               ref.notify(new Message('thread::loaded', { e: e }));
          }
          this.postData(data, cb);
     }

     this.initThread = function(msg){
          let ref = this;
          let data = { 'action': 'exec_init_thread' }
          let cb = function(e){ ref.notify(new Message('thread::inited', { e: e })); }
          this.postData(data, cb);
     }

     this.savePanel = function(msg){
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
               ref.notify(new Message('panel::saved', { e: e }));
          }
          this.postData(data, cb);
     }

     this.saveThread = function(msg){

          let ref = this;

// --------------------------------------------------------------------------------
// fixdiss :: history and book getz elephant size :: send less ysfck
// there is records of the history and the book on the server

          let book = JSON.stringify(msg.model.thread.post_content.book);
// console.log('saveThread(): book: ', book);
              book = SurveyUtil.pigpack(book);

          let history = JSON.stringify(msg.model.thread.post_content.history);
// console.log('saveThread(): history: ', history);
              history = SurveyUtil.pigpack(history);

          let conditions = JSON.stringify(msg.model.thread.post_content.conditions);
// console.log('saveThread(): conditions: ', conditions);
              conditions = SurveyUtil.pigpack(conditions);

          let hiddenFields = JSON.stringify(msg.model.thread.post_content.hidden_fields);
// console.log('saveThread(): hiddenFields: ', hiddenFields);
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
               ref.notify(new Message('thread::saved', { e: e }));
          }

          this.postData(data, cb);
     }

     this.postData = function(data, suc, err){

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

     this.downloadAssets = function(msg){
          let ref = this;
          let data = {
               action: 'exec_get_assets_by_panel_ref',
               thread_id: msg.model.thread.ID,
               panel_id: msg.model.panel.ID,
               panel_ref: msg.model.panel.post_excerpt
          }
          let cb = function(e){
               ref.notify(new Message('assets::downloaded', { e: e }));
          }
          this.postData(data, cb);
     }

     // events
     this.register(new Subscription(         'save::panel', 'savePanel',       this));
     this.register(new Subscription(           'save::toc', 'saveToc',         this));
     this.register(new Subscription(        'save::thread', 'saveThread',      this));
     // controls
     this.register(new Subscription(        'init::thread', 'initThread',      this));
     this.register(new Subscription(      'select::thread', 'loadThread',      this));
     this.register(new Subscription(    'download::assets', 'downloadAssets',  this));
     this.register(new Subscription(       'upload::asset', 'uploadAsset',     this));
     this.register(new Subscription(         'load::panel', 'loadPanel',       this));
     this.register(new Subscription(   'load::nextsection', 'loadNextSection', this));
     this.register(new Subscription(       'load::section', 'loadSection',     this));
}
