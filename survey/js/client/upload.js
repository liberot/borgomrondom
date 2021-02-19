jQuery(document).ready(function(){
     bbRenderFileupload();
});




let bbRenderFileupload = function(){

     let ref = this;

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
          // let data = bbInitImageUpload(e.dataTransfer.files);
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
          formdata.append('action', 'exec_image_upload');
          formdata.append('image_'+idx, files[idx]);
     }

     return formdata;
}



let bbParseAssets = function(){

     let ref = this;

     let parseProc = [];
     let assetCopies = [];

     let files = document.querySelector('.files').files;

     for(let idx = 0; idx < files.length; idx++){

          let file = document.querySelector('.files').files[idx];

          if(null == file){ 
               continue; 
          }

          let indx = 'image_'+idx;

          parseProc.push({ indx: indx, proc: idx, state: 0x00 });

          let r = new FileReader();
              r.onload = function(e){
                   bbScanAsset(indx, e.target.result);
              }
              r.onerror = function(e){
                   console.log('bbParseAssets(): onerror: ', e);
              }; 
              r.readAsDataURL(file);
     }
}



let bbScanAsset = function(indx, base){

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
                  base: base,
                  layoutCode: layoutCode,
                  ow: this.naturalWidth,
                  oh: this.naturalHeight,
              }

              bbBindScan(res);
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
          return false;
     }

     img.src = base;
}



let bbAssetCopies = [];
let bbBindScan = function(data){

     bbAssetCopies.push(data);

     bbRenderAssetCopies();
     bbUploadAsset();
}



let bbUploadAsset = function(base){

     let ref = this;

     let data = {

          'action': 'exec_upload_asset',
          'base': base
     }

     let cb = function(e){
          console.log(e);
     }

     bbPostData(data, cb);
}



let bbRenderAssetCopies = function(){

     let buf = '';
     for(let idx in bbAssetCopies){
          buf+= "<img src='";
          buf+= bbAssetCopies[idx].base;
          buf+= "'/>";
          console.log(bbAssetCopies[idx]);
     }

     jQuery('.asset-copies').html(buf);
}
