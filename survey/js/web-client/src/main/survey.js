class Survey extends Controller {

     constructor(queue) {
          super(queue);
          let ref = this;
          this.model = new SurveyModel();
          jQuery('.survey-messages').html(this.fillTemplate(__srv_msg_001_tmpl__, {msg: __survey.__('welcome')}));
          // controls
          this.register(new Subscription(        'parse::assets', this.parseAssets));
          this.register(new Subscription(        'select::yesno', this.bindYesNoInput));
          this.register(new Subscription(         'confirm::ref', this.bindMultipleChoiceInput));
          this.register(new Subscription(       'confirm::image', this.bindMultipleChoiceInput));
          this.register(new Subscription(       'confirm::input', this.bindTextInput));
          this.register(new Subscription(      'confirm::upload', this.bindUploadInput));
          this.register(new Subscription(       'confirm::group', this.bindGroupInput));
          this.register(new Subscription('fieldings::downloaded', this.bindFieldings));
          this.register(new Subscription(    'select::statement', this.bindSelectStatement));
          this.register(new Subscription(            'nav::back', this.evalPrevPanel));
          this.register(new Subscription(         'set::opinion', this.bindOpinion));
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
          this.register(new Subscription(         'panel::saved', this.bindSavedPanel));
          this.register(new Subscription(       'input::corrupt', this.showValidationError));
          // ------
          this.extractDeeplink(window.location.hash.substr(1));
          this.navDeeplink(window.location.hash.substr(1));
          window.addEventListener('hashchange', function(e){ ref.bindHashChange(e); });
          // ------
          history.pushState(null, null, window.location.href);
          window.onpopstate = function(e){
               history.pushState(null, null, window.location.href);
               if('undefined' == typeof(surveyQueue)){ 
                     return false; 
               }
// fixdiss
               surveyQueue.route('nav::back');
          };
          // ------
          this.notify(new Message('download::fieldings', this.model));
     }

     showValidationError(msg){
         console.log(msg);
         alert(__survey.__('invalid', 'nosuch'));
     }

     bindSavedPanel(msg){
          this.evalNextPanel();
     }
 
     storeInput(msg){
          if(false == this.model.clientAuthed){}
          this.notify(new Message('save::panel', this.model));
          this.notify(new Message('save::toc', this.model));
     }

     bindSelectStatement(){
          this.evalNextPanel();
     }

     bindOpinion(msg){
          console.log(msg);
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
               if(id != this.model.rSection){
                   this.model.rSection = id;
                   this.model.rSectionNav = true;
               }
          }
          if(tmp.length >= 2){
               let id = this.getLinkVal('thread', tmp[1]);
               if(id != this.model.rThread){
                   this.model.rThread = id;
                   this.model.rThreadNav = true;
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
          if(this.model.rThreadNav){
               if(this.model.rSectionNav){
                    if(this.model.rPanelNav){
                         this.model.navToPanelAction = function(){
                              ref.selectPanel(ref.model.rPanel);
                              ref.model.rPanelNav = false;
                         }
                    }
                    this.model.navToThreadAction = function(){
                         let model = { 'arguments': [ '', ref.model.rSection ] }
                         ref.notify(new Message('select::thread', model));
                         ref.model.rSectionNav = false;
                    }
               }
               let model = { 'arguments': [ '', this.model.rThread ] }
               this.model.rThreadNav = false;
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

          let target = this.model.toc.post_content;

          if(null == target.booktoc){ target.booktoc = []; }

          if(null == target.history){ target.history = []; }

          this.model.threadLog.setColl(target.coll);

          if(null == target.position) { target.position = 0; }
          if(null == target.tocstep) { target.tocstep = 0; }
          if(null == target.navstep) { target.navstep = 0; }

          target.tocstep = parseInt(target.tocstep);
          target.position = parseInt(target.position);
          target.navstep = parseInt(target.navstep);

console.log(target.refs);
          let link = target.refs[0];

          if(SurveyConfig.LINEAR_HISTORY == SurveyConfig.navigationHistory){
               if(null != target.history[target.navstep -1]){
                   link = target.history[target.navstep -1];
               }
          }
          else {
               if(null != target.booktoc[target.tocstep -1]){
                   link = target.booktoc[target.tocstep -1];
               }
          }

          this.model.panels = [];

          if(SurveyConfig.preloadPanels){
               for(let idx in msg.model.e.coll.panels){
                    this.model.panels[msg.model.e.coll.panels[idx].post_excerpt] = msg.model.e.coll.panels[idx];
                    this.model.panels[msg.model.e.coll.panels[idx].post_excerpt].post_content = SurveyUtil.pagpick(msg.model.e.coll.panels[idx].post_content);
               }
          }

          if(null != this.model.navToPanelAction){
               this.model.navToPanelAction();
               this.model.navToPanelAction = null;
          }

          this.loadPanel(link);
          this.setLink();
     }

     checkIfRequired(validation){
          let res = false;
          let rVals = ['true', '1', true, 1 ];
          for(let idx in rVals){
               if(rVals[idx] == validation.required){
                    res = true;
               }
          }
          return res;
     }

     bindGroupInput(msg){
          this.evalNextPanel();
     }

     bindUploadInput(msg){
          let panel = this.model.panel.post_content.ref;
          let ref = msg.model.arguments[1];
          let val = 'asset::uploaded';
          let required = this.checkIfRequired(this.model.panel.post_content.validations.required);
          switch(required){
               case true:
               case false:
                    if(1 > this.model.panel.assetCopies.length){
                         this.notify(new Message('input::corrupt', this.model));
                         return false;
                    }
                    break;
          }
          this.bindInput(panel, ref, val);
     }

     bindTextInput(msg){
          let panel = this.model.panel.post_content.ref;
          let ref = msg.model.arguments[1];
          let val = jQuery('.answer-input input').val();
          let required = this.checkIfRequired(this.model.panel.post_content.validations.required);
          switch(required){
                case true:
                     if(3 >= val.length){
                          this.notify(new Message('input::corrupt', this.model));
                          return false;
                     }
                     break;
                case false:
// false will not validate
                     if(3 >= val.length){
                          this.notify(new Message('input::corrupt', this.model));
                          return false;
                     }
                     break;
          }
          this.bindInput(panel, ref, val);
     }

     bindImageInput(msg){
     }

     bindMultipleChoiceInput(msg){
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
          let val = msg.model.arguments[2] == 'true' ? 'true' : 'false';
          this.bindInput(panel, ref, val);
     }

     bindInput(panel, ref, val){
          if('undefined' == typeof(val)){ val = ''; }
          let answer = SurveyUtil.trimIncomingString(val);
          let question = this.corrQuestion(this.model.panel.post_content.title);
              question = SurveyUtil.trimIncomingString(question);
          this.model.panel.post_content.question = question;
          this.model.panel.post_content.answer = answer;
          this.model.threadLog.add(ref, answer);
          this.notify(new Message('input::done', this.model));
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
               question = question.replace(/_/g, '');
               question = question.replace(/\*/g, '');
               question = question.replace(/\*/g, '');
               question = question.replace(/\n\r/g, '');
               question = question.replace(/\n/g, '');
               question = question.replace(refmtch[idx], val);
          }
          return question;
     }

     corrToc(ref){
          if(null == this.model.panel){ return false; }
          let pos = this.model.toc.post_content.booktoc.indexOf(ref);
          if(-1 != pos){ this.model.toc.post_content.tocstep = pos; }
     }

     pushToc(){
          let target = this.model.toc.post_content;
console.log(target);
              target.tocstep = parseInt(target.tocstep);
              target.position = parseInt(target.position);
              target.booktoc[target.tocstep] = this.model.panel.post_content.ref;
              target.history[target.navstep] = this.model.panel.post_content.ref;
              target.tocstep++;
              target.navstep++;
              target.coll = this.model.threadLog.getColl();
     }

     pullToc(){
          let target = this.model.toc.post_content;
              target.tocstep--;
              target.tocstep--;
              target.navstep--;
              target.navstep--;
              if(target.tocstep <= 0){ target.tocstep = 0; }
              let res = null;
              if(null != target.booktoc[target.tocstep]){
                   res = target.booktoc[target.tocstep];
              }
              if(SurveyConfig.LINEAR_HISTORY == SurveyConfig.navigationHistory){
                   if(null != target.history[target.navstep]){
                        res = target.history[target.navstep];
                   }
              }
              return res;
     }

     loadPanel(ref){

          this.corrToc(ref);

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
               console.log('no panel ');
               return false;
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

          if('undefined' == typeof(answer)){ answer = ''; }

          jQuery('.survey-controls2nd').html('');
          jQuery('.survey-controls3rd').html('');
          jQuery('.survey-assets').html('');
          jQuery('.fake').off();
          jQuery('.files').off();
          jQuery('.file-upload').html('');

          let target;
          switch(this.model.panel.post_content.type){

               case 'phone_number':
               case 'long_text':
               case 'email':
               case 'number':
               case 'date':
               case 'short_text':
                   buf1st = this.fillTemplate(__short_text_tmpl__, { question: question, answer: answer });
                   buf2nd = this.fillTemplate(__ctrl_tmpl_003__, { 
                        msg: __survey.__('done'), 
                        ref: this.model.panel.post_content.ref
                   });
                   break;

               case 'file_upload':
                   buf1st = this.fillTemplate(__question_text_tmpl__, { question: question });
                   buf2nd = this.fillTemplate(__ctrl_tmpl_upload__, { 
                        ref: this.model.panel.post_content.ref, 
                        msg: __survey.__('done') 
                   });
                   this.renderFileupload();
                   this.renderAssetCopies();
                   if(null == this.model.panel.assetCopies){
                        this.model.panel.assetCopies = [];
                        this.notify(new Message('download::assets', this.model ));
                   }
                   break;

               case 'multiple_choice':
                   buf1st = this.fillTemplate(__question_text_tmpl__, { question: question });
                   target = this.model.panel.post_content.properties.choices;
                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        buf2nd+= this.fillTemplate(__choice_tmpl__, { choice: choice, ref: target[idx].ref });
                   }
                   break;

               case 'picture_choice':
                   buf1st = this.fillTemplate(__question_text_tmpl__, { question: question, answer: answer });
                   target = this.model.panel.post_content.properties.choices;
                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        let src = target[idx].attachment.href;
                        buf2nd+= this.fillTemplate(__picture_choice_tmpl__, { choice: choice, src: src, ref: target[idx].ref } );
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
                   buf1st = this.fillTemplate(__group_tmpl__, { 
                        question: question,
                        description: this.model.panel.post_content.properties.description
                   });
                   buf2nd = this.fillTemplate(__ctrl_tmpl_group__, { 
                        ref: this.model.panel.post_content.ref, 
                        msg: __survey.__('done') 
                   });
                   break;

               case 'statement':
                   buf1st = this.fillTemplate(__statement_tmpl__, {
                        question: this.model.panel.post_content.title,
                        button: this.model.panel.post_content.properties.button_text,
                        ref: this.model.panel.post_content.ref,
                   })
                   break;

               case 'question_group':
               case 'website':
               case 'payment':
               case 'legal':
                    buf1st = 'No view: ' +this.model.panel.post_content.type;
                    break;

               case 'dropdown':
                    buf1st = this.fillTemplate(__question_text_tmpl__, { question: question });
                    buf2nd = this.fillTemplate(__dropdown_row_tmpl__, {});
                    for(let idx in this.model.panel.post_content.properties.choices){
                          let label = this.model.panel.post_content.properties.choices[idx].label;
                          let ref = this.model.panel.post_content.properties.choices[idx].ref;
                          buf2nd+= this.fillTemplate(__dropdown_cell_tmpl__, { label: label, ref: ref }); 
                   }
                   buf2nd+= '</div>';
                   break;

               case 'opinion_scale':
               case 'rating':
                   buf1st = this.fillTemplate(__question_text_tmpl__, { question: question });
                   buf2nd = '<div class="opinion-row">';
                   for(let idx = 0; idx < 10; idx++){
                        buf2nd+= this.fillTemplate(__opinion_cell_tmpl__, { idx: idx }); 
                   }
                   buf2nd+= '</div>';
                   break;


               default:
                   buf1st = 'Unknown type: ' +this.model.panel.post_content.type;
          }

          jQuery('.survey-questions1st').html(buf1st);
          jQuery('.survey-controls1st').html(__ctrl_tmpl_002__);
          jQuery('.survey-controls2nd').html(buf2nd);
          jQuery('.survey-controls3rd').html(buf3rd);
          jQuery('.survey-controls4th').html(this.fillTemplate(__ctlr_tmpl_init_spreads__,{init:__survey.__('spreads')})); 

          this.setLink();
          this.pushToc(this.model.panel.post_content.ref);
     }

     renderFileupload(){
          let ref = this;
          if(null == this.model.panel.post_content.conf){
               return;
          }

          jQuery('.file-upload').html(__upload_tmpl_002__);

          let files = null;
          let slots = parseInt(this.model.panel.post_content.conf.image);
          let form = document.querySelector('.files');
          let fake = document.querySelector('.fake');

          fake.addEventListener(     'drop', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragleave', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragenter', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener( 'dragover', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener(  'dragend', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragstart', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener(     'drag', function(e){ e.preventDefault(); e.stopPropagation(); });
          fake.addEventListener('dragenter', function(e){ fake.classList.add('drag'); });
          fake.addEventListener( 'dragover', function(e){ fake.classList.add('drag'); });
          fake.addEventListener(     'drop', function(e){ fake.classList.remove('drag'); });
          fake.addEventListener(  'dragend', function(e){ fake.classList.remove('drag'); });
          fake.addEventListener('dragleave', function(e){ fake.classList.remove('drag'); });
          fake.addEventListener(  'mouseup', function(e){ form.click(); });

          fake.addEventListener('drop', function(e){ 
               document.querySelector('.files').files = e.dataTransfer.files;
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
               formdata.append(    'action', 'exec_image_upload');
               formdata.append(   'panelId', this.model.panel.ID);
               formdata.append(  'threadId', this.model.thread.ID);
               formdata.append(  'panelRef', this.model.thread.post_excerpt);
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
               this.loadPanel(link);
               return true;
          }
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
               this.loadPanel(link);
               return true;
          }
          this.nextPanel();
     }

     nextPanel(){
console.log('nextPanel: ', arguments);
          let target = this.model.toc.post_content.master;

/*
          this.model.toc.post_content.position++;
          if(this.model.toc.post_content.position 
               >= this.model.toc.post_content.master.length){ 
                    this.model.toc.post_content.position = this.model.toc.post_content.master.length -1 
          }
          let ref = this.model.toc.post_content.master[this.model.toc.post_content.position];
console.log('next link from default: ', ref);
          this.loadPanel(ref);
*/
     }

     prevPanel(){
console.log('prevPanel: ', arguments);
/*
          this.corrStep();
          this.model.toc.post_content.position--;
          if(this.model.toc.post_content.position <= 0){ this.model.toc.post_content.initstep = 0 }
          let ref = this.model.toc.post_content.master[this.model.toc.post_content.position];
console.log('prev link from default: ', ref);
          this.loadPanel(ref);
*/
     }

     selectPanel(step){
console.log('selectPanel: ', arguments);
/*
          this.model.toc.post_content.position = parseInt(step);
          if(this.model.toc.post_content.position <= 0){ this.model.toc.post_content.initstep = 0 }
          if(this.model.toc.post_content.position
               >= this.model.toc.master.length.length){
                   this.model.toc.post_content.position = this.model.toc.master.length -1
          }
          let ref = this.model.toc.post_content.master[this.model.toc.post_content.position];
          this.loadPanel(ref);
*/
     }

     initSpreads(msg){
          if('undefined' == typeof(layoutQueue)){ return false; }
          layoutQueue.route('init::book', { threadId: this.model.thread.ID });
     }

     parseAssets(msg){
          let ref = this;
          this.model.parseProc = [];
          this.model.panel.assetCopies = [];
          let files = document.querySelector('.files').files;
          let buf = '';
          for(let idx = 0; idx < files.length; idx++){
               if(idx >= this.model.maxImageAssets){ return; }
               let file = document.querySelector('.files').files[idx];
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
                    panel_ref: this.model.panel.post_content.ref,
                    layout_code: this.model.panel.assetCopies[idx].layoutCode,
                    base: this.model.panel.assetCopies[idx].post_content,
               }
               this.notify(new Message('upload::asset', model));
          }
     }

     evalRsLoc(rsloc){
          let mtch = rsloc.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
          if(null == mtch){
               rsloc = rsloc.replace('data:image/png;base64,', '');
               rsloc = 'data:image/png;base64,' +rsloc;

          } 
          return rsloc;
     }

     renderAssetCopies(){
          if(null == this.model.panel.assetCopies){ return; }
          let buf = '';
          for(let idx in this.model.panel.assetCopies){
               let indx = this.model.panel.assetCopies[idx].indx;
               let rsloc = this.model.panel.assetCopies[idx].post_content;
               if(null == rsloc){ continue; }
                   rsloc = this.evalRsLoc(rsloc);
               buf+= this.fillTemplate(__src_img_011_tmpl__, { indx: indx, data: rsloc });
          }
          jQuery('.survey-assets').html(buf);
     }
}

let __upload_tmpl_002__ = `
<form>
     <input type='file' class='files' name='filename' multiple='multiple'></inpupt>
     <div class='fake'>Drop Files Here</div>
</form>
`;

let __ctrl_tmpl_003__ = `
<a href='javascript:surveyQueue.route("confirm::input", "{ref}");'>{msg}</a>
`;

let __ctrl_tmpl_upload__ = `
<a href='javascript:surveyQueue.route("confirm::upload", "{ref}");'>{msg}</a>
`;

let __ctrl_tmpl_group__ = `
<a href='javascript:surveyQueue.route("confirm::group", "{ref}");'>{msg}</a>
`;

let __ctrl_tmpl_002__ = `
<!-- <a href='javascript:surveyQueue.route("thread::prev");'>prev</a> //-->
<!-- <a href='javascript:surveyQueue.route("thread::next");'>next</a> //-->
`;

let __short_text_tmpl__ = `
<div class='question-output'>{question}</div>
<div class='answer-input'><input type='text' value='{answer}'></input></div>
`;

let __question_text_tmpl__ = `
<div class='question-output'>{question}</div>
`;


let __group_tmpl__ = `
<div class='question-output'>{question}</div>
<div class='question-output'>{description}</div>
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

let __choice_tmpl__ = `
<div class='choice-output'>
<span><a href='javascript:surveyQueue.route("confirm::ref", "{ref}");'>{choice}</a></span>
</div>
`;

let __picture_choice_tmpl__ = `
<span><a href='javascript:surveyQueue.route("confirm::image", "{ref}");'><img src="{src}"></span>
`;

let __srv_msg_001_tmpl__ = `
<div>{msg}</div>
`;

let __src_img_011_tmpl__ = `
<img class='uploaded-asset {indx}' src='{data}'></img>
`;

let __ctlr_tmpl_init_spreads__ = `
<a href='javascript:surveyQueue.route("spreads::init");'>{init}</a>
`;

let __opinion_cell_tmpl__ = `
<div class='opinion-cell'><a href='javascript:surveyQueue.route("set::opinion", "{idx}");'>{idx}</a></div>
`

let __dropdown_row_tmpl__ = `
<select class='dropdown-row' onchange='javascript:surveyQueue.routee("dropdown::row", this);'>
`

let __dropdown_cell_tmpl__ = `
<option class='dropdown-cell' value='{ref}'>{label}</option>
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
          this.rThread;
          this.rSection;
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
