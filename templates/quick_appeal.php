
<section class="et-appeal-holder">

    <div class="et-appeal-container">

        <div class="et-appeal">

            <div class="et-appeal__imageholder">
                <?php if( function_exists('get_field') && get_field('appeal_image') ): ?>
                    <img src="<?php the_field('appeal_image'); ?>" alt="appeal image">
                <?php endif; ?>
            </div>

            <form action="" method="post">
            <div class="et-appeal__main">

                <div class="et-appeal__info  mb-3">

                    <h1><?php the_title(); ?></h1>
                    <div class="et-appeal__summary  mb-3">
                        <?php if( function_exists('get_field') &&  get_field('appeal_description') ): ?>
                            <?php the_field('appeal_description'); ?>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="et-quick-appeal-container">
                    <div class="et-quick-appeal">
                         <p><small>Select your items below and 'Add to list', once ready click 'Add to Cart'</small> </p>
                        <div class="row">
                            <div class="col-md-12 col-lg-9 mb-3">
                                
                                <select class="form-control form-select form-select-lg" aria-label="Please choose a Qurbani" id="quick-appeal-fund-id">
                                    <option>Please choose...</option>

                                    <?php if(is_array($quick_appeal_funds) && count($quick_appeal_funds)): ?>
                                        <?php foreach($quick_appeal_funds as $fund): ?>
                                            <option value="<?=$fund->fund_id?>" data-name="<?=$fund->name?>" data-amount="<?=$fund->value?>"><?=$fund->name?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-auto col-lg-3 mb-3">
                                <!--<input class="form-control" type="text" placeholder="QTY" aria-label="Qty" id="quick-appeal-fund-qty" maxlength = "4" size="20"> -->
                               
                               <input type="number" min="1" max="50" value="1" step="1" class="form-control" aria-label="Qty" id="quick-appeal-fund-qty">

                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <a class="et-quick-appeal--add-button" type="submit" id="quick-appeal-add-fund">Add to list</a>
                            </div>
                        </div>
                    </div>

                    <div class="et-quick-appeal-summary">

                        <ul id="quick-appeal-item-list"></ul>
                        <div class="et-appeal__qurbani-summary-total" id="quick-appeal-total-row">
                            <h3>Total: <span class="quick-appeal-total" data-currency="<?=BLACKBAUD_DONATIONS__CURRENCY?>">£0.00</span></h3>
                        </div>
                    </div>
                </div>


                <div class="et-appeal__donate  d-grid mb-3">
                    <button class="btn btn-primary" type="submit" data-bs-toggle="modal" data-bs-target="#et-cart-modal" id="quick-appeal-add-to-cart" disabled>Add to Cart</button>
                </div>

            </div>
            </form>

        </div>
    </div>
    </div>
</section>