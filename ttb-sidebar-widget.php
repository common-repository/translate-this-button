<?php
/* 
Extends the TranslateThis Button for WordPress
Plugin URI: http://translateth.is/wordpress
Version: 0.1
*/

/*  Copyright 2010 Jon Raasch
*/

function translate_tb_widget($args, $widget_args = 1) {
		
    extract( $args, EXTR_SKIP );
    if ( is_numeric($widget_args) )
        $widget_args = array( 'number' => $widget_args );
    $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
    extract( $widget_args, EXTR_SKIP );

    $options = get_option('translate_tb_widget');
    if ( !isset($options[$number]) ) 
    return;

    $title = $options[$number]['title']; 		// single value
        
    echo $before_widget; // start widget display code 
    
    if ( $title ) echo $before_title . $title . $after_title; // title
    
    translate_this_button(); // the translate this button code
    
    echo $after_widget; // end widget display code

}


function translate_tb_widget_control($widget_args) {

    global $wp_registered_widgets;
    static $updated = false;

    if ( is_numeric($widget_args) )
        $widget_args = array( 'number' => $widget_args );			
    $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
    extract( $widget_args, EXTR_SKIP );

    $options = get_option('translate_tb_widget');
    
    if ( !is_array($options) )	
        $options = array();

    if ( !$updated && !empty($_POST['sidebar']) ) {
    
        $sidebar = (string) $_POST['sidebar'];	
        $sidebars_widgets = wp_get_sidebars_widgets();
        
        if ( isset($sidebars_widgets[$sidebar]) )
            $this_sidebar =& $sidebars_widgets[$sidebar];
        else
            $this_sidebar = array();

        foreach ( (array) $this_sidebar as $_widget_id ) {
            if ( 'translate_tb_widget' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
                $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                if ( !in_array( "ttb-widget-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
                    unset($options[$widget_number]);
            }
        }

        foreach ( (array) $_POST['ttb-widget'] as $widget_number => $simple_widget ) {
            if ( !isset($simple_widget['title']) && isset($options[$widget_number]) ) // user clicked cancel
                continue;
            
            $title = strip_tags(stripslashes($simple_widget['title']));
            
            // Pact the values into an array
            $options[$widget_number] = compact( 'title' );
        }

        update_option('translate_tb_widget', $options);
        $updated = true;
        echo '<p>Settings updated</p>';
    }

    if ( -1 == $number ) { // if it's the first time and there are no existing values

        $title = '';
        $number = '%i%';
        
    } else { // otherwise get the existing values
    
        $title = attribute_escape($options[$number]['title']);
    }
?>
<p><label>Title</label><br /><input id="title_value_<?php echo $number; ?>" class="widefat" name="ttb-widget[<?php echo $number; ?>][title]" type="text" value="<?=$title?>" /></p>

<input type="hidden" name="ttb-widget[<?php echo $number; ?>][submit]" value="1" />

<small><a href="options-general.php?page=Translate-This-Button">More options</a></small>

<?php
}


function translate_tb_widget_register() {
    if ( !$options = get_option('translate_tb_widget') )
        $options = array();
    $widget_ops = array('classname' => 'translate_tb_widget', 'description' => __('Offer 52 langauges of translation'));
    $control_ops = array('id_base' => 'ttb-widget');
    $name = __('TranslateThis Button');

    $id = false;
    
    foreach ( (array) array_keys($options) as $o ) {

        if ( !isset( $options[$o]['title'] ) )
            continue;
                    
        $id = "ttb-widget-$o";
        wp_register_sidebar_widget($id, $name, 'translate_tb_widget', $widget_ops, array( 'number' => $o ));
        wp_register_widget_control($id, $name, 'translate_tb_widget_control', $control_ops, array( 'number' => $o ));
    }
    
    if ( !$id ) {
        wp_register_sidebar_widget( 'ttb-widget-1', $name, 'translate_tb_widget', $widget_ops, array( 'number' => -1 ) );
        wp_register_widget_control( 'ttb-widget-1', $name, 'translate_tb_widget_control', $control_ops, array( 'number' => -1 ) );
    }
}

add_action('init', translate_tb_widget_register, 1);