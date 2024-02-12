<style>

    /**
     * For the activation notice on the plugins page.
     */

    #blackbaud_donations_setup_prompt {
        background: none;
        border: none;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .blackbaud_donations_activate {
        border: 1px solid #4F800D;
        padding: 5px;
        margin: 15px 0;
        background: #83AF24;
        background-image: -webkit-gradient(linear, 0% 0, 80% 100%, from(#83AF24), to(#4F800D));
        background-image: -moz-linear-gradient(80% 100% 120deg, #4F800D, #83AF24);
        -moz-border-radius: 3px;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        position: relative;
        overflow: hidden;
    }

    .blackbaud_donations_activate .aa_a {
        position: absolute;
        top: -5px;
        right: 10px;
        font-size: 140px;
        color: #769F33;
        font-family: Georgia, "Times New Roman", Times, serif;
    }

    .blackbaud_donations_activate .aa_button {
        font-weight: bold;
        border: 1px solid #029DD6;
        border-top: 1px solid #06B9FD;
        font-size: 15px;
        text-align: center;
        padding: 9px 0 8px 0;
        color: #FFF;
        background: #029DD6;
        background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#029DD6), to(#0079B1));
        background-image: -moz-linear-gradient(0% 100% 90deg, #0079B1, #029DD6);
        -moz-border-radius: 2px;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        width: 100%;
        cursor: pointer;
        margin: 0;
    }

    .blackbaud_donations_activate .aa_button:hover {
        text-decoration: none !important;
        border: 1px solid #029DD6;
        border-bottom: 1px solid #00A8EF;
        font-size: 15px;
        text-align: center;
        padding: 9px 0 8px 0;
        color: #F0F8FB;
        background: #0079B1;
        background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#0079B1), to(#0092BF));
        background-image: -moz-linear-gradient(0% 100% 90deg, #0092BF, #0079B1);
        -moz-border-radius: 2px;
        border-radius: 2px;
        -webkit-border-radius: 2px;
    }

    .blackbaud_donations_activate .aa_button_border {
        border: 1px solid #006699;
        -moz-border-radius: 2px;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        background: #029DD6;
        background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#029DD6), to(#0079B1));
        background-image: -moz-linear-gradient(0% 100% 90deg, #0079B1, #029DD6);
    }

    .blackbaud_donations_activate .aa_button_container {
        box-sizing: border-box;
        display: inline-block;
        background: #DEF1B8;
        padding: 5px;
        -moz-border-radius: 2px;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        width: 266px;
    }

    .blackbaud_donations_activate .aa_description {
        position: absolute;
        top: 22px;
        left: 285px;
        margin-left: 25px;
        color: #E5F2B1;
        font-size: 15px;
    }

    .blackbaud_donations_activate .aa_description strong {
        color: #FFF;
        font-weight: normal;
    }

    @media (max-width: 550px) {
        .blackbaud_donations_activate .aa_a {
            display: none;
        }

        .blackbaud_donations_activate .aa_button_container {
            width: 100%;
        }
    }

    @media (max-width: 782px) {
        .blackbaud_donations_activate {
            min-width: 0;
        }
    }

    @media (max-width: 850px) {
        #blackbaud_donations_setup_prompt .aa_description {
            display: none;
        }

        .blackbaud_donations_activate {
            min-width: 0;
        }
    }

</style>

<?php
    $options = get_option( 'blackbaud_donations_settings' );
    if ( ! \blackbaud\Api_auth::isAuthenticated() && isset($options['client_id']) && trim($options['client_id']) !== '' && trim($options['client_secret']) !== '' && trim($options['subscription_key']) !== '' && trim($options['payments_subscription_key']) !== '' && trim($options['payments_public_key']) !== '' && trim($options['payments_config_id']) !== '' && trim($options['auth_redirect_url']) !== ''):
?>

<div class="updated" id="blackbaud_donations_setup_prompt">
    <form name="blackbaud_donations_activate" action="<?php echo esc_url( \blackbaud\Auth::getAuthorizationUri() ); ?>" method="POST">
        <div class="blackbaud_donations_activate">
            <div class="aa_button_container">
                <div class="aa_button_border">
                    <input type="submit" class="aa_button" value="<?php esc_attr_e( 'Login and Authorise your Account', 'blackbaud_donations' ); ?>" />
                </div>
            </div>
        </div>
    </form>
</div>

<?php
    endif;
?>