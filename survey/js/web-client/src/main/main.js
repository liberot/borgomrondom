let Controller = function(queue){
     this.queue = queue;
     this.notify = function(message){
          this.queue.notify(message);
     }
     this.register = function(subscription){
          this.queue.register(subscription);
     }
     this.release = function(subscription){
          this.queue.release (subscription);
     }
     this.releaseAllSubscriptions = function(){
          this.queue.releaseAllSubscriptions(this);
     }
     this.facMessage = function(title, model){
          return new Message(title, model);
     }
     this.facSubscription = function(title, callback){
          return new Subscription(title, callback);
     }
     this.fillTemplate = function(template, model){
          let vars = template.match(/\{(.{1,32}?)\}/g);
          let view = template;
          for( var idx in vars ){
               let index = vars[ idx ];
               let title = vars[ idx ].replace( /[\{\}]/g, '' );
               let value = model[ title ];
               if( null != title && null != value ) {
                    view = view.replace( index, value );
               }
          }
          return view;
     }
     this.sync = function(model){
          let ref = this;
          let service = '';
          let req = new XMLHttpRequest();
               req.open( 'POST', service, true );
               req.setRequestHeader( 'Content-type', 'application/json' );
               req.onreadystatechange = function() {
                    if( 4 == req.readyState && 200 == req.status ) {
                         ref.notify( new Message( 'MODEL_SYNCED', req.responseText ) );
                    }
               }
          req.send( model );
     }
}
let Queue = function(){
     this.subscriptions = [];
     this.notify = function(message){
          for(let idx in this.subscriptions) {
console.log('>', message.title, 'x', this.subscriptions[idx].title);
               if(message.title == this.subscriptions[idx].title){
console.log('-->', this.subscriptions[idx]);
                    // let method = this.subscriptions[ idx ].callback.match(/^f\s+(.{1,64})\(/); 
                    // ref[method] = this.subscriptions[idx].callback;
                    // this.subscriptions[idx].callback(message);
                    let ref = this.subscriptions[idx].ref;
                    let method = this.subscriptions[idx].callback;
                    ref[method](message);
               }
          }
     }
     this.register = function(subscription){
          this.subscriptions.push(subscription);
     }
     this.release = function(subscription){
          let tmp = [];
          for(let idx in this.subscriptions) {
               if (subscription.ref == this.subscriptions[idx].ref &&
                         subscription.title == this.subscriptions[idx].title &&
                         subscription.callback == this.subscriptions[idx].callback
                    ){
                         continue;
               }
               tmp.push(this.subscriptions[idx]);
          }
          this.subscriptions = tmp;
     }
     this.releaseAllSubscriptions = function(ref){
          let tmp = [];
          for(let idx in this.subscriptions){
               if (ref == this.subscriptions[idx].ref){
                         continue;
               }
               tmp.push(this.subscriptions[idx]);
          }
          this.subscriptions = tmp;

     }
     this.route = function(title){
          let model = { date: new Date(), arguments: arguments };
          this.notify(new Message(title, model));
     }
}
let Message = function(title, model){
     this.title = title; 
     this.model = model;
}
let Subscription = function(title, callback, ref){
     this.title = title; 
     this.callback = callback;
     this.ref = ref;
}

