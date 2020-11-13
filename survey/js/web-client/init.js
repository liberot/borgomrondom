let surveyQueue = new Queue();
jQuery(document).ready(function(){
     let surveyNet = new SurveyNet(surveyQueue);
     let survey = new Survey(surveyQueue);
});
