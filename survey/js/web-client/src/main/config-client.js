SurveyConfig = {
     maxInputLength: 1024,
     serviceURL: '/wp-admin/admin-post.php',
     LINEAR_HISTORY: 0x00,
     CONTEXT_SENSITVEY_HISTORY: 0x01,
     navigationHistory: 0x00,
     preloadPanels: false
}
history.pushState(null, null, location.href);
window.onpopstate = function(e){
    history.pushState(null, null, window.location.href);
    if('undefined' == typeof(surveyQueue)){ return }
    surveyQueue.route("stoerte::back");
};
