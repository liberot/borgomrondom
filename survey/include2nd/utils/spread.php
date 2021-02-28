<?php defined('ABSPATH') || exit;



function bb_build_debug_spread($ticket){

     $field = bb_get_field_by_ref($ticket->field_ref)[0];
     if(is_null($field)){
          return false;
     }

     $group = bb_get_group_by_ref($field->group_ref);
     $rec = bb_get_rec_of_client_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref)[0];
     $assets = bb_get_assets_by_field_ref($ticket->client_id, $ticket->thread_id, $ticket->field_ref);
     $code = bb_get_layout_code($assets);

     $layouts = bb_get_layouts_by_code($code);
     if(is_null($layouts)){
          return false;
     }

//fixdiss
     $client_asset = $assets[0];
// assets have layout codes
// doc asset slots hase layout codes
// 

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
     foreach($doc['assets'] as $asset){
          if('image' == $asset['type']){
               $asset['src'] = $client_asset->doc;
               $asset = bb_fit_image_asset_into_slot($doc, $asset);
          }
          $assets_of_doc[]= $asset;
     }
     $doc['assets'] = $assets_of_doc;


//fixdiss text document
     $doc = base64_encode(json_encode($doc));
     $res = bb_insert_spread($ticket->client_id, $ticket->thread_id, $field, $doc);

bb_add_debug_field('doc:', $doc);
bb_add_debug_field('field:', $field);
bb_add_debug_field('code:', $code);
bb_add_debug_field('layout:', $layout);
bb_add_debug_field('group:', $group);
bb_add_debug_field('rec:', $rec);
bb_add_debug_field('assets:', $assets);
bb_flush_debug_field();

}



function bb_get_layout_code($assets){

     $res = '';
     foreach($assets as $asset){
          $res = sprintf('%s%s', $res, $asset->layout_code);
     }

     return $res;
}
