let Tools = function(controller){

     this.controller = controller;

     this.fillTemplate = function(template, model){
         return this.controller.fillTemplate(template, model);
     }

     this.register = function(subscription){
          this.controller.register(subscription);
     }

     this.notify = function(message){
          this.controller.notify(message);
     }

     this.setupAssetPossibs = function(){
          let keys = ['P', 'L'];
          let tab0 = [];
          let tab1 = [];
          let tab2 = [];
          let tab3 = [];
          let tab4 = [];
          let node = function(name){ this.name = name; this.childs = []; }
          let tree = new node('x');
          for(let idx = 0; idx < keys.length; idx++){
              tree.childs[idx] = new node(keys[idx]);
              tab1.push([keys[idx]]);
              for(let iidx = 0; iidx < keys.length; iidx++){
                   tree.childs[idx].childs[iidx] = new node(keys[iidx]);
                   tab2.push([keys[idx], keys[iidx]]);
                   for(let iiidx = 0; iiidx < keys.length; iiidx++){
                        tree.childs[idx].childs[iidx].childs[iiidx] = new node(keys[iiidx]);
                        tab3.push([keys[idx], keys[iidx], keys[iiidx]]);
                        for(let iiiidx = 0; iiiidx < keys.length; iiiidx++){
                             tree.childs[idx].childs[iidx].childs[iiidx].childs[iiiidx] = new node(keys[iiiidx]);
                             tab4.push([keys[idx], keys[iidx], keys[iiidx], keys[iiiidx]]);
                        }
                   }
              }
          }
          this.model.layoutDescriptor = [ tab0, tab1, tab2, tab3, tab4 ];
     }

     this.loadLocalDocument = function(){
          this.model.doc = new MockModel().model;
          this.initDocument();
     }

     this.initDocument = function(){
          switch(SpreadViewerConfig.mode){
               case SpreadViewerConfig.WEB_CLIENT:
                    this.setupNavigation();
                    break;
               case SpreadViewerConfig.SPREAD_CLIENT:
                    this.setupTools();
                    this.setupLibrary();
                    break;
               case SpreadViewerConfig.LAYOUT_CLIENT:
                    this.setupTools();
                    this.setupLibrary();
                    break;
          }
          this.selectLibraryItem(null);
          this.notify(new Message('document::inited', this.model.doc));
          jQuery('.select_ppi select').val(this.model.doc.ppi);
     }

     this.setupNavigation = function(){
          jQuery('.layout-pages').html(__tool__991__tmpl);
     }

     this.bindImportedLayouts = function(msg){
 console.log('bindImportedLayouts(): ', msg);
          jQuery('.layout-messages').html(msg.model.rules.join('; '));
     }

     this.getLayoutIndex = function(coll){
          let ldx = coll.length;
          if(ldx < 0 || ldx >= this.model.layoutDescriptor.length -1){
              return null;
          }
          let tmp = this.model.layoutDescriptor[ldx];
          let srk = coll.join('');
          for(let idx in tmp){
               if(tmp[idx].join('') == srk){
                    return { idx: idx, ldx: ldx };
               }
          }
          return null;
     }

     this.bindUnit = function(msg){
          if(null == this.model.selectedLibraryItem){
               return;
          }
          switch(msg.model.arguments[1]){
               case 'inch':
                    this.switchUnit(this.model.selectedLibraryItem, 'inch');
                    break;
               case 'mm':
                    this.switchUnit(this.model.selectedLibraryItem, 'mm');
                    break;
               case 'px':
                    this.switchUnit(this.model.selectedLibraryItem, 'px');
                    break;
          }
          this.updateEditor();
     }

     this.loadSession = function(){
          let coll = window.location.href.split('/');
          if(coll.length >= 4){
               this.notify(new Message('thread::requested', { threadId: coll[4] } ));
          }
     }

     this.lockControlKeys = function(msg){
          this.model.controlKeysLocked = true;
     }

     this.unlockControlKeys = function(msg){
          this.model.controlKeysLocked = false;
     }

     this.bindModifier = function(key){
          this.model.modifierKey = key;
     }

     this.releaseModifier = function(key){
          this.model.modifierKey = null;
     }

     this.selectAsset = function(key){
          let idx = parseInt(parseInt(key) -1);
          if(null != this.model.doc.assets[idx]){
               let indx = this.model.doc.assets[idx].indx;
               let model = { arguments: [ 'select::asset', indx ]};
               this.notify(new Message('select::asset', model ));
          };
     }

     this.handleArrowKeydown = function(key){
          switch(key){

               case 'ArrowLeft':
                    if(null == this.model.selectedLibraryItem){
                         this.prevSpread();
                         return;
                    }
                    this.transformSelectedItem(-1, +0, +0, +0);
                    break;

               case 'ArrowRight':
                    if(null == this.model.selectedLibraryItem){
                         this.nextSpread();
                         return;
                    }
                    this.transformSelectedItem(+1, +0, +0, +0);
                    break;

               case 'ArrowDown':
                    this.transformSelectedItem(+0, +1, +0, +0);
                    break;

               case 'ArrowUp':
                    this.transformSelectedItem(+0, -1, +0, +0);
                    break;

          }
          this.notify(new Message('arrowkey::pressed', this.model.doc));
     }

     this.transformSelectedItem = function(xpos, ypos, width, height){
          if(null == this.model.selectedLibraryItem){
               return;
          }
          let r = 1;
          switch(this.model.selectedLibraryItem.unit){
               case 'px':
                    r *= 100;
               case 'mm':
               case 'unit':
                    break;
          }
          switch(this.model.modifierKey){
               case 'Shift':
                    r *= 10;
                    break;
          }
          this.model.selectedLibraryItem.conf.xpos = parseFloat(this.model.selectedLibraryItem.conf.xpos);
          this.model.selectedLibraryItem.conf.ypos = parseFloat(this.model.selectedLibraryItem.conf.ypos);
          this.model.selectedLibraryItem.conf.width = parseFloat(this.model.selectedLibraryItem.conf.width);
          this.model.selectedLibraryItem.conf.height = parseFloat(this.model.selectedLibraryItem.conf.height);
          this.model.selectedLibraryItem.conf.xpos += parseFloat(xpos) *r;
          this.model.selectedLibraryItem.conf.ypos += parseFloat(ypos) *r;
          this.model.selectedLibraryItem.conf.width += parseFloat(width) *r;
          this.model.selectedLibraryItem.conf.height += parseFloat(height) *r;
     }

     this.setupMouseControls = function(){

          let ref = this;
          jQuery(document).off('mouseup');
          jQuery(document).off('mousedown');
          jQuery(document).off('mousemove');

          jQuery(document).mouseup(function(){

               if(null == ref.model.mouseDownRec){ return; }

               if(SpreadViewerConfig.quantize){
                    ref.model.selectedLibraryItem.conf.xpos-= ref.model.selectedLibraryItem.conf.xpos %5;
                    ref.model.selectedLibraryItem.conf.ypos-= ref.model.selectedLibraryItem.conf.ypos %5;
                    ref.model.selectedLibraryItem.conf.xpos = parseInt(ref.model.selectedLibraryItem.conf.xpos);
                    ref.model.selectedLibraryItem.conf.ypos = parseInt(ref.model.selectedLibraryItem.conf.ypos);
               }

               ref.model.mouseDownRec = null;
               ref.notify(new Message('mousedrag::released', ref.model.doc));
          });

          jQuery('.screen').mousedown(function(e){
               // ref.model.selectedLibraryItem = ref.selectLibraryItemByMouse(arguments[0].clientX, arguments[0].clientY);
               if(null == ref.model.selectedLibraryItem){ return; }
               ref.switchUnit(ref.model.selectedLibraryItem, ref.model.doc.unit);

               ref.model.mouseDownRec = { 
                    x: parseFloat(arguments[0].clientX), 
                    y: parseFloat(arguments[0].clientY), 
                    ctrlKey: arguments[0].ctrlKey,
                    xpos: parseFloat(ref.model.selectedLibraryItem.conf.xpos),
                    ypos: parseFloat(ref.model.selectedLibraryItem.conf.ypos),
                    width: parseFloat(ref.model.selectedLibraryItem.conf.width),
                    height: parseFloat(ref.model.selectedLibraryItem.conf.height),
                    sw: jQuery('.screen').width(),
                    sh: jQuery('.screen').height(),
                    unit: ref.model.selectedLibraryItem.conf.unit
               };
          });

          jQuery('.screen').mousemove(function(){

               if(null == ref.model.mouseDownRec || null == ref.model.doc){ return; }

               let xmove  = parseFloat(arguments[0].clientX) -parseFloat(ref.model.mouseDownRec.x);
                   xmove /= parseFloat(parseFloat(ref.model.mouseDownRec.sw)
                              /parseFloat(ref.model.doc.printSize.width))
                              /parseInt(ref.model.doc.pageSize)

               let ymove  = parseFloat(arguments[0].clientY) -parseFloat(ref.model.mouseDownRec.y);
                   ymove /= parseFloat(parseFloat(ref.model.mouseDownRec.sh) /parseFloat(ref.model.doc.printSize.height));

               let xpos = parseFloat(ref.model.mouseDownRec.xpos) +xmove; 
               let ypos = parseFloat(ref.model.mouseDownRec.ypos) +ymove

               ref.model.selectedLibraryItem.conf.xpos = xpos;
               ref.model.selectedLibraryItem.conf.ypos = ypos;

               switch(ref.model.selectedLibraryItem.type){

                    case 'image':
                         ref.notify(new Message('image::moved', ref.model.selectedLibraryItem));
                         break;

                    case 'text':
                         ref.notify(new Message('text::moved', ref.model.selectedLibraryItem));
                         break;
               }
          });
     }

     this.selectLibraryItemByMouse = function(mx, my){
          if(null != this.model.selectedLibraryItem){
               return this.model.selectedLibraryItem;
          }
          let res = null;
          let offset = jQuery('.screen').offset();
          let posY = offset.top -jQuery(window).scrollTop();
          let posX = offset.left -jQuery(window).scrollLeft(); 

          for(let idx in this.model.doc.assets){
              let target = this.model.doc.assets[idx];
              let tmp, mxx, myy, lft, top;
              lft = LayoutUtil.unitToPx(this.model.doc.ppi, posX, target.conf.unit);
              top = LayoutUtil.unitToPx(this.model.doc.ppi, posY, target.conf.unit);
              mxx = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(mx), target.conf.unit) -lft;
              mxx/= parseInt(this.model.doc.pageSize);
              myy = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(my), target.conf.unit) -top;

              tmp = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.xpos), target.conf.unit);
              if(mxx <= tmp){
                   continue;
              }
              tmp = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.xpos) +parseFloat(target.conf.width), target.conf.unit);
              if(mxx >= tmp){
                   continue;
              }
              tmp = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.ypos));
              if(myy <= tmp){
                   continue;
              }
              tmp = LayoutUtil.unitToPx(this.model.doc.ppi, parseFloat(target.conf.ypos) +parseFloat(target.conf.width), target.conf.unit);
              if(myy >= tmp){
                   continue;
              }
              this.selectLibraryItem(target);
              return target;
          }
     }

     this.setupKeyControls = function(){
          let ref = this;
          jQuery(document).off('keydown');
          jQuery(document).keydown(function(e){
               if(true == ref.model.controlKeysLocked){
                    return;
               }
               switch(e.key){
                    case 'ArrowLeft':
                    case 'ArrowRight':
                    case 'ArrowUp':
                    case 'ArrowDown':
                         ref.handleArrowKeydown(e.key);
                         break;
               }
               switch(e.key){
                    case 'Shift':
                    case 'Meta':
                         ref.bindModifier(e.key);
                         break;
               }
          });
          jQuery(document).keyup(function(e){
               if(true == ref.model.controlKeysLocked){
                    return;
               }
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
                         ref.selectAsset(e.key);
                         break;
                    case 'Shift':
                    case 'Meta':
                         ref.releaseModifier();
                         break;
                    case 'Escape':
                         ref.closeEditor();
                         break;
                    case 'Enter':
                         // ref.saveDocument();
                         break;
               }
          });
     }

     this.bindPageSize = function(msg){
          this.model.doc.pageSize = parseInt(msg.model.pageSize);
          jQuery('.select_page select').val(this.model.doc.pageSize);
          this.notify(new Message('pagesize::updated', this.model.doc.pageSize));
     }

     this.bindRenderedImageAsset = function(msg){
          for(let idx in this.model.doc.assets){
               if(msg.model.ref == this.model.doc.assets[idx].indx){
                    this.model.doc.assets[idx].locator = this.model.doc.assets[idx].src;
                    this.model.doc.assets[idx].src = msg.model.res;
               }
          }
     }

     this.bindImageAsset = function(msg){
          this.model.doc.assets.push(msg.model);
          this.selectedLibraryItemByIndx(msg.model.indx);
          this.notify(new Message('asset::updated', this.model.doc));
     }

     this.bindBook = function(msg){

          if(null == msg.model.book){
console.log('bindBook(): no book');
              return false;
          }

          this.model.book = msg.model.book;
          if(null == msg.model.chapter){
console.log('bindBook(): no chapter');
              return false;
          }

          this.model.chapter = msg.model.chapter;
          if(null == msg.model.toc){
console.log('bindBook(): no toc');
              return false;
          }

console.log('bindBook(): msg.model: ', msg.model);
          this.model.toc = msg.model.toc[0];

          this.model.toc.post_content = LayoutUtil.pagpick(this.model.toc.post_content);
console.log('bindBook(): this.model.toc: ', this.model.toc);

          this.model.spreads = [];
          this.model.spidx = 0;
// todo 
// the first chapter
          for(let idx in this.model.chapter){
               this.model.spreads = this.model.spreads.concat(this.model.chapter[idx].spreads);
          }
          for(let idx in this.model.spreads){
               this.model.spreads[idx].post_content = LayoutUtil.pagpick(this.model.spreads[idx].post_content);
          }
// todo......
          this.loadSpread();
     }

     this.nextSpread = function(){
          if(null == this.model.spreads){ return; }
          this.model.spidx +=1
          if(this.model.spidx >= this.model.toc.post_content.spread_refs.length){
               this.model.spidx = this.model.toc.post_content.spread_refs.length -1;
          }
          this.loadSpread();
     }

     this.prevSpread = function(){
          if(null == this.model.spreads){ return; }
          this.model.spidx -=1;
          if(this.model.spidx <= 0){ this.model.spidx = 0; }
          this.loadSpread();
     }
    
     this.evalSpread = function(pos){
          if(null == this.model.spreads){ return; }
          let ref = this.model.toc.post_content.spread_refs[pos];
          let res = null;
          for(let idx in this.model.spreads){
               if(this.model.spreads[idx].post_excerpt == ref){
                    return this.model.spreads[idx];
               }
          }
          return null;
     }
 
     this.loadSpread = function(){
          if(null == this.model.spreads){ return; }

          this.model.spread = this.evalSpread(this.model.spidx);

          if(null == this.model.spread){ return; }
          this.model.doc = this.model.spread.post_content;
          for(let idx in this.model.doc.assets){
               let target = this.model.doc.assets[idx]; 
               switch(target.type){
                    case 'image':
                         this.initAsset(target);
                    break;
                    case 'text':
                         for(let iidx in target.text){
                              target.text[iidx] = 
                                   LayoutUtil.sanitizePrint(target.text[iidx]);
                         }
                         break;
               }
          }
          this.initDocument();
     }

     this.initAsset = function(asset){
     }

     this.saveDocument = function(msg){
          let model = {
               doc: this.model.doc
          }
          this.notify(new Message('save::document', model));
     }

     this.saveLayout = function(msg){
          let rule = this.model.layoutDescriptor[this.model.selectedLayoutImageSize][this.model.selectedLayoutRule];
          if(null == rule){ return false; };
              rule = rule.join('');
          let model = {
               group: 'default',
               rule: rule, 
               doc: this.model.doc
          }
          this.notify(new Message('save::layout', model));
     }

     this.bindLayoutGroup = function(msg){
          this.model.selectedLayoutGroupName = 'default';
          let model = {
               group: this.model.selectedLayoutGroupName
          }
          this.notify(new Message('load::layoutgroup', model));
     }

     this.bindLoadedLayoutGroup = function(msg){
          this.model.loadedLayoutGroup = msg.model;
          for(let idx in this.model.loadedLayoutGroup){
               this.model.loadedLayoutGroup[idx].post_content = LayoutUtil.pagpick(this.model.loadedLayoutGroup[idx].post_content);
          }
     }

     this.bindLoadedLayoutPresets = function(msg){
          this.model.loadedLayoutPresets = msg.model;
          if(null == this.model.loadedLayoutPresets){ return false; }
          if(null == this.model.loadedLayoutPresets[0]){
               this.model.doc = new MockModel().model;
               this.initDocument();
               return false;
          }
          this.model.doc = LayoutUtil.pagpick(this.model.loadedLayoutPresets[0].post_content);
          this.initDocument();
     }

     this.switchUnit = function(asset, unit){
          asset.conf.xpos = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.xpos, asset.conf.unit);
          asset.conf.ypos = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.ypos, asset.conf.unit);
          asset.conf.xpos = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.xpos, unit);
          asset.conf.ypos = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.ypos, unit);
          asset.conf.width = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.width, asset.conf.unit);
          asset.conf.height = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.height, asset.conf.unit);
          asset.conf.width = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.width, unit);
          asset.conf.height = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.height, unit);
          if('text' == asset.type){
               asset.conf.font.size = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.font.size, asset.conf.unit);
               asset.conf.font.size = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.font.size, unit);
               asset.conf.font.space = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.font.space, asset.conf.unit);
               asset.conf.font.space = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.font.space, unit);
               asset.conf.font.lineHeight = LayoutUtil.unitToPx(this.model.doc.ppi, asset.conf.font.lineHeight, asset.conf.unit);
               asset.conf.font.lineHeight = LayoutUtil.pxToUnit(this.model.doc.ppi, asset.conf.font.lineHeight, unit);
          }
          asset.conf.unit = unit;
          return asset;
     }

     this.setPpi = function(msg){

          if(null == this.model.doc){
               return;
          }

          if('px' == this.model.doc.unit){
               this.model.doc.printSize.width = parseInt(LayoutUtil.pxPump(this.model.doc.printSize.width, this.model.doc.ppi, msg.model.ppiSize));
               this.model.doc.printSize.height = parseInt(LayoutUtil.pxPump(this.model.doc.printSize.height, this.model.doc.ppi, msg.model.ppiSize));
          }

          for(let idx in this.model.doc.assets){
               if('px' == this.model.doc.assets[idx].conf.unit){
                    let target = this.model.doc.assets[idx];
                        target.conf.xpos = parseFloat(LayoutUtil.pxPump(target.conf.xpos, this.model.doc.ppi, msg.model.ppiSize));
                        target.conf.ypos = parseFloat(LayoutUtil.pxPump(target.conf.ypos, this.model.doc.ppi, msg.model.ppiSize));
                        target.conf.height = parseFloat(LayoutUtil.pxPump(target.conf.height, this.model.doc.ppi, msg.model.ppiSize));
                        target.conf.width = parseFloat(LayoutUtil.pxPump(target.conf.width, this.model.doc.ppi, msg.model.ppiSize));
                        if(null != target.conf.font){
                             target.conf.font.size = parseFloat(LayoutUtil.pxPump(target.conf.font.size, this.model.doc.ppi, msg.model.ppiSize));
                             target.conf.font.space = parseFloat(LayoutUtil.pxPump(target.conf.font.space, this.model.doc.ppi, msg.model.ppiSize));
                             target.conf.font.lineHeight = parseFloat(LayoutUtil.pxPump(target.conf.font.lineHeight, this.model.doc.ppi, msg.model.ppiSize));
                        }
                        if(null != target.conf.points){
                             let tmp = target.conf.points.split(' ');
                             for(let tdx in tmp){
                                 tmp[tdx] = parseFloat(LayoutUtil.pxPump(tmp[tdx], this.model.doc.ppi, msg.model.ppiSize));
                             }
                             target.conf.points = tmp.join(' ');
                        }
                        if(null != target.conf.diam){
                             target.conf.diam = parseFloat(LayoutUtil.pxPump(target.conf.diam, this.model.doc.ppi, msg.model.ppiSize));
                        }
                        if('image' == target.type){
                             target.conf.slotX = parseFloat(LayoutUtil.pxPump(target.conf.slotX, this.model.doc.ppi, msg.model.ppiSize));
                             target.conf.slotY = parseFloat(LayoutUtil.pxPump(target.conf.slotY, this.model.doc.ppi, msg.model.ppiSize));
                             target.conf.slotW = parseFloat(LayoutUtil.pxPump(target.conf.slotW, this.model.doc.ppi, msg.model.ppiSize));
                             target.conf.slotH = parseFloat(LayoutUtil.pxPump(target.conf.slotH, this.model.doc.ppi, msg.model.ppiSize));
                             this.notify(new Message('asset::iloaded', { target: target } ));
                        }
                        if('path' == target.type){
                             target.d = LayoutUtil.corrPath(target.d, this.model.doc.ppi, msg.model.ppiSize);
                        }
               }
          }
          this.model.doc.ppi = parseFloat(msg.model.ppiSize);
          this.notify(new Message('ppi::updated', this.model.doc));
     }

     this.setPrintSize = function(msg){

          if(null == this.model.doc){
               return;
          }

// fixdiss
/*
          let p = this.getPrintSize(msg.model.printSizeIndex);
          let tempW = LayoutUtil.unitToPx(this.model.doc.ppi, p.width, 'mm');
              tempW = LayoutUtil.pxToUnit(this.model.doc.ppi, tempW, this.model.doc.unit);
          let tempH = LayoutUtil.unitToPx(this.model.doc.ppi, p.height, 'mm');
              tempH = LayoutUtil.pxToUnit(this.model.doc.ppi, tempH, this.model.doc.unit);

          p.width = tempW;
          p.height = tempH;

          this.model.doc.printSize = p;
*/

          this.model.doc.printSize = this.getPrintSize(msg.model.printSizeIndex);
          this.notify(new Message('printsize::updated', this.model.doc));
     }

     this.getPrintSize = function(idx){
          let res = this.printSizes.A4;

console.log('getPrintSize(): ', res);

          if(null != this.model.printSizes[idx]){
               res = this.model.printSizes[idx];
          }
          return res;
     }

     this.bindFontSelection = function(msg){
          this.model.doc.assets[msg.model.indx].conf.font.family = msg.model.font;
          this.notify(new Message('font::updated', this.model.doc));
     }

     this.bindFontSetting = function(msg){
          let idx = this.getIndexOfAssetBy(msg.model.arguments[1]);
          switch(msg.model.arguments[2]){     
               case 'left':
               case 'center':
               case 'right':
               case 'block':
                    this.model.doc.assets[idx].conf.font.align = msg.model.arguments[2];
                    break;
          }
          this.notify(new Message('font::updated', this.model.doc));     
     }

     this.bindAssetInput = function(msg){
          let idx = this.getIndexOfAssetBy(msg.model.arguments[1]);
          if(null == this.model.doc.assets[idx]){
               return;
          }
          if('' == msg.model.arguments[3]){
               return;
          }
          let target = this.model.doc.assets[idx];
          let value = parseFloat(msg.model.arguments[3]);
          switch(msg.model.arguments[2]){
               case 'src': 
                    break;
               case 'opacity': 
                    target.conf.opacity = value; 
                    break;
               case 'xpos':
                    target.conf.xpos = value;
                    break;
               case 'ypos':
                    target.conf.ypos = value; 
                    break;
               case 'width':
                    target.conf.width = value;
                    break;
               case 'height':
                    target.conf.height = value;
                    break;
               case 'scale':
                    let xow = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.ow, target.conf.unit);
                    let xoh = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.oh, target.conf.unit);
                    target.conf.width = parseFloat(xow) *value;
                    target.conf.height = parseFloat(xoh) *value;
                    target.conf.scale = value;
                    break;
          }
          this.notify(new Message('asset::updated', this.model.doc));
     }

     this.bindTextInput = function(msg){
          let idx = this.getIndexOfAssetBy(msg.model.arguments[1]);
          if(null == this.model.doc.assets[idx]){
               return;
          }
          switch(msg.model.arguments[2]){
               case 'text': 
                    msg.model.arguments[3] = msg.model.arguments[3].replace(/\"/gm, '“');;
                    msg.model.arguments[3] = msg.model.arguments[3].replace(/\'/gm, '’');;
                    // msg.model.arguments[3] = msg.model.arguments[3].replace(/\"/gm, '\u0027');;
                    // msg.model.arguments[3] = msg.model.arguments[3].replace(/\'/gm, '\u0022');;
                    msg.model.arguments[3] = msg.model.arguments[3].replace(/\\/gm, '');;
                    msg.model.arguments[3] = msg.model.arguments[3].split(/\n/);;
                    this.model.doc.assets[idx].text = msg.model.arguments[3];
                    break;
               case 'xpos': 
                    this.model.doc.assets[idx].conf.xpos = parseFloat(msg.model.arguments[3]);
                    break;
               case 'ypos': 
                    this.model.doc.assets[idx].conf.ypos = parseFloat(msg.model.arguments[3]);
                    break;
               case 'width': 
                    this.model.doc.assets[idx].conf.width = parseFloat(msg.model.arguments[3]);
                    break;
               case 'height': 
                    this.model.doc.assets[idx].conf.height = parseFloat(msg.model.arguments[3]);
                    break;
               case 'size': 
                    this.model.doc.assets[idx].conf.font.size = parseFloat(msg.model.arguments[3]);
                    break;
               case 'space': 
                    this.model.doc.assets[idx].conf.font.space = parseFloat(msg.model.arguments[3]);
                    break;
               case 'line':
                    this.model.doc.assets[idx].conf.font.lineHeight = parseFloat(msg.model.arguments[3]);
                    break;
               case 'c': 
                    this.model.doc.assets[idx].conf.color['cmyk'].c =  parseFloat(msg.model.arguments[3]);
                    break;
               case 'm':
                    this.model.doc.assets[idx].conf.color['cmyk'].m = parseFloat(msg.model.arguments[3]);
                    break;
               case 'y':
                    this.model.doc.assets[idx].conf.color['cmyk'].y = parseFloat(msg.model.arguments[3]);
                    break;
               case 'k':
                    this.model.doc.assets[idx].conf.color['cmyk'].k = parseFloat(msg.model.arguments[3]);
                    break;
               case 'opacity':
                    this.model.doc.assets[idx].conf.opacity = parseFloat(msg.model.arguments[3]);
                    break;
          }
          this.model.controlKeysLocked = false;
          this.notify(new Message('text::updated', this.model.doc));
     }

     this.update = function(msg){
     }

     this.bindMouseDrag = function(msg){
          this.updateEditor();
     }

     this.updateEditor = function(msg){
          if(null == this.model.selectedLibraryItem){
               return;
          }
          jQuery('.xpos').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.xpos));
          jQuery('.ypos').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.ypos));
          jQuery('.width').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.width));
          jQuery('.height').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.height));
          jQuery('.scale').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.scale));
          if('text' != this.model.selectedLibraryItem.type){ return; }
          jQuery('.size').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.font.size));
          jQuery('.line').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.font.lineHeight));
          jQuery('.space').val(LayoutUtil.formatSettingFloat(this.model.selectedLibraryItem.conf.font.space));
     }

     // toolbar is not inited by the document
     this.setupToolbar = function(){
          let ref = this;
          jQuery('.layout-toolbar').html(__tool__bar__tmpl);
          jQuery('.layout-controlbar').html(__control__bar__tmpl);
          jQuery('.select_preset_size select').off();
          jQuery('.select_preset_size select').val(this.model.selectedLayoutImageSize);
          jQuery('.select_preset_size select').change(function(e){
               ref.model.selectedLayoutImageSize = parseInt(e.target.value);
               let buf = '';
               let ldx = e.target.value;
               for(let idx in ref.model.layoutDescriptor[ldx]){
                    buf+= '<option value="'+idx+'">';
                    buf+= ref.model.layoutDescriptor[ldx][idx].join(' - ');
                    buf+= '</option>';
               }
               jQuery('.select_preset_rule select').off();
               jQuery('.select_preset_rule select').html(buf);
               jQuery('.select_preset_rule select').change(function(e){
                    ref.model.selectedLayoutRule = parseInt(e.target.value);
                    ref.loadLayoutPresets();
               });
               ref.model.selectedLayoutRule = parseInt(0);
               ref.loadLayoutPresets();
          });
          jQuery('.select_group select').val(0);
     }

     this.loadLayoutPresets = function(){
          let ref = this;
          let rule = this.model.layoutDescriptor[this.model.selectedLayoutImageSize][this.model.selectedLayoutRule];
          if(null == rule){
             return false;
          }
          rule = rule.join('');
          let model = {
               group: this.model.selectedLayoutGroupName,
               rule: rule
          }
          ref.notify(new Message('load::layoutpresets', model));
     }

     this.buildLayoutPreset = function(){
          let rule = null;
          if(0 != this.model.selectedImageSize){
               rule = this.model.layoutDescriptor[this.model.selectedLayoutImageSize][this.model.selectedLayoutRule];
          }
          this.model.doc.printSize = { "idx": "xX", "width": "210", "height": "148" };
          this.model.doc.unit = 'mm';
          this.model.doc.ppi = '300';
          this.model.doc.assets = [];
          this.addTextAsset('question',  '20', '50', this.model.mockText1st);
          this.addTextAsset('answer', '230', '50', this.model.mockText2nd);
          let psrc = SpreadViewerConfig.portraitPngLoc;
          let lsrc = SpreadViewerConfig.landscapePngLoc;
          let indx = '';
          let xpos = 25;
          let ypos = 25;
          let icnt = 0;
          for(let idx in rule){
               switch(rule[idx]){
                    case 'P':
                         indx = 'image_'+icnt;
                         this.addImageAsset(indx, xpos, ypos, 50, 90, psrc);
                         xpos += 75;
                         icnt++;
                         break;
                    case 'L':
                         indx = 'image_'+icnt;
                         this.addImageAsset(indx, xpos, ypos, 90, 50, lsrc);
                         xpos += 115;
                         icnt++;
                         break;
               }
          }
          this.initDocument();
     }

     this.setupTools = function(msg){
          let ref = this;
          jQuery('.layout-tools').empty();
          jQuery('.layout-tools').html(__tool__001__tmpl);
          jQuery('.select_ppi select').off();
          jQuery('.select_ppi select').change(function(){
               ref.notify(new Message('ppibtn::released', { ppiSize: jQuery(this).val() }));
          });
          jQuery('.select_ppi select').val(this.model.doc.ppi);
          let tmp = '<select>';
          for(var idx in this.model.printSizes){
               let title = idx;
                    title+= ': ';
                    title+= this.model.printSizes[idx].width
                    title+= 'mm';
                    title+= ' x ';
                    title+= this.model.printSizes[idx].height;
                    title+= 'mm';
               tmp+= '<option value="'+idx+'">'+title+'</option>';
          }
          tmp+= '</select>';
          jQuery('.select_size').html(tmp);
          jQuery('.select_size select').off();
          jQuery('.select_size select').change(function(){
               ref.notify(new Message('printsize::selected', { printSizeIndex: jQuery(this).val() }));
          });
          jQuery('.select_size select').val(this.model.doc.printSize.idx);
          jQuery('.select_pagesize select').off();
          jQuery('.select_pagesize select').change(function(){
               ref.notify(new Message('pagesize::selected', { pageSize: jQuery(this).val() }));
          });
          jQuery('.select_pagesize select').val(this.model.doc.pageSize);
     }

     this.setupLibrary = function(){
          jQuery('.layout-library').empty();
          jQuery('.layout-library').append(__lib__003__tmpl);
          for(let idx in this.model.doc.assets){
               let model = {
                    'title': this.model.doc.assets[idx].indx,
                    'type': this.model.doc.assets[idx].type
               }
               let tmpl = this.fillTemplate(__lib__002__tmpl, model);
               jQuery('.items').append(tmpl);
          }
          let model = {
               'indx': 'controls'
          }
          jQuery('.layout-actions').empty();
          jQuery('.layout-actions').append(this.fillTemplate(__lib__007__tmpl, model));
     }

     this.getIndexOfAssetBy = function(indx){
          let res = 0;
          for(let idx in this.model.doc.assets){
               if(indx == this.model.doc.assets[idx].indx){
                    res = idx;
               }
          }
          return res;
     }

     this.renderLayoutEditor = function(msg){
          let ref = this;
          let idx = this.getIndexOfAssetBy(msg.model.arguments[1]);
          this.selectLibraryItem(this.model.doc.assets[idx])
     }

     this.setupEditor = function(msg){

          let ref = this;
          let idx = this.getIndexOfAssetBy(msg.model.arguments[1]);
          let model;

          jQuery('.textedit').html('');
          jQuery('.assetedit').html('');

          switch(this.model.doc.assets[idx].type){

               case 'text':

                    let text = '';
                    let temp = '';
                    for(let lnx in this.model.doc.assets[idx].text){
                         temp = this.model.doc.assets[idx].text[lnx];
                         temp = temp.replace("\n", '');
                         text+= temp;
                    }

                    model = {
                            'indx': this.model.doc.assets[idx].indx,
                            'text': text,
                            'xpos': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.xpos),
                            'ypos': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.ypos),
                           'width': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.width),
                          'height': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.height),
                            'size': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.font.size),
                           'space': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.font.space),
                            'line': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.font.lineHeight),
                         'opacity': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.opacity),
                           'color': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.color)
                    }

                    if(null != this.model.doc.assets[idx].conf.color['cmyk']){
                         model.c = LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.color['cmyk'].c);
                         model.m = LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.color['cmyk'].m);
                         model.y = LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.color['cmyk'].y);
                         model.k = LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.color['cmyk'].k);
                    }

                    jQuery('.textedit').html(this.fillTemplate(__lib__001__tmpl, model));
                    let tmp = '<select>';
                    for(let idx in this.model.fonts){
                         tmp += '<option value="'+this.model.fonts[idx].family+'">'+this.model.fonts[idx].family+'</option>';
                    }
                    tmp+= '</select>';

                    jQuery('.select_font select').off();
                    jQuery('.select_font').html(tmp);
                    jQuery('.select_font select').change(function(){
                         let model = {
                              font: jQuery(this).val(),
                              indx: idx
                         };
                         ref.notify(new Message('font::selected', model));
                    });     

                    jQuery('.select_font select').val(this.model.doc.assets[idx].conf.font.family);
                    jQuery('.select_unit select').val(this.model.doc.assets[idx].conf.unit);

                    break;

               case 'image':

                    let scale = this.model.doc.assets[idx].conf.scale;

                    // if(null == scale){ scale = 1.0; }
                    // if(0.5 > scale){ scale = 0.5; }
                    // if(2.0 < scale){ scale = 2.0; }

                    model = {
                             'indx': this.model.doc.assets[idx].indx,
                              'src': this.model.doc.assets[idx].src,
                             'xpos': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.xpos),
                             'ypos': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.ypos),
                            'width': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.width),
                           'height': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.height),
                          'opacity': LayoutUtil.formatSettingFloat(this.model.doc.assets[idx].conf.opacity),
                            'scale': LayoutUtil.formatSettingFloat(scale)
                    }

                    jQuery('.assetedit').html(this.fillTemplate(__lib__004__tmpl, model));
                    jQuery('.select_unit select').val(this.model.doc.assets[idx].conf.unit);

                    break;

               default:

