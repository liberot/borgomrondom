function setShowSpreadState(ref){
     let valu = jQuery(ref).val();
     let stmp = jQuery(ref).attr('class');
     let mtch = stmp.match(/question\-(.{1,128})/);
     if(null == mtch || null == mtch[1]){
          return false;
     }
     let quid = mtch[1];
     let data = {
          action: 'exec_set_show_spread_state',
          question_id: quid,
          state: valu
     }
     console.log(data);
     // this.postCommand(data);
}

function setRedirect(ref){
     let suid = jQuery(ref).val();
     let stmp = jQuery(ref).attr('class');
     let mtch = stmp.match(/question\-(.{1,128})/);
     if(null == mtch || null == mtch[1]){
          return false;
     }
     let quid = mtch[1];
     let data = {
          action: 'exec_init_redirect',
          question_id: quid,
          survey_id: suid
     }
     this.postCommand(data);
}
function deleteSurveyPage(){
     let data = { 
          action: 'exec_delete_survey_page'
     };
     this.postCommand(data);
}

function deleteLayouts(){
     let data = { 
          action: 'exec_delete_layouts'
     };
     this.postCommand(data);
}

function dumpClientThreads(){
     let data = { 
          action: 'exec_dump_threads'
     };
     this.postCommand(data);
}

function dumpSurveys(){
     let data = { 
          action: 'exec_dump_surveys'
     };
     this.postCommand(data);
}

function deleteSurveys(){
     let data = { 
          action: 'exec_delete_surveys'
     };
     this.postCommand(data);
}

function deleteClientThreads(){
     let data = { 
          action: 'exec_delete_client_threads'
     };
     this.postCommand(data);
}

function initSurveyPage(){
     let data = { 
          action: 'exec_init_survey_page'
     };
     this.postCommand(data);
}

function deleteSurveyDB(){
     let data = { 
          action: 'exec_delete_bookbuilder_db'
     };
     this.postCommand(data);
}

function constructFieldingQuestions(){
     let data = { 
          action: 'exec_construct_typeform_survey',
          survey_file_name: 'BBC0-Cover-and-Prefa--FvSIczF7.json'
          /* survey_file_name: '201204-Cover-and-Pre--cMsCFF9a.json' */
     };
     this.postCommand(data);
}

function constructAllSurveys(){
     let data = { 
          action: 'exec_construct_all_surveys',
     };
     this.postCommand(data);
}

function saveQuestion(id, max, group){
     let data = {
          action: 'exec_save_question',
          id: id,
          max: max,
          group: group
     };
     let suc = function(e){
     }
     this.postCommand(data, suc);
}

function downloadTypeformSurveyResult(){
     let data = {
          action: 'exec_download_typeform_survey',
          auth_token: jQuery('.auth_token').val(),
          bucket: jQuery('.bucket').val(),
          type: 'result'
     };
     this.postCommand(data);
}

function downloadTypeformSurvey(){
     let data = {
          action: 'exec_download_typeform_survey',
          auth_token: jQuery('.auth_token').val(),
          bucket: jQuery('.bucket').val(),
          type: 'form'
     };
     this.postCommand(data);
}

function constructTypeformSurvey(){
     let data = {
          action: 'exec_construct_typeform_survey',
          survey_file_name: jQuery('.filename').val()
     };
     this.postCommand(data);
}

function postCommand(data, suc){
     jQuery('.messages').html(sprintf('<span>%s</span>', __service.__('wait')));
     jQuery.post('/wp-admin/admin-post.php', data, function(e){
          e = jQuery.parseJSON(e);
          switch(e.res){
               case 'success':
                    jQuery('.messages').html(sprintf('<span>%s</span>', e.message));
                    console.log(e);
                    if(null != suc){ suc(e); }
                    break;
               case 'failed':
               default:
                    jQuery('.messages').html(sprintf('<span>%s</span>', e.message));
                    console.log(e);
                    break;
          }
     });
}

