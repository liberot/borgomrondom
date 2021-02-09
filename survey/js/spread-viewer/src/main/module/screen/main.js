let Screen = function(controller){ 

     this.controller = controller;

     this.register = function(subscription){
          this.controller.register(subscription);
     }

     this.notify = function(message){
          this.controller.notify(message);
     }

     this.initScreen = function(msg){
          this.model.doc = msg.model;
console.log('initScreen(): ', this.model.doc);
          this.setViewSize();
          this.initLayers();
          this.render();
     }

     this.initLayers = function(){
           let temp = [];
           for(let idx in this.model.doc.assets){
                if(null == this.model.doc.assets[idx].conf.depth){
                     this.model.doc.assets[idx].conf.depth = parseInt(idx);
                }
                this.model.doc.assets[idx].conf.depth = parseInt(this.model.doc.assets[idx].conf.depth);
                temp.push({d: this.model.doc.assets[idx].conf.depth, i: parseInt(idx)});
           }
           temp.sort(function(a, b){ return a.d >= b.d });
           if(window.chrome) { temp.sort(function(a, b){ return a.d >= b.d ? 1 : -1 }); }
           this.model.layers = temp;
     }

     this.updateScreen = function(msg){
          if(null == this.model.doc){ return false; }
          this.setViewSize();
          this.render();
     }

     this.print = function(){

          let prints = [];
          this.model.currentScreen = this.model.printScreen;
          this.model.printFrame = false;

          let width = Math.ceil(
               parseFloat(
                    LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit)
               )
          );
          let height = Math.ceil(
               parseFloat(
                    LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.height, this.model.doc.unit)
               )
          );

          this.model.currentScreen.viewbox(0, 0, width, height);
          // parts

          /*
          for(let idx = 0; idx < parseInt(this.model.doc.pageSize); idx++){
               this.model.currentScreen.viewbox(0 +(idx *width), 0, width, height); 
               this.render();
               prints.push(jQuery('.printscreen').html());

          let model = {
               prints: prints,
               ppi: this.model.doc.ppi,
               width: Math.ceil(parseFloat(LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit))),
               height: Math.ceil(parseFloat(LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.height, this.model.doc.unit)))
          }
          this.notify(new Message('prints::gathered', model));
          */

          prints = [];

          width = Math.ceil(
               parseFloat(
                    LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit)  
                         *parseInt(this.model.doc.pageSize)
               )
          );
          height = Math.ceil(
               parseFloat(
                    LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.height, this.model.doc.unit)
               )
          );

          this.model.currentScreen.viewbox(0, 0, width, height); 
          this.render();
          prints.push(jQuery('.printscreen').html());

          let model = {
               prints: prints,
               ppi: this.model.doc.ppi,
               width: width,
               height: height 
          }
  
          this.notify(new Message('prints::gathered', model));

          width = Math.ceil(
               parseFloat(
                    LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit)
               )
          );

          this.model.currentScreen.viewbox(0, 0, width, height); 
          this.model.printFrame = true;
          this.model.currentScreen = this.model.screen;
     }

     this.setViewSize = function(){

          let width = Math.ceil(parseFloat(LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit)));
              width*= parseInt(this.model.doc.pageSize);

          let height = Math.ceil(parseFloat(LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.height, this.model.doc.unit)));

          this.model.screen.viewbox(0, 0, width, height); 

          let msg = '';
               msg+= this.model.doc.printSize.idx;
               msg+= ' ';
               msg+= this.model.doc.ppi;
               msg+= 'ppi';
               msg+= ' ';
               msg+= width; 
               msg+= 'px';
               msg+= ' ';
               msg+= 'x';
               msg+= ' ';
               msg+= height;
               msg+= 'px ';
               jQuery('.layout-messages').html(msg);
     }

     this.getPenX = function(){
          return this.model.penX;
     }

     this.getPenY = function(){
          return this.model.penY;
     }

     this.resetPenY = function(){
          this.model.penY = 0;
     }

     this.setPenStepY = function(size){
          this.model.penStepY = size;
     }

     this.stepY = function(){
          this.model.penY += this.model.penStepY;
     }

     this.render = function(){
          this.model.currentScreen.clear();
          for(let idx in this.model.layers){
               if(null == this.model.doc.assets[this.model.layers[idx].i]){ continue; }
               let target = this.model.doc.assets[this.model.layers[idx].i];
               switch(target.type){
                    case 'circle':
                         this.renderCircle(target);
                         break;
                    case 'poly':
                         this.renderPoly(target);
                         break;
                    case 'image':
                         this.renderImage(target);
                         break;
                    case 'text':
                         this.renderText(target);
                         break;
                    case 'path':
                         this.renderPath(target);
                         break;
               }
          }
          this.renderSelection();
     }

     this.bindImagePos = function(msg){
          this.model.currentScreen.clear();
          this.renderFrame();
          this.renderImage(msg.model);
     }

     this.bindTextPos = function(msg){
          this.model.currentScreen.clear();
          this.renderFrame();
          this.renderText(msg.model);
     }

     this.renderFrame = function() {
          if(true != this.model.printFrame){ 
               return;
          }
          let bx = LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.layout.frame.x, this.model.doc.unit);
          let by = LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.layout.frame.y, this.model.doc.unit);
          let  w = LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.width, this.model.doc.unit);
          let  h = LayoutUtil.unitToPx(this.model.doc.ppi, this.model.doc.printSize.height, this.model.doc.unit);
          for(let idx = 0; idx < parseInt(this.model.doc.pageSize); idx++){
               let xpos = (idx +0) *w +bx;
               let width = (idx +1) *w -bx;
               let ypos = by;
               let height = h -by;
               let op = 0.1 / ( 72 / parseFloat(this.model.doc.ppi));
               let l = this.model.currentScreen.line(xpos,ypos, width,ypos);
                    l.stroke({ color: '#000', width: 1, opacity: op });
                    l = this.model.currentScreen.line(width,ypos, width,height);
                    l.stroke({ color: '#000', width: 1, opacity: op });
                    l = this.model.currentScreen.line(width,height, xpos,height);
                    l.stroke({ color: '#000', width: 1, opacity: op });
                    l = this.model.currentScreen.line(xpos,height, xpos,ypos);
                    l.stroke({ color: '#000', width: 1, opacity: op });               
          }
     }

     this.renderCircle = function(target){
          let diam = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.diam), target.conf.unit);
          let xpos = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.xpos), target.conf.unit);
          let ypos = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.ypos), target.conf.unit);
          let colr = this.fetchColor(target);
          let c = this.model.currentScreen.circle(diam);
              c.move(xpos, ypos);
              c.fill(colr);
     }

     this.renderPath = function(target){

          let tmp = target.path.split(' ');
          let out = '';
          let chunk = '';
          for(let idx = 0; idx < tmp.length; idx++){
               chunk = tmp[idx];
               if(jQuery.isNumeric(chunk)){
                    chunk = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(chunk), target.conf.unit);
               }
               out+= chunk;
               out+= ' ';
          }
          let colr = this.fetchColor(target);
          let p = this.model.currentScreen.path(out);
              p.fill(colr);
     }

     this.renderPoly = function(target){

// textfield slot in hex green
          if(true == target.textfield){
               return;
          }

          let ref = this;
          let tmp = target.points.split(' ');
          let out = '';
          for(let idx = 0; idx < tmp.length; idx+= 2){
               out+= LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(tmp[idx +0]), target.conf.unit);
               out+= ',';
               out+= LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(tmp[idx +1]), target.conf.unit);
               out+= ' ';
          }
          let colr = this.fetchColor(target);
          let p = this.model.currentScreen.polygon(out);
              p.fill(colr);
     }

     this.renderSelection = function(){
          for(var idx in this.model.doc.assets){
               let target = this.model.doc.assets[idx];

               if(!target.selected){ continue; }

               let margin = 13;

               let sx = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.xpos), target.conf.unit);

               let sy = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.ypos), target.conf.unit);
                   sy-= margin;

               let rw = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.width), target.conf.unit);

               let l = '' +'0' +',' +'0' +' ';
                   l+= '' +rw  +',' +'0' +' ';

               let polyline = this.model.currentScreen.polyline(l);
                   polyline.fill('none').move(sx, sy)
                   polyline.stroke({ color: '#ff4d4d', width: 5 })
          }
     }

     this.renderImage = function(target){
          let ref = this;

          let xpos = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.xpos, target.conf.unit);
          let ypos = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.ypos, target.conf.unit);
          let width = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.width, target.conf.unit);
          let height = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.height, target.conf.unit);

          let attr = {
               'x': xpos,
               'y': ypos,
               'width': width,
               'height': height,
               'opacity': parseFloat(target.conf.opacity)
          }

          let img = this.model.currentScreen.image(target.src, function(e){
               if(!jQuery.isNumeric(target.conf.ow)){
                    target.conf.ow = parseFloat(e.target.width);
                    target.conf.oh = parseFloat(e.target.height);
                    ref.notify(new Message('asset::iloaded', { target: target } ));
               }
          });

          img.attr(attr);

          let slotX = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.slotX, target.conf.unit);
          let slotY = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.slotY, target.conf.unit);
          let slotW = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.slotW, target.conf.unit);
          let slotH = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.slotH, target.conf.unit);

          let rect = this.model.currentScreen.rect(slotW, slotH).move(slotX, slotY);

          img.clipWith(rect);
          img.on('mouseover', function(e){ console.log(e); })
     }

     this.renderCircles = function(){
          for(var idx in this.model.doc.assets){
               if('circle' != this.model.doc.assets[idx].type){ continue; }
               let target = this.model.doc.assets[idx];
               this.renderCircle(target);
          }
     }

     this.renderPolys = function(){
          for(var idx in this.model.doc.assets){
               if('poly' != this.model.doc.assets[idx].type){ continue; }
               let target = this.model.doc.assets[idx];
               this.renderPoly(target);
          }
     }

     this.renderImages = function() {
          for(var idx in this.model.doc.assets){
               if('image' != this.model.doc.assets[idx].type){ continue; }
               let target = this.model.doc.assets[idx];
               this.renderImage(target);
          }
     }

     this.renderTexts = function() {
          for(let idx in this.model.doc.assets){
               if('text' != this.model.doc.assets[idx].type){
                    continue;
               }
               this.renderText(this.model.doc.assets[idx]);
          }
     }

     this.fetchColor = function(target){
          let colr = '#000';
          if(null != target.conf.color){
          if(null != target.conf.color['cmyk']){
               target.conf.color['cmyk'].c = parseFloat(target.conf.color['cmyk'].c);
               target.conf.color['cmyk'].m = parseFloat(target.conf.color['cmyk'].m);
               target.conf.color['cmyk'].y = parseFloat(target.conf.color['cmyk'].y);
               target.conf.color['cmyk'].k = parseFloat(target.conf.color['cmyk'].k);
               colr = new SVG.Color(target.conf.color['cmyk']);
          }}
          return colr;
     }

     this.renderText = function(target){

          this.setPenStepY(LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.size, target.conf.unit));

          if(null != target.conf.font.lineHeight){
               this.setPenStepY(LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.lineHeight, target.conf.unit));
          }

          this.resetPenY();
          this.stepY();

          let colr = this.fetchColor(target);

          let font = {
               'family': target.conf.font.family,
               'letter-spacing': LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.space, target.conf.unit),
               'size': LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.size, target.conf.unit),
               'weight': parseFloat(target.conf.font.weight),
               'opacity': parseFloat(target.conf.opacity)
          }

          let xoffset = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.xpos, target.conf.unit);
          let x = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.xpos, target.conf.unit);
          let y = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.ypos, target.conf.unit);
          let width = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.width, target.conf.unit);
          let height = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.height, target.conf.unit);
          let align = target.conf.font.align;

          let xx = x;
          let yy = y;

          let buf = '';
          for(let iidx in target.spans){
               switch(align){
                    case 'left':
                         x = xoffset;
                         font.anchor = 'start';
                         break;
                    case 'right':
                         x = xoffset +parseFloat(target.spans[iidx].conf.maxLength);
                         font.anchor = 'end';
                         break;
                    case 'center':
                         x = xoffset +parseFloat(target.spans[iidx].conf.maxLength) /2;
                         font.anchor = 'middle';
                         break; 
               }
               let attr = { 'x': x, 'y': y +this.getPenY() };
               if('block' == align) {
                    attr = {
                         'x': x, 
                         'y': y +this.getPenY(),
                         'lengthAdjust': 'spacing',
                         'textLength': parseFloat(target.spans[iidx].conf.maxLength)
                    }
               }

               let text = this.model.currentScreen.text(target.spans[iidx].text).font(font).attr(attr).fill(colr);
                   text.tspan(target.spans[iidx].text).font(font).attr(attr).fill(colr);

               let rect = this.model.currentScreen.rect(width, height).move(xx, yy);
                   text.clipWith(rect);

               this.stepY();
          }
     }


     this.model = new ScreenModel();
     this.model.screen = SVG().addTo('.screen');
     this.model.printScreen = SVG().addTo('.printscreen');
     this.model.currentScreen = this.model.screen;

     this.register(new Subscription(   'exportbtn::released', 'print', this));
     this.register(new Subscription(      'document::inited', 'initScreen', this));
     this.register(new Subscription(     'pagesize::updated', 'updateScreen', this));
     this.register(new Subscription(   'recalcbtn::released', 'updateScreen', this));
     this.register(new Subscription(         'text::updated', 'updateScreen', this));
     this.register(new Subscription(          'ppi::updated', 'updateScreen', this));
     this.register(new Subscription(    'printsize::updated', 'updateScreen', this));
     this.register(new Subscription(     'arrowkey::pressed', 'updateScreen', this));
     this.register(new Subscription(     'document::updated', 'updateScreen', this));
     this.register(new Subscription('adaptclayout::released', 'updateScreen', this));
     this.register(new Subscription(         'font::updated', 'updateScreen', this));
     this.register(new Subscription(           'text::moved', 'updateScreen', this));
     this.register(new Subscription(   'mousedrag::released', 'updateScreen', this));
     this.register(new Subscription(        'asset::updated', 'updateScreen', this));
     this.register(new Subscription(          'image::moved', 'updateScreen', this));
     this.register(new Subscription(        'item::selected', 'updateScreen', this));
     this.register(new Subscription(      'asset::corrected', 'updateScreen', this));

}

let ScreenModel = function(){
     this.currentScreen;
     this.penX = 0;
     this.penY = 0;
     this.penStepY = 0;
     this.doc;
     this.layout;
     this.survey;
     this.layers;
     this.printFrame = true;
}
