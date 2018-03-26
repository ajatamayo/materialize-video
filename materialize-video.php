<?php
/**
 * Plugin Name: Materialize Video
 * Description: Display videos as Materialize Cards. Captions will be shown as card contents if specified.
 * Plugin URI:  https://github.com/ajatamayo/materialize-video
 * Version:     1.1
 * Author:      AJ Tamayo
 * Author URI:  https://github.com/ajatamayo
 * License:     GPL
 * Text Domain: materialize-video
 * Domain Path: /languages
 *
 */

add_action( 'plugins_loaded', array( Materialize_Video::get_instance(), 'plugin_setup' ) );

class Materialize_Video {
    protected static $instance = NULL;
    public $plugin_url = '';
    public $plugin_path = '';

    /**
     *
     * @since 1.0
     */
    public function __construct() {}

    /**
     *
     * @since 1.0
     */
    public function load_language( $domain ) {
        load_plugin_textdomain(
            $domain,
            FALSE,
            $this->plugin_path . '/languages'
        );
    }

    /**
     *
     * @since 1.0
     */
    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }

    /**
     *
     * @since 1.0
     */
    public function plugin_setup() {
        $this->plugin_url    = plugins_url( '/', __FILE__ );
        $this->plugin_path   = plugin_dir_path( __FILE__ );
        $this->load_language( 'materialize-video' );

        // Change video shortcode to use cards
        add_filter( 'wp_video_shortcode', array( &$this, 'wrap_video_in_card' ), 10, 5 );

        add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 10 );
    }

    /**
     *
     * @since 1.1
     */
    function wrap_video_in_card( $output, $atts, $video, $post_id, $library ) {
        $post = $this->getDetailsFromGUID( $atts['mp4'] );

        $caption = $post->post_excerpt;
        $description = $post->post_content;

        $classes = array( 'card' );
        if ( !empty( $caption ) || !empty( $description ) ) {
            $classes[] = 'has-caption';
        }
        $classes = implode( ' ', $classes );

        ob_start();

        ?>

        <div class="<?php echo $classes; ?>">
            <?php echo $output; ?>
            <?php if ( !empty( $caption ) || !empty( $description ) ) : ?>
                <div class="card-content">
                    <?php if ( !empty( $caption ) ) : ?>
                        <p><?php echo $caption; ?></p>
                    <?php endif; ?>
                    <?php if ( !empty( $description ) ) : ?>
                        <p class="description"><?php echo $description; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php

        return ob_get_clean();
    }

    /**
     *
     * @since 1.0
     */
    function enqueue_scripts() {
        wp_enqueue_style( 'materialize-video', $this->plugin_url . "public/styles/video.css", array(), '1.0' );
    }

    /**
     *
     * @since 1.1
     */
    function getDetailsFromGUID( $guid ) {
        global $wpdb;
        $post = $wpdb->get_row( $wpdb->prepare( "SELECT post_content, post_excerpt FROM $wpdb->posts WHERE guid=%s", $guid ), OBJECT );
        return $post;
    }
}

?>
