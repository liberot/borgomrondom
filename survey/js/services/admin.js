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



