class Correct extends Controller {

     constructor(queue){
          super(queue);
          this.model = new CorrectModel();
          this.model.offScreen = SVG().addTo('.offscreen'); 
          this.register(new Subscription('document::inited', this.initWorker));
          this.register(new Subscription('asset::iloaded', this.corrAssetSize));
          this.register(new Subscription('ppi::updated', this.splitRowspans));
          this.register(new Subscription('text::updated', this.splitRowspans));
          this.register(new Subscription('recalcbtn::released', this.splitRowspans));
          this.register(new Subscription('adaptclayout::released', this.implementLayout));
     }

     initWorker(msg){
          this.model.doc = msg.model;
          this.splitRowspans();
     }

     corrAssetSize(msg){


          let target = msg.model.target;
          let width = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.ow, target.conf.unit);
          let height = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.oh, target.conf.unit);
          let slotW = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.slotW, target.conf.unit);
          let slotH = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.slotH, target.conf.unit);
          let slotX = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.slotX, target.conf.unit);
          let slotY = LayoutUtil.pxToUnit(this.model.doc.ppi, target.conf.slotY, target.conf.unit);
          let maxScaleRatio = parseFloat(target.conf.maxScaleRatio);
          let w = width;
          let h = height;
          let r = 1;

          switch(target.conf.scaleType){
          case 'cut_into_slot':
               let xr = slotW /width;
               let yr = slotH /height;
               r = xr >= yr ? xr : yr;
               if(r >= maxScaleRatio){
                   r = maxScaleRatio;
               }
               w = width *r;
               h = height *r;
               break;

          default:
               switch(target.conf.layoutCode){
               case 'L':
                    if(width >= slotW){
                         r = slotW /width;
                         h = height *r;
                         h = height *r;
                    }
                    if(h >= slotH){
                         r = slotH /height;
                         w = width *r;
                         h = height *r;
                    }
                    break;
               case 'P':
                    if(height > slotH){
                         r = slotH /height;
                         w = width *r;
                         h = height *r;
                    }
                    if(w >= slotW){
                         r = slotW /width;
                         w = width *r;
                         h = height *r;
                    }
                    break;
               }
          }

          let xoffset = (slotW -w) /2;
          let yoffset = (slotH -h) /2;

          target.conf.scale = r;
          target.conf.width = w;
          target.conf.height = h;
          target.conf.opacity = parseFloat(target.conf.opacity);

          target.conf.xpos = parseFloat(slotX) +xoffset;
          target.conf.ypos = parseFloat(slotY) +yoffset;

          this.notify(new Message('asset::corrected'));

          // console.log({ conf: target.conf, r: r, xoffset: xoffset, yoffset: yoffset });
     }

     corrBlockWidth(target){
          if(parseFloat(target.conf.xpos) <= 0){
               target.conf.xpos = 0;
          }
          if(parseFloat(target.conf.xpos) +parseFloat(target.conf.width) >= parseFloat(this.model.doc.printSize.width)){
               target.conf.width = parseFloat(this.model.doc.printSize.width) -parseFloat(target.conf.xpos);
          }
          this.splitRowspans();
     }

     splitRowspans(msg) {
          this.model.offScreen.clear();
          for(let idx in this.model.doc.assets){
               if('text' != this.model.doc.assets[idx].type){
                    continue;
               }
               let res = [];
               let target = this.model.doc.assets[idx];
               let xPos1 = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.xpos, target.conf.unit);
               let xPos2 = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.width, target.conf.unit);
               let maxWidth = xPos2;
               let font = {
                    'family': target.conf.font.family,
                    'letter-spacing': LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.space, target.conf.unit),
                    'size': LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.size, target.conf.unit)
               }
               let offScreenFont =  { 'x': 0, 'y': -10000 };
               // calcs the width of a current whitespace which is 0 for some reason
               let nuu1 = 'o O';
               let emp1 = this.model.offScreen.text(nuu1);
                    emp1.attr(offScreenFont);
                    emp1.font(font);
               let nwd1 = emp1.length(); 
               let nuu2 = 'oO';
               let emp2 = this.model.offScreen.text(nuu2);
                    emp2.attr(offScreenFont);
                    emp2.font(font);
               let nwd2 = emp2.length(); 
               let whitespaceWidth = nwd1 -nwd2;
               // calcs spans as in for breaking into a flowlayout
               for(let iidx in target.text){
                    if(null == target.text[iidx]){
                         continue;
                    }
                    let words = target.text[iidx].split(/\s+/);
                    // let words = target.text[iidx];
                    let sizes = [];
                    // calcs width the current span that is to be written
                     for(let widx in words){
                         let text = this.model.offScreen.text(words[widx]);
                              text.attr(offScreenFont);
                              text.font(font);
                         let width = parseFloat(text.length());
                         if(window.chrome){
                              let space = LayoutUtil.unitToPx(this.model.doc.ppi, target.conf.font.space, target.conf.unit);
                              let numOfletters = parseInt(words[widx].length);
                              width = ((numOfletters+1) *space) +text.length();
                         }
                         sizes.push(width);
                    }
                    // calcs the linebreaks at the given configuration
                    let spans = [];
                        spans[0] = { 'text': '', 'conf': { 'maxLength': maxWidth, 'wordCount': 0 } };
                    let currentWidth = 0;
                    let row = 0;
                    for(let widx in words){
                         currentWidth += parseFloat(sizes[widx]) +parseFloat(whitespaceWidth);
                         if(currentWidth >= maxWidth){
                              if(spans[row].text.match(/\s+$/gm)){
                                   spans[row].text = spans[row].text.replace(/\s+$/gm, '');
                                   currentWidth -= whitespaceWidth;
                              }
                              // sets up the next row
                              row++;
                              spans[row] = { 
                                   text: words[widx],
                                   conf: {
                                        'maxLength': maxWidth,
                                        'wordCount': 1
                                   }
                              };
                              spans[row].text += ' ';
                              // sets current width to length of the first word of the added row 
                              currentWidth = sizes[widx] +whitespaceWidth;
                         }
                         else {
                              spans[row].text += words[widx];
                              spans[row].text += ' ';
                              spans[row].conf.wordCount += 1;
                         }
                    }
                    res = res.concat(spans);
               }
               target.spans = res;
          }
     }
}

class CorrectModel extends Model{
     constructor(){
          super();
          this.offScreen; 
          this.doc;
     }
}
