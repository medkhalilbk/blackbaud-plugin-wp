<?php

class Blackbaud_donations {

    private static $debug = true;
    private static $initiated = false;
    private static $funds = null;
    private static $donationCookieName = 'donationCartId';

    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks()
    {
        self::$initiated = true;

        add_action('wp_enqueue_scripts', array('Blackbaud_donations', 'add_scripts'), 26);
        add_action('wp_ajax_add_donation_to_user_cart', array('Blackbaud_donations', 'add_donation_to_user_cart'));
        add_action('wp_ajax_nopriv_add_donation_to_user_cart', array('Blackbaud_donations', 'add_donation_to_user_cart'));
        add_action('wp_ajax_remove_donation_from_user_cart', array('Blackbaud_donations', 'remove_donation_from_user_cart'));
        add_action('wp_ajax_nopriv_remove_donation_from_user_cart', array('Blackbaud_donations', 'remove_donation_from_user_cart'));
        add_action('wp_ajax_complete_checkout_transaction', array('Blackbaud_donations', 'complete_checkout_transaction'));
        add_action('wp_ajax_nopriv_complete_checkout_transaction', array('Blackbaud_donations', 'complete_checkout_transaction'));
        add_action('wp_ajax_change_quantity', array('Blackbaud_donations', 'change_quantity'));
        add_action('wp_ajax_nopriv_change_quantity', array('Blackbaud_donations', 'change_quantity'));
    }

    /**
     * Subtract quantity from a specific fund in a users cart
     */
    public static function change_quantity() {
        $id = intval($_POST['id']);
        $operation = ($_POST['operation'] === 'add') ? 'add' : 'subtract';
        $quantity = (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) ? intval($_POST['quantity']) : 1;

        if(empty($id) || empty($quantity) ) {
            return;
        }

        self::subtract_or_add_quantity_in_cart($id, $quantity, $operation);

        header("Content-Type: application/json");

        $donation = self::get_donation($id);
        $donations_total = self::get_donations_total();
        $donations_total_pence = self::get_donations_total(2, '', '');

        echo json_encode([
            'qty' => $donation->quantity,
            'fund_total' => number_format($donation->amount * $donation->quantity, 2, '.', ''),
            'total' => BLACKBAUD_DONATIONS__CURRENCY . $donations_total,
            'total_pence' => $donations_total_pence,
            'giftaid_total' => BLACKBAUD_DONATIONS__CURRENCY . self::get_donations_giftaid_total($donations_total),
        ]);
        exit;
    }

    /**
     * Get total of possible gift aid donation for the cart total
     *
     * @param $total
     * @return string
     */
    public static function get_donations_giftaid_total($total) {
        return number_format(str_replace(',', '', $total) * 0.25, 2);
    }

    /**
     * Add a donation to the users cart
     */
    public static function add_donation_to_user_cart() {
        $id = sanitize_text_field($_POST['id']);
        $type = (isset($_POST['type']) && in_array($_POST['type'], ['single', 'recurring'])) ? $_POST['type'] : 'single';
        $amount = ($_POST['amount'] == 'other') ? sanitize_text_field($_POST['otherAmount']) : sanitize_text_field($_POST['amount']);
        $quantity = (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) ? intval($_POST['quantity']) : 1;
        $is_quick_appeal_item = (isset($_POST['is_quick_appeal_item'])) ? 1 : 0;

        if(empty($id) || empty($type) || empty($amount)  || empty($quantity) ) {
            return;
        }

        self::add_donation($id, $type, $amount, $quantity, $is_quick_appeal_item);

        ob_start();
        require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/quick_donate_sidebar.php';
        $html = ob_get_clean();

        header("Content-Type: application/json");
        echo json_encode([
            'html' => $html,
            'total' => BLACKBAUD_DONATIONS__CURRENCY . self::get_donations_total(),
            'total_pence' => self::get_donations_total(2, '', ''),
        ]);
        exit;
    }

