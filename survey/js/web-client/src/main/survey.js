let Survey = function(controller) {

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

     this.bindSection = function(msg){

          if(null == msg.model.e.coll.section){
               console.log('bindSection(): no section');
               return false;
          }

          this.model.section = msg.model.e.coll.section;
          this.model.section.post_content = SurveyUtil.pagpick(this.model.section.post_content);
console.log('bindSection(): this.model.section: ', this.model.section);

          this.evalHiddenFields();
          this.recSection();

          let sectionId = this.model.section.ID;
          let panelRef = this.model.section.post_content.toc.refs[0];

          this.loadPanel(sectionId, panelRef);
     }

     this.evalHiddenFields = function(){

          this.model.linkedSurveyRef = this.model.section.post_content.survey.settings.redirect_after_submit_url;
console.log('evalHiddenFields(): this.model.linkedSurveyRef: ', this.model.linkedSurveyRef);

          if(null == this.model.linkedSurveyRef){ 
               return false; 
          }

// mock
// this.model.linkedSurveyRef = '#respondent={{field:f9b233e2-8036-4d0a-a249-ee28b99c11d0}}&partner={{field:c7c6ea2f-bc5f-4a13-ab02-ebd6d0e82d8d}}}}';
// this.model.linkedSurveyRef = '#respondent={{field:f9b233e2-8036-4d0a-a249-ee28b99c11d0}}';
// this.model.linkedSurveyRef = '#';
// 
          let temp;

          let sectionId = this.model.section.ID;
          let panelRef = this.model.section.post_content.toc.refs[0];

          let hash = this.model.linkedSurveyRef.match(/\#(.{1,256})/);

          if(null == hash || null == hash[1]){ 
               return false; 
          }

          hash = hash[1];
          hash = hash.split('&');

          for(let idx in hash){

               temp = hash[idx].split('=');

               if(false == jQuery.isArray(temp)){
                    return false;
               }

               let fieldVal = null;
               let fieldRef = temp[1];
                   fieldRef = fieldRef.replace(/[{}]/g, '');
                   fieldRef = fieldRef.split(':');

                   if(null == fieldRef){ 
                        return false; 
                   }

                   if(false == jQuery.isArray(fieldRef)){ 
                        return false; 
                   }

                   if(null == fieldRef[1]){ 
                        return false; 
                   }

                   fieldRef = fieldRef[1];
                   fieldVal = temp[0];

               this.addHiddenField(sectionId, panelRef, fieldRef, fieldVal);
          }
     }

     this.recSection = function(){
          if(null == this.model.sections) { this.model.sections = []; }
          if(-1 == this.model.sections.indexOf(this.model.section)){
               this.model.sections.push(this.model.section);
          }

console.log('recSection(): ', this.model.sections);
     }

     this.raiseErrorMessage = function(errorMessage){
         alert(errorMessage);
     }

     this.showValidationError = function(msg){
         this.raiseErrorMessage(__survey.__('input incomplete', 'bookbuilder'));
     }

     this.bindSavedPanel = function(msg){
          this.evalNextPanel();
     }
 
     this.saveThread = function(msg){
console.log('saveThread(): ', msg);
          this.notify(new Message('save::panel', this.model));
          this.notify(new Message('save::thread', this.model));
     }

     this.bindHashChange = function(e){
     }

     this.navDeeplink = function(){
          let lnk = window.location.hash.substr(1);
          let ref = this;
     }

     this.setLink = function(){
     }

     this.bindThread = function(msg){

// thread
          if(null == msg.model.e.coll.thread){
console.log('bindThread: no thread');
               return false;
          }

          this.model.thread = msg.model.e.coll.thread;
          this.model.thread.post_content = SurveyUtil.pagpick(this.model.thread.post_content);

// hidden fields
          if(null == this.model.thread.post_content.hidden_fields){
               this.model.thread.post_content.hidden_fields = [];
          }

// variables
          if(null == this.model.thread.post_content.variables){
               this.model.thread.post_content.variables = [];
          }

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
          let sectionId; 
          let panelRef; 

          if(SurveyConfig.resetSurveyState){
               if(0 <= this.model.thread.post_content.history.length){
                    let history = this.model.thread.post_content.history.pop();
                    if(null != history){
                         sectionId = history.sectionId;
                         panelRef = history.panelRef;
                    }
               }
          }

console.log('bindThread(): ', this.model.thread);

// link hash
          this.setLink();

// loads from the start 
          if(null == panelRef){
               sectionId = this.model.section.ID;
               panelRef = this.model.section.post_content.toc.refs[0];
          }

// loads panel as in initial panel
          this.loadPanel(sectionId, panelRef);

     }

     this.checkIfRequired = function(validation){
          let res = false;
          let rVals = ['true', '1', true, 1 ];
          for(let idx in rVals){
               if(rVals[idx] == validation.required){
                    res = true;
               }
          }
          return res;
     }

     this.bindGroupInput = function(msg){
          this.evalNextPanel();
     }

     this.bindUploadInput = function(msg){

          let sectionId = this.model.section.ID;
          let panelRef = this.model.panel.post_content.ref;

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

          this.bindInput(sectionId, panelRef, ref, val);
     }

     this.bindSelectStatement = function(msg){

          // let section = this.model.section.post_excerpt;
          let section = this.model.section.ID;
          let panel = this.model.panel.post_content.ref;

          let ref = msg.model.arguments[1];
          let val = 'noticed';

          this.bindInput(section, panel, ref, val);
     }

     this.bindOpinion = function(msg){
console.log('bindOpinion: ', msg);
          this.evalNextPanel();
     }

     this.bindTextInput = function(msg){

          // let section = this.model.section.post_excerpt;
          let section = this.model.section.ID;
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

     this.bindMultipleChoiceInput = function(msg){
console.log('bindMultipleChoiceInput(): ', msg);

          let sectionId = this.model.section.ID;
          let panelRef = this.model.panel.post_content.ref;

          let key = msg.model.arguments[1];
          let val = '';

          let choice;
          for(let idx in this.model.panel.post_content.properties.choices){
               choice = this.model.panel.post_content.properties.choices[idx]; 
               if(key == choice.ref){
                   val = choice.label;
               }
          };

console.log('bindMultipleChoiceInput(): ', 'sectionId: ', sectionId, 'panelRef:', panelRef, 'key: ', key, 'val: ', val);
          this.clearInput(sectionId, panelRef, key, val);
          this.bindInput(sectionId, panelRef, key, val);
     }

     this.bindYesNoInput = function(msg){

console.log('bindYesNoInput(): ', msg);

          let sectionId = this.model.section.ID;
          let panelRef = this.model.panel.post_content.ref;

          let ref = msg.model.arguments[1];
          let val = msg.model.arguments[2] == 'true' ? 'true' : 'false';

          this.bindInput(sectionId, panelRef, ref, val);
     }

     this.clearInput = function(sectionId, panelRef, ref, val){

          let target = this.model.thread.post_content.conditions;
          let copy = [];

          for(let idx in target){
               if(sectionId == target[idx].sectionId){
                    if(panelRef == target[idx].panelRef){
                         continue;
                    }
               }
               copy.push(target[idx]);
          }

          this.model.thread.post_content.conditions = copy;
     }

     this.bindInput = function(sectionId, panelRef, key, val){
console.log('bindInput(): ', sectionId, panelRef, key, val);

          if('undefined' == typeof(val)){ 
               val = ''; 
          }

          let answer = SurveyUtil.trimIncomingString(val);

          let question = this.model.panel.post_content.question;
              question = SurveyUtil.trimIncomingString(question);
              question = this.initStringOutput(question);

          this.model.panel.post_content.question = question;
          this.model.panel.post_content.answer = answer;
          this.model.panel.post_content.condition_ref = key;

          this.setCondition(sectionId, panelRef, key, val);

/*
          if(null != coll.additions[0]){
               let fieldRef = coll.additions[0].key;
               let fieldTitle = coll.additions[0].val;
               this.addHiddenField(sectionId, panelRef, fieldRef, fieldTitle);
          }
*/

          this.notify(new Message('input::done', this.model));
     }

     this.setCondition = function(sectionId, panelRef, key, val){

console.log('setCondition(): ', sectionId, panelRef, key, val);

// list of conditions
          let target = this.model.thread.post_content.conditions;

// rec of the condition
          let conditionRec = false;

          for(let idx in target){

// updates the condition
               if(sectionId == target[idx].sectionId){
                    if(panelRef == target[idx].panelRef){
                         if(key == target[idx].key){
console.log('found the condition at: ', idx);
                              target[idx].val = val;
                              conditionRec = true;
                         }
                    }
               }
          }

// writes condition
          if(false == conditionRec){
               target.push({sectionId: sectionId, panelRef: panelRef, key: key, val: val});
          }

          console.log('setCondition(): target: ', target);
     }

// returns whether or not a given answer ref is stored
     this.isStoredAnswerRef = function(sectionId, panelRef, key){

          let res = false;
          let target = this.model.thread.post_content.conditions;
          for(let idx in target){
               if(sectionId == target[idx].sectionId){
                    if(panelRef == target[idx].panelRef){
                         if(key == target[idx].key){
                             res = true;
                         }
                    }
               }
          }

          return res;
     }

// guess this be obsolette soon
// returns the value of a referenced answer
     this.getStoredAnswer = function(sectionId, panelRef, key){
console.log('getStoredAnswer(): sectionId: ', sectionId, 'panelRef: ', panelRef, 'key: ', key);

          let res = null;
          let target = this.model.thread.post_content.conditions;

console.log('getStoredAnswer(): target: ', target);
          for(let idx in target){
               if(sectionId == target[idx].sectionId){
                    if(panelRef == target[idx].panelRef){
                         if(key == target[idx].key){
                             res = target[idx].val;
                         }
                    }
               }
          }

console.log('getStoredAnswer(): res: ', res);
          return res;
     }

     this.getStoredAnswerByPanelRef = function(ref){
console.log('getStoredAnswerByPanelRef(): ref: ', ref);

          res = null;
          let target = this.model.thread.post_content.conditions;

          for(let idx in target){
               if(ref == target[idx].panelRef){
                   res = target[idx].val;
               }
          }

console.log('getStoredAnswerByPanelRef(): res: ', res);
          return res;
     }

     this.getStoredAnswerByKey = function(key){
console.log('getStoredAnswerByKey(): key: ', key);

          res = null;
          let target = this.model.thread.post_content.conditions;
          for(let idx in target){
               if(key == target[idx].key){
                    res = target[idx].val;
               }
          }

console.log('getStoredAnswerByKey(): res: ', res);
          return res;
     }


// sets up: inits: the strings of a 'field or call it 'panel
// it is mainly questions that consist of previously answered questions

     this.initStringOutput = function(istring){
console.log('initStringOutput(): istring: ', istring);

          if(null == istring){
               return;
          }

// evals the fields as {{field:hokuspokus12345}}
          let mtch = istring.match(/{{(.{1,128}?)}}/g);

console.log('initStringOutput(): mtch: ', mtch);
          for(let idx in mtch){

               let temp = mtch[idx]; 
                   temp = temp.replace(/[{}]/g, '');
                   temp = temp.split(':');

               let type = temp[0];
               let key = temp[1];

               let val = '';
               switch(type){

                   case 'field':
                        val = this.getStoredAnswerByPanelRef(key);
                        break;

                   case 'hidden':
                        val = this.getHiddenFieldValByTitle(key);
                        break;

                   case 'var':
                        val = this.getVariableValByTitle(key);
                        break;
               }

               val = null == val ? '' : val;

               istring = istring.replace(mtch[idx], val);
          }

console.log('istring: ', istring);

          return istring;
     }

// adds an entry to the book table of contents
     this.addBookRec = function(){

          if(null == this.model.panel){ 
               return false; 
          }

          let threadId = this.model.thread.ID;
          let sectionId = this.model.section.ID;
          let groupRef = this.model.panel.post_content.conf.parent;
          let panelRef = this.model.panel.post_content.ref;

          let imprint = this.model.panel.post_content.question;
          if(null == imprint
               || '' == imprint
               || -1 == imprint.indexOf('is going to read')){
console.log('addBookRec(): not going to compute: ', imprint);
               return false;
          }
          // imprint = this.initStringOutput(imprint);

console.log('addBookRec(): gathered information of relevance: ', imprint);
console.log('addBookRec(): todo: image assets of the current group');
          let target = this.model.thread.post_content;

// fills the list of information that is of relevance while generating the spreads
          let panelRec = false;
          for(let idx in target.book){
               if(sectionId == target.book[idx].sectionId){
                    if(panelRef == target.book[idx].panelRef){
                         panelRec = true;
                    }
               }
          }

          if(false == panelRec){
              target.book.push({
                   threadId: threadId,
                   sectionId: sectionId,
                   groupRef: groupRef,
                   panelRef: panelRef
              });
          }

console.log('addBookRec(): ', target.book);
     }

     this.addHiddenField = function(sectionId, panelRef, fieldRef, fieldTitle){
// a hidden field is a named reference that maps stored answer field value
// as in 
// {{var:child}}  -> ref:1635abcd.... "child"
// {{field:1635...}} ref:1635abcd.... "Joybubble"

          let target = this.model.thread.post_content.hidden_fields;

          let rec = {
               sectionId : sectionId,
               panelRef: panelRef,
               fieldRef: fieldRef,
               fieldTitle: fieldTitle
          }

          let temp;

          if(null == (temp = this.getHiddenField(sectionId, panelRef, fieldRef, fieldTitle))){
               target.push(rec);
          }

console.log('addHiddenField(): ', target);
     }

     this.getHiddenField = function(sectionId, panelRef, fieldRef, fieldTitle){
console.log('getHiddenField(): ', sectionId, panelRef, fieldRef, fieldTitle);
          let target = this.model.thread.post_content.hidden_fields;
          for(let idx in target){
               if(sectionId == target[idx].sectionId){
                    if(panelRef == target[idx].panelRef){
                         if(fieldRef == target[idx].fieldRef){
                              if(fieldTitle == target[idx].fieldTitle){
                                   return { idx: idx, val: target[idx] };
                              }
                         }
                    }
               }
          }

          return null;
     }

// todo
     this.getVariableValByTitle = function(key){
          let target = this.model.thread.post_content.variables;
console.log('getVariableValByTitle(): ', key, target);
          return 230;
     }

     this.getHiddenFieldValByTitle = function(key){

          let target = this.model.thread.post_content.hidden_fields;
console.log('getHiddenFieldValByTitle(): ', key, target);

          let res;
          let ref;

          for(let idx in target){

               // there is forwarded reference names within the hidden field names
               //     like {{child::child}}
               // and then {{child::ae123}}

               if(target[idx].fieldTitle == target[idx].fieldRef){
                    continue;
               }

               if(key == target[idx].fieldTitle){
                    ref = target[idx].fieldRef;
                    res = this.getStoredAnswerByKey(ref);
               }
          }

console.log('getHiddenFieldValByTitle(): res: ', res);

          return res;
     }

// todo
// book toc is semantic linear
// history is wild steps from field to field
     this.addHistoryRec = function(){

          if(null == this.model.section){ return false; }
          if(null == this.model.panel){ return false; }

          let sectionId = this.model.section.ID;
          let panelRef = this.model.panel.post_content.ref;
          let target = this.model.thread.post_content;

          target.history.push({ sectionId: sectionId, panelRef: panelRef });

console.log('addHistoryRec(): ', target.history);
     }

     this.loadPanel = function(sectionId, panelRef){
console.log('loadPanel(): ', sectionId, panelRef);

          if(null == sectionId){ return false; }
          if(null == panelRef){ return false; }

          if(null != this.model.sections[sectionId]){ 
               this.model.section = this.model.sections[sectionId];
               if(null != this.model.panels[panelRef]){
                    this.model.panel = this.model.panels[panelRef];
                    this.initPanel();
                    return;
               }
          }

          this.notify(new Message('load::panel', { threadId: this.model.thread.ID, sectionId: sectionId, panelRef: panelRef } ));
     }

     this.bindPanel = function(msg){
console.log('bindPanel(): ', msg);

          if(null == msg.model.e.coll['panel'][0]){ 
               console.log('bindPanel(): no panel');
               return false; 
          }

          this.model.panel = msg.model.e.coll['panel'][0];
          this.model.panel.post_content = SurveyUtil.pagpick(this.model.panel.post_content);

          this.selectSection(msg.model.e.coll['section_id']);

          this.initPanel();
     }

     this.selectSection = function(sectionId){

          let section;
          for(let idx in this.model.sections){
               if(sectionId == this.model.sections[idx].ID){
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

     this.setupInputKeys = function(){
          jQuery('.answer-input').off('keyup');
          if(null == jQuery('.answer-input')){
                 return false;
          }
          let ref = this;
          jQuery('.answer-input').keyup(function(e){
               switch(e.key){
                    case 'Enter':
                         let act = 'confirm::input';
                         let rff = ref.model.panel.post_content.ref;
                         let msg = { model: { arguments: [ act, rff] }};
                         ref.bindTextInput(msg);
                         break;
               }
          });
          return true;
     }

// initpanel sets up the panel . the field 
     this.initPanel = function(){
console.log('initPanel(): this.model.panel: ', this.model.panel);
          let ref = this;

          if(null == this.model.panel){
               console.log('initPanel(): no panel');
               return false;
          }

          this.model.maxImageAssets = 1;

          let buf1st = '';
          let buf2nd = '';
          let buf3rd = '';

          let section = this.model.section.ID;
          let group = this.model.panel.post_content.conf.parent;
          let panel = this.model.panel.post_content.ref;

// the question stored in the title field will not be written
          let question = this.model.panel.post_content.title;

              question = SurveyUtil.trimIncomingString(question);
              question = this.initStringOutput(question);
              question = null == question ? '' : question;

          this.model.panel.post_content.question = question;

// answer might or not be set
          let answer = this.model.panel.post_content.answer;
              answer = SurveyUtil.trimIncomingString(answer);
              answer = null == answer ? '' : answer;

// description might or not be set
          let description = '';
          if(null != this.model.panel.post_content.properties){
               if(null != this.model.panel.post_content.properties.description){
                    description = this.model.panel.post_content.properties.description;
               }
          }
          description = SurveyUtil.trimIncomingString(description);
          description = this.initStringOutput(description);

// setup of the view components
          jQuery('.survey-controls2nd').html('');
          jQuery('.survey-controls3rd').html('');
          jQuery('.survey-controls5th').html('');
          jQuery('.survey-controls6th').html('');
          jQuery('.survey-assets').html('');
          jQuery('.fake').off();
          jQuery('.files').off();
          jQuery('.file-upload').html('');

          buf1st = this.fillTemplate(__section_title_tmpl__, { section: section });
          jQuery('.survey-controls5th').html(buf1st);

          buf1st = this.fillTemplate(__group_title_tmpl__, { group: group });
          jQuery('.survey-controls6th').html(buf1st);

          buf1st = this.fillTemplate(__panel_title_tmpl__, { panel: panel });
          jQuery('.survey-controls7th').html(buf1st);

          let target;
          switch(this.model.panel.post_content.type){

               case 'short_text':
               case 'long_text':
               case 'phone_number':
               case 'email':
               case 'date':
               case 'number':

                   buf1st = this.fillTemplate(__short_text_tmpl__, { 
                        description: description,
                        question: question,
                        answer: answer 
                   });
                   buf2nd = this.fillTemplate(__ctrl_tmpl_003__, { 
                        ref: this.model.panel.post_content.ref,
                        msg: __survey.__('done')
                   });
                   break;

               case 'file_upload':

                   buf1st = this.fillTemplate(__question_text_tmpl__, { 
                        description: description,
                        question: question
                   });

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

                   buf1st = this.fillTemplate(__question_text_tmpl__, { 
                        description: description,
                        question: question
                   });

                   target = this.model.panel.post_content.properties.choices;

                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        buf2nd+= this.fillTemplate(__multiple_choice_tmpl__, { 
                             choice: choice, 
                             ref: target[idx].ref 
                        });
                   }

                   break;

               case 'picture_choice':

                   buf1st = this.fillTemplate(__question_text_tmpl__, { 
                        description: description,
                        question: question
                   });

                   buf2nd = "<div class='row'>";
                   target = this.model.panel.post_content.properties.choices;

                   for(let idx in target){
                        let choice = SurveyUtil.trimIncomingString(target[idx].label);
                        let src = target[idx].attachment.href;
                        buf2nd+= this.fillTemplate(__picture_choice_tmpl__, { 
                             choice: choice, 
                             src: src, 
                             ref: target[idx].ref 
                        });
                   }
                   buf2nd+= '<div>';
                   break;

               case 'yes_no':

                   buf1st = this.fillTemplate(__yes_no_tmpl__, { 
                        description: description,
                        question: question,
                        yes: __survey.__('yes', 'bookbuilder'), 
                        no: __survey.__('no', 'bookbuilder'),
                        ref: this.model.panel.post_content.ref
                   });
                   break;

               case 'group':

                   buf1st = this.fillTemplate(__group_tmpl__, { 
                        description: description,
                        question: question
                   });

                   buf2nd = this.fillTemplate(__ctrl_tmpl_group__, { 
                        ref: this.model.panel.post_content.ref, 
                        msg: __survey.__('done') 
                   });

                   break;

               case 'statement':

                   buf1st = this.fillTemplate(__statement_tmpl__, {
                        description: description,
                        question: question,
                        msg: this.model.panel.post_content.properties.button_text,
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

                    buf1st = this.fillTemplate(__question_text_tmpl__, { 
                         description: description,
                         question: question
                    });

                    buf2nd = this.fillTemplate(__dropdown_row_tmpl__, {});

                    for(let idx in this.model.panel.post_content.properties.choices){
                          let label = this.model.panel.post_content.properties.choices[idx].label;
                          let ref = this.model.panel.post_content.properties.choices[idx].ref;
                          buf2nd+= this.fillTemplate(__dropdown_cell_tmpl__, { label: label, ref: ref });
                    }

                    break;

               case 'opinion_scale':
               case 'rating':

                    buf1st = this.fillTemplate(__question_text_tmpl__, { 
                         description: description,
                         question: question 
                    });

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

          if(this.isBottomPanel()){
               ref.notify(new Message('bottompanel::reached', this.model.panel ));
          }

          if(this.isTopPanel()){
               ref.notify(new Message('toppanel::reached', this.model.panel ));
          }

          this.addBookRec();
          this.addHistoryRec();
          this.setLink();

          this.setupInputKeys();
     }

     this.isBottomPanel = function(){
console.log('isBottomPanel(): ', this.model.section.post_content);

          let res = false;
          let target = this.model.section.post_content.toc;

          if(this.model.panel.post_content.ref == target.refs[parseInt(target.refs.length -1)]){
               res = true;
          }

          return res;
     }

     this.isTopPanel = function(){

          let res = false;
          let target = this.model.section.post_content.toc;

          if(this.model.panel.post_content.ref == target.refs[0]){
               res = true;
          }

          return res;
     }

     this.bindTopPanel = function(msg){

         console.log('bindTopPanel(): ', msg);
     }

// bottom panel as in the last panel of the survey
     this.bindBottomPanel = function(msg){
         console.log('bindBottomPanel(): ', msg);
     }

     this.renderFileupload = function(){

          let ref = this;

          if(null == this.model.panel.post_content.conf){
               return;
          }

          jQuery('.file-upload').html(__upload_tmpl_002__);

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

     this.initImageUpload = function(files){
console.log('this.initImageUpload(): ', files);

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

     this.evalCondition = function(condition){
          // condition = new MockLogic().logic.condition;
          let res = null;
              res = this.evalRuleR(condition);
              res = this.evalGroup(condition);
              res = condition;
          return res;
     }

     this.evalGroup = function(condition){

          condition.result = null;

          switch(condition.op){

               case 'equal':
               case 'is':
               case 'and':
               case 'answered':
                    for(let idx in condition.vars){
                         let val = true == condition.vars[idx].result ? 1 : 0;
                         if(null == condition.result){ 
                              condition.result = val; 
                         }
                         else{
                              condition.result &= val; 
                         }
                    }
                    break;

               case 'or':
                    condition.result = null;
                    for(let idx in condition.vars){
                         let val = true == condition.vars[idx].result ? 1 : 0;
                         if(null == condition.result){
                              condition.result = val; 
                         }
                         else{ 
                              condition.result |= val; 
                         };
                    }
                    break;

               case 'always':
                    condition.result = 1;
                    break;

          }

          condition.result = 1 == condition.result ? true : false;

// groups might be trees also
          if(null != condition.vars){
               for(let idx in condition.vars){
                    if(null != condition.vars[idx].op){
                         this.evalGroup(condition.vars[idx]);
                    }
               }
          }
     }

     this.evalRuleR = function(rule){

// evaluates condition rules
          if(null == rule.vars){ 
               return false; 
          }

          for(let idx in rule.vars){

// does the cycle until all condition rules is evaluated
               if(null != rule.vars[idx].op){ 
                    this.evalRuleR(rule.vars[idx]);
                    continue;
               }

// evals the condition of a *leaf
               rule.vars[idx].result = false;

               let sectionId = this.model.section.ID;
               let panelRef = this.model.panel.post_content.ref;
               let key = this.model.panel.post_content.condition_ref;

               let queriedPanelRef = '';
               let storedAnswerVal = '';

               switch(rule.vars[idx].type){

// evals field reference and the index of the rule
// as in does the logic action refer to this field
                    case 'field':
                         queriedPanelRef = rule.vars[idx].value;
                         rule.vars[idx].result = rule.vars[idx].value == panelRef;
                         break;

// evals a multiple choice field
// in terms of is the selected value of the condition the stored value of the runtime
                    case 'choice':
                         rule.vars[idx].result = rule.vars[idx].value == key;
                         break;

// evals a yes no type
// as in is the answer in this field yes or no
                    case 'constant':
                         storedAnswerVal = this.getStoredAnswerByKey(key);
                         if(false == storedAnswerVal || 'false' == storedAnswerVal ){ storedAnswerVal = '0'; }
                         if( true == storedAnswerVal ||  'true' == storedAnswerVal ){ storedAnswerVal = '1'; }

                         rule.vars[idx].result = rule.vars[idx].value == storedAnswerVal;

                         break;
               }
          }
     }

     this.evalPrevPanel = function(){

          let target = this.model.thread.post_content;

          let history = target.history.pop();
              history = target.history.pop();

          if(null == history){
               this.loadPrevPanel();
               return false;
          }

          this.loadPanel(history.sectionId, history.panelRef);

          return true;
     }

     this.evalNextPanel = function(){
console.log('evalNextPanel(): ', this.model.panel);

          let redirectSurveyId = this.model.panel.post_content.redirect_survey_id;
console.log('evalNextPanel(): redirectSurveyId: ', redirectSurveyId);

          if(null != redirectSurveyId && 'no_redirect' != redirectSurveyId){
               this.loadSectionBySurveyId(redirectSurveyId)
               return true;
          }

// evaluates whether or not this panel is the last one in the section
          if(this.isBottomPanel()){
               this.loadNextSection();
               return true;
          }

// the next panel (field) to be displayed
          let toc = this.model.section.post_content.toc;
          let sectionId = this.model.section.ID;

// loads panel from logic (jump)
          let panelRef;
          let coll = this.evalLogicJump();
console.log('evalNextPanel(): evalutated logic jumps: ', coll);

// evaluates the reference of the next panel to be loaded
          if(null != coll.links[0]){
               panelRef = coll.links[0];
          }
          else if(null != coll.defaultLink){
               panelRef = coll.defaultLink;
console.log('evalNextPanel(): link of type "in all other cses jump to: "', panelRef);
          }

          if(null != coll.additions[0]){
               let fieldRef = coll.additions[0].key;
               let fieldTitle = coll.additions[0].val;
console.log('evalNextPanel(): todo: ', coll);
               // this.addHiddenField(sectionId, panelRef, fieldRef, fieldTitle);
          }

// loads the next panel by its reference index
          if(null != panelRef){
               this.loadPanel(sectionId, panelRef);
               return true;
          }

// loads panel from default list
          this.loadNextPanel();

          return true;
     }

     this.evalLogicJump = function(){
console.log('evalLogicJump(): ');

// evaluates the conditions of the logic action jumps

          let ref = this;

          let panel = this.model.panel;
          let toc = this.model.section.post_content.toc;

          let res = {
               defaultLink: null,
               links: [],
               additions: []
          };


          for(let idx in toc.rulez){
               let rule = toc.rulez[idx];

// actions that are missing this field
               if(panel.post_content.ref != rule.ref){ 
                    continue; 
               }

console.log('evalLogicJump(): rule: ', rule);
               rule.actions.forEach(function(actionpack){
                    let c = ref.evalCondition(actionpack.condition);

                    if(false != c.result){
console.log('evalLogicJump(): actionpack: ', actionpack);

                         switch(actionpack.action){

                              case 'jump':

                                   if('always' == c.op){
                                        res.defaultLink = actionpack.details.to.value;
                                   }
                                   else{
                                        res.links.push(actionpack.details.to.value);
                                   }

                                   break;

                              case 'add':

                                   res.additions.push({ 
                                        key: actionpack.details.target.value,
                                        val: actionpack.details.value.value
                                   });

                                   break;
                         }
                    }
               });
          }

console.log('evalLogicJump(): res: ', res);
          return res;
     }

     this.loadSectionBySurveyId = function(surveyId){
console.log('loadSectionBySurveyId(): ', surveyId);
          let model = {
               surveyId: surveyId,
          }
          this.notify(new Message('load::section', model));
     }

     this.loadNextSection = function(){
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
                        return true;
                   }
               }
          }

          this.notify(new Message('load::nextsection'));
          return true;
     }

     this.loadNextPanel = function(){

          if(null == this.model.section){ 
               return false; 
          }

          if(null == this.model.panel){ 
               return false; 
          }

          let sectionId = this.model.section.ID;

          let target = this.model.section.post_content.toc;
console.log('loadNextPanel(): ', target);

          let pos = target.refs.indexOf(this.model.panel.post_content.ref);
              pos+= 1;

          if(pos >= target.refs.length -1){
              pos = target.refs.length -1;
          }

          let panelRef = target.refs[pos];
console.log('loadNextPanel(): next link from default: ', sectionId, panelRef);

          this.loadPanel(sectionId, panelRef);

          return true;
     }

     this.loadPrevPanel = function(){

          if(null == this.model.section){ return false; }
          if(null == this.model.panel){ return false; }

          let target = this.model.section.post_content.toc;
          let sectionId = this.model.section.ID;

          let pos = target.refs.indexOf(this.model.panel.post_content.ref);
              pos-= 1;

          if(pos <= 0){ pos = 0; }

          let panelRef = target.refs[pos];
console.log('loadPrevPanel(): prev link from default: ', sectionId, panelRef);

          this.loadPanel(sectionId, panelRef);
          return true;
     }

     this.selectPanel = function(pos){

          let target = this.model.section.post_content.toc;
          let sectionId = this.model.section.ID;

          if(pos <= 0){ pos = 0; }
          if(pos >= target.refs.length -1){ pos = target.refs.length -1; }

          let panelRef = target.refs[pos];

          this.loadPanel(sectionId, panelRef);

          return true;
     }

     this.initSpreads = function(msg){
console.log('initSpreads(): msg: ', msg);
console.log('initSpreads(): layoutQueue: ', layoutQueue)

          if('undefined' == typeof(layoutQueue)){ return false; }
          layoutQueue.route('init::book', { threadId: this.model.thread.ID });
     }

     this.parseAssets = function(msg){
console.log('parseAssets(): ', msg);

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

     this.scanAsset = function(indx, base, proc, upload){
console.log('scanAsset(): ', indx, base, proc, upload);

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
             let acceptedAssetType = false;
             if(null != base.match(/^data:image\/png;base64/)){
                  base = base.replace('data:image/png;base64,', '');
                  base = 'data:image/png;base64,' +base;
                  acceptedAssetType = true;
             }
             if(null != base.match(/^data:image\/jpeg;base64/)){
                  base = base.replace('data:image/jpeg;base64,', '');
                  base = 'data:image/jpeg;base64,' +base;
                  acceptedAssetType = true;
             }
             if(false == acceptedAssetType){
                  this.raiseErrorMessage(__survey.__('asset type invalid', 'bookbuilder'));
                  return false;
             }
             img.src = base;
     }

     this.bindScan = function(msg){
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

     this.bindAssets = function(msg){
          if(null == this.model.panel){ return false; }
          this.model.panel.assetCopies = msg.model.e.coll;
          this.model.panel.assetCopies.sort(function(asset){ return asset.post_excerpt > asset.post_excerpt; });
          this.notify(new Message('assets::bound'));
     }

     this.uploadAssets = function(msg){

          for(let idx in this.model.panel.assetCopies){

               if(true != this.model.panel.assetCopies[idx].upload){ 
                    continue; 
               }

               let model = {
                    sectionId: this.model.section.ID,
                    panelRef: this.model.panel.post_content.ref,
                    groupRef: this.model.panel.post_content.conf.parent,
                    layoutCode: this.model.panel.assetCopies[idx].layoutCode,
                    base: this.model.panel.assetCopies[idx].post_content,
               }

               this.notify(new Message('upload::asset', model));
          }
     }

     this.evalRsLoc = function(rsloc){
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

     this.renderAssetCopies = function(){
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

     let ref = this;
     this.model = new SurveyModel();
     jQuery('.survey-messages').html(this.fillTemplate(__srv_msg_001_tmpl__, {msg: __survey.__('welcome')}));
     // controls

     this.register(new Subscription(         'parse::assets', 'parseAssets', this));
     this.register(new Subscription(         'select::yesno', 'bindYesNoInput', this));
     this.register(new Subscription(     'confirm::multiple', 'bindMultipleChoiceInput', this));
     this.register(new Subscription(        'confirm::image', 'bindMultipleChoiceInput', this));
     this.register(new Subscription(        'confirm::input', 'bindTextInput', this));
     this.register(new Subscription(       'confirm::upload', 'bindUploadInput', this));
     this.register(new Subscription(        'confirm::group', 'bindGroupInput', this));
     this.register(new Subscription( 'fieldings::downloaded', 'bindFieldingQuestions', this));
     this.register(new Subscription(     'select::statement', 'bindSelectStatement', this));
     this.register(new Subscription(             'nav::back', 'evalPrevPanel', this));
     this.register(new Subscription(          'set::opinion', 'bindOpinion', this));
     // events
     this.register(new Subscription(          'thread::next', 'loadNextPanel', this));
     this.register(new Subscription(          'thread::prev', 'evalPrevPanel', this));
     this.register(new Subscription(        'thread::loaded', 'bindThread', this));
     this.register(new Subscription(        'thread::inited', 'bindThread', this));
     this.register(new Subscription(      'assets::uploaded', 'bindAssets', this));
     this.register(new Subscription(    'assets::downloaded', 'bindAssets', this));
     this.register(new Subscription(         'spreads::init', 'initSpreads', this));
     this.register(new Subscription(        'asset::scanned', 'bindScan', this));
     this.register(new Subscription(           'scans::done', 'uploadAssets', this));
     this.register(new Subscription(           'scans::done', 'renderAssetCopies', this));
     this.register(new Subscription(         'assets::bound', 'renderAssetCopies', this));
     this.register(new Subscription(         'panel::loaded', 'bindPanel', this));
     this.register(new Subscription(           'input::done', 'saveThread', this));
     this.register(new Subscription(          'panel::saved', 'bindSavedPanel', this));
     this.register(new Subscription(        'input::corrupt', 'showValidationError', this));
     this.register(new Subscription(   'nextsection::loaded', 'bindSection', this));
     this.register(new Subscription(       'section::loaded', 'bindSection', this));
     this.register(new Subscription(  'bottompanel::reached', 'bindBottomPanel', this));
     this.register(new Subscription(     'toppanel::reached', 'bindTopPanel', this));

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

let __upload_tmpl_002__= ""+
     "<form>"+
          "<input type='file' class='files' name='filename' multiple='multiple' accept='image/jpeg, image/png'></input>"+
          "<div class='fake'>Drop Files Here</div>"+
     "</form>";

let __ctrl_tmpl_003__ = ""+
     "<a href='javascript:surveyQueue.route(\"confirm::input\", \"{ref}\");'>{msg}</a>";

let __ctrl_tmpl_upload__ = ""+
     "<a href='javascript:surveyQueue.route(\"confirm::upload\", \"{ref}\");'>{msg}</a>";

let __ctrl_tmpl_group__ = ""+
     "<a href='javascript:surveyQueue.route(\"confirm::group\", \"{ref}\");'>{msg}</a>";

let __ctrl_tmpl_002__ = "";

let __group_title_tmpl__ = ""+
     "<div class='parent-output'>Group: {group}</div>";

let __section_title_tmpl__ = ""+
     "<div class='section-output'>Section: {section}</div>";

let __panel_title_tmpl__ = ""+
     "<div class='panel-output'>Panel: {panel}</div>";

let __short_text_tmpl__ = ""+
     "<div class='description-output'>{description}</div>"+
     "<div class='question-output'>{question}</div>"+
     "<div class='answer-input'>"+
          "<input type='text' value='{answer}'></input>"+
     "</div>";

let __question_text_tmpl__ = ""+
    "<div class='description-output'>{description}</div>"+
    "<div class='question-output'>{question}</div>";


let __group_tmpl__ = ""+
     "<div class='description-output'>{description}</div>"+
     "<div class='question-output'>{question}</div>";

let __statement_tmpl__ = ""+
     "<div class='description-output'>{description}</div>"+
     "<div class='question-output'>{question}</div>"+
     "<a href='javascript:surveyQueue.route(\"select::statement\", \"{ref}\", \"false\");'>{msg}</a>";

let __yes_no_tmpl__ = ""+
     "<div class='description-output'>{description}</div>"+
     "<div class='question-output'>{question}</div>"+
     "<div class='yesno-input'>"+
          "<a href='javascript:surveyQueue.route(\"select::yesno\", \"{ref}\", \"true\");'>{yes}&nbsp;</a>"+
          "<a href='javascript:surveyQueue.route(\"select::yesno\", \"{ref}\", \"false\");'>{no}</a>"+
     "</div>";



let __multiple_choice_tmpl__ = ""+
     "<div class='choice-output'>"+
          "<span class='choice-output-cell'>"+
               "<a href='javascript:surveyQueue.route(\"confirm::multiple\", \"{ref}\");'>{choice}</a>"+
          "</span>"+
     "</div>";



let __picture_choice_tmpl__ = ""+
     "<div class='picture-choice block'>"+
          "<span><a href='javascript:surveyQueue.route(\"confirm::image\", \"{ref}\");'><img src=\"{src}\"></span>"+
     "</div>";

let __srv_msg_001_tmpl__ = ""+
     "<div>{msg}</div>";

let __src_img_011_tmpl__ = ""+
     "<img class='uploaded-asset {indx}' src='{data}'></img>";

/*
let __ctlr_tmpl_init_spreads__ = ""+
     "<a href='javascript:surveyQueue.route(\"spreads::init\");'>{init}</a>";
*/

let __opinion_cell_tmpl__ = ""+
     "<div class='opinion-cell'><a href='javascript:surveyQueue.route(\"set::opinion\", \"{idx}\");'>{idx}</a></div>";

let __dropdown_row_tmpl__ = ""+
     "<select class='dropdown-row' onchange='javascript:surveyQueue.routee(\"dropdown::row\", this);'>";

let __dropdown_cell_tmpl__ = ""+
     "<option class='dropdown-cell' value='{ref}'>{label}</option>"

let SurveyModel = function(){
     this.thread;
     this.section;
     this.panels;
     this.panel;
     this.maxImageAssets;
// deeplink -----------------------------
     this.requestedThread;
     this.requestedSectionId;
     this.requestedPanelRef;
// hidden fields ------------------------
     this.hiddenFields;
     this.linkedSurveyRef;
// --------------------------------------
     this.parseProc;
     this.layoutGroup;
// --------------------------------------
}

let MockLogic = function(){
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
