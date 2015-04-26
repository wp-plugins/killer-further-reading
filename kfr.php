<?php
/*
Plugin Name: Killer Further Reading
Plugin URI: http://wordpress.org/extend/plugins/killer-further-reading/
Description: The best content from your blog is shown to the user for further reading only if he has not read the content. The tracking is done through cookie being placed for the content already read.
Author: Shabbir Bhimani
Version: 0.05
Author URI: http://imtips.co/killer-further-reading.html
*/
// Creating the widget
class kfr_widget extends WP_Widget {
    
    function __construct() {
        parent::__construct(
            // Base ID of your widget
            'killer_further_reading',

            // Widget name will appear in UI
            __('Killer Further Reading', 'killer_further_reading'),

            // Widget description
            array( 'description' => __( 'Killer Further Reading Widget', 'killer_further_reading' ), )
            );
    }
    
    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {

        if ( ! empty( $instance['tag_slug'] ) && is_numeric($instance['post_count']))
        {
            $title = apply_filters( 'widget_title', $instance['title'] );
            // before and after widget arguments are defined by themes
            echo $args['before_widget'];
            if ( ! empty( $title ) )
                echo $args['before_title'] . $title . $args['after_title'];
            
            // This is where you run the code and display the output
            $tag_slug = $instance['tag_slug'];
            $post_count = $instance['post_count'];
            kfr_best_posts($tag_slug, $post_count);
        }
        echo $args['after_widget'];
    }
    
    // Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'Further Reading', 'killer_further_reading' );
        }
        if ( isset( $instance[ 'tag_slug' ] ) ) {
            $tag_slug = $instance[ 'tag_slug' ];
        }
        else {
            $tag_slug = __( '', 'killer_further_reading' );
        }
        if ( isset( $instance[ 'post_count' ] ) ) {
            $post_count = $instance[ 'post_count' ];
        }
        else {
            $post_count = __( '5', 'killer_further_reading' );
        }
        
        // Widget admin form
    ?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'tag_slug' ); ?>"><?php _e( 'Tag Slug for Further Reading:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'tag_slug' ); ?>" name="<?php echo $this->get_field_name( 'tag_slug' ); ?>" type="text" value="<?php echo esc_attr( $tag_slug ); ?>" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'post_count' ); ?>"><?php _e( 'Number of Posts to Show:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'post_count' ); ?>" name="<?php echo $this->get_field_name( 'post_count' ); ?>" type="text" value="<?php echo esc_attr( $post_count ); ?>" />
</p>
    <?php
    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'Further Reading';
        $instance['tag_slug'] = ( ! empty( $new_instance['tag_slug'] ) ) ? strip_tags( $new_instance['tag_slug'] ) : '';
        $instance['post_count'] = ( ! is_numeric( $new_instance['post_count'] ) ) ? strip_tags( $new_instance['post_count'] ) : '5';
        return $instance;
    }
} // Class kfr_widget ends here

// Register and load the widget
function kfr_load_widget() {
    register_widget( 'kfr_widget' );
}
add_action( 'widgets_init', 'kfr_load_widget' );

function kfr_best_posts($tag_slug, $number_posts = 5) {
    echo "<ul class=\"killer-further-reading\">";
    $excluded = get_the_ID();
    if(isset($_COOKIE['haveseen']))
        $excluded = $_COOKIE['haveseen'].','.get_the_ID();
    $args = array(
                  'numberposts'     => $number_posts,
                  'offset'          => 0,
                  'category'        => 0,
                  'tag'             => $tag_slug,
                  'orderby'         => 'post_date',
                  'order'           => 'RAND',
                  'include'         => '',
                  'exclude'         => $excluded,
                  'meta_key'        => '',
                  'meta_value'      => '',
                  'post_type'       => 'post',
                  'post_mime_type'  => '',
                  'post_parent'     => '',
                  'post_status'     => 'publish' );
    $posts_array = get_posts($args);
    foreach($posts_array as $eachpost) {
        echo "<li><a href=\"" . get_permalink($eachpost->ID) . "\" rel=\"nofollow\">" . get_the_title($eachpost->ID) . "</a></li>";
    }
    echo "</ul>";
}

function set_haveseen_cookie()
{
    if(is_single())
    {
        $seen_posts = array();
        if(isset($_COOKIE['haveseen']))
        {
            $seen_posts = explode(',',$_COOKIE['haveseen']);
        }
        $just_seen = get_the_ID();
        if(!in_array($just_seen,$seen_posts)) {
            array_push($seen_posts,$just_seen);
        }
        setcookie('haveseen', implode(',',$seen_posts), time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
    }
}
add_action( 'wp', 'set_haveseen_cookie');