    /**
     * Remove a donation from the users cart
     */
    public static function remove_donation_from_user_cart() {
        self::remove_donation(sanitize_text_field($_POST['id']));

        ob_start();
        require_once BLACKBAUD_DONATIONS__PLUGIN_DIR . '/templates/quick_donate_sidebar.php';
        $html = ob_get_clean();

        header("Content-Type: application/json");

        $donations_total = self::get_donations_total();
        $donations_total_pence = self::get_donations_total(2, '', '');

        echo json_encode([
            'html' => $html,
            'total' => BLACKBAUD_DONATIONS__CURRENCY . $donations_total,
            'total_pence' => $donations_total_pence,
            'giftaid_total' => BLACKBAUD_DONATIONS__CURRENCY . self::get_donations_giftaid_total($donations_total),
        ]);
        exit;
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
     * @static
     */
    public static function plugin_activation()
    {
//        if ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
            add_option( 'Activated_Blackbaud_donations', true );
//        }

        self::blackbaud_donations_install_db();
    }

    /**
     * Create necessary DB tables
     */
    public static function blackbaud_donations_install_db()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . self::get_donations_tablename() . " (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
  cart_id varchar(55) DEFAULT '' NOT NULL,
  status enum('pending','completed') NOT NULL DEFAULT 'pending',
  PRIMARY KEY  (id)
) $charset_collate;";

