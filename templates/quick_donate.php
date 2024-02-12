
<form action="" method="post" id="quick-donation-form">
<section class="et-qd__holder">
    <div class="et-qd">
        <div class="et-qd__title">
            <h2>Quick Donate</h2>
        </div>

        <div class="et-qd__select-option">
            <select name="quick_donation[fund_id]" id="quick-donation-fund-id" class="form-control et-qd__dropdown-options">
                <option>Choose...</option>
                <?php if(count($funds)): ?>
                    <?php foreach($funds as $fund): ?>
                        <option value="<?=$fund['id']?>" data-fund-<?=$fund['id']?>-description="<?=$fund['description']?>"><?=$fund['name']?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

<!--        <div class="et-qd__select-option">-->
<!--            <select class="form-control et-qd__dropdown-options" name="quick_donation[type]" id="quick-donation-type">-->
<!--                <option value="single">Single Payment</option>-->
<!--                <option value="regular">Regular Payment</option>-->
<!--            </select>-->
<!--        </div>-->

        <div class="et-qd__select-option">
            <select class="form-control et-qd__dropdown-options" name="quick_donation[amount]" id="quick-donation-amount">
                <?php if(isset($amounts) && is_array($amounts) && count($amounts)): ?>
                    <?php foreach($amounts as $amount): ?>
                        <option value="<?=$amount?>"><?php echo BLACKBAUD_DONATIONS__CURRENCY ?><?=$amount?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
                <option value="other">Other Amount</option>
            </select>
            <input type="hidden" name="quick_donation[amount]" id="quick-donation-other-amount" value="" placeholder="<?php echo BLACKBAUD_DONATIONS__CURRENCY ?> Other amount" class="form-control fund-money-format">
        </div>

<!--        <div class="et-qd__select-option">-->
<!--            <select class="form-control et-qd__dropdown-options">-->
<!--                <option>Sadaqah</option>-->
<!--                <option>Zakat</option>-->
<!--                <option>General Donation</option>-->
<!--            </select>-->
<!--        </div>-->

        <div class="et-qd__submit">
            <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#et-cart-modal" id="quick-donation-form-submit" disabled>
                <span>Donate</span>
            </button>
        </div>

    </div>
</section>
</form>
