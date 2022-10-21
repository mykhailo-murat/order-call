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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front']);

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

    public function enqueue_front()
    {
        wp_enqueue_style('ordercallStyle', plugins_url('/styles.css', __FILE__));
        wp_enqueue_script('ordercallScript', plugins_url('/scripts.js', __FILE__));
    }


    static function order_call_form()
    {
        ob_start(); ?>
        <div class="order-call">
            <form class="order-call__form" action="<? echo $_SERVER['PHP_SELF']?>" method="post">
                <h3 class="order-call__form-title">We can call u</h3>
                <input class="order-call__form-input" type="text" name="name" required="required"
                       placeholder="Enter your name"/>
                <input class="order-call__form-input" type="email" name="email" required="required"
                       placeholder="Enter your email"/>
                <input class="order-call__form-input" type="tel" name="phone" required="required"
                       placeholder="Enter your phone"/>
                <button class="order-call__form-submit" name="submit" type="submit">request a call</button>
            </form>
        </div>
        <?php
        $out = ob_get_clean();

        return $out;
    }

    public static function form_handler($postArray)
    {
        if (isset($postArray['name'], $postArray['email'], $postArray['phone'])) {
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
    }

    function add_admin_page()
    {
        // add top level menu page
        add_menu_page(
            'All Orders', //Page Title
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
            <div class="order-call">
                <p class="order-call__info"><?php esc_html_e('you can use [order_call_form] shortcode to display form', 'default'); ?></p>

                <?php if ($retrieve_data): ?>
                    <div class="order-call__table">
                        <div class="order-call__table-labels order-call__table-row">
                            <p><?php esc_html_e('Name', 'default'); ?></p>
                            <p><?php esc_html_e('Email', 'default'); ?></p>
                            <p><?php esc_html_e('Phone', 'default'); ?></p>
                            <p><?php esc_html_e('Date', 'default'); ?></p>
                        </div>
                        <div class="order-call__table-body">
                            <?php foreach ($retrieve_data as $index => $retrieved_data): ?>
                                <div class="order-call__table-row <?php echo $index % 2 == 0 ? 'dark-row' : ''; ?>">
                                    <div class="order-call__table-cell"><p><?php echo $retrieved_data->name; ?></p>
                                    </div>
                                    <div class="order-call__table-cell"><p><?php echo $retrieved_data->email; ?></p>
                                    </div>
                                    <div class="order-call__table-cell"><p><?php echo $retrieved_data->phone; ?></p>
                                    </div>
                                    <div class="order-call__table-cell"><p><?php echo $retrieved_data->date; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

if (class_exists('OrderCall')) {
    $orderCall = new OrderCall();
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $formSub = new OrderCall();
        $formSub->form_handler($_POST);
        exit();
    }
};

register_activation_hook(__FILE__, array($orderCall, 'insert_table_into_db'));
