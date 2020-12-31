let layoutQueue = new Queue();
let layoutController = new Controller(layoutQueue);
jQuery(document).ready(function(){
     let layoutBitmap = new Bitmap(layoutController);
     let layoutNet = new Net(layoutController);
     let layoutCorrect = new Correct(layoutController);
     let layoutScreen = new Screen(layoutController);
     let layoutTools = new Tools(layoutController);
});
