


function bbClientInit(){
     window.addEventListener('hashchange', function(e){ ref.bindHashChange(e); });
     history.pushState(null, null, window.location.href);
     window.onpopstate = function(e){
          history.pushState(null, null, window.location.href);
          bbPostData(
               { 
                    'action': 'exec_nav_prev_field'
               },
               function(e){ 
                    console.log(e); 
               },
               function(e){ 
                    console.log(e); 
               }
          );
     };
}



jQuery(document).ready(function(){
     bbClientInit();
});



function bbPostData(data, suc, err){
     let serviceURL = '/wp-content/plugins/bookbuilder/survey/include2nd/services/post.php';
     let ref = this;
     jQuery('.layout-messages').html('wait...');
     jQuery.post(serviceURL, data, function(e){
          e = jQuery.parseJSON(e);
console.log('postData(): e: ', e);
          switch(e.res){
               case 'success':
                    null != suc ? suc(e) : false;
                    break;
               case 'failed':
               default:
                    null != err ? err() : false;
                    break;
          }
     });
}