/**** grausam
                    model = {
                          'indx': this.model.doc.assets[idx].indx,
                         'depth': this.model.doc.assets[idx].conf.depth,
                    }
                    jQuery('.assetedit').html(this.fillTemplate(__lib__depth__tmpl, model));
                    let points = this.model.doc.assets[idx].conf.points;
                        points = points.split(' ');
                    let xs = [];
                    let ys = [];
                    let pp = [];
                    let oe = 0;
                    for(let idx in points){
                         oe = idx & 1;
                         switch(oe){
                              case 0:
                                   xs.push(parseFloat(points[idx]));
                                   break;
                              case 1:
                                   ys.push(parseFloat(points[idx]));
                                   break;
                         }
                    }
                    xs.sort(function(a, b){ return a >= b; });
                    ys.sort(function(a, b){ return a >= b; });
                    let xmin = xs[0] *-1;
                    let ymin = ys[0] *-1;
                    let xmax = xs[xs.length -1];
                    let ymax = ys[ys.length -1];
                    points = points.join(' ');
                    this.model.toolsvg = SVG().addTo('.toolsvg');
                    this.model.toolsvg.viewbox(xmin, xmin, xmax, ymax); 
                    this.model.toolsvg.polygon(points).fill('#000').stroke({width:1}).transform({translateX: xmin, translateY: ymin});
*/
                    break;

          }

          this.selectLibraryItem(this.model.doc.assets[idx])
     }

     this.selectedLibraryItemByIndx = function(indx){
          for(let idx in this.model.doc.assets){
               if(indx == this.model.doc.assets[idx].indx){
                    this.selectLibraryItem(this.model.doc.assets[idx]);
                    return;
               }
          }
     }

     this.selectLibraryItem = function(item){
          this.model.selectedLibraryItem = item;
          for(let idx in this.model.doc.assets){
               this.model.doc.assets[idx].selected = false;
               if(item == this.model.doc.assets[idx]){
                    this.model.doc.assets[idx].selected = true;
               }
          }
          this.notify(new Message('item::selected'));
     }

     this.closeEditor = function(msg){
          jQuery('.textedit').empty();
          jQuery('.assetedit').empty();
          this.model.selectedLibraryItem = null;
     }

     this.addTextAsset = function(indx, xpos, ypos, text){
          if(null == indx){ indx = 'T_' +parseInt(Math.random() *1000); }
          let model = {
               "indx": indx,
               "type": "text",
               "text": [ 
                    text
               ],
               "conf": {
                    "unit": "mm",
                    "font": {
                         "family": "American Typewriter",
                         "size": "10",
                         "space": "0",
                         "lineHeight": "13",
                         "align": "left"
                    },
                    "color": {
                         "cmyk": { "c": "0.0", "m": "0.0", "y": "0.0", "k": "1.0" }
                    },
                    "xpos": xpos,
                    "ypos": ypos,
                    "width": "190",
                    "opacity": "1.0",
                    "depth": "30"
               } 
          }
          this.model.doc.assets.push(model);
     }

     this.addImageAsset = function(indx, xpos, ypos, width, height, src){
          let asset = {
               "indx": indx,
               "type": "image",
               "conf": {
                    "unit": "mm",
                    "xpos": xpos,
                    "ypos": ypos,
                    "width": width,
                    "height": height,
                    "scale": "1",
                    "opacity": "1"
                },
                "src": src 
          }
          this.model.doc.assets.push(asset);
          let model = { src: src, scale: 1.0, ref: indx }
          this.notify(new Message('image::targeted', model ));
     }

     this.splitModels = function(){
          if(null == this.model.doc){
               return null;
          }
          let models = [];
          let max = parseInt(this.model.doc.pageSize);
          for(let idx = 0; idx < max; idx++){
               // let model = {...this.model.doc};
               let model = this.model.doc;
                    model.pageSize = 1; 
                    let xmin = parseInt(model.printSize.width) *idx;
                    let xmax = xmin +parseInt(model.printSize.width);
                    let assets = [];
                    for(let iidx in model.assets){
                         let currx = parseInt(model.assets[iidx].conf.xpos);
                         if(currx > xmin && currx <= xmax){
                              assets.push(model.assets[iidx]);
                         }
                    }
               model.assets = assets;
               models.push(model);
          }
          return models;
     }

