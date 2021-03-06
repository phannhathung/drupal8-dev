<?php 
namespace Drupal\custom_pagebuilder\Shortcodes;
use Drupal\custom_pagebuilder\Core\ClassVideoEmbed;
if(!class_exists('cpb_video_box')):
   class cpb_video_box{

      public function render_form(){
         $fields = array(
            'type' => 'cpb_video_box',
            'title' => ('Video Box'), 
            'size' => 3,
            'fields' => array(
                  
               array(
                  'id'        => 'content',
                  'type'      => 'text',
                  'title'     => t('Data Url'),
                  'desc'      => t('example: //player.vimeo.com/video/88558878?color=ffffff&title=0&byline=0&portrait=0'),
               ),
              
              array(
                  'id'        => 'auto_play',
                  'type'      => 'select',
                  'title'     => t('Auto Play'),
                  'desc'      => t(''),
                  'options'   => array('yes' => 'Yes', 'no' => 'No'),
              ),
              
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => custom_pagebuilder_animate(),
               ),
              
              array(
                'id'    => 'duration',
                'type'    => 'text',
                'title'   => ('Anumate Duration'),
                'desc'    => ('Change the animation duration'),
                'class'   => 'small-text',
              ),

              array(
                'id'    => 'delay',
                'type'    => 'text',
                'title'   => ('Anumate Delay'),
                'desc'    => ('Delay before the animation starts'),
                'class'   => 'small-text',
              ),
               
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),

            ),                                       
         );
         return $fields;
      }

      public function render_content( $item ) {
         if( ! key_exists('content', $item['fields']) ) $item['fields']['content'] = '';
         return self::sc_video_box( $item['fields'], $item['fields']['content'] );
      }


      public static function sc_video_box( $attr, $content = null ){
         $output = '';
         
         // Amination Box
         $class_animate = '';
         $duration = '';
         $delay = '';
         if(!empty($attr['animate'])) {
           $class_animate = ' wow '. $attr['animate'];
           $duration = !empty($attr['duration']) ? 'data-wow-duration='.$attr['duration'].'s' : '';
           $delay = !empty($attr['delay']) ? 'data-wow-delay='.$attr['delay'].'s' : '';
         }
         
         $autoembed = new ClassVideoEmbed($attr['auto_play']);
         $video = trim($autoembed->getEmbedVideo($attr['content']));
         $output .= '<div class="wrapper-custom-pagebuilder-videoembed '. $attr['el_class'] .' '. $class_animate .' " {{ duration }} {{ delay }} >';
            $output .= '<div class="inner-content">';
              $output .= $video;
            $output .= '</div>';
         $output .= '</div>';
         
         
         return array(
                    '#type' => 'inline_template',
                    '#template' => $output,
                    '#context' => array(
                      'duration' => $duration,
                      'delay' => $delay,
                    ),
                  ); 
      
      }
   }
endif;   




