let layoutQueue = new Queue();
jQuery(document).ready(function(){
     let layoutBitmap = new Bitmap(layoutQueue);
     let layoutNet = new Net(layoutQueue);
     let layoutCorrect = new Correct(layoutQueue);
     let layoutScreen = new Screen(layoutQueue);
     let layoutTools = new Tools(layoutQueue);
});
