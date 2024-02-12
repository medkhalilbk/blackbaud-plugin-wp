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


<div class="updated" id="blackbaud_donations_setup_prompt">
    <?php
        if (isset($settings['error'], $settings['error_description'])) {
            echo '<p style="color:red">' . $settings['error_description'] . '</p>';
        } else {
    ?>
            <table cellspacing="5" cellpadding="5" style="color:green">
                <?php /*<tr>
                    <td>Access Token</td>
                    <td><?=$settings['access_token']?> (expires in <?=$settings['expires_in']?> seconds)</td>
                </tr>
                <tr>
                    <td>Refresh Token</td>
                    <td><?=$settings['refresh_token']?> (expires in <?=$settings['refresh_token_expires_in']?> seconds)</td>
                </tr>*/?>
                <tr>
                    <td>User ID</td>
                    <td><?=$settings['user_id']?></td>
                </tr>
                <tr>
                    <td>Environment</td>
                    <td><?=$settings['environment_name']?> (<?=$settings['environment_id']?>)</td>
                </tr>
                <tr>
                    <td>Entity</td>
                    <td><?=$settings['legal_entity_name']?> (<?=$settings['legal_entity_id']?>)</td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><?=$settings['given_name']?> <?=$settings['family_name']?> (<?=$settings['email']?>)</td>
                </tr>
                <tr>
                    <td>Mode</td>
                    <td><?=$settings['mode']?></td>
                </tr>
                <tr>
                    <td>Access Token</td>
                    <td><?=$settings['access_token']?></td>
                </tr>
                <tr>
                    <td>Refresh Token</td>
                    <td><?=$settings['refresh_token']?></td>
                </tr>
            </table>
    <?php
        }
    ?>
</div>