// init
     this.model = new ToolsModel();
     let ref = this;
     jQuery('.layout-messages').html(__msg__001__tmpl);

     // control sequences
     this.register(new Subscription(      'fontbtn::released', 'bindFontSetting', this));
     this.register(new Subscription(     'textinput::updated', 'bindTextInput', this));
     this.register(new Subscription(    'assetinput::updated', 'bindAssetInput', this));
     this.register(new Subscription(          'select::asset', 'setupEditor', this));
     this.register(new Subscription(         'font::selected', 'bindFontSelection', this));
     this.register(new Subscription(       'ppibtn::released', 'setPpi', this));
     this.register(new Subscription(    'printsize::selected', 'setPrintSize', this))
     this.register(new Subscription(  'nextsectbtn::released', 'nextSpread', this));
     this.register(new Subscription(  'prevsectbtn::released', 'prevSpread', this));
     this.register(new Subscription(      'savebtn::released', 'saveDocument', this));
     this.register(new Subscription(     'pagesize::selected', 'bindPageSize', this));
     this.register(new Subscription('savelayoutbtn::released', 'saveLayout', this));
     this.register(new Subscription(  'layoutgroup::selected', 'bindLayoutGroup', this));
      // event messages
     this.register(new Subscription(    'mousedrag::released', 'bindMouseDrag', this));
     this.register(new Subscription(         'layout::loaded', 'bindLayout', this));
     this.register(new Subscription(       'document::loaded', 'initDocument', this));
     this.register(new Subscription(     'textinput::focused', 'lockControlKeys', this));
     this.register(new Subscription(        'textinput::done', 'unlockControlKeys', this));
     this.register(new Subscription(          'image::loaded', 'bindImageAsset', this));
     this.register(new Subscription(           'book::loaded', 'bindBook', this));
     this.register(new Subscription(      'unitbtn::released', 'bindUnit', this));
     this.register(new Subscription(    'layoutgroup::loaded', 'bindLoadedLayoutGroup', this));
     this.register(new Subscription(  'layoutpresets::loaded', 'bindLoadedLayoutPresets', this));
     this.register(new Subscription(      'layouts::imported', 'bindImportedLayouts', this));
     //
     this.setupAssetPossibs();
     if(SpreadViewerConfig.mouseControls){ this.setupMouseControls(); }
     this.loadLocalDocument();
     this.setupToolbar();
     this.notify(new Message('spread-iewer::inited', this.model));
}

