history.pushState(null, null, location.href);
window.onpopstate = function(e){
    console.log(e);
    console.log(window.location.href);
    history.pushState(null, null, window.location.href);
};
