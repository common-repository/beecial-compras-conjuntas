<?php

class importerBeecialSyncOrders {

	public static function importBeecialOrders() {
        
        global $woocommerce;

        // Recuperamos configuración del plugin
        $options = get_option( 'beecial_sync_orders_settings' );
        $api_code = $options['beecial_sync_orders_text_field_API_code'];
        $shippment = $options['beecial_sync_orders_select_field_shippment'];
        $payment = $options['beecial_sync_orders_select_field_payment_method'];
        $order_state = $options['beecial_sync_orders_select_field_order_state'];
        
        // Recuperamos pedidos mediante la API
        $url = 'https://api.beecial.com/partnersOrders';
        $args = array(
            'headers' => array(
                'token' => $api_code,
                'Cookie' => 'beetkn='.$api_code.'; PHPSESSID=jpe3v8rpi6sq0alraikghm3btv'
            )
        );
        $response = wp_remote_get( $url, $args );
        $arr_response = json_decode($response['body']);

        $orderCounter = 0;
        $newCustomers = 0;
        $debug = false;

        if(isset( $arr_response->_embedded->order )){
            
            $createCustomerAccount = true;
            // Recuperamos configuración woocommerce para ver si acepta pedidos sin cuenta de cliente
            $pedidosSinCuenta = get_option( 'woocommerce_enable_guest_checkout' );
            $crearCuentaCheckout = get_option( 'woocommerce_enable_signup_and_login_from_checkout' );
            $crearCuentaMyAccount = get_option( 'woocommerce_enable_myaccount_registration' );

            if($pedidosSinCuenta == 'yes' && $crearCuentaCheckout == 'no' && $crearCuentaMyAccount == 'no'){
                $createCustomerAccount = false;
            }

            foreach ( $arr_response->_embedded->order as $key => $importedOrder ) {

                if($createCustomerAccount){

                    // Buscamos si el cliente ya existe
                    $customer = get_user_by( 'email', $importedOrder->billingAddress->email );

                    if(!$customer){
                        // Creamos el cliente (email, username, password) si no existe
                        $username = str_replace( ' ', '_', $importedOrder->billingAddress->firstName ).'_'.str_replace( ' ', '_', $importedOrder->billingAddress->lastName ).'_'.$importedOrder->id;
                        $customer = wc_create_new_customer( $importedOrder->billingAddress->email, $username, rand() );
                        
                        update_user_meta( $customer, "first_name", $importedOrder->billingAddress->firstName );
                        update_user_meta( $customer, "last_name", $importedOrder->billingAddress->lastName );

                        update_user_meta( $customer, "billing_first_name", $importedOrder->billingAddress->firstName );
                        update_user_meta( $customer, "billing_last_name", $importedOrder->billingAddress->lastName );
                        update_user_meta( $customer, "billing_company", $importedOrder->billingAddress->company );
                        update_user_meta( $customer, "billing_address_1", $importedOrder->billingAddress->street );
                        update_user_meta( $customer, "billing_address_2", $importedOrder->billingAddress->street2 );
                        update_user_meta( $customer, "billing_city", $importedOrder->billingAddress->city );
                        update_user_meta( $customer, "billing_postcode", $importedOrder->billingAddress->postalCode );
                        update_user_meta( $customer, "billing_country", $importedOrder->billingAddress->country );
                        update_user_meta( $customer, "billing_state", $importedOrder->billingAddress->state );
                        update_user_meta( $customer, "billing_email", $importedOrder->billingAddress->email );
                        update_user_meta( $customer, "billing_phone", $importedOrder->billingAddress->mobilePhone );

                        update_user_meta( $customer, "shipping_first_name", $importedOrder->shippingAddress->firstName );
                        update_user_meta( $customer, "shipping_last_name", $importedOrder->shippingAddress->lastName );
                        update_user_meta( $customer, "shipping_company", $importedOrder->shippingAddress->company );
                        update_user_meta( $customer, "shipping_address_1", $importedOrder->shippingAddress->street );
                        update_user_meta( $customer, "shipping_address_2", $importedOrder->shippingAddress->street2 );
                        update_user_meta( $customer, "shipping_city", $importedOrder->shippingAddress->city );
                        update_user_meta( $customer, "shipping_postcode", $importedOrder->shippingAddress->postalCode );
                        update_user_meta( $customer, "shipping_country", $importedOrder->shippingAddress->country );
                        update_user_meta( $customer, "shipping_state", $importedOrder->shippingAddress->state );

                        $customerObj = get_user_by( 'id', $customer );
                        $customer = $customerObj;

                        $newCustomers++;

                        // Enviar email al usuario para restablecer password
                        $user = get_userdata( $customer->ID );
                        if(!$debug){
		                    retrieve_password( $user->user_login );
                        }
                    }
                }
                
                //Creamos el pedido
                $order = wc_create_order();

                // Asignamos la fecha del pedido
                // No asignamos fecha para que se guarde la fecha del momento de la importación
                //$order->set_date_created( wc_string_to_datetime( $importedOrder->updatedAt ) );

                // Creamos las direcciones del pedido (envío y facturación) y las asignamos al pedido
                $address_shipping = array(
                    'first_name' => $importedOrder->shippingAddress->firstName,
                    'last_name'  => $importedOrder->shippingAddress->lastName,
                    'company'    => $importedOrder->shippingAddress->company,
                    'email'      => $importedOrder->shippingAddress->email,
                    'phone'      => $importedOrder->shippingAddress->mobilePhone,
                    'address_1'  => $importedOrder->shippingAddress->street,
                    'address_2'  => $importedOrder->shippingAddress->street2,
                    'city'       => $importedOrder->shippingAddress->city,
                    'state'      => $importedOrder->shippingAddress->state,
                    'postcode'   => $importedOrder->shippingAddress->postalCode,
                    'country'    => $importedOrder->shippingAddress->country
                );

                $address_billing = array(
                    'first_name' => $importedOrder->billingAddress->firstName,
                    'last_name'  => $importedOrder->billingAddress->lastName,
                    'company'    => $importedOrder->billingAddress->company,
                    'email'      => $importedOrder->billingAddress->email,
                    'phone'      => $importedOrder->billingAddress->mobilePhone,
                    'address_1'  => $importedOrder->billingAddress->street,
                    'address_2'  => $importedOrder->billingAddress->street2,
                    'city'       => $importedOrder->billingAddress->city,
                    'state'      => $importedOrder->billingAddress->state,
                    'postcode'   => $importedOrder->billingAddress->postalCode,
                    'country'    => $importedOrder->billingAddress->country
                );
                
                $order->set_address( $address_billing, 'billing' );
                $order->set_address( $address_shipping, 'shipping' );

                // Asignamos el pedido al usuario
                if($createCustomerAccount){
                    $order->set_customer_id( $customer->ID );
                }

                // Añadimos el producto(s) en el pedido
                foreach ( $importedOrder->items as $item ) {
                    // Buscamos el producto a traves de su SKU
                    $productId = wc_get_product_id_by_sku( $item->reference );
                    if( $productId ){
                        $price = [
                            //'subtotal'  => $item->price,
                            'total'     => $item->priceWithoutTaxes,
                        ];
                        $order->add_product( wc_get_product( $productId ), $item->quantity, $price );
                    }else{
                        // No existe el producto
                        // Eliminamos el pedido sino se ha podido asignar el producto
                        wp_delete_post( $order->get_id(), true );
                        $response = array(
                            "status" => "Error",
                            "message" => 'No existe el producto con el SKU: '.$item->reference.' en la tienda. Pedido con la ref. '.$importedOrder->reference.' no importado.'
                        );
                        self::escribirLog("Error: " . $response['message']);
                        echo json_encode($response);
                        return;
                    }
                }

                // Asignamos el método de pago según la configuración del plugin
                $order->set_payment_method( $payment );
                $order->set_currency( $importedOrder->payment->currency );

                // Asignamos el estado del pedido según la configuración del plugin
                /*** NO LO USAMOS YA QUE PROVOCA QUE SE ENVIE UN CORREO AL CLIENTE ***/
                /*** EN SU LUGAR USAMOS wp_update_post USADO MÁS ABAJO UNA VEZ SE GRABA EL PEDIDO ***/
                //$order->set_status($order_state, 'Pedido importado desde Beecial (#'.$importedOrder->reference.')', TRUE);

                // Asignamos el método de envío según la configuración del plugin
                $shipping = new WC_Order_Item_Shipping();
                $shipping->set_method_id( $shippment );
                $shipping->set_total( $importedOrder->shippment->shippmentWithoutTaxes );
                $order->add_item( $shipping );

                // Calculamos el total del pedido
                $order->calculate_totals();

                // Asignamos la referencia del pedido a un custom meta field
                $order->update_meta_data( 'reference', $importedOrder->reference );

                $order->set_created_via( 'Plugin: Beecial - Compras Conjuntas' );

                if($order->save() == ''){
                    $response = array(
                        "status" => "Error",
                        "message" => 'Error al crear el pedido.'
                    );
                    self::escribirLog("Error: " . $response['message']);
                    echo json_encode( $response );
                    return;
                }

                // NO usamos el método anterior ya que provoca que se mande un email al cliente al actualizar el estado del pedido
                wp_update_post( ['ID' => $order->get_id(), 'post_status' => $order_state] );

                if(!$debug){
                    // Actualizamos el estado del pedido mediante el API.
    				$status = 4;
                    $arr_response_upd_status = self::upd_beecial_order_status($api_code, $importedOrder->id, $importedOrder->reference, $status);

                    // En caso de error al actualizar el estado del pedido con la API, mostramos el error que nos devuelve la API.
                    if($arr_response_upd_status->code != 1001){
                        $return_response['status'] = 'Error';
                        $return_response['message'] = $arr_response_upd_status['message'];
                        self::escribirLog("Error: " . $return_response['message'] . ".");
                        return $return_response;
                    }
                }

                $orderCounter++;
            }
        }

        // VALIDACIÓN CONEXIÓN API
        if( isset( $arr_response->_embedded->order ) ){
            $response = array(
                "status" => "Success",
                "message" => $orderCounter.' pedidos importados y '.$newCustomers.' clientes nuevos creados.'
            );
		    self::escribirLog($response['message']);
        }elseif( $arr_response->count == 0 ){
            $response = array(
                "status" => "Success",
                "message" => 'No hay pedidos para importar.'
            );
		    self::escribirLog($response['message']);
        }else{
            $response = array(
                "status" => "Error",
                "message" => 'Ha ocurrido un error al conectar con la API. Revise el código API y vuelva a intentarlo.'
            );
		    self::escribirLog("Error: " . $response['message']);
        }
        echo json_encode($response);
    }

