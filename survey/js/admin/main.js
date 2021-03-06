let BBAdmin = {};



BBAdmin.bbSelectRootSurvey = function(ref){
     let target_survey_title = jQuery(ref).val();
     let data = {
          action: 'bb_set_root_survey',
          target_survey_title: target_survey_title
     }
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbSelectTargetField = function(ref){
}



BBAdmin.bbSelectTargetSurvey = function(ref){
     let target_survey_ref = jQuery(ref).val();
     let clazz = jQuery(ref).attr('class');
     let match = clazz.match(/bind:(.{1,128})/);
     if(null == match || null == match[1]){
          return false;
     }
     let choice_ref = match[1];
     let data = {
          action: 'bb_set_target_survey',
          choice_ref: choice_ref,
          target_survey_ref: target_survey_ref 
     }
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbDeleteDB = function(){
     if(!confirm('This will delete BookBuilder DB and can not be undone')){
          return;
     }
     let data = { 
          action: 'bb_delete_db'
     };
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbInitDB = function(){
     let data = { 
          action: 'bb_init_db'
     };
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbInitPage = function(){
     let data = { 
          action: 'bb_init_page'
     };
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbInsertTypeformSurveys = function(){
     let data = { 
          action: 'bb_insert_typeform_surveys'
     };
     BBAdmin.bbPostCommand(data);
}



BBAdmin.bbPostCommand = function(data, suc){
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



