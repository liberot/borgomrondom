class Survey extends Controller {

     constructor(queue) {
          super(queue);
          let ref = this;
          this.model = new SurveyModel();
          jQuery('.survey-messages').html(this.fillTemplate(__srv__msg__001__tmpl, {msg: __survey.__('welcome')}));
          // controls
          this.register(new Subscription(        'parse::assets', this.parseAssets));
          this.register(new Subscription(        'select::yesno', this.bindYesNoInput));
          this.register(new Subscription(         'confirm::ref', this.bindMultipleInput));
          this.register(new Subscription(       'confirm::image', this.bindMultipleInput));
          this.register(new Subscription(       'confirm::input', this.bindTextInput));
          this.register(new Subscription('fieldings::downloaded', this.bindFieldings));
          this.register(new Subscription(    'select::statement', this.bindSelectStatement));
          // events
          this.register(new Subscription(         'thread::next', this.nextPanel));
          this.register(new Subscription(         'thread::prev', this.evalPrevPanel));
          this.register(new Subscription(       'thread::loaded', this.bindThread));
          this.register(new Subscription(       'thread::inited', this.bindThread));
          this.register(new Subscription(     'assets::uploaded', this.bindAssets));
          this.register(new Subscription(   'assets::downloaded', this.bindAssets));
          this.register(new Subscription(        'spreads::init', this.initSpreads));
          this.register(new Subscription(       'asset::scanned', this.bindScan));
          this.register(new Subscription(          'scans::done', this.uploadAssets));
          this.register(new Subscription(          'scans::done', this.renderAssetCopies));
          this.register(new Subscription(        'assets::bound', this.renderAssetCopies));
          this.register(new Subscription(        'panel::loaded', this.bindPanel));
          this.register(new Subscription(          'input::done', this.storeInput));
          // ------
          this.extractDeeplink(window.location.hash.substr(1));
          this.navDeeplink(window.location.hash.substr(1));
          window.addEventListener('hashchange', function(e){ ref.bindHashChange(e);});
          // ------
          this.notify(new Message('download::fieldings', this.model));
     }

     storeInput(msg){
          if(false == this.model.clientAuthed){

          }
          this.notify(new Message('save::toc', this.model));
          this.notify(new Message('save::panel', this.model));
     }

     bindSelectStatement(){
          this.evalNextPanel();
     }

     bindFieldings(msg){
          if(null == msg.model.e.coll.thread){
               console.log('no thread');
               return false;
          }
          if(null == msg.model.e.coll.toc){
               console.log('no toc');
               return false;
          }
          let thread = msg.model.e.coll.thread;
          let panels = msg.model.e.coll.panels;
          let sections = msg.model.e.coll.sections;
          let toc = msg.model.e.coll.toc;
          let m = { model: { e: { coll: { thread: thread, toc: toc, panels: panels, sections: sections }}}}
          this.bindThread(m);
     }

     clear(){
          this.model.thread = null;
          this.model.questions = null;
          jQuery('.survey-questions1st').html('');
          jQuery('.survey-controls1st').html('');
          jQuery('.survey-controls2nd').html('');
          jQuery('.survey-controls3rd').html('');
          jQuery('.survey-controls4th').html('');
          jQuery('.survey-assets').html('');
     }

     bindHashChange(e){
          this.extractDeeplink(window.location.hash.substr(1));
          if(this.model.rPanelNav){
          }
     }

     extractDeeplink(lnk){
          let ref = this;
          let tmp = lnk.split('/');
          if(tmp.length <= 1){
              return;
          }
          if(tmp.length >= 4){
               let id = this.getLinkVal('panel', tmp[3]);
               if(id != this.model.rPanel){
                   this.model.rPanel = id;
                   this.model.rPanelNav = true;
               }
          }
          if(tmp.length >= 3){
               let id = this.getLinkVal('section', tmp[2]);
               if(id != this.model.rThread){
                   this.model.rThread = id;
                   this.model.rThreadNav = true;
               }
          }
          if(tmp.length >= 2){
               let id = this.getLinkVal('thread', tmp[1]);
               if(id != this.model.rSurvey){
                   this.model.rSurvey = id;
                   this.model.rSurveyNav = true;
               }
          }
     }

     getLinkVal(target, chunk){
          let res = null;
          let tmp = chunk.split(':');
          if(2 == tmp.length){
               if(target == tmp[0]){
                    res = tmp[1];
               }
          }
          return res;
     }

     navDeeplink(lnk){
          let ref = this;
          if(this.model.rSurveyNav){
               if(this.model.rThreadNav){
                    if(this.model.rPanelNav){
                         this.model.navToPanelAction = function(){
                              ref.selectPanel(ref.model.rPanel);
                              ref.model.rPanelNav = false;
                         }
                    }
                    this.model.navToThreadAction = function(){
                         let model = { 'arguments': [ '', ref.model.rThread ] }
                         ref.notify(new Message('select::thread', model));
                         ref.model.rThreadNav = false;
                    }
               }
               let model = { 'arguments': [ '', this.model.rSurvey ] }
               this.model.rSurveyNav = false;
          }
     }

     setLink(){
          let ref = this;
          let chunk = '';
          let lnk = window.location.href.substr(1);
          if(lnk.match(/\/+$/)){
               chunk = chunk.replace(/^\//, '');
          }
          if(null != this.model.thread){
               chunk+= '/thread:'+this.model.thread.ID;
               if(null != this.model.section){
                    chunk+= '/section:'+this.model.section.ID;
               }
          }
          window.location.hash = chunk;
     }

     bindThread(msg){

          if(null == msg.model.e.coll.thread[0]){
               console.log('no thread');
               return false;
          }

          if(null == msg.model.e.coll.toc[0]){
               console.log('no toc');
               return false;
          }

          if(null == msg.model.e.coll.sections){
               console.log('no sections');
               return false;
          }

          this.model.section = msg.model.e.coll.sections[0];
          this.model.section.post_content = SurveyUtil.pagpick(this.model.section.post_content);

          this.model.thread = msg.model.e.coll.thread[0];

          this.model.toc = msg.model.e.coll.toc[0];
          this.model.toc.post_content = SurveyUtil.pagpick(this.model.toc.post_content);

          if(null == this.model.toc.post_content.booktoc){
               this.model.toc.post_content.booktoc = [];
          }
          if(null == this.model.toc.post_content.history){
               this.model.toc.post_content.history = [];
          }

          this.model.threadLog.setColl(this.model.toc.post_content.coll);

          if(null == this.model.toc.post_content.initstep) { this.model.toc.post_content.initstep = 0; }
          if(null == this.model.toc.post_content.tocstep) { this.model.toc.post_content.tocstep = 0; }
          if(null == this.model.toc.post_content.navstep) { this.model.toc.post_content.navstep = 0; }
          this.model.toc.post_content.tocstep = parseInt(this.model.toc.post_content.tocstep);
          this.model.toc.post_content.initstep = parseInt(this.model.toc.post_content.initstep);
          this.model.toc.post_content.navstep = parseInt(this.model.toc.post_content.navstep);

          let link = this.model.toc.post_content.init_refs[0];
          if(SurveyConfig.LINEAR_HISTORY == SurveyConfig.navigationHistory){
               if(null != this.model.toc.post_content.history[this.model.toc.post_content.navstep -1]){
                     link = this.model.toc.post_content.history[this.model.toc.post_content.navstep -1];
               }
          }
          else {
               target = this.model.toc.post_content.booktoc;
               if(null != this.model.toc.post_content.booktoc[this.model.toc.post_content.tocstep -1]){
                    link = this.model.toc.post_content.booktoc[this.model.toc.post_content.tocstep -1];
               }
          }

          this.model.panels = [];
          if(SurveyConfig.preloadPanels){
               for(let idx in msg.model.e.coll.panels){
                    this.model.panels[msg.model.e.coll.panels[idx].post_excerpt] = msg.model.e.coll.panels[idx];
                    this.model.panels[msg.model.e.coll.panels[idx].post_excerpt].post_content = SurveyUtil.pagpick(msg.model.e.coll.panels[idx].post_content);
               }
          }

          this.loadPanel(link);

          this.setLink();
          if(null != this.model.navToPanelAction){
               this.model.navToPanelAction();
               this.model.navToPanelAction = null;
          }
     }

     bindTextInput(msg){
          let panel = this.model.panel.post_content.ref;
          let ref = msg.model.arguments[1];
          let val = jQuery('.answer-input input').val();
          this.bindInput(panel, ref, val);
     }

     bindImageInput(msg){
     }

     bindMultipleInput(msg){
          let panel = this.model.panel.post_content.ref;
          let ref = msg.model.arguments[1];
          let val = null;
          let choice = null;
          for(let idx in this.model.panel.post_content.properties.choices){
               choice = this.model.panel.post_content.properties.choices[idx]; 
               if(ref == choice.ref){
                   val = choice.label;
               }
          };
          this.bindInput(panel, ref, val);
     }

     bindYesNoInput(msg){
          let panel = this.model.panel.post_content.ref;
          let ref = msg.model.arguments[1];
          let val = msg.model.arguments[2] == "true" ? true : false;
          this.bindInput(panel, ref, val);
     }

     bindInput(panel, ref, val){
          let question = this.corrQuestion(this.model.panel.post_content.title);
          this.model.panel.post_content.question = SurveyUtil.trimIncomingString(question);
          this.model.panel.post_content.answer = val;
          this.model.panel.post_content.answer = SurveyUtil.trimIncomingString(val);
          this.model.threadLog.add(ref, val);
          this.notify(new Message('input::done', this.model));
          this.evalNextPanel();
     }

     corrQuestion(question){
          let refmtch = question.match(/{{(.{1,128}?)}}/g);
          for(let idx in refmtch){
               let key = refmtch[idx]; 
                   key = key.replace(/[{}]/g, '');
                   key = key.split(':');
                   key = key[1];
               let val = this.model.threadLog.get(key);
               if(null == val){ 
                   val = 'Could not find ref: ' +key;
                   val = '';
               }
               question = question.replace(refmtch[idx], val);
          }
          question = question.replace(/_/gi, '');
          question = question.replace(/\*/gi, '');
          return question;
     }

     corrToc(ref){
          let pos = this.model.toc.post_content.booktoc.indexOf(ref);
          if(-1 != pos){ this.model.toc.post_content.tocstep = pos; }
     }

     corrStep(){
          let ref = this.model.panel.post_content.ref;
          let pos = this.model.toc.post_content.init_refs.indexOf(ref);
          if(-1 != pos){ this.model.toc.post_content.initstep = pos; }
     }

     pushToc(){
          this.model.toc.post_content.booktoc[this.model.toc.post_content.tocstep] = this.model.panel.post_content.ref;
          this.model.toc.post_content.history[this.model.toc.post_content.navstep] = this.model.panel.post_content.ref;
          this.model.toc.post_content.tocstep = this.model.toc.post_content.tocstep;
          this.model.toc.post_content.initstep = this.model.toc.post_content.initstep;
          this.model.toc.post_content.coll = this.model.threadLog.getColl();
          this.model.toc.post_content.tocstep++;
          this.model.toc.post_content.navstep++;
     }

     pullToc(){
          this.model.toc.post_content.tocstep--;
          this.model.toc.post_content.tocstep--;
          this.model.toc.post_content.navstep--;
          this.model.toc.post_content.navstep--;
          if(this.model.toc.post_content.tocstep <= 0){ this.model.toc.post_content.tocstep = 0; }
          let target = this.model.toc.post_content.booktoc;
          let res = null;
          if(null != target[this.model.toc.post_content.tocstep]){
               res = target[this.model.toc.post_content.tocstep];
          }
          if(SurveyConfig.LINEAR_HISTORY == SurveyConfig.navigationHistory){
               target = this.model.toc.post_content.history;
               if(null != target[this.model.toc.post_content.navstep]){
                    res = target[this.model.toc.post_content.navstep];
               }
          }
          return res;
     }

     loadPanel(ref){
          if(null != this.model.panels[ref]){
               this.model.panel = this.model.panels[ref];
               this.initPanel();
               return;
          }
          this.model.panelRef = ref;
          this.notify(new Message('load::panel', this.model));
     }

     bindPanel(msg){
          if(null == msg.model.e.coll[0]){
               return false;
          }
          this.model.panel = msg.model.e.coll[0];
          this.model.panel.post_content = SurveyUtil.pagpick(this.model.panel.post_content);
          this.initPanel();
     }

     initPanel(){
          let ref = this;

          if(null == this.model.panel){
               console.log('no panel ', this.model.thread.ID);
               return;
          }

          this.model.maxImageAssets = 1;

          let buf1st = '';
          let buf2nd = '';
          let buf3rd = '';

          let question = this.model.panel.post_content.title;
              question = SurveyUtil.trimIncomingString(question);
              question = this.corrQuestion(question);

          let answer = this.model.panel.post_content.answer;
              answer = SurveyUtil.trimIncomingString(answer);

          if(null == answer || undefined == answer){
              answer = '';
          }

          jQuery('.survey-controls2nd').html('');
          jQuery('.survey-controls3rd').html('');
          jQuery('.survey-assets').html('');

          let target;
          switch(this.model.panel.post_content.type){

               case 'short_text':
                   buf1st = this.fillTemplate(__short_text_tmpl__, { question: question, answer: answer });
                   buf2nd = this.fillTemplate(__ctrl__tmpl__003__, { 
                        msg: __survey.__('done'), 
                        ref: this.model.panel.post_content.ref
                   });
                   break;

               case 'file_upload':
                   buf1st = this.fillTemplate(__file_upload_tmpl__, { question: question });
                   buf3rd = this.fillTemplate(__ctrl__tmpl__003__, { msg: __survey.__('done') });
                   if(null == this.model.panel.assetCopies){
                        this.model.panel.assetCopies = [];
                        this.notify(new Message('download::assets', this.model ));
                   }
                   this.renderAssetCopies();
                   this.renderFileupload();
                   break;

               case 'multiple_choice':
                   buf1st = this.fillTemplate(__multiple_choice_tmpl__, { question: question });
                   target = this.model.panel.post_content.properties.choices;
                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        buf2nd+= this.fillTemplate(__choice_tmpl__, { 
                             choice: choice, ref: target[idx].ref 
                        });
                   }
                   break;

               case 'picture_choice':
                   buf1st = this.fillTemplate(__multiple_choice_tmpl__, { question: question, answer: answer });
                   target = this.model.panel.post_content.properties.choices;
                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        let src = target[idx].attachment.href;
                        buf+= this.fillTemplate(__picture_choice_tmpl__, { choice: choice, src: src, ref: target[idx].ref } );
                   }
                   break;

               case 'yes_no':
                   buf1st = this.fillTemplate(__yes_no_tmpl__, { 
                        question: question, 
                        yes: __survey.__('yes', 'nosuch'), no: __survey.__('no', 'nosuch'),
                        ref: this.model.panel.post_content.ref
                   });
                   break;

               case 'group':
                   buf1sr = this.fillTemplate(__group_tmpl__, { question: question } );
                   buf2nd = this.fillTemplate(__ctrl__tmpl__003__, { msg: __survey.__('done') } );
                   break;

               case 'statement':
                   buf1st = this.fillTemplate(__statement_tmpl__, {
                        question: this.model.panel.post_content.title,
                        button: this.model.panel.post_content.properties.button_text,
                        ref: this.model.panel.post_content.ref,
                   })
                   break;

               default:
                   buf1st = 'Unknown type: ' +this.model.panel.post_content.type;
          }

          jQuery('.survey-questions1st').html(buf1st);
          jQuery('.survey-controls1st').html(__ctrl__tmpl__002__);
          jQuery('.survey-controls2nd').html(buf2nd);
          jQuery('.survey-controls3rd').html(buf3rd);
          jQuery('.survey-controls4th').html(this.fillTemplate(__ctrl__tmpl__102__,{init:__survey.__('spreads')})); 

          this.setLink();

          this.pushToc(this.model.panel.post_content.ref);
     }

     renderFileupload(){
          let ref = this;
          if(null == this.model.panel.post_content.conf){
               return;
          }

          let files = null;
          let slots = parseInt(this.model.panel.post_content.conf.image);
          let buf = jQuery('.survey-controls2nd').html(__upload__tmpl__002__);
          let form = document.querySelector('.fileupload');
          let fake = document.querySelector('.fake');

          fake.addEventListener('drop',      function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragleave', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragenter', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragover',  function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragend',   function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragstart', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragenter', function(e){ fake.classList.add('drag'); });
          fake.addEventListener('dragover',  function(e){ fake.classList.add('drag'); });
          fake.addEventListener('drag',      function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('drop',      function(e){ fake.classList.remove('drag'); });
          fake.addEventListener('dragend',   function(e){ fake.classList.remove('drag'); });
          fake.addEventListener('dragleave', function(e){ fake.classList.remove('drag'); });
          fake.addEventListener('mouseup',   function(e){ form.click(); });

          fake.addEventListener('drop', function(e){ 
               document.querySelector('.fileupload').files = e.dataTransfer.files;
               let data = ref.initImageUpload(e.dataTransfer.files);
               ref.notify(new Message('parse::assets', { form: data, panel: ref.model.panel }));
          });

          form.addEventListener('change', function(e){
              let data = ref.initImageUpload(form.files); 
              ref.notify(new Message('parse::assets', { form: data, panel: ref.model.panel }));
          });
     }

     initImageUpload(files){
          let formdata = new FormData();
          let max = 10;
          for(let idx in files){
               if(idx >= 10){ continue; }
               if(idx >= this.model.maxImageAssets){ continue; }
               formdata.append('action', 'exec_image_upload');
               formdata.append('panelId', this.model.panel.ID);
               formdata.append('thradId', this.model.thread.ID);
               formdata.append('panelRef', this.model.thread.post_excerpt);
               formdata.append('image_'+idx, files[idx]);
          }
          return formdata;
     }

     evalCondition(condition){
          // condition = new MockLogic().logic.condition;
          let res = null;
              res = this.evalRule(condition);
              res = this.evalGroup(condition);
              res = condition.result;
console.log(condition);
          return res;
     }

     evalGroup(condition){
          switch(condition.op){
               case 'is':
               case 'and':
               case 'answered':
                    for(let idx in condition.vars){
                         let val = true == condition.vars[idx].result ? 1 : 0;
                         if(null == condition.result){ condition.result = val; }
                         else{ condition.result &= val; }
                    }
                    break;
               case 'or':
                    condition.result = null;
                    for(let idx in condition.vars){
                         let val = true == condition.vars[idx].result ? 1 : 0;
                         if(null == condition.result){ condition.result = val; }
                         else{ condition.result |= val; };
                    }
                    break;
               case 'always':
                    condition.result = true;
                    break;
          }
          condition.result = 1 == condition.result ? true : false;
          if(null != condition.vars){
               for(let idx in condition.vars){
                    if(null != condition.vars[idx].op){
                         this.evalGroup(condition.vars[idx]);
                    }
               }
               return;
          }
     }

     evalRule(rule){
          if(null == rule.vars){ return false; }
          for(let idx in rule.vars){
               if(null != rule.vars[idx].op){ 
                    this.evalRule(rule.vars[idx]);
                    continue;
               }
               rule.vars[idx].result = false;
               let panel = this.model.panel;
               if(null != this.model.panels[rule.vars[idx].value]){
                  panel = this.model.panels[rule.vars[idx].value]
                  rule.vars[idx].result = true;
               }
               switch(rule.vars[idx].type){
                    case 'choice':
                    case 'constant':
                         rule.vars[idx].result = panel.post_content.answer == this.model.threadLog.get(rule.vars[idx].value);
                         break;
               }
          }
     }

     evalPrevPanel(){
          let link = this.pullToc();
          console.log('prev link from toc: ', link);
          if(null != link){
               this.corrToc(link);
               this.loadPanel(link);
               return true;
          }
          this.corrStep();
          this.prevPanel();
     }

     evalNextPanel(){
          let ref = this;
          let links = [];
          let target = this.model.panel.post_content.ref;
console.log(target);
          for(let idx in this.model.toc.post_content.rulez){
               let rule = this.model.toc.post_content.rulez[idx];
               if(target != rule.ref){ continue; }
console.log(rule);
               rule.actions.forEach(function(actionpack){
console.log(actionpack);
                    if(false != ref.evalCondition(actionpack.condition)){
                         switch(actionpack.action){
                              case 'jump':
console.log('link from logic:', actionpack.details.to.value);
console.log(actionpack);
                                   links.push(actionpack.details.to.value);
                                   break;
                         }
                    }
               });
          }
          let link = links[0];
          if(null != link){
               this.corrToc(link);
               this.loadPanel(link);
               return true;
          }
          this.corrStep();
          this.nextPanel();
     }

     nextPanel(){
          this.model.toc.post_content.initstep++;
          if(this.model.toc.post_content.initstep 
               >= this.model.toc.post_content.init_refs.length){ 
                    this.model.toc.post_content.initstep = this.model.toc.post_content.init_refs.length -1 
          }
          let ref = this.model.toc.post_content.init_refs[this.model.toc.post_content.initstep];
console.log('next link from default: ', ref);
          this.loadPanel(ref);
     }

     prevPanel(){
          this.model.toc.post_content.initstep--;
          if(this.model.toc.post_content.initstep <= 0){ this.model.toc.post_content.initstep = 0 }
          let ref = this.model.toc.post_content.init_refs[this.model.toc.post_content.initstep];
console.log('prev link from default: ', ref);
          this.loadPanel(ref);
     }

     selectPanel(step){
          this.model.toc.post_content.initstep = parseInt(step);
          if(this.model.toc.post_content.initstep <= 0){ this.model.toc.post_content.initstep = 0 }
          if(this.model.toc.post_content.initstep 
               >= this.model.toc.init_refs.length.length){ 
                   this.model.toc.post_content.initstep = this.model.toc.init_refs.length -1 
          }
          let ref = this.model.toc.post_content.init_refs[this.model.toc.post_content.initstep];
          this.loadPanel(ref);
     }

     initSpreads(msg){
          if('undefined' == typeof(layoutQueue)){ return false; }
          layoutQueue.route('init::book', { threadId: this.model.thread.ID });
     }

     parseAssets(msg){
          let ref = this;
          this.model.parseProc = [];
          this.model.panel.assetCopies = [];
          let files = document.querySelector('.fileupload').files;
          let buf = '';
          for(let idx = 0; idx < files.length; idx++){
               if(idx >= this.model.maxImageAssets){ return; }
               let file = document.querySelector('.fileupload').files[idx];
               if(null == file){ continue; }
               let indx = 'image_'+idx;
               this.model.parseProc.push({ indx: indx, proc: idx, state: 0x00 });
               let r = new FileReader();
                   r.onload = function(e){
                        ref.scanAsset(indx, e.target.result, idx, true);
                   }
                   r.onerror = function(e){
                        console.log(e);
                   }; 
               r.readAsDataURL(file);
          }
     }

     scanAsset(indx, base, proc, upload){
         let ref = this;
         let scaleR = 1;
         let img = new Image();
             img.onload = function(){
                   let canvas = document.createElement('canvas');
                       canvas.width = this.naturalWidth *scaleR;
                       canvas.height = this.naturalHeight *scaleR;
                   let ctx = canvas.getContext("2d");
                       ctx.drawImage(this, 0, 0, this.naturalWidth *scaleR, this.naturalHeight *scaleR);
                   let base = canvas.toDataURL('image/png');
                   let layoutCode = 'L';
                   if(this.naturalHeight >= this.naturalWidth){
                       layoutCode = 'P';
                   }
                   let res = {
                       indx: indx,
                       proc: proc,
                       post_content: base,
                       layoutCode: layoutCode,
                       ow: this.naturalWidth,
                       oh: this.naturalHeight,
                       upload: upload
                   }
                   ref.notify(new Message('asset::scanned', res));
             }
             base = base.replace('data:image/png;base64,', '');
             base = 'data:image/png;base64,' +base;
             img.src = base;
     }

     bindScan(msg){
          this.model.panel.assetCopies.push(msg.model);
          let done = false;
          let cnt = 0;
          for(let idx in this.model.parseProc){
               if(this.model.parseProc[idx].proc == msg.model.proc){
                    this.model.parseProc[idx].state = 0x01;
               }
               if(0x01 == this.model.parseProc[idx].state){
                    cnt++;
               }
          }
          if(cnt >= this.model.parseProc.length){ done = true; }
          if(done){
               this.notify(new Message('scans::done'));
          }
     }

     bindAssets(msg){
          if(null == this.model.panel){ return false; }
          this.model.panel.assetCopies = msg.model.e.coll;
          this.model.panel.assetCopies.sort(function(asset){ return asset.post_excerpt > asset.post_excerpt; });
          this.notify(new Message('assets::bound'));
     }

     uploadAssets(msg){
console.log('will not upload until client is authed');
console.log(this.model.clientAuthed);
          for(let idx in this.model.panel.assetCopies){
               if(true != this.model.panel.assetCopies[idx].upload){ continue; }
               let model = {
                    sectionId: this.model.section.ID,
                    panelRef: this.model.panel.post_excerpt,
                    image: this.model.panel.assetCopies[idx],
                    panel: this.model.panel.post_content
               }
               this.notify(new Message('upload::asset', model));
          }
     }

     renderAssetCopies(){
          if(null == this.model.panel.assetCopies){ return; }
          let buf = '';
          for(let idx in this.model.panel.assetCopies){
               let indx = this.model.panel.assetCopies[idx].indx;
               let d = this.model.panel.assetCopies[idx].post_content;
                   d = d.replace('data:image/png;base64,', '');
                   d = 'data:image/png;base64,' +d;
               buf+= this.fillTemplate(__src__img__011__tmpl, { indx: indx, data: d });
          }
          jQuery('.survey-assets').html(buf);
     }
}

