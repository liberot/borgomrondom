let SurveyUtil = {

     REF: 0x03,
     KEY: 0x01,
     VALUE: 0x02,
     FULL: 0x04,

     trimIncomingString: function(target){
          if(null == target){ return target; }
          if('boolean' == typeof(target)){ return target; }
          target = target.substring(0, SurveyConfig.maxInputLength);
          target = target.replace(  /\"/gm, '“');
          target = target.replace(  /\'/gm, '’');
          target = target.replace(  /\\/gm, '');
          target = target.replace(/\s+$/gm, '');
          return target;
     },

     pigpack: function(doc){
          return Base64.encode(doc);
     },

     pagpick: function(pack){
          let res = pack;
          if(null == res){ return res; }
          try{ 
               res = Base64.decode(res); 
          }
          catch(exc){ 
               return pack;
          }
          if(null == res){ return res; }
          res = jQuery.parseJSON(res);
          return res;
     },

     flattenTocRefs: function(toc, res){
          if(null == res){ res = []; }
          for(let idx = 0; idx < toc.length; idx++){
               res.push(toc[idx].title);
               if(0 >= toc[idx].group.length){
                    res = this.flattenTocRefs(toc[idx].group, res);
               }
          }
          return res;
     }
}
