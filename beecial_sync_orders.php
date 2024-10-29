<?php
/*
Plugin Name: Beecial - Compras Conjuntas
Plugin URI: Beecial.com
Description: Importador de pedidos masivos mediante la API de Beecial
Version: 1.0.1
Author: Beecial
License: GPL
*/

require_once( plugin_dir_path( __FILE__ ) . 'admin/classes/importerBeecialOrders.php' );

/**
 *
 * Check si el plugin de Woocommerce está instalado y activado
 */

if ( ! function_exists( 'is_plugin_active' ) )
require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ){
    $msg = 'El plugin "Beecial - Compras Conjuntas", requiere tener instalado y activado el plugin de Woocommerce"';
    add_action('admin_notices', function() use ( $msg ) { general_notice_message( $msg ); });
}

/**
 *
 * Muestra alertas generales de WP
 */

function general_notice_message($msg) {
	echo '<div class="notice notice-error is-dismissible"><p>'.esc_html($msg).'</p></div>';
}


/**
 *
 * Añadimos enlace de configuración en la vista de listado de plugins
 */

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'beecial_sync_orders_add_plugin_config_link' );

function beecial_sync_orders_add_plugin_config_link( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'importador_pedidos_beecial',
		get_admin_url() . 'admin.php'
	) );
	$settings_link = "<a href='$url'>" . __( 'Configuración' ) . '</a>';
	array_push(
		$links,
		$settings_link
	);
	return $links;
}


/**
 *
 * Añadimos acceso al plugin en el menu
 */

add_action( 'admin_menu', 'beecial_sync_orders_add_admin_menu' );

function beecial_sync_orders_add_admin_menu(  ) { 
	add_menu_page( 'Beecial - Compras Conjuntas', 'Beecial - Compras Conjuntas', 'manage_options', 'importador_pedidos_beecial', 'beecial_sync_orders_options_page' );
}


/**
 *
 * Creamos los campos del formulario de la configuración del plugin
 */

add_action( 'admin_init', 'beecial_sync_orders_settings_init' );

function beecial_sync_orders_settings_init(  ) { 

	register_setting( 'pluginPage', 'beecial_sync_orders_settings' );

    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

        add_settings_section(
            'beecial_sync_orders_pluginPage_section', 
            __( 'Configuración', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_settings_section_callback', 
            'pluginPage'
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_API_code', 
            __( 'Código API', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_API_code_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_selector', 
            __( 'Selector', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_selector_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_product_code', 
            __( 'Código producto', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_product_code_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_mode', 
            __( 'Modo', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_mode_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_attribute', 
            __( 'Atributo', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_attribute_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_button_position', 
            __( 'Posición del botón', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_button_position_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_text_field_js_verbose', 
            __( 'Verbose', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_text_field_js_verbose_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_select_field_shippment', 
            __( 'Método de envío', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_select_field_shippment_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_select_field_payment_method', 
            __( 'Método de pago', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_select_field_payment_method_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_select_field_order_state', 
            __( 'Estado del pedido', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_select_field_order_state_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_checkbox_field_front_active', 
            __( 'Mostrar botón en detalle producto', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_checkbox_field_front_active_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_checkbox_field_front_test_connection', 
            __( 'Test Conexión API', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_checkbox_field_front_test_connection_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

        add_settings_field( 
            'beecial_sync_orders_checkbox_field_front_sync', 
            __( 'Sync', 'beecial_sync_orders_order_import' ), 
            'beecial_sync_orders_checkbox_field_front_sync_render', 
            'pluginPage', 
            'beecial_sync_orders_pluginPage_section' 
        );

    }
}


function beecial_sync_orders_text_field_API_code_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='text' name='beecial_sync_orders_settings[beecial_sync_orders_text_field_API_code]' value='<?php echo esc_attr($options['beecial_sync_orders_text_field_API_code']); ?>' style="width: 300px;">
	<?php

}


function beecial_sync_orders_text_field_js_selector_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='text' name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_selector]' value='<?php echo esc_attr($options['beecial_sync_orders_text_field_js_selector']); ?>'>
	<?php

}


function beecial_sync_orders_text_field_js_product_code_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='text' name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_product_code]' value='<?php echo esc_attr($options['beecial_sync_orders_text_field_js_product_code']); ?>'>
	<?php

}


function beecial_sync_orders_text_field_js_mode_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
    <select name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_mode]'>
        <option value='html' <?php selected( $options['beecial_sync_orders_text_field_js_mode'], 'html' ); ?>>html</option>
        <option value='attr' <?php selected( $options['beecial_sync_orders_text_field_js_mode'], 'attr' ); ?>>attr</option>
	</select>
	<?php

}


function beecial_sync_orders_text_field_js_attribute_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
    <select name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_attribute]'>
        <option value='true' <?php selected( $options['beecial_sync_orders_text_field_js_attribute'], 'true' ); ?>>true</option>
        <option value='false' <?php selected( $options['beecial_sync_orders_text_field_js_attribute'], 'false' ); ?>>false</option>
	</select>
	<?php

}


function beecial_sync_orders_text_field_js_button_position_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
    <select name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_button_position]'>
        <option value='after' <?php selected( $options['beecial_sync_orders_text_field_js_button_position'], 'after' ); ?>>after</option>
        <option value='before' <?php selected( $options['beecial_sync_orders_text_field_js_button_position'], 'before' ); ?>>before</option>
	</select>
	<?php
    

}


function beecial_sync_orders_text_field_js_verbose_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
    <select name='beecial_sync_orders_settings[beecial_sync_orders_text_field_js_verbose]'>
        <option value='true' <?php selected( $options['beecial_sync_orders_text_field_js_verbose'], 'true' ); ?>>true</option>
        <option value='false' <?php selected( $options['beecial_sync_orders_text_field_js_verbose'], 'false' ); ?>>false</option>
	</select>
	<?php

}


function beecial_sync_orders_select_field_shippment_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
    $arr_shipping_methods = array();
    foreach ( WC()->shipping->get_shipping_methods() as $i => $method ) {
        if($method->enabled == 'yes'){
            $arr_shipping_methods[$i]['id_shipping_method'] = $method->id;
            $arr_shipping_methods[$i]['name_shipping_method'] = $method->method_title;
        }
    }
	?>
    <select name='beecial_sync_orders_settings[beecial_sync_orders_select_field_shippment]'>
        <?php   foreach ($arr_shipping_methods as $method) { ?>
                    <option value='<?php echo esc_attr($method["id_shipping_method"]) ?>' <?php selected( $options['beecial_sync_orders_select_field_shippment'], $method["id_shipping_method"] ); ?>>
                        <?php echo esc_attr($method["name_shipping_method"]) ?>
                    </option>
        <?php   }
        ?>
	</select>

<?php

}