let __msg__001__tmpl = "";

let __control__bar__tmpl = ""+ 
"<div class='row'>"+
"<div class='block'>"+
     "<a href='javascript:layoutQueue.route(\"savelayoutbtn::released\")'>i would like to save this layout</a></select>"+
"</div>"+
"</div>"+
"<div class='row'>"+
"<div class='block'>"+
     "<a href='javascript:layoutQueue.route(\"import::layouts\")'>i would like to import the layouts</a></select>"+
"</div>"+
"</div>";

let __tool__bar__tmpl = ""+ 
"<div class='row'>"+
"<div class='block select_group'>"+
     "<select onchange='javascript:layoutQueue.route(\"layoutgroup::selected\", this.value)'>"+
          "<option value='x'>Layout Groups</option>"+
          "<option value='0'>Default Group</option>"+
     "</select>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block select_preset_size'>"+
     "<select>"+
          "<option value='x'>Layout Image Size</option>"+
          "<option value='0'>No Image</option>"+
          "<option value='1'>One Image</option>"+
          "<option value='2'>Two Images</option>"+
          "<option value='3'>Three Images</option>"+
          "<option value='4'>Four Images</option>"+
     "</select>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block select_preset_rule'>"+
     "<select>"+
          "<option value='x'>Layout Preset Rules</option>"+
     "</select>"+
