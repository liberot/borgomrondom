<?php defined('ABSPATH') || exit;

add_action('admin_post_exec_export_prints', 'exec_export_prints');
function exec_export_prints(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     if(false == file_exists(Path::INKSCAPE)){
          $message = esc_html(__('no inkscape', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $rnd = random_string(25);
     $svg_path = sprintf('/tmp/%s.svg', $rnd);
     $pdf_path = sprintf('/tmp/%s.pdf', $rnd);
     $png_path = sprintf('/tmp/%s.png', $rnd);

     $dpi = trim_incoming_numeric($_POST['ppi']);
     $width = trim_incoming_numeric($_POST['width']);
     $height = trim_incoming_numeric($_POST['height']);
     $type = 'png';
     $type = 'pdf';

// fixdiss... no assets 
     // $svg = substr($_POST['svg'], 0, (1024 *1024));

     $svg = $_POST['svg'];
     $svg = rawurldecode($svg);

     $res = @file_put_contents($svg_path, $svg);

     $cmd = sprintf(
          '%s %s --export-dpi=%s --export-width=%s --export-height=%s --export-type=%s',
               Path::INKSCAPE, $svg_path, $dpi, $width, $height, $type
     );
     $res = @exec($cmd);

     $coll = ['cmd'=>$cmd, 'svg'=>$svg_path, 'pdf'=>$pdf_path, 'png'=>$png_path];
     $message = esc_html(__('prints done', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
}

add_action('admin_post_exec_export_separations', 'exec_export_separations');
function exec_export_separations(){

     if(!policy_match([Role::ADMIN])){
          $message = esc_html(__('policy match', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     if(false == file_exists(Path::GHOSTSCRIPT)){
          $message = esc_html(__('no inkscape', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     $dpi = trim_incoming_numeric($_POST['ppi']);
     // $input_pdf = trim_incoming_filename($_POST['input_pdf']);
     $input_pdf = trim_incoming_string($_POST['input_pdf']);
     $input_pdf = preg_replace('/\s+/', '_', $input_pdf);
     $output_pdf = preg_replace('/(\..{1,10})$/', '_cmyk.pdf', $input_pdf);

     if(-1 == strpos($input_pdf, '.pdf')){
          $message = esc_html(__('no input', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message));
          return false;
     }

     if(false == file_exists($input_pdf)){
          $message = esc_html(__('no input file', 'nosuch'));
          echo json_encode(array('res'=>'failed', 'message'=>$message, 'file'=>$input_pdf));
          return false;
     }

     $cmd = sprintf(
          '%s -r%s -dSAFER -dBATCH -dNOPAUSE -dNOCACHE -sDEVICE=%s -sColorConversionStrategy=%s -dProcessColorModel=%s -sOutputFile=%s %s',
               Path::GHOSTSCRIPT, $dpi, 'pdfwrite', 'CMYK', '/DeviceCMYK', $output_pdf, $input_pdf
     );
     $res = @exec($cmd);

     $input_sep_pdf = $output_pdf;
     $output_tiff = preg_replace('/(\..{1,10})$/', '.tiff', $input_pdf);
     $cmd = sprintf(
          '%s -r%s -dNOPAUSE -dBATCH -sDEVICE=%s -sOutputFile=%s %s',
               Path::GHOSTSCRIPT, $dpi, 'tiffsep', $output_tiff, $input_sep_pdf
     );
     $res = @exec($cmd);

     $coll = [
          'cmd'=>$cmd,
          'input_pdf'=>$input_pdf,
          'output_pdf'=>$output_pdf,
          'output_tiff'=>$output_tiff
     ];


     $message = esc_html(__('seps written', 'nosuch'));
     echo json_encode(array('res'=>'success', 'message'=>$message, 'coll'=>$coll));
};


