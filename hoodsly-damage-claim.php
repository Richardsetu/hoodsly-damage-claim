<?php
/**
 * Plugin Name: Hoodsly Damage Claim
 * Plugin URI:  https://keendevs.com
 * Description: This is a damage claim plugin for woocommerce.
 * Version:     1.0.0
 * Author:      KeenDevs
 * Author URI:  https://keendevs.com
 * Text Domain: damageclaim
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin Main Class
 */
final class damage_claim {

    /**
     * Order Details Property
     *
     * @var array
     */
    public $order_details = [];

    /**
     * Plugin Constructor
     */
    public function __construct() {

        add_action('init', [$this, 'hoodsly_order_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'damage_claim_assets_loader'] ); 
        add_action('wp_enqueue_scripts', [$this, 'damage_claim_assets_loader'] ); 
        
    }

    /**
     * Plugin Hooks handler
     *
     * @return void
     */
    public function hoodsly_order_scripts() {
        add_filter( 'gform_pre_render_1', [$this, 'my_orders'] );
        if ( is_user_logged_in() ){
        add_action( "wp_ajax_order_item_details", [$this, "order_item_details"] );
        add_action( "wp_ajax_nopriv_order_item_details", [$this, "order_item_details"] );
        } else {
            add_action('wp_head', [$this, 'damage_g_form_style']);
        }
    }

    /**
     * Plugin Assets loader
     *
     * @return void
     */
    function damage_claim_assets_loader() {

        wp_enqueue_script( 'damage-claim-ajax', plugins_url('', __FILE__)."/assets/ajax.js", ['jquery'], time(), true );

        wp_localize_script('damage-claim-ajax', 'damageClaim', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'damage_nonce' => wp_create_nonce('damage-claim-nonce')
        ]);

        wp_register_style('damage-claim-select-style', plugins_url('', __FILE__)."/lib/select2.min.css", null);
        wp_register_style('damage-claim-style', plugins_url('', __FILE__)."/assets/damage.css", null, time());
        
        wp_enqueue_style('damage-claim-select-style');
        wp_enqueue_style('damage-claim-style');

        wp_enqueue_script( 'damage-claim-select', plugins_url('', __FILE__)."/lib/select2.min.js", ['jquery'], time(), true );
    }

    /**
     * Form Handler
     *
     * @param [type] $form
     * @return void
     */
    public function my_orders( $form ) {
        if ( is_user_logged_in() ){
            $user_id = get_current_user_id(); // current user ID here for example
            
            $args = array(
                'limit'=>-1,
                'customer_id' => $user_id
            );

            $orders = wc_get_orders($args);

            $order_id = [
                [
                    "value" => '#',
                    "text"  => 'select',
                ]
            ];

            foreach ( $orders as $order ) {  
                $order_id[] = [
                    "value" => $order->get_id(),
                    "text"  => "#".$order->get_id()
                ];
            }

            foreach ( $form["fields"] as &$field ) {
                if ( $field["label"] == 'Order Or Invoice #' ) {
                    //$field["type"]    = "select";
                    $field["choices"] = $order_id;
                }
            }

        }  else {
            ?>
                <div class="damage-claim-login">Please <a href="<?php echo wp_login_url( get_permalink() ); ?>"><button><i class="_mi _before fa fa-user-o" aria-hidden="true"></i> Login</button></a></div>
            <?php
        }
    
        return $form;
      
    }

    /**
     * G form invisible method if user not login
     *
     * @return void
     */
    public function damage_g_form_style() {
        ?>
        <style>
            #gform_1{
                display: none;
            }
            .uael-gf-style, .uael-gf-form-title{
                text-align: center !important;
            }
        </style>
        <?php
    }

    /**
     * Get Order Items details
     * 
     * Ajax Function
     *
     * @param [type] $order_id
     * @return void
     */
    public function order_item_details( $order_id ) {

            $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
            $order = wc_get_order( $order_id );
            $items = $order->get_items();

            foreach ( $items as $item_key => $item ) {

                $formatted_meta_data = $item->get_formatted_meta_data( '_', true );

                foreach ( $formatted_meta_data as $product_data ){

                    $this->order_details[] = $product_data->display_key . ": " . $product_data->display_value;
                    
                }

            }
            echo json_encode($this->order_details);
            exit;
    }
}

new damage_claim;
