class Survey extends Controller {

     constructor(queue) {
          super(queue);
          let ref = this;
          this.model = new SurveyModel();
          jQuery('.survey-messages').html(this.fillTemplate(__srv_msg_001_tmpl__, {msg: __survey.__('welcome')}));
          // controls
          this.register(new Subscription(         'parse::assets', this.parseAssets));
          this.register(new Subscription(         'select::yesno', this.bindYesNoInput));
          this.register(new Subscription(     'confirm::multiple', this.bindMultipleChoiceInput));
          this.register(new Subscription(        'confirm::image', this.bindMultipleChoiceInput));
          this.register(new Subscription(        'confirm::input', this.bindTextInput));
          this.register(new Subscription(       'confirm::upload', this.bindUploadInput));
          this.register(new Subscription(        'confirm::group', this.bindGroupInput));
          this.register(new Subscription( 'fieldings::downloaded', this.bindFieldingQuestions));
          this.register(new Subscription(     'select::statement', this.bindSelectStatement));
          this.register(new Subscription(             'nav::back', this.evalPrevPanel));
          this.register(new Subscription(          'set::opinion', this.bindOpinion));
          // events
          this.register(new Subscription(          'thread::next', this.loadNextPanel));
          this.register(new Subscription(          'thread::prev', this.evalPrevPanel));
          this.register(new Subscription(        'thread::loaded', this.bindThread));
          this.register(new Subscription(        'thread::inited', this.bindThread));
          this.register(new Subscription(      'assets::uploaded', this.bindAssets));
          this.register(new Subscription(    'assets::downloaded', this.bindAssets));
          this.register(new Subscription(         'spreads::init', this.initSpreads));
          this.register(new Subscription(        'asset::scanned', this.bindScan));
          this.register(new Subscription(           'scans::done', this.uploadAssets));
          this.register(new Subscription(           'scans::done', this.renderAssetCopies));
          this.register(new Subscription(         'assets::bound', this.renderAssetCopies));
          this.register(new Subscription(         'panel::loaded', this.bindPanel));
          this.register(new Subscription(           'input::done', this.saveThread));
          this.register(new Subscription(          'panel::saved', this.bindSavedPanel));
          this.register(new Subscription(        'input::corrupt', this.showValidationError));
          this.register(new Subscription(   'nextsection::loaded', this.bindSection));
          this.register(new Subscription(  'bottompanel::reached', this.bindBottomPanel));
          this.register(new Subscription(     'toppanel::reached', this.bindTopPanel));
          // ------
          // 127.0.0.1:8083/welcome.php?page_id=112932/#/child=joséf&mother=marikkah
          this.extractHiddenFields();

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
          // this.notify(new Message('download::fieldings', this.model));
          this.notify(new Message('init::thread', this.model));
     }

     bindSection(msg){
     
          if(null == msg.model.e.coll.section){
               console.log('bindSection(): no section');
               return false;
          }

          this.model.section = msg.model.e.coll.section;
          this.model.section.post_content = SurveyUtil.pagpick(this.model.section.post_content);

console.log('bindSection(): this.model.section: ', this.model.section);

          this.evalHiddenFields();
          this.recSection();

          let section = this.model.section.post_excerpt;
          let panel = this.model.section.post_content.toc.refs[0];

          this.loadPanel(section, panel);
     }

