let SurveyConfig = {
     maxInputLength: 1024,
     serviceURL: '/wp-admin/admin-post.php',
     publicURL: '/wp-admin/admin-ajax.php'
}
history.pushState(null, null, location.href);
window.onpopstate = function(e){
    console.log(e);
    console.log(window.location.href);
    history.pushState(null, null, window.location.href);
};