let __upload__tmpl__002__ = `
<form>
     <input type='file' class='fileupload' name='filename' multiple='multiple'></inpupt>
     <div class='fake'>Drop Files Here</div>
</form>
`;

let __ctrl__tmpl__003__ = `
<a href='javascript:surveyQueue.route("confirm::input", "{ref}");'>{msg}</a>
`;

let __ctrl__tmpl__002__ = `
<a href='javascript:surveyQueue.route("thread::prev");'>prev</a>
<!-- <a href='javascript:surveyQueue.route("thread::next");'>next</a> //-->
`;

let __short_text_tmpl__ = `
<div class='question-output'>{question}</div>
<div class='answer-input'><input type='text' value='{answer}'></input></div>
`;

let __multiple_choice_tmpl__ = `
<div class='question-output'>{question}</div>
`;


let __group_tmpl__ = `
<div class='question-output'>{question}</div>
`;

let __statement_tmpl__ = `
<div class='question-output'>{question}</div>
<a href='javascript:surveyQueue.route("select::statement", "{ref}", "false");'>{button}</a>
`;

let __yes_no_tmpl__ = `
<div class='question-output'>{question}</div>
<div class="yesno-input">
     <a href='javascript:surveyQueue.route("select::yesno", "{ref}", "true");'>{yes}</a>
     <a href='javascript:surveyQueue.route("select::yesno", "{ref}", "false");'>{no}</a>
</div>
`;

