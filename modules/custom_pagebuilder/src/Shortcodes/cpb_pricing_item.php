<?php
namespace Drupal\custom_pagebuilder\Shortcodes;
if(!class_exists('cpb_pricing_item')):
   class cpb_pricing_item{

      public function render_form(){
         $fields = array(
            'type' => 'cpb_pricing_item',
            'title' => ('Pricing Item'), 
            'size' => 3,
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'desc'      => t('Pricing item title'),
                  'class'     => 'display-admin'
               ),
               array(
                  'id'        => 'price',
                  'type'      => 'text',
                  'title'     => t('Price'),
               ),
               
               array(
                  'id'        => 'currency',
                  'type'      => 'text',
                  'title'     => t('Currency'),
               ),
                  
               array(
                  'id'        => 'period',
                  'type'      => 'text',
                  'title'     => t('Period'),
               ),
               
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content'),
                  'desc'      => t('HTML tags allowed.'),
                  'std'       => '<ul><li><strong>List</strong> item</li></ul>',
               ),
               
               array(
                  'id'        => 'link_title',
                  'type'      => 'text',
                  'title'     => t('Link title'),
                  'desc'      => t('Link will appear only if this field will be filled.'),
               ),
               
               array(
                  'id'        => 'link',
                  'type'      => 'text',
                  'title'     => t('Link'),
                  'desc'      => t('Link will appear only if this field will be filled.'),
               ),

               array(
                  'id'        => 'featured',
                  'type'      => 'select',
                  'title'     => t('Featured'),
                  'options'   => array( 'off' => 'No', 'on' => 'Yes' ),
               ),

               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),

               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation'),
                  'options'   => custom_pagebuilder_animate()
               ),
               
            ),                                          
         );
         return $fields;
      }

      public function render_content( $item ) {
         if( ! key_exists('content', $item['fields']) ) $item['fields']['content'] = '';
         print self::sc_pricing_item( $item['fields'], $item['fields']['content'] );
      }

      public static function sc_pricing_item( $attr, $content = null ){
         extract(shortcode_atts(array(
            'title'        => '',
            'currency'     => '',
            'price'        => '',
            'period'       => '',
            'link_title'   => 'Sign Up Now',
            'link'         => '',
            'featured'     => 'off',
            'el_class'     => '',
            'animate'      => '',
         ), $attr));
            $class = $el_class;
            if($featured == 'on') $class .= 'highlight-plan'; 
         ?>
         <div class="pricing-table <?php if($class) print (' ' . $class) ?>">
            <div class="plan-name">
               <h3><?php print $title; ?></h3>
            </div>
            <div class="plan-price">
               <div class="price-value">
                  <sup class="dollar" itemprop="priceCurrency"><?php print $currency?></sup>
                  <div class="value" itemprop="price"><?php print $price; ?></div>
               </div>
               <div class="interval"><?php print $period ?></div>
            </div>
            <div class="plan-list">
               <?php print $content ?>
            </div>
            <?php if($link){ ?>
               <div class="plan-signup">
                  <a class="btn-theme btn" href="<?php print $link; ?>"><?php print $link_title ?></a>
               </div>
            <?php } ?>   
         </div>
         <?php
      }

      public function load_shortcode(){
         add_shortcode( 'pricing_item', array( 'cpb_pricing_item', 'sc_pricing_item' ));
      }
   }
endif;   



