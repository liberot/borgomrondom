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
          // return btoa(doc);
          return Base64.encode(doc);
     },

     pagpick: function(pack){
          if(null == pack){ return pack; }
          // let tmp = atob(pack);
          let tmp = Base64.decode(pack);
          if(null == tmp){ return pack; }
          let res = jQuery.parseJSON(tmp);
          if(null == res){ return pack; }
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
