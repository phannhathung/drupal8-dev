<?php 
namespace Drupal\custom_pagebuilder\Controller;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\custom_pagebuilder\Core\ClassCustomPagebuilder;

class CustomPagebuilderAdminController extends ControllerBase {
  
  public function custom_pagebuilder_config_page($custom_pagebuilder){
    //return array("#markup" => 'sadasdasdas');
    $page = '';
    $abs_url_config = \Drupal::url('custom_pagebuilder.admin.save', array(), array('absolute' => FALSE)); 
    $_url_get_img = \Drupal::url('custom_pagebuilder.admin.get_images_upload', array(), array('absolute' => FALSE));
    $cpb = new ClassCustomPagebuilder($custom_pagebuilder);
    $cpb->custom_pagebuilder_load_shortcodes(true);
    $page = array(
      '#attached' => array( 
        'library' => array( 'custom_pagebuilder/custom_pagebuilder.assets.admin' , 'custom_pagebuilder/custom_pagebuilder.font-awesome') ,
        'drupalSettings' => array(
          'custom_pagebuilder'=> array(
            'saveConfigURL' => $abs_url_config,
            'getImageUpload' => $_url_get_img
          )
         )
      ),
      '#type' => 'html',
      '#cache' => array('max-age' => 0),
      '#theme' => 'custom_pagebuilder_admin_builder', 
      '#pid' => (!empty($cpb->get_ID())) ? $cpb->get_ID() : $custom_pagebuilder,
      '#cpb_title' => $cpb->get_title(),
      '#cpb_rows_count' => $cpb->get_rows_count(),
      '#cpb_els_ops' => $cpb->custom_pagebuilder_shortcodes_forms(), 
      '#cpb_rows_opts' => $cpb->row_opts(),
      '#cpb_columns_opts' => $cpb->column_opts(),
      '#cpb_page_els' => $cpb->get_json_decode(),
    );
    return $page;
  }
  
  
  public function custom_pagebuilder_save(){
    header('Content-type: application/json');
    $data = $_REQUEST['data'];
    $pid = $_REQUEST['pid'];
    $params = '';
    if($data){
      $data = base64_decode($data);
      $data = json_decode($data, true);
      $params = $this->custom_pagebuilder_save_element($data);
      //print_r($params);die();
    } 
    if($params==null) $params = '';

    $result = array(
      'pid' => $pid,
      'data' => $data
    );
    
    \Drupal::database()->merge('custom_pagebuilder_content')
    ->key(array('id' => $pid))
    ->insertFields(array(
  		'id' => $pid,  
  		'params' => $params,  
  	))
    ->updateFields(array(
  		'params' => $params,
	   ))->execute();
    
    print json_encode($result);
    exit(0);
  }
  
  function custom_pagebuilder_save_element($data) {
    $cpb_els = array();
    if( isset($data['cpb-row-id']) && is_array($data['cpb-row-id'])){
      foreach( $data['cpb-row-id'] as $rowID_k => $rowID ){
        $row = array();
        if( isset($data['cpb-rows']) && is_array($data['cpb-rows'])){
          foreach ( $data['cpb-rows'] as $row_attr_k => $row_attr ){
            $row['attr'][$row_attr_k] = $row_attr[$rowID_k];
          }
        }
        $row['columns'] = array();
        $cpb_els[] = $row;
      }
    
      $array_rows_id = array_flip( $data['cpb-row-id'] );
    } 
    $col_row_id = array();
    if( isset($data['cpb-column-id']) && is_array($data['cpb-column-id'])){
      foreach( $data['cpb-column-id'] as $column_id_key => $column_id ){
        if($column_id){
          $column = array();
          if( isset($data['cpb-columns']) && is_array($data['cpb-columns'])){
            foreach ( $data['cpb-columns'] as $col_attr_k => $col_attr ){
              $column['attr'][$col_attr_k] = $col_attr[$column_id_key];
            }
          }
          $column['items'] = '';
          $parent_row_id = $data['column-parent'][$column_id_key];
          //print_r($cpb_els);
          $new_parent_row_id = $array_rows_id[$parent_row_id];
          if(isset($cpb_els[$new_parent_row_id])){
            $cpb_els[$new_parent_row_id]['columns'][$column_id] = $column;
          }
          $col_row_id[$column_id] = $new_parent_row_id;
        }
      }  
    } 

    // items 
    if( key_exists('element-type', $data) && is_array($data['element-type'])){
      $count = array();
      $count_tabs = array();
      $i_c = 0;
      $c_count = 0;
      foreach( $data['element-type'] as $type_k => $type ){ 
        $item = array();
        $item['type'] = $type;
        $item['size'] = 12;
        
        if(isset($data['element-size'][$type_k]) && $data['element-size'][$type_k]){
          $item['size'] = $data['element-size'][$type_k];
        }

        if( ! key_exists($type, $count) ) $count[$type] = 0;
        if( ! key_exists($type, $count_tabs) ) $count_tabs[$type] = 0;
        
        if($type == 'cpb_carousel') {
          $c_count = ( !empty($data['cpb-items']['cpb_carousel']['count'][$i_c]) ) ? $data['cpb-items']['cpb_carousel']['count'][$i_c] + $c_count:0;
          $count_carousel = ( $c_count == 0 ) ? 0 : $c_count - $data['cpb-items']['cpb_carousel']['count'][$i_c];
        }
        
        if( key_exists($type, $data['cpb-items']) ){ 
          foreach(  $data['cpb-items'][$type] as $attr_k => $attr ){
            
            if( $attr_k == 'tabs'){
              // field tabs fields
              $item['fields']['count'] = $attr['count'][$count[$type]];
              if( !empty($item['fields']['count']) ){
                for ($i = 0; $i < $item['fields']['count']; $i++) {
                  $tab = array();
                  $tab['icon'] = (!empty($attr['icon'][$count_tabs[$type]])) ? stripslashes($attr['icon'][$count_tabs[$type]]) : '';
                  $tab['title'] = (!empty($attr['title'][$count_tabs[$type]])) ? stripslashes($attr['title'][$count_tabs[$type]]) : '';
                  $tab['content'] = (!empty($attr['content'][$count_tabs[$type]])) ? stripslashes($attr['content'][$count_tabs[$type]]) : '';
                  $item['fields']['tabs'][] = $tab;
                  $count_tabs[$type]++;
                }
              }
              //$count_c = $count_c + $item['fields']['count'];
            } elseif( $attr_k == 'multiple_images' ) {
              //debug_print($attr);
              if( $c_count != 0 ) {
                for ($i = $count_carousel; $i < $c_count; $i++) {
                  //if(!empty($attr[$i]['url_image'])) {
                    $carousel['title'] = (!empty($attr[$i]['title'])) ? stripslashes($attr[$i]['title']) : '';
                    $carousel['sub_title'] = (!empty($attr[$i]['sub_title'])) ? stripslashes($attr[$i]['sub_title']) : '';
                    $carousel['url_image'] = (!empty($attr[$i]['url_image'])) ? stripslashes($attr[$i]['url_image']) : '';
                    $item['fields']['multiple_images'][] = $carousel;
                  //}
                }
              }
              $i_c++;
            } else {
              $item['fields'][$attr_k] = (!empty($attr[$count[$type]])) ? stripslashes($attr[$count[$type]]) : '';            
            }
            
          }
        }
        $count[$type] ++;
        $column_id = $data['element-parent'][$type_k];
        $parent_row_id = $data['element-row-parent'][$type_k];

        $new_parent_row_id = $array_rows_id[$parent_row_id];
        $new_column_id = $column_id;
        if(empty($cpb_els[$new_parent_row_id]['columns'][$new_column_id]['items'])) {
          $cpb_els[$new_parent_row_id]['columns'][$new_column_id]['items'] = array();
        }
        $cpb_els[$new_parent_row_id]['columns'][$new_column_id]['items'][] = $item;
      }
     
    }
    if( $cpb_els ){
      $new = base64_encode(json_encode($cpb_els));    
    }
    return $new;
  }

