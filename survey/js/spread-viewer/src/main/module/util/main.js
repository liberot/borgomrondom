LayoutUtil = {
     corrPath: function(path, ppi1st, ppi2nd){
          path+= 'x';
          let temp = path.match(/([a-zA-Z])(.*?)(?=[a-zA-Z])/gi);
          let buf = '';
          for(let idx in temp){
               let command = temp[idx].substring(0, 1);
               let chunk = temp[idx].substring(1, temp[idx].length);
                   chunk = chunk.replace(/\-/gi, ',-');
               let ary = chunk.split(',');
               let r = [];
               let x; let y;
               switch(command){
                    case 'M': case 'm':
                         x = LayoutUtil.pxPump(parseFloat(ary[0]), ppi1st, ppi2nd);
                         y = LayoutUtil.pxPump(parseFloat(ary[1]), ppi1st, ppi2nd);
                         buf+= command +x+','+y;
                         break;
                     case 'c': case 'C': case 's': case 'S':
                         r = [];
                         for(let iidx in ary){
                              if(null == ary[iidx]){ continue; }
                              r.push(LayoutUtil.pxPump(parseFloat(ary[iidx]), ppi1st, ppi2nd));
                         }
                         r = r.join(',');
                         r = r.replace(/,\-/gi, '-');
                         buf+= command +r;
                         break;
                    case 'a': case 'A':
                         r = [];
                         c = 0;
                         for(let iidx in ary){
                              switch(c){
                                   case 2: case 3: case 4:
                                        r.push(ary[iidx]);
                                        break;
                                   default:
                                        r.push(LayoutUtil.pxPump(ary[iidx], ppi1st, ppi2nd));
                                        break;
                              }
                              c++;
                       }
                       r = r.join(',');
                       r = r.replace(/,\-/gi, '-');
                       buf+= command +r;
                       break;
                  case 'l': case 'L':
                       x = LayoutUtil.pxPump(parseFloat(ary[0]), ppi1st, ppi2nd);
                       y = LayoutUtil.pxPump(parseFloat(ary[1]), ppi1st, ppi2nd);
                       buf+= command +x+','+y;
                       break;
                  case 'z': case 'Z':
                       buf+= command;
                       break;
             }
        }
        return buf;
     },
     pxPump: function(px, ppi1st, ppi2nd){
          ppi1st = null == ppi1st ? 0 : ppi1st;
          ppi2nd = null == ppi2nd ? 0 : ppi2nd;
          ppi1st = parseFloat(ppi1st);
          ppi2nd = parseFloat(ppi2nd);
          let res = parseFloat(px);
          res /= ppi1st;
          res *= ppi2nd;
          return res;
     },
     unitToPx: function(ppi, val, unit) {
          if(undefined == val){ 
               val = 0; 
          }
          val = parseFloat(val);
          let px = 0;
          switch(unit){
               case 'px':
                    px = val;
                    break;
               case 'inch':
                     px = (2480 /(210 /25.4) /300) *parseFloat(ppi) *val;
                    break;
               case 'mm':
               default:
                    px = (2480 /210 /300) *parseFloat(ppi) *val;
                    break;
          }
          return px; 
     },
     pxToUnit: function(ppi, val, unit){
          if(undefined == val){ 
               val = 0.0; 
          }
          val = parseFloat(val);
          let res = 0;
          switch(unit){
               case 'px':
                    res = val;
                    break;
               case 'inch':
                    res = val /((2480 /(210 /25.4) /300) *parseFloat(ppi));
                    break;
               case 'mm':
               default:
                    res = val /((2480 /210 /300) *parseFloat(ppi));
                    break;
          }
          return res;
     },
     mmToPx(ppi, val){
          if(undefined == val){ 
               val = 0; 
          }
          val = parseFloat(val);
          return 2480 /210 /300 *parseFloat(ppi) *val;
     },
     pigpack: function(doc){
          return btoa(doc);
     },
     pagpick: function(pack){
          if(undefined == pack || null == pack){
               return '';
          }
          let tmp1st = atob(pack);
          let tmp2nd = jQuery.parseJSON(tmp1st);
          return tmp2nd;
     },
     sanitizePrint(text){
          if(null == text){
               return '';
          }
          res = text.replace('/\$/', '');
          res = text.replace('/\n\r/', '');
          res = text.replace(/\"/gm, '“');;
          res = text.replace(/\'/gm, '’');;
          return res;
     },
     formatSettingFloat(val){
          let res = val;
          res = parseFloat(res).toFixed(2);
          return res;
     }
}