     evalHiddenFields(){

          this.model.redirect = this.model.section.post_content.survey.settings.redirect_after_submit_url;
          if(null == this.model.redirect){ return false; }
// mock
// this.model.redirect = '#respondent={{field:f9b233e2-8036-4d0a-a249-ee28b99c11d0}}&partner={{field:c7c6ea2f-bc5f-4a13-ab02-ebd6d0e82d8d}}}}';
// this.model.redirect = '#respondent={{field:f9b233e2-8036-4d0a-a249-ee28b99c11d0}}';
// this.model.redirect = '#';
// 
          let temp = null;
          let section = this.model.section.post_excerpt;
          let panel = this.model.section.post_content.toc.refs[0];
          let hash = this.model.redirect.match(/\#(.{1,256})/);
          if(null == hash){ return false; }
          if(null == hash[1]){ return false; }
          hash = hash[1];
          hash = hash.split('&');
          for(let idx in hash){
               temp = hash[idx].split('=');
               let title = temp[0];
               let ref = temp[1];
                   ref = ref.replace('{{', '');
                   ref = ref.replace('}}', '');
                   ref = ref.split(':');
                   if(null == ref){ return false; }
                   if(false == jQuery.isArray(ref)){ return false; }
                   if(null == ref[1]){ return false; }
                   ref = ref[1];

               this.pushHiddenField(section, panel, title, ref);
          }
     }

     recSection(){
          if(null == this.model.sections) { this.model.sections = []; }
          if(-1 == this.model.sections.indexOf(this.model.section)){
               this.model.sections.push(this.model.section);
          }
console.log('recSection(): ', this.model.sections);
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
 
     saveThread(msg){
// fixdiss
console.log('saveThread(): ', msg);
          this.notify(new Message('save::thread', this.model));
          this.notify(new Message('save::panel', this.model));
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
          if(null == msg.model.e.coll.thread){
               console.log('bindThread: no thread');
               return false;
          }

          this.model.thread = msg.model.e.coll.thread;
          this.model.thread.post_content = SurveyUtil.pagpick(this.model.thread.post_content);

// sections
          if(null == msg.model.e.coll.sections[0]){
               console.log('bindThread: no sections');
               return false;
          }

          this.model.sections = msg.model.e.coll.sections;
          for(let idx in this.model.sections){
               this.model.sections[idx].post_content = SurveyUtil.pagpick(this.model.sections[idx].post_content);
          }

          this.model.section = msg.model.e.coll.sections[0];

          this.recSection();

// panels
          this.model.panels = [];

// loads the current panel by its reference
          let section; 
          let panel; 

          if(SurveyConfig.resetSurveyState){
               if(0 <= this.model.thread.post_content.history.length){
                    let history = this.model.thread.post_content.history.pop();
                    if(null != history){
                         section = history.section;
                         panel = history.panel;
                    }
               }
          }

// loads from the start 
          if(null == panel){
               section = this.model.section.post_excerpt;
               panel = this.model.section.post_content.toc.refs[0];
          }

console.log('bindThread(): ', this.model.thread);

// link hash
          this.setLink();

// loads panel as in initial panel
          this.loadPanel(section, panel);

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

          let section = this.model.section.post_excerpt;
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

          this.bindInput(section, panel, ref, val);
     }

     bindSelectStatement(msg){

          let section = this.model.section.post_excerpt;
          let panel = this.model.panel.post_content.ref;

          let ref = msg.model.arguments[1];
          let val = 'noticed';

          this.bindInput(section, panel, ref, val);
     }

     bindOpinion(msg){
console.log('bindOpinion: ', msg);
          this.evalNextPanel();
     }

     bindTextInput(msg){

          let section = this.model.section.post_excerpt;
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

          this.bindInput(section, panel, ref, val);
     }

     bindMultipleChoiceInput(msg){

          let section = this.model.section.post_excerpt;
          let panel = this.model.panel.post_content.ref;

          let ref = msg.model.arguments[1];
          let val = '';
          let choice = null;

          for(let idx in this.model.panel.post_content.properties.choices){
               choice = this.model.panel.post_content.properties.choices[idx]; 
               if(ref == choice.ref){
                   val = choice.label;
               }
          };

          this.clearInput(section, panel, ref, val);
          this.bindInput(section, panel, ref, val);
     }

     bindYesNoInput(msg){

          let section = this.model.section.post_excerpt;
          let panel = this.model.panel.post_content.ref;

          let ref = msg.model.arguments[1];
          let val = msg.model.arguments[2] == 'true' ? 'true' : 'false';

          this.bindInput(section, panel, ref, val);
     }

     clearInput(section, panel, ref, val){
          let target = this.model.thread.post_content.conditions;
          let copy = [];
          for(let idx in target){
               if(section == target[idx].section){
                    if(panel == target[idx].panel){
                         continue;
                    }
               }
               copy.push(target[idx]);
          }
          this.model.thread.post_content.conditions = copy;
     }

     bindInput(section, panel, key, val){
          if('undefined' == typeof(val)){ val = ''; }
          let answer = SurveyUtil.trimIncomingString(val);
          let question = this.corrQuestion(this.model.panel.post_content.title);
              question = SurveyUtil.trimIncomingString(question);
          this.model.panel.post_content.question = question;
          this.model.panel.post_content.condition_ref = key;
          this.model.panel.post_content.answer = answer;
          this.setCondition(section, panel, key, val);
          this.notify(new Message('input::done', this.model));
     }

     setCondition(section, panel, key, val){
          let target = this.model.thread.post_content.conditions;
          let conditionRec = false;
          for(let idx in target){
               if(section == target[idx].section){
                    if(panel == target[idx].panel){
                         if(key == target[idx].key){
                              target[idx].val = val;
                              conditionRec = 0x01;
                         }
                    }
               }
          }
          if(false == conditionRec){
               target.push({section: section, panel: panel, key: key, val: val});
          }

console.log('setCondition(): ', target);

     }

     getCondition(section, panel, key){
          let res = null;
          let target = this.model.thread.post_content.conditions;
          for(let idx in target){
               if(section == target[idx].section){
                    if(panel == target[idx].panel){
                         if(key == target[idx].key){
                             res = target[idx].key;
                         }
                    }
               }
          }
          return res;
     }

     getAnswer(key){
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

               let val = this.getAnswer(key);

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
     pushBook(){

          if(null == this.model.panel){ return false; }

          let section = this.model.section.post_excerpt;
          let panel = this.model.panel.post_content.ref;

          let target = this.model.thread.post_content;
          let panelRec = false;

          for(let idx in target.book){
               if(section == target.book[idx].section){
                    if(panel == target.book[idx].panel){
                         panelRec = true;
                    }
               }
          }

          if(!panelRec){
              target.book.push({section: section, panel: panel });
          }


console.log('pushBook(): ', target.book);
     }

     pushHiddenField(section, panel, title, ref){

          if(null == this.model.thread.post_content.hidden_fields){
               this.model.thread.post_content.hidden_fields = [];
          }

          let target = this.model.thread.post_content.hidden_fields;

          let rec = {
               section : section, 
               panel: panel,
               title: title,
               ref: ref
          }

          let temp;

          if(null == (temp = this.getHiddenField(section, panel, title, ref))){
               target.push(rec);
          }
          else {
               target[temp.idx] = temp.val;
          }

console.log('pushHiddenField(): ', target);
     }

     getHiddenField(section, panel, title, ref){

          let target = this.model.thread.post_content.hidden_fields;

          for(let idx in target){
               if(section == target[idx].section){
                    if(panel == target[idx].panel){
                         if(title == target[idx].title){
                              if(ref == target[idx].ref){
                                   return { idx: idx, val: target[idx] };
                              }
                         }
                    }
               }
          }

          return null;
     }

// todo
// book toc is semantic linear
// history is wild steps from field to field
     pushHistory(){

          if(null == this.model.section){ return false; }
          if(null == this.model.panel){ return false; }

          let section = this.model.section.post_excerpt;
          let panel = this.model.panel.post_content.ref;
          let target = this.model.thread.post_content;

          target.history.push({ section: section, panel: panel });

console.log('pushHistory(): ', target.history);
     }

     loadPanel(section, panel){
console.log('loadPanel(): ', section, panel);

          if(null == section){ return false; }
          if(null == panel){ return false; }

          if(null != this.model.sections[section]){ 
               this.model.section = this.model.sections[section];
               if(null != this.model.panels[panel]){
                    this.model.panel = this.model.panels[panel];
                    this.initPanel();
                    return;
               }
          }

          this.model.requestedPanel = panel;
          this.model.requestedSection = section;

          this.notify(new Message('load::panel', this.model));
     }

     bindPanel(msg){

          if(null == msg.model.e.coll['panel'][0]){ 
               console.log('bindPanel(): no panel');
               return false; 
          }

          this.model.panel = msg.model.e.coll['panel'][0];
          this.model.panel.post_content = SurveyUtil.pagpick(this.model.panel.post_content);

console.log('bindPanel(): ', msg);
          this.selectSection(msg.model.e.coll['section_ref']);

          this.initPanel();
     }

     selectSection(ref){

          let section;

          for(let idx in this.model.sections){
               if(ref == this.model.sections[idx].post_excerpt){
                    section = this.model.sections[idx];
               }
          }

          if(null == section){
               return false;
          }

          this.model.section = section;
console.log('selectSection(): ', this.model.section);
 
          this.evalHiddenFields() 
     }

// initpanel sets up the panel . the field 
     initPanel(){

          let ref = this;

          if(null == this.model.panel){
               console.log('initPanel(): no panel');
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
                        buf2nd+= this.fillTemplate(__mutliple_choice_tmpl__, { choice: choice, ref: target[idx].ref });
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

          if(this.isBottomPanel()){
               ref.notify(new Message('bottompanel::reached', this.model.panel ));
          }

          if(this.isTopPanel()){
               ref.notify(new Message('toppanel::reached', this.model.panel ));
          }

          this.pushBook();
          this.pushHistory();

          this.setLink();
     }

     isBottomPanel(){
          let res = false;
          let target = this.model.section.post_content.toc;
console.log('isBottomPanel(): ', this.model.section.post_content);
          if(this.model.panel.post_content.ref == target.refs[parseInt(target.refs.length -1)]){
               res = true;
          }
          return res;
     }

     isTopPanel(){
          let res = false;
          let target = this.model.section.post_content.toc;
          if(this.model.panel.post_content.ref == target.refs[0]){
               res = true;
          }
          return res;
     }

     bindTopPanel(msg){
         console.log('bindTopPanel(): ', msg);
     }

     bindBottomPanel(msg){
         console.log('bindBottomPanel(): ', msg);
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

          condition.result = null;

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
console.log('evalGroup(): condition: "always" found');
                    condition.result = true;
                    // condition.result = false;
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

                         let section = this.model.section.post_excerpt;
                         let condition = this.model.panel.post_content.condition_ref;

                         let panel = this.model.panel.post_content.ref;
                         let key = rule.vars[idx].value;

                         rule.vars[idx].result = condition == this.getCondition(section, panel, key);
                         break;
               }
          }
     }

     evalPrevPanel(){
          let target = this.model.thread.post_content;
          let history = target.history.pop();
              history = target.history.pop();
          if(null == history){
               this.loadPrevPanel();
               return false;
          }
          this.loadPanel(history.section, history.panel);
          return true;
     }

     evalNextPanel(){
// evaluates whether or not this panel is the last one in the section
          if(this.isBottomPanel()){
               this.loadNextSection();
               return true;
          }
// the next panel (field) to be displayed
          let settings = this.model.section.post_content.survey.settings;

          let toc = this.model.section.post_content.toc;
          let section = this.model.section.post_excerpt;

// loads panel from logic
          let panel = this.evalLogicAction(toc);
          if(null != panel){
               this.loadPanel(section, panel);
               return true;
          }

// loads panel from default list
          this.loadNextPanel();
          return true;
     }

     evalLogicAction(toc){

// evaluatess the conditions of logic jummps
          let ref = this;
          let links = [];
          let panel = this.model.panel;

          for(let idx in toc.rulez){
               let rule = toc.rulez[idx];
               if(panel.post_content.ref != rule.ref){ continue; }

console.log('evalLogicAction(): rule: ', rule);
               rule.actions.forEach(function(actionpack){
                    if(false != ref.evalCondition(actionpack.condition)){
                         switch(actionpack.action){
                              case 'jump':

console.log('evalLogicAction(): link from logic: ', actionpack.details.to.value);
console.log('evalLogicAction(): actionpack: ', actionpack);
                                   links.push(actionpack.details.to.value);
                                   break;
                         }
                    }
               });
          }

          return links[0];
     }

     loadNextSection(){

console.log('loadNextSection(): this.model.sections: ', this.model.sections);

          let pos = null;
          let nextSection;

// evaluates position of current section within the loaded sections
          if(null != this.model.sections){
               pos = this.model.sections.indexOf(this.model.section);
               if(-1 != pos){
                   pos+= 1;
                   nextSection = this.model.sections[pos];
                   if(null != nextSection){
                        this.model.section = nextSection;
                        this.notify(new Message('nextsection::loaded', { e: { coll: { section: this.model.section }}}));
                        // this.evalNextPanel();
                        return true;
                   }
               }
          }

          this.notify(new Message('load::nextsection'));
          return true;
     }

     loadNextPanel(){

          if(null == this.model.section){ return false; }
          if(null == this.model.panel){ return false; }

          let section = this.model.section.post_excerpt;
          let target = this.model.section.post_content.toc;

console.log('loadNextPanel(): ', target);

          let pos = target.refs.indexOf(this.model.panel.post_content.ref);
              pos+= 1;

          if(pos >= target.refs.length -1){
              pos = target.refs.length -1;
          }

          let panel = target.refs[pos];
console.log('loadNextPanel(): next link from default: ', section, panel);

          this.loadPanel(section, panel);
          return true;
     }

     loadPrevPanel(){

          if(null == this.model.section){ return false; }
          if(null == this.model.panel){ return false; }

          let section = this.model.section.post_excerpt;
          let target = this.model.section.post_content.toc;

          let pos = target.refs.indexOf(this.model.panel.post_content.ref);
              pos-= 1;

          if(pos <= 0){ pos = 0; }

          let panel = target.refs[pos];
console.log('loadPrevPanel(): prev link from default: ', section, panel);

          this.loadPanel(section, panel);
          return true;
     }

     selectPanel(pos){

          let section = this.model.section.post_excerpt;
          let target = this.model.section.post_content.toc;

          if(pos <= 0){ pos = 0; }
          if(pos >= target.refs.length -1){ pos = target.refs.length -1; }

          let panel = target.refs[pos];

          this.loadPanel(section, panel);
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
                        console.log('parseAssets(): onError: ', e);
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
// rsloc as in resource locator
          if(null == rsloc){ return rsloc; }
          let mtch = rsloc.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g);
// no resource locator
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

let __mutliple_choice_tmpl__ = `
<div class='choice-output'>
<span><a href='javascript:surveyQueue.route("confirm::multiple", "{ref}");'>{choice}</a></span>
</div>
`;

let __picture_choice_tmpl__ = `
<div class='picture-choice'>
<span><a href='javascript:surveyQueue.route("confirm::image", "{ref}");'><img src="{src}"></span>
</div>
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
          this.thread;
          this.section;
          this.panels;
          this.panel;
          this.maxImageAssets;
// deeplink -----------------------------
          this.requestedThread;
          this.requestedSection;
          this.requestedPanel;
// hidden fields ------------------------
          this.hiddenFields;
          this.redirect;
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