"</div>"+
"</div>";

let __tool__001__tmpl = ""+
"<div class='row'>"+
"<div class='block'>"+
     "<a href='javascript:layoutQueue.route(\"exportbtn::released\");'>i would like to export the spreads</a>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block select_ppi'>"
     "<select>"+
          "<option value='600'>600 ppi</option>"+
          "<option value='300'>300 ppi</option>"+
          "<option value='150'>150 ppi</option>"+
          "<option value='96'>96 ppi</option>"+
          "<option value='72'>72 ppi</option>"+
     "</select>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block select_pagesize'>"+
     "<select>"+
          "<option value='5'>5</option>"+
          "<option value='3'>3</option>"+
          "<option value='2'>2</option>"+
          "<option value='1'>1</option>"+
     "</select>"+
"</div>"+
"</div>";

let __tool__981__tmpl = ""+
"<div>"+
     "<a href='javascript:layoutQueue.route(\"prevsectbtn::released\", \"\");'>prev</a>"+
     "<a href='javascript:layoutQueue.route(\"nextsectbtn::released\", \"\");'>next</a>"+
"</div>";

let __tool__991__tmpl = ""+
"<div>"+
     "<a href='javascript:layoutQueue.route(\"prevsectbtn::released\", \"\");'>prev</a>"+
     "<a href='javascript:layoutQueue.route(\"nextsectbtn::released\", \"\");'>next</a>"+
