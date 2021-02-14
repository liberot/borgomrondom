
function selectTargetField(ref){}



function selectTargetSurvey(ref){
     let target_survey_ref = jQuery(ref).val();
     let clazz = jQuery(ref).attr('class');
     let match = clazz.match(/bind:(.{1,128})/);
     if(null == match || null == match[1]){
          return false;
     }
     let choice_ref = match[1];
     let data = {
          action: 'exec_set_target_survey',
          choice_ref: choice_ref,
          target_survey_ref: target_survey_ref 
     }
     this.postCommand(data);
}



function deleteDB(){
     if(!confirm('This will delete BookBuilder DB and can not be undone')){
          return;
     }
     let data = { 
          action: 'exec_delete_db'
     };
     this.postCommand(data);
}



function initDB(){
     let data = { 
          action: 'exec_init_db'
     };
     this.postCommand(data);
}



function insertTypeformSurveys(){
     let data = { 
          action: 'exec_insert_typeform_surveys'
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



