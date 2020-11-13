class Model{}
class View {}

class Controller {

     constructor ( queue ) {
          this.queue = queue;
     }

     notify( message ) {
          this.queue.notify ( message );
     }

     register( subscription ) {
          subscription.ref = this;
          this.queue.register ( subscription );
     }

     release( subscription ) {
          subscription.ref = this;
          this.queue.release ( subscription );
     }

     releaseAllSubscriptions () {
          this.queue.releaseAllSubscriptions ( this );
     }
     
     facMessage ( title, model ) {
          return new Message( title, model );
     }

     facSubscription ( title, callback ) {
          return new Subscription( title, callback );
     }

     fillTemplate ( template, model ) {
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

     sync ( model ) {
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

class Queue {

     constructor () { 
          this.subscriptions = [];
     }

     notify ( message ) {
          // console.log(message);

          for( let idx in this.subscriptions ) {
               if( message.title == this.subscriptions[ idx ].title ) {
                    // let method = this.subscriptions[ idx ].callback.match(/^f\s+(.{1,64})\(/); 
                    let ref = this.subscriptions[ idx ].ref;
                    let method = this.subscriptions[ idx ].callback.name;
                    // ref[ method ] = this.subscriptions[ idx ].callback;
                    ref[ method ]( message );
               }
          }
     }

     register( subscription ) {
          this.subscriptions.push( subscription );
     }

     release( subscription ) {
          let tmp = [];
          for( var idx in this.subscriptions ) {
               if ( subscription.ref == this.subscriptions[ idx ].ref && 
                         subscription.title == this.subscriptions[ idx ].title && 
                         subscription.callback == this.subscriptions[ idx ].callback  
                    ) {
                         continue;
               }
               tmp.push( this.subscriptions[ idx ] );
          }
          this.subscriptions = tmp;
     }

     releaseAllSubscriptions ( ref ) {
          let tmp = [];
          for( var idx in this.subscriptions ) {
               if ( ref == this.subscriptions[ idx ].ref ) {
                         continue;
               }
               tmp.push( this.subscriptions[ idx ] );
          }
          this.subscriptions = tmp;

     }

     route( title ) {
          let model = { date: new Date(), arguments: arguments };
          // ...
          this.notify( new Message( title, model ) );
     }
}

class Message {

     constructor ( title, model ) {
          this.title = title; 
          this.model = model;
     } 
}

class Subscription {

     constructor ( title, callback ) {
          this.title = title; 
          this.callback = callback;
     }      
}