function beecial_sync_orders_select_field_payment_method_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
    $arr_payment_methods = array();
    foreach ( WC()->payment_gateways->payment_gateways() as $i => $method ) {
        if($method->enabled == 'yes'){
            $arr_payment_methods[$i]['id_payment_method'] = $method->id;
            $arr_payment_methods[$i]['name_payment_method'] = $method->title;
        }
    }
	?>
	<select name='beecial_sync_orders_settings[beecial_sync_orders_select_field_payment_method]'>
        <?php   foreach ($arr_payment_methods as $method) { ?>
                    <option value='<?php echo esc_attr($method["id_payment_method"]) ?>' <?php selected( $options['beecial_sync_orders_select_field_payment_method'], $method["id_payment_method"] ); ?>>
                        <?php echo esc_attr($method["name_payment_method"]) ?>
                    </option>
        <?php   }
        ?>
	</select>

<?php

}


function beecial_sync_orders_select_field_order_state_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );    
    
    if ( !function_exists( 'wc_get_order_statuses' ) ) { 
        require_once '/includes/wc-order-functions.php'; 
    }

    $order_statuses = wc_get_order_statuses();
    $arr_order_statuses = array();
    $i = 0;
    foreach ($order_statuses as $k => $value) {
        $arr_order_statuses[$i]['slug'] = $k;
        $arr_order_statuses[$i]['name'] = $value;
        $i++;
    }
    
	?>
	<select name='beecial_sync_orders_settings[beecial_sync_orders_select_field_order_state]'>
        <?php   foreach ( $arr_order_statuses as $k => $status) { ?>
                    <option value='<?php echo esc_attr($status['slug']) ?>' <?php selected( $options['beecial_sync_orders_select_field_order_state'], $status['slug'] ); ?>>
                        <?php echo esc_attr($status['name']) ?>
                    </option>
        <?php   }
        ?>
	</select>

<?php

}


function beecial_sync_orders_checkbox_field_front_active_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='checkbox' name='beecial_sync_orders_settings[beecial_sync_orders_checkbox_field_front_active]' <?php checked( $options['beecial_sync_orders_checkbox_field_front_active'], 1 ); ?> value='1'>
	<?php

}

function beecial_sync_orders_checkbox_field_front_test_connection_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='button' id='test-connection' class='button button-secondary' value='Testear configuración'>
    <div id='test-response' style='display:inline-block;'></div>

	<?php

}

function beecial_sync_orders_checkbox_field_front_sync_render(  ) { 

	$options = get_option( 'beecial_sync_orders_settings' );
	?>
	<input type='button' id='force-sync' class='button button-secondary' value='Sincronizar'>
    <div id='sync-response' style='display:inline-block;'></div>
    <small style='display:block;margin-top:5px;'><i>Guarde los cambios antes de sincronizar</i></small>
    
    <a href="<?php echo get_rest_url( null, 'beecial-sync/token/' ); echo generateBeecialSyncOrdersToken(); ?>" target="_blank" style="margin:20px 0;display:block;"><?php echo get_rest_url( null, 'beecial-sync/token/' ); echo generateBeecialSyncOrdersToken(); ?> </a>
    
	<?php
}