  public function custom_pagebuilder_export($bid){
    $pbd_single = custom_pagebuilder_load($bid);
    $data = $pbd_single->params;
    $title = date('Y_m_d_h_i_s') . '_bb_export'; 
    header("Content-Type: text/txt");
    header("Content-Disposition: attachment; filename={$title}.txt");
    print $data;
    exit;
  }

  public function custom_pagebuilder_upload_file(){
    // A list of permitted file extensions
    global $base_url;
    $allowed = array('png', 'jpg', 'gif','zip');
    $_id = custom_pagebuilder_makeid(6);
    if(isset($_FILES['upl']) && $_FILES['upl']['error'] == 0){

      $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

      if(!in_array(strtolower($extension), $allowed)){
        echo '{"status":"error extension"}';
        exit;
      }  
      $path_folder = \Drupal::service('file_system')->realpath(file_default_scheme(). "://cpb-uploads");

      $ext = end(explode('.', $_FILES['upl']['name']));
      $image_name =  basename($_FILES['upl']['name'], ".{$ext}");

      //$file_path = $path_folder . '/' . $_id . '-' . $_FILES['upl']['name'];
      $file_path = $path_folder . '/' . $image_name . "-{$_id}" . ".{$ext}";

      $file_url = str_replace($base_url, '',file_create_url(file_default_scheme(). "://cpb-uploads"). '/' .  $image_name . "-{$_id}" . ".{$ext}"); 
      if (!is_dir($path_folder)) {
        @mkdir($path_folder); 
      }
      if(move_uploaded_file($_FILES['upl']['tmp_name'], $file_path)){
        $result = array(
          'file_url' => $file_url,
          'file_url_full' => $base_url . $file_url
        );
        print json_encode($result);
        exit;
        }
    }

    echo '{"status":"error"}';
    exit;
  }

  public function get_images_upload(){
    header('Content-type: application/json');
    global $base_url; 

    $file_path = \Drupal::service('file_system')->realpath(file_default_scheme(). "://cpb-uploads");

    $file_url = file_create_url(file_default_scheme(). "://cpb-uploads"). '/';
    $list_file = glob($file_path . '/*.{jpg,png,gif}', GLOB_BRACE);
    usort( $list_file, function( $a, $b ) { return filemtime($b) - filemtime($a); } );
    $files = array();
    $data = '';
    foreach ($list_file as $key => $file) {
      if(basename($file)){
        $file_url = str_replace($base_url, '', file_create_url(file_default_scheme(). "://cpb-uploads"). '/' .  basename($file)); 
        $files[$key]['file_url'] = $file_url;
        $files[$key]['file_url_full'] = $base_url . $file_url;
      }  
    }
    $result = array(
      'data' => $files
    );
    print json_encode($result);
    exit(0);
  }
}
?>