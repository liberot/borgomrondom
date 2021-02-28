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

     foreach($layouts as $layout){
          $layout->doc = json_decode(base64_decode($layout->doc), true);
     }

bb_add_debug_field('field:', $field);
bb_add_debug_field('code:', $code);
bb_add_debug_field('layouts:', $layouts);
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
