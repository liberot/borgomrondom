function constructFieldingQuestions(){
      let data = { 
           action: 'exec_construct_typeform_survey',
           survey_file_name: 'fielding_questions.json'
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

