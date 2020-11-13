LayoutUtil = {
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
                    px = (2540 /100 /300) *parseFloat(ppi) *val;
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
                    res = val /((2540 /100 /300) *parseFloat(ppi));
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