let __file_upload_tmpl__ = `
<div class='question-output'>{question}</div>
`;

let __choice_tmpl__ = `
<div class='choice-output'>
<span><a href='javascript:surveyQueue.route("confirm::ref", "{ref}");'>{choice}</a></span>
</div>
`;

let __picture_choice_tmpl__ = `
<span><a href='javascript:surveyQueue.route("confirm::image", "{ref}");'><img src="{src}"></span>
`;

let __srv__msg__001__tmpl = `
<div>{msg}</div>
`;

let __srv__msg__002__tmpl = `
<span><a href='javascript:surveyQueue.route("select::survey", "{id}");'>{title}</a></span>
`

let __srv__msg__003__tmpl = `
<span class='thread-list'><a href='javascript:surveyQueue.route("select::thread", "{id}");'>{id} :: {date}</a></span>
`;

let __src__q__001__tmpl = `
<div><span>{question}</span></div>
`;

let __src__img__011__tmpl = `
<img class='uploaded-asset {indx}' src='{data}'></img>
`;

let __ctrl__tmpl__102__ = `
<a href='javascript:surveyQueue.route("spreads::init");'>{init}</a>
`;

let __srv__msg__004__tmpl = `
<span class='thread-list'><a href='javascript:surveyQueue.route("init::thread", "{id}");'>{start}</a></span>
`

