class Survey extends Controller {

     constructor(queue) {
          super(queue);
          let ref = this;
          this.model = new SurveyModel();
          jQuery('.survey-messages').html(this.fillTemplate(__srv_msg_001_tmpl__, {msg: __survey.__('welcome')}));
          // controls
          this.register(new Subscription(         'parse::assets', this.parseAssets));
          this.register(new Subscription(         'select::yesno', this.bindYesNoInput));
          this.register(new Subscription(          'confirm::ref', this.bindMultipleChoiceInput));
          this.register(new Subscription(        'confirm::image', this.bindMultipleChoiceInput));
          this.register(new Subscription(        'confirm::input', this.bindTextInput));
          this.register(new Subscription(       'confirm::upload', this.bindUploadInput));
          this.register(new Subscription(        'confirm::group', this.bindGroupInput));
          this.register(new Subscription( 'fieldings::downloaded', this.bindFieldingQuestions));
          this.register(new Subscription(     'select::statement', this.bindSelectStatement));
          this.register(new Subscription(             'nav::back', this.evalPrevPanel));
          this.register(new Subscription(          'set::opinion', this.bindOpinion));
          // events
          this.register(new Subscription(          'thread::next', this.nextPanel));
          this.register(new Subscription(          'thread::prev', this.evalPrevPanel));
          this.register(new Subscription(         'thread::loaded', this.bindThread));
          this.register(new Subscription(        'thread::inited', this.bindThread));
          this.register(new Subscription(      'assets::uploaded', this.bindAssets));
          this.register(new Subscription(    'assets::downloaded', this.bindAssets));
          this.register(new Subscription(         'spreads::init', this.initSpreads));
          this.register(new Subscription(        'asset::scanned', this.bindScan));
          this.register(new Subscription(           'scans::done', this.uploadAssets));
          this.register(new Subscription(           'scans::done', this.renderAssetCopies));
          this.register(new Subscription(         'assets::bound', this.renderAssetCopies));
          this.register(new Subscription(         'panel::loaded', this.bindPanel));
          this.register(new Subscription(           'input::done', this.storeInput));
          this.register(new Subscription(          'panel::saved', this.bindSavedPanel));
          this.register(new Subscription(        'input::corrupt', this.showValidationError));

          // ------
          // 127.0.0.1:8083/welcome.php?page_id=112932/#/child=joséf&mother=marikkah
          this.extractHiddenFields();
console.log('child: ' , this.getHiddenFieldVal('child'));

          // ------
          window.addEventListener('hashchange', function(e){ ref.bindHashChange(e); });
          // ------
          history.pushState(null, null, window.location.href);
          window.onpopstate = function(e){
               history.pushState(null, null, window.location.href);
               if('undefined' == typeof(surveyQueue)){ 
                     return false; 
               }
               surveyQueue.route('nav::back');
          };
          // ------
          this.notify(new Message('download::fieldings', this.model));
     }

     extractHiddenFields(){
          this.model.hiddenFields = [];
/*
          let lnk = window.location.hash.substr(1);
          let tmp = lnk.split('/');
          if(tmp.length <= 1){ return false; }
          for(let idx in tmp){
               let t = tmp[idx].split(':');
               if(2 < t.length){ continue; }
               if(null == t[0] || '' == t[0]){ continue; }
               this.model.hiddenFields.push({ key: t[0], val: t[1] });
          }
*/
          // let link = window.location.search.substr(1);
          let link = window.location.hash.substr(1);
          let prms = link.split('&');
          for(let idx in prms){
               let temp = prms[idx].split('=');
               if(null == temp || 2 < temp.length){ continue; }
               this.model.hiddenFields.push({ key: unescape(temp[0]), val: unescape(temp[1]) });
          }
     }

     getHiddenFieldVal(key){
          let res = null;
          if(null == this.model.hiddenFields){ return res; }
          for(let idx in this.model.hiddenFields){
               if(key == this.model.hiddenFields[idx].key){
                   res = this.model.hiddenFields[idx].val;
               }
          }
          return res;
     }

     showValidationError(msg){
         alert(__survey.__('invalid', 'nosuch'));
     }

