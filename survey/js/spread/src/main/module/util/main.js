LayoutUtil = {
     corrPath: function(path, ppi1st, ppi2nd){
          path+= 'x';
          let temp = path.match(/([a-zA-Z])(.*?)(?=[a-zA-Z])/gi);
          let buf = '';
          for(let idx in temp){
               let command = temp[idx].substring(0, 1);
               let chunk = temp[idx].substring(1, temp[idx].length);
                   chunk = chunk.replace(/\,-/gi, '-');
                   chunk = chunk.replace(/\-/gi, ',-');
               let ary = chunk.split(',');
               let r = [];
               let x; let y;
               switch(command){
                    case 'M': case 'm':
                         ary[0] = ary[0].trim();
                         ary[1] = ary[1].trim();
                         x = LayoutUtil.pxPump(parseFloat(ary[0]), ppi1st, ppi2nd);
                         y = LayoutUtil.pxPump(parseFloat(ary[1]), ppi1st, ppi2nd);
                         buf+= command +x+','+y;
                         break;
                    case 'c': case 'C': case 's': case 'S':
                         r = [];
                         for(let iidx in ary){
                              if(null == ary[iidx]){ continue; }
                              ary[iidx] = ary[iidx].trim();
                              ary[iidx] = parseFloat(ary[iidx]);
                              if(isNaN(ary[iidx])){ continue; }
                              r.push(LayoutUtil.pxPump(parseFloat(ary[iidx]), ppi1st, ppi2nd));
                         }
                         r = r.join(',');
                         // r = r.replace(/,-/gi, '-');
                         buf+= command +r;
                         break;
                    case 'a': case 'A':
                         r = [];
                         c = 0;
                         for(let iidx in ary){
                         ary[iidx] = ary[iidx].trim();
                              if(null == ary[iidx]){ continue; }
                              ary[iidx] = ary[iidx].trim();
                              ary[iidx] = parseFloat(ary[iidx]);
                              if(isNaN(ary[iidx])){ continue; }
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
                         // r = r.replace(/,-/gi, '-');
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
          ppi = parseFloat(ppi);
          let px = 0;
          switch(unit){
               case 'px':
                    px = val;
                    break;
               case 'inch':
                     // px = ppi *val;
                     px = (2480 /(210 /25.4) /300) *ppi *val;
                    break;
               case 'mm':
               default:
                    px = (2480 /210 /300) *ppi *val;
                    break;
          }
          return px; 
     },
     pxToUnit: function(ppi, val, unit){
          if(undefined == val){ 
               val = 0.0; 
          }
          val = parseFloat(val);
          ppi = parseFloat(ppi);
          let res = 0;
          switch(unit){
               case 'px':
                    res = val;
                    break;
               case 'inch':
                    // res = val *ppi;
                    res = val /((2480 /(210 /25.4) /300) *ppi);
                    break;
               case 'mm':
               default:
                    res = val /((2480 /210 /300) *ppi);
                    break;
          }
          return res;
     },
/*
     mmToPx: function(ppi, val){
          if(undefined == val){ 
               val = 0; 
          }
          val = parseFloat(val);
          return 2480 /210 /300 *parseFloat(ppi) *val;
     },
*/
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
     sanitizePrint: function(text){
          if(null == text){
               return '';
          }
          res = text.replace('/\$/', '');
          res = text.replace('/\n\r/', '');
          res = text.replace(/\"/gm, '“');;
          res = text.replace(/\'/gm, '’');;
          return res;
     },
     formatSettingFloat: function(val){
          let res = val;
          res = parseFloat(res).toFixed(2);
          return res;
     }
}
