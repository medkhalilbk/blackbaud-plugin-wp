<section class="et-appeal-holder">

    <div class="et-appeal-container">

        <div class="et-appeal">

<!--        Appeal Section - Left -->
            <div class="et-appeal__imageholder">
                <?php if( function_exists('get_field') && get_field('appeal_image') ): ?>
                    <img src="<?php the_field('appeal_image'); ?>" alt="appeal image">
                <?php endif; ?>
            </div>

<!--        Appeal Section - Right -->            
            <div class="et-appeal__main">

<!--            Appeal Page Summary --> 
                <div class="et-appeal__info">
                    <h1><?php the_title(); ?></h1>
                    <div class="et-appeal__summary">
                        <?php if( function_exists('get_field') &&  get_field('appeal_description') ): ?>
                            <?php the_field('appeal_description'); ?>
                        <?php endif; ?>
                    </div>                   
                </div>

                <hr class="et-appeal__divider"/>

<!--            Appeal Form --> 
                <form action="" method="post" id="quick-donation-form">

<!--                Donation type -->  
                    <div class="et-appeal__donation-options">
                        <?php if(count($donation_types) > 1): ?>
                            <div>
                                
                                    <div class="et-appeal__subtitle">Filter Donation Type</div>
                                    <div class="et-appeal__types-holder">
                                        <?php foreach($donation_types as $donation_type): ?>
                                            <div class="et-appeal__item">
                                                <input type="radio" name="fund_type" value="<?=$donation_type?>" class="appeal-donation-fund-type-filter"> <label><?=$donation_type?></label>
                                            </div>
                                        <?php endforeach;?>
                                    </div>
                                
                            </div>
                        <?php else:?>
                                <div class="et-appeal__donation-type"><p>Donation type: <?=@array_shift($donation_types)?></p></div>  
                        <?php endif;?>
                        
<!--                    Funds -->
                        <div class="et-appeal__select-category">
                        <label class="et-appeal__subtitle" for="appeal-donation-fund-id">Select Project</label>
                            <select class="form-control form-select form-select-lg appeal-donation-fund-description" name="quick_donation[fund_id]" id="appeal-donation-fund-id">
                                <option>Select one...</option>
                                <?php if(count($funds)): ?>
                                <?php foreach($funds as $fund): ?>
                                    <option class="appeal-donation-fund-filter-item appeal-donation-fund-filter-type-<?=$fund['type']?>" value="<?=$fund['id']?>" ></option>
                                <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </div>

<!--                    Fund Description -->
                        <div class="et-appeal__selected-desc">
                            <span id="appeal-donation-description"></span>
                        </div>

<!--                    Amount Options -->
                        <div class="et-appeal__select-amount">
                            <div class="et-appeal__subtitle">Choose Donation Amount</div>
                            <div class="et-appeal__items-holder">
                                <?php if(count($amounts)): ?>
                                <?php foreach($amounts as $amount): ?>
                                    <div class="et-appeal__item">
                                        <input type="radio" <?=(isset($selected_amount) && $selected_amount == $amount) ? 'checked="checked"' : ''?> name="quick_donation[amount]" class="appeal-donation-amount" value="<?=$amount?>">
                                        <label><?php echo BLACKBAUD_DONATIONS__CURRENCY ?><?=$amount?></label>
                                    </div>
                                <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if(isset($form['hide_other_amount']) && ! $form['hide_other_amount']):?>
                                    <input pattern="[0-9]*\.?[0-9]+" placeholder="<?php echo BLACKBAUD_DONATIONS__CURRENCY ?> Other amount" class="form-control fund-money-format" id="appeal-donation-other-amount" value="" data-min="<?=$form['min_amount']?>" />
                                <?php endif; ?>
                            </div>
                        </div>

<!--                    Submit Donation -->                    
                        <div class="et-appeal__donate  d-grid mb-3">
                            <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#et-cart-modal" id="appeal-donation-form-submit" disabled>Add Donation</button>
                        </div>

                    </div>
                </form>
                <div class="et-appeal__dd story">
                    <p><strong>Donate by Direct Debit?</strong> <br />To make a recurring payment by Direct Debit please <a href="<?PHP echo site_url(); ?>/ehsaas-recurring-donations/">click here!</a></p>
                </div>
            </div>
        </div>
    </div>
</section>