     bindSavedPanel(msg){
          this.evalNextPanel();
     }
 
     storeInput(msg){
          this.notify(new Message('save::panel', this.model));
          this.notify(new Message('save::thread', this.model));
     }

     bindSelectStatement(){
          this.evalNextPanel();
     }

     bindOpinion(msg){
          this.evalNextPanel();
     }

     bindFieldingQuestions(msg){

          if(null == msg.model.e.coll.thread){
               console.log('no thread');
               return false;
          }

          let thread = msg.model.e.coll.thread;
          let panels = msg.model.e.coll.panels;
          let sections = msg.model.e.coll.sections;

          let m = { model: { e: { coll: { thread: thread, panels: panels, sections: sections }}}}

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
     }

     navDeeplink(){
          let lnk = window.location.hash.substr(1);
          let ref = this;
     }

     setLink(){
/*
          if(null == this.model.hiddenFields){ return false; }

          let ref = this;
          let lnk = window.location.href.substr(1);
          let chunk = '';

          if(lnk.match(/\/+$/)){ chunk = chunk.replace(/^\//, '');}
          let temp = '';
          for(let idx in this.model.hiddenFields){
              temp+= '/'+this.model.hiddenFields[idx].key +':' +this.model.hiddenFields[idx].val;
          }

          window.location.hash = chunk +temp;
*/
     }

     bindThread(msg){

// thread
          if(null == msg.model.e.coll.thread[0]){
               console.log('no thread');
               return false;
          }

          this.model.thread = msg.model.e.coll.thread[0];
          this.model.thread.post_content = SurveyUtil.pagpick(this.model.thread.post_content);

          let target = this.model.thread.post_content;

// section
// todo: there might be more than one section
          if(null == msg.model.e.coll.sections){
               console.log('no sections');
               return false;
          }

          this.model.section = msg.model.e.coll.sections[0];
          this.model.section.post_content = SurveyUtil.pagpick(this.model.section.post_content);

          this.model.panels = [];
          let ref; 

// loads the current panel by its reference
          if(SurveyConfig.resetSurveyState){
// survey loads the page that was left
               if(0 <= this.model.thread.post_content.history.length){
                    ref = this.model.thread.post_content.history.pop();
               }
          }
          if(null == ref){
               ref = this.model.section.post_content.toc.refs[0];
          }
          this.loadPanel(ref);

// link hash
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
// no validation
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

// stores condition ref for logic jumps
          this.setCondition(ref, val);

          this.notify(new Message('input::done', this.model));
     }

     setCondition(key, val){
          let target = this.model.thread.post_content.conditions;
          let fill = 0x01;
          for(let idx in target){
               if(key == target[idx].key){
                    target[idx].val = val;
                    fill = 0x02;
               }
          }
          if(0x01 == fill){
               target.push({key: key, val: val});
          }
     }

     getCondition(key){
          let res = null;
          let target = this.model.thread.post_content.conditions;
          for(let idx in target){
               if(key == target[idx].key){
                   res = target[idx].val;
               }
          }
          return res;
     }

     corrQuestion(question){

          let mtch = question.match(/{{(.{1,128}?)}}/g);

          for(let idx in mtch){

               let key = mtch[idx]; 
                   key = key.replace(/[{}]/g, '');
                   key = key.split(':');
                   key = key[1];

               let val = this.getCondition(key);

               if(null == val){ 
                   val = 'Could not find ref: ' +key;
                   val = '';
               }
               question = question.replace(/_/g, '');
               question = question.replace(/\*/g, '');
               question = question.replace(/\*/g, '');
               question = question.replace(/\n\r/g, '');
               question = question.replace(/\n/g, '');

               question = question.replace(mtch[idx], val);
          }

          return question;
     }

// adds an entry to the book table of contents
     pushBookToc(){
          if(null == this.model.panel){ 
               return false; 
          }
          let ref = this.model.panel.post_content.ref;
          let target = this.model.thread.post_content;
          if(-1 == target.book.indexOf(ref)){
              target.book.push(ref);
          }
     }

// todo
// book toc is semantic linear
// history is wild steps from field to field
     pushHistory(){
          if(null == this.model.panel){ 
               return false; 
          }
          let ref = this.model.panel.post_content.ref;
          let target = this.model.thread.post_content;
              target.history.push(ref);
     }

