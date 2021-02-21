


jQuery(document).ready(function(){
     bbClientInit();
     bbRenderFileupload();
     bbRenderAssetCopies();
});




let bbRenderFileupload = function(){

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
          bbParseAssets();
     });

     form.addEventListener('change', function(e){
          // let data = bbInitImageUpload(form.files);
          bbParseAssets();
     });

}



let bbInitImageUpload = function(files){

     let formdata = new FormData();
     for(let idx in files){
          formdata.append('action', 'bb_upload_asset');
          formdata.append('image_'+idx, files[idx]);
     }

     return formdata;
}



let bbParseAssets = function(){

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
                   bbScanAsset(index, e.target.result);
              }
              r.onerror = function(e){
                   console.log('bbParseAssets(): onerror: ', e);
              }; 
              r.readAsDataURL(file);
     }
}



let bbScanAsset = function(index, base){

     let scaleR = 0.33;

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

              bbBindScan(res);
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



let bbBindScan = function(scan){

     bbAssetCopies.push(scan);
     bbRenderAssetCopies(scan);
     bbUploadAsset(scan);
}



let bbUploadAsset = function(scan){

     let ref = this;

     let data = {
          'action': 'bb_upload_asset',
          'scan': scan 
     }

     let cb = function(e){
          // window.location.reload();
     }

     bbPostData(data, cb);
}



let bbRenderAssetCopies = function(scan){

     let buf = '';

     for(let idx in bbAssetCopies){
          buf+= "<img src='";
          if(null != bbAssetCopies[idx]['doc']){
               buf+= bbAssetCopies[idx]['doc'];
          }
          else if(null != bbAssetCopies[idx]['base']){
               buf+= bbAssetCopies[idx]['base'];
          }
          buf+= "'/>";
     }

     jQuery('.asset-copies').html(buf);
}



let bbAssetCopies = [];
let bbClientInit = function(){
     window.addEventListener('hashchange', function(e){ ref.bindHashChange(e); });
     history.pushState(null, null, window.location.href);
     window.onpopstate = function(e){
          history.pushState(null, null, window.location.href);
          bbPostData(
               { 
                    'action': 'bb_nav_prev_field'
               },
               function(e){
                    window.location.reload();
               },
               function(e){ 
                    console.log(e); 
               }
          );
     };
     if(null != assetsOfField){
          bbAssetCopies = assetsOfField;
     }
}



let bbPostData = function(data, suc, err){
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