"</div>";

let __lib__003__tmpl = ""+
"<div class='items'></div>"+
"<div class='textedit'></div>"+
"<div class='assetedit'></div>"+
"<div class='file'></div>";

let __lib__depth__tmpl = ""+
"<div class='imgpanel'>"+
     "<div class='toolsvg'></div>"+
"</div>"+

"<div class='label'>depth:</div>"+
"<div class='row'>"+
     "<div class='block select_unit'>"+
          "<input class='xpos' type='number' step='1' value='{depth}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'depth', this.value);\""+
          "></input>"+
     "</div>"+
"</div>";

let __lib__004__tmpl = "";
"<div class='imgpanel'>"+
     "<img src='{src}' height='71'/>"+
"</div>"+

"<div class='label'>unit:</div>"+
"<div class='row'>"+
     "<div class='block select_unit'>"+
          "<select onchange='javascript:layoutQueue.route(\"unitbtn::released\", this.value);'>"+
               "<option value='mm'>mm</option>"+
               "<option value='inch'>inch</option>"+
               "<option value='px'>px</option>"+
          "</select>"+
     "</div>"+
"</div>"+

"<div class='label'>pos:</div>"+
"<div class='row'>"+
     "<div class='block'>"+
          "<input class='xpos' type='number' step='1' value='{xpos}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'xpos', this.value);\""+
          "></input>"+
     "</div>"+
     "<div class='block'>"+
          "<input class='ypos' type='number' step='1' value='{ypos}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'ypos', this.value);\""+
          "></input>"+
     "</div>"+
