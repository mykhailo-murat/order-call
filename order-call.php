<?php

/**
 * Plugin Name:       order call
 * Plugin URI:        https://github.com/mykhailo-murat/
 * Description:       oder call
 * Version:           1
 * Requires PHP:      7.4
 * Author:            murat
 * Author URI:        https://github.com/mykhailo-murat/
 * Domain Path:       order-call
 */

defined('ABSPATH') or die();

class OrderCall
{

    public function __construct()
    {
        add_shortcode('order_call_form', array($this, 'order_call_form'));

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);

        add_action('admin_menu', [$this, 'add_admin_page']);
    }

    function insert_table_into_db()
    {
        global $wpdb;
        // set the default character set and collation for the table
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'ordercall_table';
        // Check that the table does not already exist before continuing
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name varchar(50) NOT NULL,
		email varchar(50) NOT NULL,
		phone varchar(50) NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        $is_error = empty($wpdb->last_error);
        return $is_error;
    }

    public function enqueue_admin()
    {
        wp_enqueue_style('ordercallStyle', plugins_url('/admin/styles.css', __FILE__));
        wp_enqueue_script('ordercallScript', plugins_url('/admin/scripts.js', __FILE__));
    }

    static function order_call_form()
    {
        ob_start(); ?>
        <form action="" method="post">
            <div>
                <label for="name">Name:</label>
                <input type="text" name="name" required="required" placeholder="Enter your name"/>
            </div>

            <div>
                <label for="name">Email:</label>
                <input type="email" name="email" required="required" placeholder="Enter your email"/>
            </div>

            <div>
                <label for="phone">Phone:</label>
                <input type="tel" name="phone" required="required" placeholder="Enter your phone"/>
            </div>

            <button name="submit" type="submit">request a call</button>
        </form>
        <?php
        $out = ob_get_clean();

        return $out;
    }

    function add_admin_page()
    {
        // add top level menu page
        add_menu_page(
            'all orders', //Page Title
            'Order Call', //Menu Title
            'manage_options', //Capability
            'order-call', //Page slug
            array($this, 'admin_page_html') //Callback to print html
        );
    }

    function admin_page_html()
    {
        global $wpdb;
        ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
            <!-- Print the page title -->
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php
            // this adds the prefix which is set by the user upon instillation of wordpress
            $table_name = $wpdb->prefix . 'ordercall_table';
            // this will get the data from your table
            $retrieve_data = $wpdb->get_results("SELECT * FROM $table_name");
            ?>
            <div class="db">

                <?php foreach ($retrieve_data as $retrieved_data): ?>
                    <ul class="db_row">
                        <li><?php echo $retrieved_data->name; ?></li>
                        <li><?php echo $retrieved_data->email; ?></li>
                        <li><?php echo $retrieved_data->phone; ?></li>
                        <li><?php echo $retrieved_data->date; ?></li>
                    </ul>
                <?php endforeach; ?>

            </div>
        </div>
        <?php
    }
}

if (class_exists('OrderCall')) {
    $orderCall = new OrderCall();
//    $orderCall->form_handler();
};

register_activation_hook(__FILE__, array($orderCall, 'insert_table_into_db'));

if (isset($_REQUEST['name'], $_REQUEST['email'], $_REQUEST['phone'])) {
    global $wpdb;
    $date = date('Y/m/d H:i:s');
    $name = $_REQUEST['name'];
    $email = $_REQUEST['email'];
    $phone = $_REQUEST['phone'];
    $table_name = $wpdb->prefix . 'ordercall_table';
    $wpdb->insert($table_name, array(
        'date' => $date,
        'name' => $name,
        'email' => $email,
        'phone' => $phone
    ));
    echo "Thanks $name for your request.<br>";
} else {
    echo 'You need to fill all fields';
}