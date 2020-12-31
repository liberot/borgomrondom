let surveyQueue = new Queue();
let surveyController = new Controller(surveyQueue);
jQuery(document).ready(function(){
     let surveyNet = new SurveyNet(surveyController);
     let survey = new Survey(surveyController);
});