"</div>"+

"<div class='label'>size:</div>"+
"<div class='row'>"+
     "<div class='block'>"+
          "<input class='width' type='number' step='any' value='{width}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'width', this.value);\""+
          "></input>"+
     "</div>"+
     "<div class='block'>"+
          "<input class='height' type='number' step='any' value='{height}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'height', this.value);\""+
          "></input>"+
     "</div>"+
"</div>"+

"<div class='row'>"+
     "<div class='block'>"+
          "<input type='number' step='0.01' min='0.5' max='2' value='{scale}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'scale', this.value);\""+
          "></input>"+
     "</div>"+
"</div>"+

"<div class='label'>opac:</div>"+
"<div class='row'>"+
     "<div class='block'>"+
          "<input type='number' step='0.01' min='0' max='1' value='{opacity}'"+
               "onchange=\"javascript:layoutQueue.route('assetinput::updated', '{indx}', 'opacity', this.value);\""+
          "></input>"+
     "</div>"+
"</div>";

let __lib__002__tmpl = ""+
"<a href='javascript:layoutQueue.route(\"select::asset\", \"{title}\")'>{title}</a>";

let __lib__001__tmpl = ""+
"<div class='row'>"+
"<div class='block'>"+
     "<textarea onchange='javascript:layoutQueue.route(\"textinput::updated\", \"{indx}\", \"text\", this.value);'>{text}</textarea>"+
"</div>"+
"</div>"