class ThreadLog extends Model {
     constructor(){
          super();
          this.coll = [];
     }
     add(ref, val){
          if(null == this.coll) { this.coll = []; }
          this.coll[ref] = val;
     }
     getColl(){
          let res = [];
          for(let idx in this.coll){
               res.push({ key: idx, val: this.coll[idx] })
          }
          return res;
     }
     setColl(coll){
          for(let idx in coll){
               let key = coll[idx].key;
               let val = coll[idx].val;
               this.coll[key] = val;
          }
     }
     get(ref){
          if(null == this.coll){ this.coll = []; }
          if(null == this.coll[ref]){
               return null;
          }
          return this.coll[ref];
     }
}

class SurveyModel extends Model {
     constructor(){
          super();
          this.threadLog = new ThreadLog();
          this.clientAuthed = false;
          this.toc;
          this.sections;
          this.section;
          this.panels;
          this.panel;
          this.threads;
          this.thread;
          this.step = 0;
          this.tocstep = 0;
          this.maxImageAssets;
// deeplink -----------------------------
          this.rSurvey;
          this.rThread;
          this.rPanel;
          this.rQuestionId;
          this.navToThreadAction;
          this.navToPanelAction;
// --------------------------------------
          this.parseProc;
          this.layoutGroup;
     }
}

class MockLogic extends Model {
     constructor(){
          super();
          this.logic = {
               action: 'jump',
               condition: {
                    // op: 'and',
                    op: 'always',
                    vars: [
                    {
                         op: 'answered',
                         vars: [
                              { type: 'field', value: '1223' },
                              { type: 'content', value: true }
                         ]
                    },
                    {
                         op: 'is',
                         vars: [
                              { type: 'field', value: '1223' },
                              { type: 'choice', value: '1223' }
                         ]
                    },
                    {
                         op: 'and',
                         vars: {
                              'op':'is',
                              'vars': [
                                   { type: 'field', value: '1223' },
                                   { type: 'choice', value: '1223' }
                              ]
                         }
                    }
                    ]
               },
               details: {
                    to: {
                        type: 'field',
                        value: '489ed355-35b8-47c4-9a08-28fcc5b94c88'
                    }
               }
          }
      }
}
