<?php
    $donations = Blackbaud_donations::get_donations();
    $further_donation_funds = Blackbaud_donations::get_further_donation_funds_list();

    if (count($donations)):
?>
    <div class="et-cart__summary-items">
        <?php foreach($donations as $donation): ?>


            <div class="et-summary__item fund-id-<?=$donation->fund_id?>">
                <div class="et-summary__item-desc">
                    <div class="et-summary__item-title"><strong><?=$donation->description?></strong> <small><?=$donation->fund_type?></small> </div>
                                                               
                </div>
                <div class="et-summary__item__footer">

                    
                    <div class="et-summary__item__updates">
                        <?php if($donation->is_quick_appeal_item): ?>
                            <div  class="et-summary__item__update-qty">
                                <div class="et-summary__item__change-qty">
                                    <a href="#" class="et-summary__item__change-qty--plus change_quantity fund-id-<?=$donation->fund_id?>-attr" data-fund-id="<?=$donation->fund_id?>" data-qty="<?=$donation->quantity?>" data-operation="subtract">-</a>
                                        <span class="et-summary__item__change-qty--num fund-id-<?=$donation->fund_id?>-qty"><?=$donation->quantity?></span>
                                    <a href="#" class="et-summary__item__change-qty--minus change_quantity fund-id-<?=$donation->fund_id?>-attr" data-fund-id="<?=$donation->fund_id?>" data-qty="<?=$donation->quantity?>" data-operation="add">+</a>
                                </div>
                            </div>
                        <?php endif;?>
                        <div class="et-summary__item-remove-button">
                            <a class="quick-donation-remove <?=(array_key_exists($donation->fund_id, $further_donation_funds)) ? 'help-further-fund' : ''?>" data-fund-id="<?=$donation->fund_id?>">
                                Remove
                            </a>
                        </div>
                    </div>
                    

                    <div class="et-summary__item-amount">
                        <span><?=BLACKBAUD_DONATIONS__CURRENCY?><span class="fund-id-<?=$donation->fund_id?>-total"><?=$donation->amount * $donation->quantity?></span></span>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if(count($further_donation_funds)): ?>
    <div class="et-cart__summary-extras">
        <div class="et-summary__extras">
            <h3 class="et-summary__extras-title">Please can you help further</h3>

            <?php
                foreach($further_donation_funds as $fund_id => $fund_details):
                    ?>
                    <div class="et-summary__extras-item <?=array_key_exists($fund_id, $donations) ? 'et-summary__extras-item--active' : ''?>" id="help-further-fund-id-<?=$fund_id?>">
                        <div class="et-summary__extras-item-desc">
                            <div class="et-summary__extras-item-check">
                                <svg viewBox="0 0 512 512" height="18px" width="18px">
                                    <path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z" fill="currentcolor"/>
                                </svg>
                            </div>
                            <span class="et-summary__extras-item-title"><?=$fund_details['name']?></span>
                            <input type="checkbox" class="checkout-help-further-appeal-items help-further-fund-id-<?=$fund_id?>" data-id="<?=$fund_id?>" data-amount="<?=$fund_details['amount']?>" <?=array_key_exists($fund_id, $donations) ? 'checked="checked"' : ''?>>
                        </div>
                        <span class="et-summary__extras-item-amount"> <?=BLACKBAUD_DONATIONS__CURRENCY?><?=$fund_details['amount']?></span>
                    </div>
                <?php
                endforeach;
            ?>
        </div>
    </div>
<?php endif;  ?>