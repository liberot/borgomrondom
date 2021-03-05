<?php defined('ABSPATH') || exit;



// fixdiss: build spreads on rec write rather than read
function bb_build_debug_spread(){

     $client_id = bb_get_author_id();
     $ticket = bb_get_ticket_of_client($client_id)[0];
     $field = bb_get_field_by_ref($ticket->field_ref)[0];
     if(is_null($field)){
          return false;
     }

     $group = bb_get_group_by_ref($field->group_ref)[0];
     $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref)[0];
     $assets = bb_get_assets_by_group_ref($ticket->client_id, $ticket->thread_id, $field);

     $code = bb_get_layout_code($assets);
     $layouts = bb_get_layouts_by_code($code);
     if(is_null($layouts)){
          return false;
     }

//fixdiss
//there is a heaps of matching layout suggestions
     $layout = $layouts[0];
     if(is_null($layout)){
          return false;
     }

     $doc = json_decode(base64_decode($layout->doc), true);
     $doc = bb_walk_the_doc($doc);
     if(is_null($doc)){
          return false;
     }

     $assets_of_doc = [];
     $idx = 0;
     foreach($doc['assets'] as $asset){
          if('image' == $asset['type']){
               $asset['src'] = is_null($assets[$idx]) ? '' : $assets[$idx]->doc;
               $asset = bb_fit_image_asset_into_slot($doc, $asset);
               $idx = $idx +1;
          }
          $assets_of_doc[]= $asset;
     }
     $doc['assets'] = $assets_of_doc;


// fixdiss
     $mockup_text = [];

     $mockup_text[0] = bb_trim_for_print('Mockup Entry 1st');
     $mockup_text[1] = sprintf('%s: %s', 'Group', bb_trim_for_print($group->title));
     $mockup_text[2] = sprintf('%s: %s', 'Rec', bb_trim_for_print($rec->doc));

     $assets_of_doc = [];
     $idx = 0;
     foreach($doc['assets'] as $asset){
          if('text' == $asset['type']){
               $asset['text'] = [];
               if($idx == 0){
                    $asset['text'] = $mockup_text;
               }
               $idx = $idx +1;
          }
          $assets_of_doc[]= $asset;
     }
     $doc['assets'] = $assets_of_doc;

     $doc = base64_encode(json_encode($doc));
     $res = bb_insert_spread($ticket->client_id, $ticket->thread_id, $field, $doc);

}



function bb_get_layout_code($assets){

     $res = '';
     foreach($assets as $asset){
          $res = sprintf('%s%s', $res, $asset->layout_code);
     }

     return $res;
}