"<div class='row'>"+
"<div class='block'>"+
     "<a href='javascript:layoutQueue.route(\"fontbtn::released\", \"{indx}\", \"left\");'>left</a>"+
     "<a href='javascript:layoutQueue.route(\"fontbtn::released\", \"{indx}\", \"center\");'>center</a>"+
     "<a href='javascript:layoutQueue.route(\"fontbtn::released\", \"{indx}\", \"right\");'>right</a>"+
     "<a href='javascript:layoutQueue.route(\"fontbtn::released\", \"{indx}\", \"block\");'>block</a>"+
     "<a href='javascript:layoutQueue.route(\"recalcbtn::released\", \"{indx}\", \"fit\");'>fixfits</a>"+
"</div>"+
"</div>"+

"<div class='label'>font:</div>"+
"<div class='row'>"+
     "<div class='block select_font'></div>"+
"</div>"+

"<div class='row'>"+
"<div class='block'>"+
     "<input class='size' type='number' step='0.1' value='{size}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'size', this.value);\""+
     "></input>"+
"</div>"+

"<div class='block'>"+
     "<input class='space' type='number' step='0.1' value='{space}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'space', this.value);\""+
     "></input>"+
"</div>"+

"<div class='block'>"+
     "<input class='line' type='number' step='0.1' value='{line}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'line', this.value);\""+
     "></input>"+
"</div>"+
"</div>"+

"<div class='label'>unit:</div>"+
"<div class='row'>"+
"<div class='block select_unit'>"+
     "<select onchange=\"javascript:layoutQueue.route('unitbtn::released', this.value);\">"+
          "<option value='mm'>mm</option>"+
          "<option value='inch'>inch</option>"+
          "<option value='px'>px</option>"+
     "</select>"+
"</div>"+
"</div>"+

"<div class='label'>pos:</div>"+
"<div class='row'>"+
"<div class='block'>"+
     "<input class='xpos' type='number' step='1' value='{xpos}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'xpos', this.value);\""+
     "></input>"+
"</div>"+
"<div class='block'>"+
     "<input class='ypos' type='number' step='1' value='{ypos}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'ypos', this.value);\""+
     "></input>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block'>"+
     "<input class='width' type='number' step='1' value='{width}'"+ 
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'width', this.value);\""+
     "></input>"+
"</div>"+
"</div>"+

"<div class='label'>color:</div>"+
"<div class='row'>"+
"<div class='block'>"+
     "<input type='number' step='0.01' min='0' max='1' value='{c}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'c', this.value);\""*
     "></input>"+
"</div>"+

"<div class='block'>"+
     "<input type=\"number\" step='0.01' min='0' max='1' value='{m}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'm', this.value);\""+
     "></input>"+
"</div>"+

"<div class='block'>"+
     "<input type='number' step='0.01' min='0' max='1' value='{y}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'y', this.value);\""+
     "></input>"+
"</div>"+

"<div class='block'>"+
     "<input type='number' step='0.01' min='0' max='1' value='{k}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'k', this.value);\""+
     "></input>"+
"</div>"+
"</div>"+

"<div class='row'>"+
"<div class='block'>"+
     "<input type='number' step='0.01' min='0' max='1' value='{opacity}'"+
          "onchange=\"javascript:layoutQueue.route('textinput::updated', '{indx}', 'opacity', this.value);\""+
     "></input>"+
"</div>"+
"</div>"+

"</div>";

let __lib__007__tmpl = "";

let __lib__009__tmpl = ""+
"<form>"+
     "<input type='file' class='fileupload' name='filename' multiple='multiple'></inpupt>"+
"</form>";

let ToolsModel = function() {
     this.spidx = 0;
     this.tocidx = 0;
     this.spreads;
     this.spread;
     this.printSizes = new PrintSizes().sizes;
     this.fonts = new Font().fonts;
     this.doc;
     this.selectedLibraryItem;
     this.selectedEditor;
     this.surveys;
     this.env;
     this.modifierKey;
     this.controlKeysLocked;
     this.deeplink;
     this.collection;
     this.section;
     this.selectedLayouts;
     this.layoutDescriptor;
     this.selectedLayoutRule = 'x';
     this.selectedLayoutImageSize = 'x';
     this.selectedLayoutGroupName = 'default';
     this.loadedLayoutGroup;
     this.loadedLayoutPresets;
     this.mockText1st = 'Local punk Kyla Waters has spent the past 24 hours trying to decide if her roommate’s new tattoo either looks nothing like Jack…';
     this.mockText2nd = 'Local anarcho-punk Noah Wallin claimed today that he is prepared to take the lives of Scottish indie-rock…';
}

// https://papersizes.io/a/a4
let PrintSizes = function(){
     this.sizes = {
          "A4": {
               "inch": { "width": "8.3", "height": "11.7" },
               "mm": { "width": "210", "height": "297" },
               "px": {
                    "ppi300": { "width": "2480", "height": "3508" }
               }
          }
     }
}

let Font = function(){ 
     this.fonts = [
          { family: "Georgia" },
          { family: "Helvetica" },
          { family: "American Typewriter" },
          { family: "Arial" },
          { family: "Arial Black" },
          { family: "Andale Mono" },
          { family: "American Typewriter" },
          { family: "Times New Roman" },
          { family: "Trebuchet MS" },
          { family: "Courier" }
    ] 
}

let MockModel = function(){
     this.model = {
          "uuid": "",
          "surveyId": "",
          "questionId": "",
          "pageSize": "2",
          "unit": "mm",
          "ppi": "300",
          "printSize": { "idx": "xX", "width": "210", "height": "148" },
          "layout": {
               "frame": {
                    "x": "5",
                    "y": "10"
               }
          },
          "opt": "",
          "assets": [
               { 
                    "indx": "question",
                    "type": "text",
                    "text": [
                         "Default Question"
                    ],
                    "selected": "false",
                    "conf": {
                         "unit": "mm",
                         "font": {
                              "family": "American Typewriter", 
                              "size": "9.5",
                              "space": "1",
                              "weight": "300",
                              "lineHeight": "11",
                              "align": "left"
                         },
                         "color": {
                              "cmyk": { "c": "0.05", "m": "0.75", "y": "1", "k": "0" }
                         },
                         "xpos": "20",
                         "ypos": "35",
                         "width": "170",
                         "height": "300",
                         "opacity": "1.0",
                         "depth": "10"
                    }
               },
               {
                    "indx": "answer",
                    "type": "text",
                    "text": [
                         "Default Answer"
                    ],
                    "selected": "false",
                    "conf": {
                         "unit": "mm",
                         "font": {
                              "family": "American Typewriter",
                              "size": "19.0",
                              "space": "0.05",
                              "weight": "300",
                              "lineHeight": "18",
                              "align": "right"
                         },
                         "color": { 
                              "cmyk": { "c": "1", "m": "0.35", "y": "0.1", "k": "0" }
                         },
                         "xpos": "25",
                         "ypos": "70",
                         "width": "170",
                         "height": "300",
                         "opacity": "1.0",
                         "depth": "20"
                    },
               }
          ]
     };
}