    // Función que realiza una conexión a la API para recuperar los pedidos.
    public static function api_connect_beecial($num_api){

        $url = 'https://api.beecial.com/partnersOrders';
        $args = array(
            'headers' => array(
                'token' => $num_api,
                'Cookie' => 'beetkn='.$num_api.'; PHPSESSID=jpe3v8rpi6sq0alraikghm3btv'
            )
        );
        $response = wp_remote_get( $url, $args );
        $arr_response = json_decode($response['body']);

        if( isset($arr_response->page) ){
            $response = array(
                "status" => "Success",
                "message" => 'Conexión realizada correctamente con la API.'
            );
        }else{
            $response = array(
                "status" => "Error",
                "message" => 'Ha ocurrido un error al conectar con la API. Revise el código API y vuelva a intentarlo.'
            );
        }

        echo json_encode($response);
    }

    // Función que realiza una actualización del estado del pedido.
    public static function upd_beecial_order_status($num_api, $id_order, $reference, $status){

        // Actualizamos pedido mediante la API
        $url = 'https://api.beecial.com/orders/'.$id_order;
        $data = array(
            'order' => array(
                'reference' => $reference,
                'status' => $status,
            )
        );
        $args = array(
            'headers' => array(
                'token' => $num_api,
                'Content-Type: text/plain',
                'Cookie' => 'beetkn='.$num_api.'; PHPSESSID=jpe3v8rpi6sq0alraikghm3btv'
            ),
            'method' => 'PUT',
            'body' => json_encode($data)
        );

        $response = wp_remote_request( $url, $args );
        $arr_response = json_decode($response['body']);

        return $arr_response;
    }

    ///////////////////// FUNCIONES DE LOG
    function escribirLog($texto,$activo='si') {
        if($activo=="si"){
            // Log
            if (!file_exists(plugin_dir_path( __FILE__ ).'../../logs')) {
                mkdir(plugin_dir_path( __FILE__ ).'../../logs', 0777, true);
            }
            $logfilename = plugin_dir_path( __FILE__ ).'../../logs/beecialLog.log';
            file_put_contents($logfilename, date('M d Y G:i:s') . ' -- ' . $texto . "\r\n", is_file($logfilename)?FILE_APPEND:0);
        }
    }

}