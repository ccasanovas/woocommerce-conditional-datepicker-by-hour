// LLAMO AL PLUGIN DE JQUERY
add_action( 'wp_enqueue_scripts', 'enabling_date_picker' );

function enabling_date_picker() {

    // ESTE BLOQUE ADMITE QUE SOLAMENTE SE VEA EN EL FRONT
    if( is_admin() || ! is_checkout() ) return;

    // CARGO EL DATEPICKER DE JQUERY
    wp_enqueue_script( 'jquery-ui-datepicker' );
}

// LLAMO LA FUNCIONALIDAD DE DATEPICKER DENTRO DE MI BLOQUE PERSONALIZADO
add_action('woocommerce_after_order_notes', 'my_custom_checkout_field');
function my_custom_checkout_field( $checkout ) {

    date_default_timezone_set('Argentina/Buenos_Aires');
    $mydateoptions = array('' => __('Select PickupDate', 'woocommerce' ));

    echo '<div id="my_custom_checkout_field">
    <h3>'.__('Fecha de envío').'</h3>';

// DECLARO LA HORA DEL SERVER $HORA_ACTUAL, LA HORA DE CORTE $HORA_CORTE Y LA HORA DE NUEVO INICIO $HORA_INICIO 
$hora_actual = Date("H",time());
$hora_corte = date("18", time());
$hora_inicio = date("00",time());
// DECLARO QUE SI LA HORA ACTUAL ES MAYOR A LA DE CORTE, ENTONCES LLAMO UN SCRIPT JQUERY DATEPICKER QUE ESTABLECE 2 DÍAS MINIMO EN ADELANTE (minDate)
    if ($hora_actual > $hora_corte) {
    echo '
    <script>
        jQuery(function($){
          jQuery("#datepicker").datepicker({
                minDate: 2 ,
                    beforeShowDay: function(date) {
                        var day = date.getDay();
                        return [(day != 1 )];
                 }
                });
        });
    </script>'; 
// DECLARO QUE SI LA HORA ACTUAL ES MAYOR QUE LA DE NUEVO INICIO DEL DÍA, ENTONCES LLAMO UN SCRIPT JQUERY DATEPICKER QUE ESTABLECE 1 DÍA MINIMO EN ADELANTE (minDate)
    } elseif ($hora_actual > $hora_inicio) {
        echo  '
    <script>
        jQuery(function($){
          jQuery("#datepicker").datepicker({
                        minDate: 1 ,
                    beforeShowDay: function(date) {
                        var day = date.getDay();
                        return [(day != 1 )];
                 }
                });
        });
    </script>'; 
    }
// PARA TODO LO DEMÁS NO SE ESTABLECE NADA, SE CIERRA EL BLOQUE CONDICIONAL
    else { 
	    
    }
// ARMO EL CAMPO DE WOOCOMMERCE QUE ES UN INPUT EN FORMATO DE TEXTO ((NOO TOCAR!))
   woocommerce_form_field( 'cylinder_collect_date', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'id'            => 'datepicker',
        'required'      => true,
        'label'         => __('Seleccionar fecha de envío'),
        'placeholder'       => __('Fecha'),
        'options'     =>   $mydateoptions
        ),
    $checkout->get_value( 'cylinder_collect_date' ));

    echo '</div>';
}

// PROCESO EL CHECKOUT
 
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {
    global $woocommerce;

 // ESTO ES UNA PRUEBA QUE DICE EN PHP SI EL POST ES DISTINTO A UNA FECHA SELECCIONADA (NADA) ENTONCES DIGO QUE ES UN CAMPO REQUERIDO. LLAMO UN CODIGO DE ERROR DE WORDPRESS.
    if (!$_POST['cylinder_collect_date'])
         wc_add_notice( '<strong>Seleccionar fecha de envío</strong> ' . __( 'es un campo requerido.', 'woocommerce' ), 'error' );
}

// ACTUALIZO LOS META CON LO QUE RECOLECTA DEL CAMPO PERSONALIZADO.

 add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');

 function my_custom_checkout_field_update_order_meta($order_id) {

if (!empty($_POST['cylinder_collect_date'])) {
    update_post_meta($order_id, 'Día de envío', sanitize_text_field($_POST['cylinder_collect_date']));
}
}

add_filter( 'woocommerce_order_details_after_order_table', 'add_delivery_date_to_order_received_page', 10 , 1 );
function add_delivery_date_to_order_received_page ( $order ) {
    if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
        $order_id = $order->get_id();
    } else {
        $order_id = $order->id;
    }
    $delivery_date = get_post_meta( $order_id, 'Día de envío', true );
    
    if ( '' != $delivery_date ) {
        echo '<p><strong>' . __( 'Día de envío', 'add_extra_fields' ) . ':</strong> ' . $delivery_date;
    }
}

add_filter( 'woocommerce_email_order_meta_fields', 'add_delivery_date_to_emails' , 10, 3 );
function add_delivery_date_to_emails ( $fields, $sent_to_admin, $order ) {
    if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {            
        $order_id = $order->get_id();
    } else {
        $order_id = $order->id;
    }
    $delivery_date = get_post_meta( $order_id, 'Día de envío', true );
    if ( '' != $delivery_date ) {
        $fields[ 'cylinder_collect_date' ] = array(
        'label' => __( 'Día de envío', 'add_extra_fields' ),
        'value' => $delivery_date,
        );
     }
    return $fields;
}
