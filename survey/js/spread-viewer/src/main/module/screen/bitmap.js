let Bitmap = function(){

     this.register = function(subscription){}
     this.notify = function(message){}

     this.model = new BitmapModel();
     this.register(new Subscription('image::targeted', this.renderImage));
     this.model.images = [];

     this.initImage = function(msg){
     }

     this.getImage = function(index, tag){
          let res = null;
          if(null != this.model.images[index]){
               if(null != this.model.images[index][tag]){
                    return this.model.images[index][tag]
               }
          }
          return res;
     }

     this.renderImage = function(msg){
          let src = msg.model.src;
          let scale = msg.model.scale;
          let iref = msg.model.ref;
          let ref = this;
          let img = new Image();
              img.onload = function(){
                   let canvas = document.createElement('canvas');
                       canvas.width = this.naturalWidth *scale;
                       canvas.height = this.naturalHeight *scale;
                   let ctx = canvas.getContext('2d');
                       ctx.drawImage(this, 0, 0, this.naturalWidth *scale, this.naturalHeight *scale);

                   let res = canvas.toDataURL('image/png');
                   let model = {
                       'src': src,
                       'res': res,
                       'ref': iref
                   }
                   ref.notify(new Message('image::rendered', model));
                   return res;
              }
              img.src = src;
     }
}

let BitmapModel = function(){
     this.images = null;
}
