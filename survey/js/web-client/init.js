let surveyQueue = new Queue();
let controller = new Controller(surveyQueue);
jQuery(document).ready(function(){
     let surveyNet = new SurveyNet(controller);
     let survey = new Survey(controller);
});
