let SurveyUtil = {

     REF: 0x03,
     KEY: 0x01,
     VALUE: 0x02,
     FULL: 0x04,

     trimIncomingString: function(target){
          if(null == target){ 
               return target; 
          }
          if('boolean' == typeof(target)){ 
               return target 
          }
          target = target.substring(0, SurveyConfig.maxInputLength);
          target = target.replace(/\"/gm, '“');;
          target = target.replace(/\'/gm, '’');;
          target = target.replace(/\\/gm, '');;
          return target;
     },

     // json2b
     pigpack: function(doc){
          return btoa(doc);
     },

     // b2json
     pagpick: function(pack){
          if(null == pack){
               return pack;
          }
          let tmp = atob(pack);
          if(null == tmp){
               return pack; 
          }
          let res = jQuery.parseJSON(tmp);
          if(null == res){
               return pack;
          }
          return res;
     }
}


