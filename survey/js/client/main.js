let BBClient = {};



BBClient.btnSubmitRecPressed = function(e){

     let types = ['text', 'textinput', 'textarea'];
     let activeElement = document.activeElement;
     if(null != activeElement){
          if(null != activeElement.type){
               if(-1 !== types.indexOf(activeElement.type.toLowerCase())){
                    return false;
               }
          }
     }

     let btnSubmitRec = jQuery('.btn-submit-rec');
     if(null == btnSubmitRec){ 
          return false;
     }

     let clientInput = jQuery('.client-input-form');
     if(null == clientInput){
          return false;
     }

     clientInput.submit();
}



BBClient.bbSetupKeys = function(){

     let ref = this;

     jQuery(document).off('keydown');
     jQuery(document).off('keyup');

     jQuery(document).keydown(function(e){

          switch(e.key){

               case 'Shift':
               case 'Meta':
                    console.log('modifier: ', e.key);
                    break;
          }
     });

     jQuery(document).keyup(function(e){

          switch(e.key){

               case '0':
               case '1':
               case '2':
               case '3':
               case '4':
               case '5':
               case '6':
               case '7':
               case '8':
               case '9':
                    console.log('numpad: ', e.key);
                    break;

               case 'b':
               case 'B':
                    BBClient.btnSubmitRecPressed(e);
                    break;

               case 'Shift':
               case 'Meta':
                    console.log('modifier: ', e.key);
                    break;

               case 'Escape':
                    console.log('esc: ', e.key);
                    break;

               case 'Enter':
                    console.log('enter: ', e.key);
                    break;

               case 'ArrowLeft':
               case 'ArrowRight':
               case 'ArrowUp':
               case 'ArrowDown':
                    console.log('arrow: ', e.key);
                    break;
          }
     });
}



BBClient.bbRenderFileupload = function(){

     let form = document.querySelector('.files');
     let fake = document.querySelector('.fake');

     if(null == form || null == fake) {
          return;
     }

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
          // let data = bbInitImageUpload(e.dataTransfer.files);
          document.querySelector('.files').files = e.dataTransfer.files;
          BBClient.bbParseAssets();
     });

     form.addEventListener('change', function(e){
          // let data = bbInitImageUpload(form.files);
          BBClient.bbParseAssets();
     });

}



BBClient.bbInitImageUpload = function(files){

     let formdata = new FormData();
     for(let idx in files){
          formdata.append('action', 'bb_upload_asset');
          formdata.append('image_'+idx, files[idx]);
     }

     return formdata;
}



BBClient.bbParseAssets = function(){

     let assetCopies = [];
     let files = document.querySelector('.files').files;

     for(let idx = 0; idx < files.length; idx++){

          let file = document.querySelector('.files').files[idx];
          if(null == file){ 
               continue; 
          }

          let index = 'image_'+idx;

          let r = new FileReader();
              r.onload = function(e){
                   BBClient.bbScanAsset(index, e.target.result);
              }
              r.onerror = function(e){
                   console.log('bbParseAssets(): onerror: ', e);
              }; 
              r.readAsDataURL(file);
     }
}



BBClient.bbScanAsset = function(index, base){

     // let scaleR = 0.33;
     let scaleR = 1.00;

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
                  index: index,
                  base: base,
                  layout_code: layoutCode,
                  width: this.naturalWidth,
                  height: this.naturalHeight,
              }

              BBClient.bbBindScan(res);
        }

     let acceptedAssetType = false;

     if(null != base.match(/^data:image\/png;base64/)){
          base = base.replace('data:image/png;base64,', '');
          base = 'data:image/png;base64,' +base;
          acceptedAssetType = true;
     }

     if(false == acceptedAssetType){
          return false;
     }

     img.src = base;
}



BBClient.bbBindScan = function(scan){

     BBClient.bbAssetCopies.push(scan);
     BBClient.bbRenderAssetCopies(scan);
     BBClient.bbUploadAsset(scan);
}



BBClient.bbUploadAsset = function(scan){

     let ref = this;

     let data = {
          'action': 'bb_upload_asset',
          'scan': scan 
     }

     let cb = function(e){
          console.log(e);
          // window.location.reload();
     }

     BBClient.bbPostData(data, cb);
}



BBClient.bbRenderAssetCopies = function(scan){

     let buf = '';

     buf+= '<div>';
     for(let idx in BBClient.bbAssetCopies){
          buf+= "<img src='";
          if(null != BBClient.bbAssetCopies[idx]['doc']){
               buf+= BBClient.bbAssetCopies[idx]['doc'];
          }
          else if(null != BBClient.bbAssetCopies[idx]['base']){
               buf+= BBClient.bbAssetCopies[idx]['base'];
          }
          buf+= "'/>";
     }
     buf+= '</div>';

     jQuery('.asset-copies').html(buf);
}



BBClient.bbInitClient = function(){
     window.addEventListener('hashchange', function(e){ ref.bindHashChange(e); });
     history.pushState(null, null, window.location.href);
     window.onpopstate = function(e){
          history.pushState(null, null, window.location.href);
          BBClient.bbPostData(
               { 
                    'action': 'bb_nav_prev_field'
               },
               function(e){
                    console.log(e);
                    window.location.reload();
               },
               function(e){ 
                    console.log(e); 
               }
          );
     };
     BBClient.bbAssetCopies = null == assetsOfField ? [] : assetsOfField;
}



BBClient.bbPostData = function(data, suc, err){
     let serviceURL = '/wp-content/plugins/bookbuilder/survey/include2nd/services/post.php';
     let ref = this;
     jQuery('.layout-messages').html('wait...');
     jQuery.post(serviceURL, data, function(e){
          e = jQuery.parseJSON(e);
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



jQuery(document).ready(function(){
     BBClient.bbInitClient();
     BBClient.bbRenderFileupload();
     BBClient.bbRenderAssetCopies();
     BBClient.bbSetupKeys();
});