     loadPanel(ref){

console.log('loadPanel: ', ref);
          if(null == ref){
               return false;
          }

          this.model.requestedPanel = ref;

          if(null != this.model.panels[ref]){
               this.model.panel = this.model.panels[ref];
               this.initPanel();
               return;
          }

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

// initpanel sets up the screen
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

console.log('q:', question);

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
                        question: question,
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

          this.pushBookToc();
          this.pushHistory();

          this.setLink();
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
              res = this.evalRuleR(condition);
              res = this.evalGroup(condition);
              res = condition.result;

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
// this i don't know  i guess always evaluates always to true
                    // condition.result = true;
                    condition.result = false;
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

     evalRuleR(rule){

// evaluates condition groups
          if(null == rule.vars){ 
               return false; 
          }

          for(let idx in rule.vars){

// does the cycle until all condition groups is evaluated
               if(null != rule.vars[idx].op){ 
                    this.evalRuleR(rule.vars[idx]);
                    continue;
               }

// evals the condition of a *leaf
               rule.vars[idx].result = false;

               switch(rule.vars[idx].type){

// evals field of index of the rule 
                    case 'field':
                         rule.vars[idx].result =
                              rule.vars[idx].value == this.model.panel.post_content.ref;
                         break;

// evals input condition of the rule
                    case 'choice':
                    case 'constant':
                         rule.vars[idx].result =
                              this.model.panel.post_content.answer == this.getCondition(rule.vars[idx].value);
                         break;
               }
          }
     }

     evalPrevPanel(){

          let target = this.model.thread.post_content;
          let ref = target.history.pop();
              ref = target.history.pop();

          if(null != ref){
               this.loadPanel(ref);
               return true;
          }


          this.prevPanel();
     }

     evalNextPanel(){
          let ref = this;
          let links = [];
          let target = this.model.section.post_content.toc;
          let panel = this.model.panel.post_content.ref;
// console.log(panel);
// console.log(target.rulez);
          for(let idx in target.rulez){
               let rule = target.rulez[idx];
               if(panel != rule.ref){ continue; }
console.log(rule);
               rule.actions.forEach(function(actionpack){
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

// loads evaluated panel
          let link = links[0];
          if(null != link){
               this.loadPanel(link);
               return true;
          }

// loads default panel
          this.nextPanel();
     }

     nextPanel(){
          let target = this.model.section.post_content.toc;
          let currentPos = target.refs.indexOf(this.model.panel.post_content.ref);
          let pos = currentPos +1;
          if(pos >= target.refs.length -1){
              pos = target.refs.length -1;
          }
          let ref = target.refs[pos];
console.log('next link from default: ', ref);
          this.loadPanel(ref);
     }

     prevPanel(){
          if(null == this.model.panel){
               return false;
          }
          let target = this.model.section.post_content.toc;
          let currentPos = target.refs.indexOf(this.model.panel.post_content.ref);
          let pos = currentPos -1;
          if(pos <= 0){
               pos = 0;
          }
          let ref = target.refs[pos];
console.log('prev link from default: ', ref);
          this.loadPanel(ref);
          return true;
     }

     selectPanel(pos){
          let target = this.model.section.post_content.toc;
          if(pos <= 0){
               pos = 0;
          }
          if(pos >= target.refs.length -1){
              pos = target.refs.length -1;
          }
          let ref = target.refs[pos];
          this.loadPanel(ref);
          return true;
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

class SurveyModel extends Model {
     constructor(){
          super();
// --------------------------------------
          this.threads;
          this.sections;
          this.panels;
          this.thread;
          this.section;
          this.panel;
          this.maxImageAssets;
// deeplink -----------------------------
          this.requestedThread;
          this.requestedSection;
          this.requestedPanel;
// hidden fields ------------------------
          this.hiddenFields;
// --------------------------------------
          this.parseProc;
          this.layoutGroup;
// --------------------------------------
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
