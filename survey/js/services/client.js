function initSession(surveyId){
     let data = {
          action: 'exec_init_survey_thread',
          survey_id: surveyId
     };
     postCommand(data, function(e){
          console.log(e.threadId);
          window.location.href = '/thread/'+e.threadId;
     });
}

function loadSession(threadId){
     window.location.href = '/thread/'+threadId;
}

function postCommand(data, cb){
     jQuery('.messages').html('wait...');
     jQuery.post('/wp-admin/admin-post.php', data, function(e){
     e = jQuery.parseJSON(e);
          switch(e.res){
               case 'success':
                    jQuery('.messages').html(e.message);
                    console.log(e);
                    cb(e);
                    break;
               case 'failed':
               default:
                    jQuery('.messages').html(e.message);
                    console.log(e);
                    break;
          }
     });
}
