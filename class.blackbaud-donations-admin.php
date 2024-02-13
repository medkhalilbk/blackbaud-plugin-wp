<?php

class Blackbaud_donations_admin {

    const NONCE = 'blackbaud-donations-update-key';
    private static $initiated = false;


    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    public static function init_hooks() {

        self::$initiated = true;

        add_action( 'admin_init', array( 'Blackbaud_donations_admin', 'admin_init' ) );
        add_action( 'admin_menu', array( 'Blackbaud_donations_admin', 'admin_menu' ), 5 ); # Priority 5, so it's called before Jetpack's admin_menu.
    }

    public static function admin_init() {
        register_setting( 'blackbaud_donations_settings', 'blackbaud_donations_settings');
        register_setting( 'blackbaud_donations_options', 'blackbaud_donations_options');

        if ( get_option( 'Activated_Blackbaud_donations' ) ) {
            delete_option( 'Activated_Blackbaud_donations' );
            if ( ! headers_sent() ) {
                wp_redirect( add_query_arg( array( 'page' => 'blackbaud-donations' ), menu_page_url( 'blackbaud-donations', false ) ) );
            }
        }

        add_settings_section( 'api_settings', 'API Settings', array('Blackbaud_donations_admin', 'section_text'), 'blackbaud_donations_settings' );
        add_settings_section( 'fund_settings', 'Fund Settings', array('Blackbaud_donations_admin', 'section_text2'), 'blackbaud_donations_funds' );

        add_settings_field( 'quick_donation_funds', '', array('Blackbaud_donations_admin', 'quick_donation_funds'), 'blackbaud_donations_funds', 'fund_settings' );
        add_settings_field( 'client_id', 'Application ID', array('Blackbaud_donations_admin', 'client_id'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'client_secret', 'Application Secret', array('Blackbaud_donations_admin', 'client_secret'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'subscription_key', 'Raisers Edge NXT API Key', array('Blackbaud_donations_admin', 'subscription_key'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'payments_subscription_key', 'Payments API Key', array('Blackbaud_donations_admin', 'payments_subscription_key'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'payments_public_key', 'Payments API Public Key', array('Blackbaud_donations_admin', 'payments_public_key'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'payments_config_id', 'Payments Config ID', array('Blackbaud_donations_admin', 'payments_config_id'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'auth_redirect_url', 'Auth Redirect URL', array('Blackbaud_donations_admin', 'auth_redirect_url'), 'blackbaud_donations_settings', 'api_settings' );
        add_settings_field( 'quick_appeals_json', 'Quick Appeals JSON', array('Blackbaud_donations_admin', 'quick_appeals_json'), 'blackbaud_donations_options', 'fund_settings' );
        add_settings_field('thank_you_page_donation' , "Thank you page link", array('Blackbaud_donations_admin', 'thank_you_page_donation'), 'blackbaud_thank_you_page_donation', 'thank_you_page_donation' );
    
    }

    public static function admin_menu() {
        add_menu_page( 'Blackbaud Donations', 'Donations', 'manage_options', 'blackbaud-donations', array('Blackbaud_donations_admin', 'settings_page') );
        add_submenu_page( 'blackbaud-donations', 'Settings', 'Settings', 'manage_options', 'blackbaud-donations', array('Blackbaud_donations_admin', 'settings_page'), 1 );
        add_submenu_page( 'blackbaud-donations', 'Funds', 'Funds', 'manage_options', 'funds', array('Blackbaud_donations_admin', 'funds_page'), 2 );
        // adding error logging section
        add_submenu_page( 'blackbaud-donations', 'Error Logging', 'Error Logs', 'manage_options', 'blackbaud-errors-logs', array('Blackbaud_donations_admin', 'error_logs_page') );
        add_submenu_page( 'blackbaud-donations', 'USA Mode', 'Currency', 'manage_options', 'usa_switch', array('Blackbaud_donations_admin', 'usa_switch_page') );
    }

 
 


public static function usa_switch_page(){

$currency = get_option('blackbaud_currency_symbol', '&pound;');
$selected_currency = isset($_POST['currency-symbol']) ? $_POST['currency-symbol'] : $currency; // Default to the current currency if not submitted

echo '<h1>Choose Currency Symbol :</h1>';
echo '<p>Select the currency symbol you prefer:</p>';

echo '<form method="post">';
echo '<label for="currency-symbol">Currency Symbol:</label>';
echo '<select name="currency-symbol" id="currency-symbol">';
echo '<option value="&pound;" ' . ($currency == "£" ? "selected" : "") . '>Pound (£)</option>';
echo '<option value="&dollar;" ' . ($currency == "$" ? "selected" : "") . '>Dollar ($)</option>';
// Add more currency options as needed
echo '</select>';
echo '<button type="submit" class="button button-primary">Submit</button>';
echo '</form>';

 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve selected currency symbol
    $selected_currency = $_POST['currency-symbol'];
   
    update_option('blackbaud_currency_symbol', $selected_currency);
   /*  define('BLACKBAUD_DONATIONS__CURRENCY', $selected_currency); */
    echo '<p>Currency symbol selected: ' . htmlspecialchars($selected_currency) . '</p>';
}

}
 

public static function error_logs_page() {
// Page content here
    echo '<h1>Error Logging Page</h1>';
    echo '<p>This is the content of your new page.</p>';
 
    // example an array of error messages
    $error_messages = array(
        'Error 1: Something went wrong.',
        'Error 2: Another error occurred.',
      
    );
 
       if (!empty($error_messages)) {
        echo '<table class="table">';
        echo '<thead><tr><th>Error Message</th></tr></thead>';
        echo '<tbody>';
        foreach ($error_messages as $error) {
            echo '<tr><td>' . $error . '</td></tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No errors to display.</p>';
    }
   
}
    public static function funds_page() {
        ?>
        <h2>Blackbaud Donations</h2>
        <?php
        if (isset( $_GET['view']) && $_GET['view'] == 'reload_cached') {
            $funds = Blackbaud_donations::get_funds($reload_cached = true);
            echo '<p style="color:green">The cached funds data has been reloaded</p>';
        }
        ?>
        <form action="options.php" method="post">
            <?php
                settings_fields( 'blackbaud_donations_options' );
                do_settings_sections( 'blackbaud_donations_funds' ); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
            <a href="<?=add_query_arg(array( 'page' => 'funds', 'view' => 'reload_cached' ))?>">Reload Cached Funds</a>
        </form>
        <?php
    }


    public static function settings_page() {
        ?>
        <h2>Blackbaud Donations</h2>
        <?php
        if (isset( $_GET['view'], $_GET['code'] ) && $_GET['view'] == 'callback') {
            $settings = json_decode(Blackbaud\Auth::exchangeCodeForAccessToken(sanitize_text_field($_GET['code'])), true);
            include BLACKBAUD_DONATIONS__PLUGIN_DIR . 'views/finished_setup.php';
        } elseif (isset( $_GET['view']) && $_GET['view'] == 'deauthorise') {
            \blackbaud\Api_auth::logout();
        } else {
            include BLACKBAUD_DONATIONS__PLUGIN_DIR . 'views/start.php';
        }
        ?>
        <form action="options.php" method="post">
            <?php
                settings_fields( 'blackbaud_donations_settings' );
                do_settings_sections( 'blackbaud_donations_settings' ); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
        <?php
    }


    public static function section_text() {
        echo '<p>Here you can set all the options for using the Blackbaud API.</p>';
    }

    public static function section_text2() {
        echo '<p>Select which funds are available to donate to by your users.</p>';
    }

    public static function client_id() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='client_id' name='blackbaud_donations_settings[client_id]' type='text' value='" . esc_attr( @$options['client_id'] ) . "' size='50' />";
        echo ' <a href="https://developer.blackbaud.com/apps/" target="_blank">Get your credentials</a>';
    }

    public static function client_secret() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='client_secret' name='blackbaud_donations_settings[client_secret]' type='text' value='" . esc_attr( @$options['client_secret'] ) . "' size='50' />";
    }

    public static function subscription_key() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='subscription_key' name='blackbaud_donations_settings[subscription_key]' type='text' value='" . esc_attr( @$options['subscription_key'] ) . "' size='50' />";
        echo ' <a href="https://developer.blackbaud.com/subscriptions/" target="_blank">Get your key</a>';
    }

    public static function payments_subscription_key() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='payments_subscription_key' name='blackbaud_donations_settings[payments_subscription_key]' type='text' value='" . esc_attr( @$options['payments_subscription_key'] ) . "' size='50' />";
        echo ' <a href="https://developer.blackbaud.com/subscriptions/" target="_blank">Get your key</a>';
    }


    public static function thank_you_page_donation(){
        $options = get_option('blackbaud_donations_settings') ; 
        echo "<input id='thank_you_page_donation' name='blackbaud_donations_settings[thank_you_page_donation]' type='text' value='" . esc_attr( @$options['thank_you_page_donation'] ) . "' size='50' />";
        
}

    public static function payments_public_key() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='payments_public_key' name='blackbaud_donations_settings[payments_public_key]' type='text' value='" . esc_attr( @$options['payments_public_key'] ) . "' size='50' />";
        echo ' <a href="https://developer.sky.blackbaud.com/docs/services/payments/operations/GetPublicKey" target="_blank">Get your key</a>';
    }

    public static function payments_config_id() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='payments_config_id' name='blackbaud_donations_settings[payments_config_id]' type='text' value='" . esc_attr( @$options['payments_config_id'] ) . "' size='50' />";
        echo ' <a href="https://developer.sky.blackbaud.com/docs/services/payments/operations/ListPaymentConfiguration" target="_blank">Get your config ID</a>';
    }