        $sql2 = "CREATE TABLE " . self::get_donations_items_tablename() . " (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  donation_id mediumint(9) NOT NULL DEFAULT 0,
  type varchar(7) DEFAULT '' NOT NULL,
  description varchar(55) DEFAULT '' NOT NULL,
  fund_type varchar(55) DEFAULT '' NOT NULL,
  amount decimal(10,2) DEFAULT 0 NOT NULL,
  fund_id varchar(55) DEFAULT '' NOT NULL,
  quantity mediumint(9) NOT NULL DEFAULT 1,
  is_quick_appeal_item mediumint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  INDEX donation_id (donation_id),
  INDEX fund_id (fund_id)
) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        dbDelta( $sql2 );

        update_option( 'blackbaud_donations_db_version', BLACKBAUD_DONATIONS__DB_VERSION );
    }

    /**
     * Removes all connection options
     * @static
     */
    public static function plugin_deactivation( )
    {
        \blackbaud\Api_auth::logout();
    }

    /**
     * Get table name for the donations table
     *
     * @return string
     */
    public static function get_donations_tablename()
    {
        global $wpdb;

        return $wpdb->prefix . 'donations';
    }

    /**
     * Get table name for the donation items table
     *
     * @return string
     */
    public static function get_donations_items_tablename()
    {
        global $wpdb;

        return $wpdb->prefix . 'donations_items';
    }

    /**
     * Gets unique cart ID from the cookie. Creates one if not found
     *
     * @param bool $setCookie
     * @return string
     */
    public static function get_cart_id( $setCookie = true )
    {
        $cart_id = isset($_COOKIE[self::$donationCookieName]) ? $_COOKIE[self::$donationCookieName] : wp_hash(time() . rand());

        if ($setCookie) {
            $_COOKIE[self::$donationCookieName] = $cart_id;
            $domain = '.' . str_replace('www.', '', parse_url(get_site_url(), PHP_URL_HOST));
            setcookie(self::$donationCookieName, $cart_id, strtotime('+1 month'), '/', $domain, true, true);
        }

        return $cart_id;
    }

    /**
     * Gets unique donation ID from the DB. Creates it if it doesn't exist.
     */
    public static function get_donation_id( $cart_id )
    {
        global $wpdb;

        $table_name = self::get_donations_tablename();

        $donations = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name WHERE cart_id = %s AND status = 'pending' LIMIT 0,1", $cart_id));

        if ( ! isset($donations->id) || ! $donations->id ) {
            $wpdb->insert($table_name, [
                'cart_id' => $cart_id,
                'time' => date('Y-m-d H:i:s'),
            ]);

            return $wpdb->insert_id;
        }

        return $donations->id;
    }

    /**
     * Marks a donation as completed
     */
    public static function mark_donation_completed( $cart_id )
    {
        global $wpdb;

        $table_name = self::get_donations_tablename();

        $donations = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name WHERE cart_id = %s AND status = 'pending' LIMIT 0,1", $cart_id));

        if (isset($donations->id) && $donations->id > 0) {
            $wpdb->update($table_name, ['status' => 'completed'], ['id' => $donations->id]);
            return true;
        }

        return false;
    }

    /**
     * Subtract /Add a quantity of 1 from a specific fund in a users cart
     *
     * @param $id
     * @param $quantity
     * @param $operation
     * @return bool
     */
    public static function subtract_or_add_quantity_in_cart($id, $quantity = 1, $operation = 'subtract')
    {
        global $wpdb;

        $donation_id = self::get_donation_id(self::get_cart_id());

        $table_name = self::get_donations_items_tablename();

        $donations = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name WHERE donation_id = %s AND fund_id = %s LIMIT 0,1", $donation_id, $id));

        if ( ! isset($donations->id) || ! $donations->id) {
            return false;
        }

        $quantity = ($operation === 'subtract') ? $quantity - 1 : $quantity + 1;

        $wpdb->update($table_name, ['quantity' => $quantity], ['id' => $donations->id]);

        return true;
    }

    /**
     * Add a donation to a users cart
     *
     * @param $id
     * @param $type
     * @param $amount
     * @param $quantity
     * @param $is_quick_appeal_item
     * @return bool
     */
    public static function add_donation($id, $type, $amount, $quantity = 1, $is_quick_appeal_item = 0)
    {
        global $wpdb;

        $donation_id = self::get_donation_id(self::get_cart_id());

        $table_name = self::get_donations_items_tablename();

        $donations = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name WHERE donation_id = %s AND fund_id = %s LIMIT 0,1", $donation_id, $id));

        $fund = self::get_appeal_donation_fund($id);

        $data = [
            'amount' => $amount,
            'type' => $type,
            'description' => $fund['name'],
            'fund_type' => $fund['type'],
            'quantity' => $quantity,
            'is_quick_appeal_item' => $is_quick_appeal_item,
        ];

        if (isset($donations->id) && $donations->id > 0) {
            $wpdb->update($table_name, $data, ['id' => $donations->id]);
            return true;
        }

        $wpdb->insert($table_name, array_merge(['donation_id' => $donation_id, 'fund_id' => $id], $data));

        return true;
    }

    /**
     * Remove a users donation from their cart
     *
     * @param $fund_id
     * @return bool
     */
    public static function remove_donation($fund_id)
    {
        global $wpdb;

        if ( ! isset($_COOKIE[self::$donationCookieName]) ) {
            return false;
        }

        $donation_id = self::get_donation_id(self::get_cart_id(false));

        return $wpdb->delete( self::get_donations_items_tablename(), array( 'fund_id' => $fund_id,  'donation_id' => $donation_id) );
    }

    /**
     * Get all user donations
     *
     * @return array
     */
    public static function get_donations()
    {
        global $wpdb;

        if ( ! isset($_COOKIE[self::$donationCookieName]) ) {
            return [];
        }

        $donations_items_tablename = self::get_donations_items_tablename();
        $donation_id = self::get_donation_id(self::get_cart_id(false));

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $donations_items_tablename WHERE donation_id = %s", $donation_id));

        $donations = [];

        if (is_array($results)) {
            foreach($results as $result) {
                $donations[$result->fund_id] = $result;
            }
        }

        return $donations;
    }

    /**
     * Get a donation to a specific fund already in the users cart
     *
     * @param $fund_id
     * @return array|null|object
     */
    public static function get_donation($fund_id)
    {
        global $wpdb;

        $donation_id = self::get_donation_id(self::get_cart_id());

        $table_name = self::get_donations_items_tablename();

        $donation = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE donation_id = %s AND fund_id = %s LIMIT 0,1", $donation_id, $fund_id));

        return $donation;
    }

    /**
     * Get donations total
     *
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return float
     */
    public static function get_donations_total($decimals = 2, $dec_point = '.', $thousands_sep = ',')
    {
        global $wpdb;

        if ( ! isset($_COOKIE[self::$donationCookieName]) ) {
            return 0.00;
        }

        $donations_items_tablename = self::get_donations_items_tablename();
        $donation_id = self::get_donation_id(self::get_cart_id(false));

        $total = $wpdb->get_var("SELECT SUM(amount * quantity) FROM $donations_items_tablename WHERE donation_id = '$donation_id'");

        return $total ? number_format($total, $decimals, $dec_point, $thousands_sep) : 0.00;
    }

    /**
     * Get the list of funds from the API
     *
     * @return array
     */
    public static function get_funds($reload_cached = false)
    {
        if (is_array(self::$funds) && count(self::$funds)) {
            return self::$funds;
        }

        $funds = get_option( 'blackbaud_donation_funds_cache', false );

        // use cached funds, if available and a reload isn't requested
        if ( is_array($funds) &&  count($funds) && ! $reload_cached ) {
            return $funds;
        }

        // clear individual funds cache
        update_option('blackbaud_donation_individual_funds_cache', []);

        $data = \blackbaud\Funds::getAll();
        $statusCode = \blackbaud\Funds::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Funds::init();
            $data = \blackbaud\Funds::getAll();
            $statusCode = \blackbaud\Funds::lastResponseCode();
        }

        $funds = self::$funds = (isset($data['value']) ? $data['value'] : []);

        // add to cache
        update_option('blackbaud_donation_funds_cache', $funds);

        return $funds;
    }

    /**
     * Output debugging info
     *
     * @param $data
     */
    public static function debug($key, $data = null)
    {
        if (self::$debug) {
            echo "$key: ";
            var_dump($data);
        }
    }

    /**
     * Complete the checkout transaction
     */
    public static function complete_checkout_transaction()
    {
        $amount = number_format(str_replace(',', '', sanitize_text_field($_POST['amount'])), 2, '.', '');
        $auth_token = sanitize_text_field($_POST['authorization_token']);
        $title = sanitize_text_field($_POST['title']);
        $firstname = sanitize_text_field($_POST['firstname']);
        $lastname = sanitize_text_field($_POST['lastname']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_text_field($_POST['address']);
        $city = sanitize_text_field($_POST['city']);
        $postcode = sanitize_text_field($_POST['postcode']);
        $country = sanitize_text_field($_POST['country']);
        $email_marketing_optin = (isset($_POST['email_marketing_optin'])) ? (bool)$_POST['email_marketing_optin'] : false;
        $phone_marketing_optin = (isset($_POST['phone_marketing_optin'])) ? (bool)$_POST['phone_marketing_optin'] : false;
        $address_marketing_optin = (isset($_POST['address_marketing_optin'])) ? (bool)$_POST['address_marketing_optin'] : false;
        $giftaid = (isset($_POST['giftaid']) && $_POST['giftaid']) ? true : false;
        $note = (isset($_POST['note']) && trim($_POST['note']) != '') ? sanitize_text_field($_POST['note']) : null;
        
        // add $giftaid to $note

        $note_giftaid_message = '';
        if( ! $giftaid ) {
            $note_giftaid_message = ' (Gift Aid declaration: Omitted)'; 
        } else {
            $note_giftaid_message = ' (Gift Aid declaration: Yes)'; 
        }
        $note = $note . $note_giftaid_message;

        $body = [
            'amount' => str_replace('.', '', $amount),
            'authorization_token' => $auth_token,
            'application_fee' => sanitize_text_field($_POST['application_fee']),
        ];

        self::debug('Calling complete transaction method', $body);

        $data = \blackbaud\Checkout::completeTransaction($body);
        $statusCode = \blackbaud\Checkout::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Checkout::init();
            $data = \blackbaud\Checkout::completeTransaction($body);
            $statusCode = \blackbaud\Checkout::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        $checkout_transaction_id = $data['id'];
        $constituent_id = self::create_constituent($email, $phone, $address, $city, $country, $postcode, $title, $firstname, $lastname, !$email_marketing_optin, !$phone_marketing_optin, !$address_marketing_optin);
        $gift_id = self::split_donations_info_funds($amount, $constituent_id, $checkout_transaction_id, $note);
        self::create_giftaid_declaration($constituent_id, $giftaid);
        self::create_gift_note($gift_id, $note);

        self::send_email($checkout_transaction_id, $email, $amount, $firstname, $lastname, $address, $city, $postcode, $country, $note);

        // mark donation completed
        self::mark_donation_completed(self::get_cart_id());

        header("Content-Type: application/json");

        echo json_encode($data);
        exit;
    }

    /**
     * EMAIL ON DONATION COMPLETION
     */
    public static function send_email($checkout_transaction_id, $email, $amount, $firstname, $lastname, $address, $city, $postcode, $country, $note)
    {
        $display_note = "";
        $date = date("d M Y");
        $time = date("h:i:sa");
        
        // Only show note if not empty
        if(!empty($note)) {
            $display_note = '<h3>Note...</h3>
        <p>
            <i>'.esc_html($note).'</i>
        </p>';
        }

        $funds_list = '';
        // for the amount for the is_quick_appeal_item the amount needs to be multiplied by the quantity to get the donation_amount
        foreach (self::get_donations() as $donation) {

            $donation_amount = number_format($donation->amount);

            if( $donation->is_quick_appeal_item == 1 ) {
                $units_total =  $donation_amount * $donation->quantity;
                $funds_list .= "<li>{$donation->description} - {$donation->fund_type}: " . BLACKBAUD_DONATIONS__CURRENCY . "$units_total (" . BLACKBAUD_DONATIONS__CURRENCY . "$donation_amount x {$donation->quantity})</li>";
            } else {
                $funds_list .= "<li>{$donation->description} - {$donation->fund_type}: " . BLACKBAUD_DONATIONS__CURRENCY . "$donation_amount</li>";
            }
        }

        // Format email message
        $message_html = '
    <div style="text-align: center; margin: 20px auto; width: 600px;">
        <div style=" font-family: sans-serif; font-weight: 300; max-width: 600px; line-height: 140%; text-align: left;">
            <p>Dear '.esc_html($firstname).'</p>
            <p>Thank you for your kind donation. This is to confirm we have received your payment of &pound;'.$amount.'</p>
            <p>A summary of the donation is listed below for your reference</p>
            <div class="email-holder__">
                <h3>Transaction ID</h3>
                <p>' . $checkout_transaction_id . '</p>
                <p>Date: '.$date .' / '.$time.'</p>
                <h3>Donation Summary...</h3>
                <p>
                    '.esc_html($firstname).' '.esc_html($lastname).'<br />
                    '.esc_html($address).'<br />
                    '.esc_html($city).' '.esc_html($postcode).'<br />
                    '.esc_html($country).'
                </p>
                '.$display_note.'
                <h3>Funds you donated to...</h3>
                <ul>
                    ' . $funds_list . '
                    <li><strong>TOTAL ' . BLACKBAUD_DONATIONS__CURRENCY . $amount . '</strong></li>
                </ul>
                <p>Thank you again</p>
            </div>
            <div style="margin-top: 30px;">
                
                <p><img src="https://i0.wp.com/www.ehsaastrust.org/wp-content/uploads/2023/01/ehsaas_logo.png" alt="Ehsaas Trust" width="100px"></p>
                Telephone: UK +44 (0) 20 3617 7786 | Australia 612 8103 4117<br />
                <a href="https://wwww.ehsaastrust.org">ehsaastrust.org</a> |  <a href="mailto:info@ehsaastrust.org">info@ehsaastrust.org</a><br />
                </p>
                <p><small>A UK Registered Charity No. 1144950</small></p>
            </div>
        </div>
    </div>
    ';


        $subject = 'Thank You for your donation';
        $message = $message_html;
        $headers = array(
            'From: Ehsaas Trust <donotreply@ehsaastrust.org>',
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: Ehsaas Foundation <info@ehsaasfoundation.org>',
            //'CC: Ehsaas Foundation <info@ehsaastrust.org>',
        );

        return wp_mail($email, $subject, $message, $headers);
    }

    /**
     * Get a specific fund from the API
     *
     * @return array
     */
    public static function get_fund($id)
    {
        if ( ! \blackbaud\Api_auth::isAuthenticated() ) {
            return [];
        }

        $funds = get_option( 'blackbaud_donation_individual_funds_cache', [] );

        // use cached funds if available
        if ( is_array($funds) && isset($funds[$id])) {
            return $funds[$id];
        }

        $data = \blackbaud\Funds::getById($id);
        $statusCode = \blackbaud\Funds::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Funds::init();
            $data = \blackbaud\Funds::getById($id);
        }

        $options = get_option( 'blackbaud_donations_options' );
        $quick_donate_fund_descriptions = (isset($options['quick_donate_fund_descriptions'])) ? $options['quick_donate_fund_descriptions'] : [];
        $data['description'] = (array_key_exists($id, $quick_donate_fund_descriptions) && trim($quick_donate_fund_descriptions[$id]) != '') ? $quick_donate_fund_descriptions[$id] : @$data['description'];
        $quick_donation_fund_names = (isset($options['quick_donate_fund_names'])) ? $options['quick_donate_fund_names'] : [];
        $data['name'] = (array_key_exists($id, $quick_donation_fund_names) && trim($quick_donation_fund_names[$id]) != '') ? $quick_donation_fund_names[$id] : @$data['description'];

        if( ! isset($data['id']) ) {
            return [];
        }

        // add to cache
        $funds[$id] = $data;
        update_option('blackbaud_donation_individual_funds_cache', $funds);

        return $data;
    }

    /**
     * Get details for a specific appeal page fund
     *
     * @return array
     */
    public static function get_appeal_donation_fund($fund_id)
    {
        $funds = self::get_funds();
        $options = get_option( 'blackbaud_donations_options' );
        $quick_donation_fund_names = (isset($options['quick_donate_fund_names'])) ? $options['quick_donate_fund_names'] : [];
        $quick_donate_fund_descriptions = (isset($options['quick_donate_fund_descriptions'])) ? $options['quick_donate_fund_descriptions'] : [];

        foreach($funds as $fund) {
            if($fund['id'] !== $fund_id) {
                continue;
            }

            $name = (array_key_exists($fund['id'], $quick_donation_fund_names) && trim($quick_donation_fund_names[$fund['id']]) != '') ? $quick_donation_fund_names[$fund['id']] : $fund['description'];
            $description = (array_key_exists($fund['id'], $quick_donate_fund_descriptions) && trim($quick_donate_fund_descriptions[$fund['id']]) != '') ? $quick_donate_fund_descriptions[$fund['id']] : '';

            return [
                'name' => $name,
                'description' => $description,
                'type' => $fund['type'],
            ];
        }
    }

    /**
     * Get a list of further donation funds a user can donation to direct from the cart
     *
     * @return array
     */
    public static function get_further_donation_funds_list()
    {
        $funds = self::get_funds();
        $options = get_option( 'blackbaud_donations_options' );
        $further_donate_funds = (isset($options['further_donate_funds'])) ? $options['further_donate_funds'] : [];
        $further_donate_amounts = (isset($options['further_donate_amounts'])) ? $options['further_donate_amounts'] : [];
        $quick_donation_fund_names = (isset($options['quick_donate_fund_names'])) ? $options['quick_donate_fund_names'] : [];

        $array = [];
        foreach($funds as $fund) {
            if(in_array($fund['id'], $further_donate_funds)) {
                $name = (array_key_exists($fund['id'], $quick_donation_fund_names) && trim($quick_donation_fund_names[$fund['id']]) != '') ? $quick_donation_fund_names[$fund['id']] : $fund['description'];
                $amount = (array_key_exists($fund['id'], $further_donate_amounts)) ? $further_donate_amounts[$fund['id']] : 0.00;

                $array[$fund['id']] = [
                    'name' => $name,
                    'amount' => $amount,
                ];
            }
        }

        return $array;
    }

    /**
     * @param $email
     * @param $phone
     * @param $address
     * @param $city
     * @param $country
     * @param $postcode
     * @param $firstname
     * @param $lastname
     * @param $email_marketing_optout
     * @param $phone_marketing_optout
     * @param $address_marketing_optout
     *
     * @return int
     */
    private static function create_constituent($email, $phone, $address, $city, $country, $postcode, $title, $firstname, $lastname, $email_marketing_optout = true, $phone_marketing_optout = true, $address_marketing_optout = true)
    {
        self::debug('Searching for constituent by email', $email);

        // first try to find an existing constituent by searching on the email address
        $data = \blackbaud\Constituents::search($email);
        $statusCode = \blackbaud\Constituents::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Constituents::init();
            $data = \blackbaud\Constituents::search($email);
            $statusCode = \blackbaud\Constituents::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        $constituent_id = 0;
        if(isset($data['value'][0]['id'])) {
            $constituent_id = $data['value'][0]['id'];
        }

        $body = [
            'email' => [
                'address' => $email,
                'type' => 'Email',
                'primary' => true,
                'inactive' => false,
                'do_not_email' => (bool)$email_marketing_optout,
            ],
            'phone' => [
                'number' => $phone,
                'type' => 'Mobile',
                'primary' => true,
                'inactive' => false,
                'do_not_call' => (bool)$phone_marketing_optout,
            ],
            'address' => [
                'address_lines' => $address,
                'city' => $city,
                'country' => $country,
                'postal_code' => $postcode,
                'type' => 'Home',
                'do_not_mail' => (bool)$address_marketing_optout,
            ],
            'first' => $firstname,
            'last' => $lastname,
            'title' => $title,
            'type' => 'Individual',
        ];

        if($constituent_id > 0) {
            $body['requests_no_email'] = (bool)$email_marketing_optout;

//            self::debug('Updating constituent', $body);

//            $data = \blackbaud\Constituents::update($constituent_id, $body);
        } else {
            self::debug('Creating constituent', $body);

            $data = \blackbaud\Constituents::create($body);
            $constituent_id = isset($data['id']) ? $data['id'] : 0;
        }

        $statusCode = \blackbaud\Constituents::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Constituents::init();

            if($constituent_id > 0) {
//                $data = \blackbaud\Constituents::update($constituent_id, $body);
            } else {
                $data = \blackbaud\Constituents::create($body);
                $constituent_id = isset($data['id']) ? $data['id'] : 0;
            }

            $statusCode = \blackbaud\Constituents::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        self::create_constituent_consent($constituent_id, $email_marketing_optout, 'Email');
        self::create_constituent_consent($constituent_id, $phone_marketing_optout, 'Phone');
        self::create_constituent_consent($constituent_id, $address_marketing_optout, 'Mail');

        return $constituent_id;
    }

    /**
     * @param int $constituent_id
     * @param bool $opt_out
     * @param string $channel Email | Mail | Phone
     *
     * @return int
     */
    private static function create_constituent_consent($constituent_id, $opt_out, $channel)
    {
        if( ! $constituent_id ) {
            return false;
        }

        $status = ($opt_out) ? 'OptOut' : 'OptIn';

        $body = [
            'constituent_id' => $constituent_id,
            'channel' => "$channel",
            'category' => "Online Form",
            'source' => "Donation Form",
            'consent_date' => date('Y-m-d') . 'T' . date('H:i:s') . 'Z',
            'constituent_consent_response' => $status,
            'privacy_notice' => "We would like to contact you from time to time to give you updates on our projects and events and how you can support us in the future. Please opt in to receiving these.",
            'consent_statement' => "Get Updates on Our Projects",
        ];

        self::debug('Creating constituent consent', $body);

        $data = \blackbaud\Consent::create($body);
        $statusCode = \blackbaud\Consent::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Consent::init();
            $data = \blackbaud\Consent::create($body);
            $statusCode = \blackbaud\Consent::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        return isset($data['id']) ? $data['id'] : 0;
    }

    /**
     * @param $constituent_id
     * @param $giftaid
     *
     * @return int
     */
    private static function create_giftaid_declaration($constituent_id, $giftaid)
    {
       
        if( ! $giftaid ) {

            // do nothing in 
            return false;
            
        } else {
            $body = [
                'constituent_id' => $constituent_id,
                'constituent_pays_tax' => 'Yes',
                'declaration_made' => date('Y-m-d') . 'T00:00:00Z',
                'declaration_starts' => date('Y-m-d') . 'T00:00:00Z',
                'declaration_source' => 'BB Plugin'
                //'declaration_ends' => date('Y-m-d', strtotime('1 day')) . 'T00:00:00Z',
            ];
        }

        self::debug('Creating gift aid tax declaration', $body);

        $data = \blackbaud\Giftaid::createTaxDeclaration($body);
        $statusCode = \blackbaud\Giftaid::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Giftaid::init();
            $data = \blackbaud\Giftaid::createTaxDeclaration($body);
            $statusCode = \blackbaud\Giftaid::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        return isset($data['id']) ? $data['id'] : 0;
    }

    /**
     * @param $gift_id
     * @param $text
     *
     * @return int
     */
    private static function create_gift_note($gift_id, $text)
    {
        if( ! $gift_id || trim($text) == '' ) {
            return false;
        }

        $body = [
            'gift_id' => $gift_id,
            'note_type_id' => 38314,
            'date' => [
                'd' => date('d'),
                'm' => date('m'),
                'y' => date('Y')
            ],
            'summary' => 'Gift Notes',
            'text' => $text,
        ];

        self::debug('Creating gift note', $body);

        $data = \blackbaud\Giftnote::create($body);
        $statusCode = \blackbaud\Giftnote::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Giftnote::init();
            $data = \blackbaud\Giftnote::create($body);
            $statusCode = \blackbaud\Giftnote::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        return isset($data['id']) ? $data['id'] : 0;
    }

    /**
     * Split the donations into the correct funds
     *
     * @param $amount
     * @param $constituent_id
     * @param $checkout_transaction_id
     * @return int
     */
    private static function split_donations_info_funds($amount, $constituent_id, $checkout_transaction_id, $notes = null)
    {
        $gift_splits = [];
        foreach (self::get_donations() as $donation) {
            $gift_splits[] = [
                'amount' => ['value' => number_format($donation->amount * $donation->quantity, 2, '.', '')],
                'fund_id' => $donation->fund_id,
            ];
        }

        $payments = array();
        $payments[] = [
            'payment_method' => 'CreditCard',
            'checkout_transaction_id' => $checkout_transaction_id,
        ];

        $gifts = [
            'amount' => ['value' => $amount],
            'constituent_id' => $constituent_id,
            'type' => 'Donation',
            'gift_splits' => $gift_splits,
            'payments' => $payments,
        ];

        if (trim($notes) === '') {
            $gifts['reference'] = $notes;
        }

        self::debug('Splitting donation into the correct funds', $gifts);

        $data = \blackbaud\Gifts::create($gifts);
        $statusCode = \blackbaud\Gifts::lastResponseCode();

        // Access token has expired. Attempt to refresh.
        if ($statusCode == 401) {
            self::debug('Response data', $data);
            self::debug('Response status code', $statusCode);

            \blackbaud\Auth::refreshAccessToken();
            \blackbaud\Gifts::init();
            $data = \blackbaud\Gifts::create($gifts);
            $statusCode = \blackbaud\Gifts::lastResponseCode();
        }

        self::debug('Response data', $data);
        self::debug('Response status code', $statusCode);

        return isset($data['id']) ? $data['id'] : 0;
    }

    /**
     * Add scripts to the page for unauthenticated users
     */
    public static function add_scripts()
    {
        wp_enqueue_script( 'blackbaud-donations', plugins_url( '/js/blackbaud-donations.js', __FILE__ ));

        // Option 1: Manually enqueue the wp-util library.
        wp_enqueue_script( 'wp-util' );

        // Option 2: Make wp-util a dependency of your script (usually better).
        wp_enqueue_script( 'blackbaud-donations', 'blackbaud-donations.js', [ 'wp-util' ] );

        wp_localize_script( 'blackbaud-donations', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
}