function beecial_sync_orders_settings_section_callback(  ) { 

	//echo __( 'Descripción plugin', 'beecial_sync_orders_order_import' );

}


function beecial_sync_orders_options_page(  ) { 

    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){

		?>
        
		<form action='options.php' method='post'>

			<h2>Beecial - Compras Conjuntas</h2>

			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button();
			?>

		</form>

		<?php

    }else{

        ?>

        <h2>Beecial - Compras Conjuntas</h2>

        <div>Este plugin requiere tener instalado y activado el plugin de Woocommerce</div>

        <?php
    }

}

/**
 *
 * Generamos Token para validación
*/
function generateBeecialSyncOrdersToken(){
    $plugin_name = 'beecial_sync_orders';
    $site = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
    $token = md5($plugin_name.'/forcesync/'.$site);
    return $token;
}

/**
 *
 * Mostramos bloque Beecial en pagina de producto de Woocommerce
*/
function add_JS_product_page_BeecialSyncOrders() {
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){
        $options = get_option( 'beecial_sync_orders_settings' );
        $selector = $options['beecial_sync_orders_text_field_js_selector'];
        $product_code = $options['beecial_sync_orders_text_field_js_product_code'];
        $mode = $options['beecial_sync_orders_text_field_js_mode'];
        $attribute = $options['beecial_sync_orders_text_field_js_attribute'];
        $button_position = $options['beecial_sync_orders_text_field_js_button_position'];
        $verbose = $options['beecial_sync_orders_text_field_js_verbose'];
        $enabled = $options['beecial_sync_orders_checkbox_field_front_active'];
        $api_code = $options['beecial_sync_orders_text_field_API_code'];
        if($enabled){
            wp_enqueue_script('beecialSO_lib', 'https://js.beecial.com/'.esc_attr($api_code).'/beecialButton.js', array(), '1', true);
            echo '<script>
                    jQuery(function(){
                        jQuery("'.esc_js($selector).'").BeecialButton(["'.esc_js($product_code).'","'.esc_js($mode).'",'.esc_js($attribute).',"'.esc_js($button_position).'",'.esc_js($verbose).']);
                    });
                  </script>';
        }
    }
}
add_action('woocommerce_after_add_to_cart_button','add_JS_product_page_BeecialSyncOrders');


/**
 *
 * Función callback del endpoint
*/
function beecialSOSyncEndPoint( $data ){
    if ( md5('beecial_sync_orders/forcesync/'.sanitize_text_field( wp_unslash($_SERVER['HTTP_HOST']))) != sanitize_text_field($data["token"]) ) {
        die('Token no válido :(');
    }else{
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){
            $response = importerBeecialSyncOrders::importBeecialOrders();
        }else{
            die('El plugin no está activado en '.sanitize_text_field( wp_unslash($_SERVER['HTTP_HOST'])));
        }
    }
    return $response;
}


/**
 *
 * Registramos la ruta de la endpoint
*/
add_action('rest_api_init', function(){
    register_rest_route('beecial-sync', '/token/(?P<token>[a-zA-Z0-9-]+)', [
        'methods' => 'GET',
        'callback' => 'beecialSOSyncEndPoint'
    ]);
});


/**
 *
 * Registramos las funciones JS para la llamada AJAX
*/
function beecialSO_enqueue_script(){
    wp_register_script('BeecialSO_script', plugins_url('/admin/js/beecialSO_script.js', __FILE__), array('jquery'), '1' );
    wp_localize_script('BeecialSO_script', 'beecialSO_vars', ['ajaxurl' => admin_url('admin-ajax.php')]);
    wp_enqueue_script('BeecialSO_script');
}
add_action('admin_enqueue_scripts', 'beecialSO_enqueue_script');


/**
 *
 * AJAX Call check conexión API
*/
function beecialSO_check_connection(){
    $options = get_option( 'beecial_sync_orders_settings' );
    $api_code = $options['beecial_sync_orders_text_field_API_code'];
    $response = importerBeecialSyncOrders::api_connect_beecial($api_code);
    echo array_map( 'esc_html', $response );
    wp_die();
}
add_action('wp_ajax_check_connection', 'beecialSO_check_connection');


/**
 *
 * AJAX Call sincronización pedidos Beecial
*/
function beecialSO_sync_orders(){
    $response = array();
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ){
        $response = importerBeecialSyncOrders::importBeecialOrders();
    }else{
        $response = json_encode(
            array(
                "status" => "Error",
                "message" => 'El plugin Woocommerce no está activado.'
            )
        );
    }
    echo array_map( 'esc_html', $response );
    wp_die();
}
add_action('wp_ajax_sync_orders', 'beecialSO_sync_orders');

 
/**
 *
 * Flush rewrite rules on activation
 */
function beecialSO_rewrite_activation()
{
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'beecialSO_rewrite_activation' );
register_activation_hook( __FILE__, 'beecialSO_rewrite_activation' );