    public static function auth_redirect_url() {
        $options = get_option( 'blackbaud_donations_settings' );
        echo "<input id='auth_redirect_url' name='blackbaud_donations_settings[auth_redirect_url]' type='text' value='" . esc_attr( @$options['auth_redirect_url'] ) . "' size='100' />";
        echo ' <br>e.g. https://localhost/wp-admin/admin.php?page=blackbaud-donations&view=callback';
    }

    public static function quick_donation_funds() {
        $options = get_option( 'blackbaud_donations_options' );
        $further_donate_funds = (isset($options['further_donate_funds'])) ? $options['further_donate_funds'] : [];
        $further_donate_amounts = (isset($options['further_donate_amounts'])) ? $options['further_donate_amounts'] : [];
        $quick_donate_fund_names = (isset($options['quick_donate_fund_names'])) ? $options['quick_donate_fund_names'] : [];
        $quick_donate_fund_descriptions = (isset($options['quick_donate_fund_descriptions'])) ? $options['quick_donate_fund_descriptions'] : [];
        $funds = Blackbaud_donations::get_funds();

        echo '<table cellspacing="0" cellpadding="0">';
        echo "<tr><td>Quick Appeals JSON</td>";
        echo "<td><textarea id='quick_appeals_json' cols='50' rows='10' name='blackbaud_donations_options[quick_appeals_json]'>" . esc_attr( @$options['quick_appeals_json'] ) . "</textarea></td></tr>";
        echo '</table>';

        if(is_array($funds)) {
            echo '<table cellspacing="0" cellpadding="0">';
            echo '<tr align="left"><th>ID</th><th>Type</th><th>Description</th><th>Custom Name</th><th>Custom Description</th><th>Further Donation</th><th>Further Donation Amount</th></tr>';
            foreach ($funds as $fund) {
                if ( ! $fund['inactive'] && isset($fund['type']) ) {
                    $checked2 = (in_array($fund['id'], $further_donate_funds)) ? 'checked="checked"' : '';
                    $amount = (array_key_exists($fund['id'], $further_donate_amounts) && trim($further_donate_amounts[$fund['id']]) != '') ? $further_donate_amounts[$fund['id']] : '';
                    $name = (array_key_exists($fund['id'], $quick_donate_fund_names) && trim($quick_donate_fund_names[$fund['id']]) != '') ? $quick_donate_fund_names[$fund['id']] : '';
                    $description = (array_key_exists($fund['id'], $quick_donate_fund_descriptions) && trim($quick_donate_fund_descriptions[$fund['id']]) != '') ? $quick_donate_fund_descriptions[$fund['id']] : '';

                    echo '<tr align="left">';
                    echo "<td>{$fund['id']}</td>";
                    echo "<td>{$fund['type']}</td>";
                    echo "<td>{$fund['description']}</td>";
                    echo "<td><input name='blackbaud_donations_options[quick_donate_fund_names][{$fund['id']}]' type='text' size='30' value='$name' /></td>";
                    echo "<td><input name='blackbaud_donations_options[quick_donate_fund_descriptions][{$fund['id']}]' type='text' size='30' value='$description' /></td>";
                    echo "<td><input $checked2 name='blackbaud_donations_options[further_donate_funds][{$fund['id']}]' type='checkbox' value='{$fund['id']}' /></td>";
                    echo "<td><input name='blackbaud_donations_options[further_donate_amounts][{$fund['id']}]' type='text' size='7' value='$amount' /></td>";
                    echo '</tr>';
                }
            }
            echo '</table>';
        }
    }
}
