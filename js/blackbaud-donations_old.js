
(function () {
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };
})();

var $ = jQuery.noConflict();

appeal_donation_button_disabled = true;
quick_appeal_total = 0;


$( document ).ready(function()
{
    $('.et-appeal__selected-desc').hide();

    $(document).on('click', ".change_quantity", function (e) {
        e.preventDefault();

        var fund_id = $(this).attr('data-fund-id');
        var qty = $(this).attr('data-qty');
        var operation = $(this).attr('data-operation');
        
        if(operation === 'subtract' && parseInt(qty) - 1 === 0) {
            // remove item
            var data = {
                id: fund_id,
                action: 'remove_donation_from_user_cart'
            };

            jQuery.post(ajax_object.ajax_url, data, function(response) {
                $('.fund-id-' + fund_id).remove();
                $('.donations-cart-total').html(response.total);
                $('#donate-now').attr('data-amount', response.total.replace('&pound;', '').replace('$', '').replace('.', ''));
                $('#donate-now').attr('data-amount-formatted', response.total.replace('&pound;', '').replace('$', ''));
                jQuery( document.body ).trigger( 'post-load' );
            });

            return true;
        }

        jQuery.post(ajax_object.ajax_url, {
            id: fund_id,
            quantity: qty,
            operation: operation,
            action: 'change_quantity'
        }, function(response) {
            $('.fund-id-' + fund_id + '-attr').attr('data-qty', response.qty);
            $('.fund-id-' + fund_id + '-qty').html(response.qty);
            $('.fund-id-' + fund_id + '-total').html(response.fund_total);
            $('.donations-cart-total').html(response.total);
            $('#donations-giftaid-total').html(response.giftaid_total);
            jQuery( document.body ).trigger( 'post-load' );
        });
    });

    $(document).on('click', ".appeal-donation-fund-type-filter", function () {
        $(".appeal-donation-fund-filter-item").hide();
        $(".appeal-donation-fund-filter-type-" + $(this).val()).show();
        $('#appeal-donation-fund-id').val('Select one...');
        $('.et-appeal__selected-desc').hide();
    });

    $(document).on('change', "#quick-donation-fund-id, #quick-donation-other-amount, #quick-donation-amount", function () {
        check_if_quick_donation_button_can_be_enabled();
    });

    $(document).on('change', "#appeal-donation-fund-id, #appeal-donation-other-amount, .appeal-donation-amount", function () {
        check_if_button_can_be_enabled();
    });

    $(document).on('change', "#quick-donation-amount", function () {
        if ($(this).val() === "other") {
            $(this).hide();
            $("#quick-donation-other-amount").attr('type', 'text');
        }
    });

    $(document).on('click', "#donation-cart-checkout", function (e) {
        e.preventDefault();

        window.location = '/checkout';
    });

    $(document).on('click', ".checkout-help-further-appeal-items", function (e) {

        var fund_id = $(this).attr('data-id');
        var fund_amount = $(this).attr('data-amount');
        var quantity = $(this).attr('data-quantity');

        if($(this).is(':checked') === true) {
            $('#help-further-fund-id-' + fund_id).addClass('et-summary__extras-item--active');

            // add further donation to the cart
            jQuery.post(ajax_object.ajax_url, {
                id: fund_id,
                type: 'single',
                amount: fund_amount,
                quantity: 1,
                otherAmount: 0,
                action: 'add_donation_to_user_cart'
            }, function(response) {
                $('#donations-cart-html').html(response.html);
                $('.donations-cart-total').html(response.total);
                jQuery( document.body ).trigger( 'post-load' );
            });
        } else {
            $('#help-further-fund-id-' + fund_id).removeClass('et-summary__extras-item--active');

            // remove further donation from the cart
            jQuery.post(ajax_object.ajax_url, data = {
                id: fund_id,
                action: 'remove_donation_from_user_cart'
            }, function(response) {
                $('.fund-id-' + fund_id).remove();
                $('.donations-cart-total').html(response.total);
                $('#donate-now').attr('data-amount', response.total.replace('&pound;', '').replace('$', '').replace('.', ''));
                $('#donate-now').attr('data-amount-formatted', response.total.replace('&pound;', '').replace('$', ''));
                jQuery( document.body ).trigger( 'post-load' );
            });
        }
    });

    $(document).on('click', ".et-appeal__item", function (e) {
        $('.et-appeal__item').removeClass('active');
        $(this).addClass('active');
        $('#appeal-donation-other-amount').val('');
    });

    $(document).on('click', "#appeal-donation-other-amount", function (e) {
        $('.et-appeal__item').removeClass('active');
        $('.appeal-donation-amount').removeAttr('checked');

        check_if_button_can_be_enabled();
    });

    $(document).on('click', "#appeal-donation-form-submit", function (e) {
        e.preventDefault();

        var appeal_donation_amount = $("input[name='quick_donation[amount]']:checked").val();

        var data = {
            id: $('#appeal-donation-fund-id').val(),
            type: 'single',
            quantity: 1,
            amount: (typeof appeal_donation_amount === 'undefined') ? 'other' : appeal_donation_amount,
            otherAmount: $('#appeal-donation-other-amount').val(),
            action: 'add_donation_to_user_cart'
        };

        jQuery.post(ajax_object.ajax_url, data, function(response) {
            $('#donations-cart-html').html(response.html);
            $('.donations-cart-total').html(response.total);

            // reset form
            $('.appeal-donation-fund-type-filter:checked').val('');
            $('#appeal-donation-fund-id').val('Select one...');
            $("input[name='quick_donation[amount]']:checked").val('');
            $('#appeal-donation-other-amount').val('');

            jQuery( document.body ).trigger( 'post-load' );
        });
    });

    $(document).on('click', "#quick-donation-form-submit", function (e) {
        e.preventDefault();

        var data = {
            id: $('#quick-donation-fund-id').val(),
            type: 'single',
            quantity: 1,
            amount: $('#quick-donation-amount').val(),
            otherAmount: $('#quick-donation-other-amount').val(),
            action: 'add_donation_to_user_cart'
        };

        jQuery.post(ajax_object.ajax_url, data, function(response) {
            $('#donations-cart-html').html(response.html);
            $('.donations-cart-total').html(response.total);
            jQuery( document.body ).trigger( 'post-load' );
        });
    });

    $(document).on('change', "#country", function (e) {
        if($(this).val() === 'UK') {
            $('#uk-gift-aid-declaration').show();
        } else {
            $('#uk-gift-aid-declaration').hide();
        }
    });


    $(document).on('change', ".appeal-donation-fund-description", function (e) {
        $('#appeal-donation-description').text($(this).find(':selected').data('description'));
        $('.et-appeal__selected-desc').show();
    });


    $(document).on('click', ".quick-donation-remove", function (e) {
        e.preventDefault();

        fund_id = $(this).attr('data-fund-id');

        if($(this).hasClass('help-further-fund')) {
            $('#help-further-fund-id-' + fund_id).removeClass('et-summary__extras-item--active');
            $('.help-further-fund-id-' + fund_id).removeAttr('checked');
        }

        var data = {
            id: fund_id,
            action: 'remove_donation_from_user_cart'
        };

        jQuery.post(ajax_object.ajax_url, data, function(response) {
            $('.fund-id-' + fund_id).remove();
            $('.donations-cart-total').html(response.total);
            $('#donations-giftaid-total').html(response.giftaid_total);
            $('#donate-now').attr('data-amount', response.total.replace('&pound;', '').replace('$', '').replace('.', ''));
            $('#donate-now').attr('data-amount-formatted', response.total.replace('&pound;', '').replace('$', ''));
            jQuery( document.body ).trigger( 'post-load' );
        });
    });


    /**
     * Quick Appeal
     */
    $(document).on('click', "#quick-appeal-add-fund", function (e) {

        var quick_appeal_fund_object = $('#quick-appeal-fund-id');
        var quick_appeal_fund_qty_object = $('#quick-appeal-fund-qty');
        var quick_appeal_fund_id = quick_appeal_fund_object.val();
        var quick_appeal_fund_name = quick_appeal_fund_object.find(':selected').data('name');
        var quick_appeal_qty = quick_appeal_fund_qty_object.val();
        var quick_appeal_amount = quick_appeal_fund_object.find(':selected').data('amount');
        var quick_appeal_total_amount = parseFloat(quick_appeal_amount) * parseInt(quick_appeal_qty);

        if(quick_appeal_qty === '' || quick_appeal_qty == 0) {
            alert('Please enter a quantity');
            quick_appeal_fund_qty_object.val(1);
            return false;
        }

        var existing_item = $('#quick-appeal-item-'+quick_appeal_fund_id);
        if(existing_item.length > 0) {
            var existing_item2 = $('.quick-appeal-fund-item-'+quick_appeal_fund_id);

            var amount = existing_item2.attr('data-amount');
            var qty = existing_item2.attr('data-qty');
            var totalAmount = parseFloat(amount) * parseInt(qty);

            quick_appeal_total = parseFloat(quick_appeal_total) - parseFloat(totalAmount);

            existing_item.remove();
        }

        quick_appeal_total = parseFloat(quick_appeal_total) + parseFloat(quick_appeal_total_amount);
        var quick_appeal_item = '<li id="quick-appeal-item-'+quick_appeal_fund_id+'"><strong>'+quick_appeal_fund_name+'</strong> <br /> <span class="quick-appeal-item-list--qty">Qty '+quick_appeal_qty+'</span> <span class="quick-appeal-item-list--divider">|</span> <span class="quick-appeal-item-list--delete"><a class="et-appeal__qurbani-delete quick-appeal-remove-item quick-appeal-fund-item quick-appeal-fund-item-'+quick_appeal_fund_id+'" data-fund-id="'+quick_appeal_fund_id+'" data-amount="'+quick_appeal_amount+'" data-qty="'+quick_appeal_qty+'"  href="#">Remove</a></span></li>';
        $('#quick-appeal-item-list').append(quick_appeal_item);
        $('#quick-appeal-total-row').show();
        quick_appeal_fund_object.val('Please choose...');
        quick_appeal_fund_qty_object.val(1);
        $('.quick-appeal-total').html($('.quick-appeal-total').attr('data-currency') + quick_appeal_total);

        if($('.quick-appeal-fund-item').length > 0) {
            $('#quick-appeal-add-to-cart').removeAttr('disabled');
        } else {
            $('#quick-appeal-add-to-cart').attr('disabled');
        }
    });


    $(document).on('click', ".quick-appeal-remove-item", function (e) {
        e.preventDefault();

        var fund_id = $(this).attr('data-fund-id');
        var amount = $(this).attr('data-amount');
        var qty = $(this).attr('data-qty');
        var totalAmount = parseFloat(amount) * parseInt(qty);

        quick_appeal_total = parseFloat(quick_appeal_total) - parseFloat(totalAmount);

        $('#quick-appeal-item-' + fund_id).remove();
        $('.quick-appeal-total').html($('.quick-appeal-total').attr('data-currency') + quick_appeal_total);

        if($('#quick-appeal-item-list li').length > 0) {
            $('#quick-appeal-add-to-cart').removeAttr('disabled');
        } else {
            $('#quick-appeal-add-to-cart').attr('disabled', true);
        }
    });

    /**
     * Quick appeal add items to the cart
     */
    $(document).on('click', "#quick-appeal-add-to-cart", function (e) {
        e.preventDefault();

        var quick_appeal_fund_items = $('.quick-appeal-fund-item');

        if(quick_appeal_fund_items.length == 0) {
            return false;
        }

        quick_appeal_fund_items.each(function(index) {

            var data = {
                id: $(this).attr('data-fund-id'),
                type: 'single',
                quantity: $(this).attr('data-qty'),
                amount: $(this).attr('data-amount'),
                is_quick_appeal_item: 1,
                otherAmount: 0,
                action: 'add_donation_to_user_cart'
            };

            jQuery.post(ajax_object.ajax_url, data, function(response) {
                $('#donations-cart-html').html(response.html);
                $('.donations-cart-total').html($('.donations-cart-total').attr('data-currency') + response.total.replace('&pound;', '').replace('$', ''));

                $('#quick-appeal-item-list').html('');
                $('.quick-appeal-total').html($('.quick-appeal-total').attr('data-currency') + '0');
                $('#quick-appeal-total-row').hide();
                quick_appeal_total = 0;

                jQuery( document.body ).trigger( 'post-load' );
            });
        });
    });
});


function get_appeal_donation_amount()
{
    var appeal_donation_other_amount = $('#appeal-donation-other-amount').val();
    var appeal_donation_amount = $("input[name='quick_donation[amount]']:checked").val();
    return (parseInt(appeal_donation_other_amount) > 0) ? appeal_donation_other_amount : appeal_donation_amount;
}
function check_if_button_can_be_enabled()
{
    if(parseInt($('#appeal-donation-fund-id').val()) > 0 && parseInt(get_appeal_donation_amount()) > 0) {
        var other_amount = parseInt($('#appeal-donation-other-amount').val());
        var min_amount = parseInt($('#appeal-donation-other-amount').data('min'));

        if(other_amount > 0 && min_amount > 0 && other_amount < min_amount) {
            alert('The minimum donation amount is £' + min_amount + '. We have adjusted your donation amount for you.');
            $('#appeal-donation-other-amount').val(min_amount);
            return false;

        }

        $('#appeal-donation-form-submit').removeAttr('disabled');
        return true;
    }

    $('#appeal-donation-form-submit').attr('disabled', true);
    return false;
}


function get_quick_donation_amount()
{
    var quick_donation_other_amount = $('#quick-donation-other-amount').val();
    var quick_donation_amount = $("#quick-donation-amount").val();
    return (parseInt(quick_donation_other_amount) > 0) ? quick_donation_other_amount : quick_donation_amount;
}
function check_if_quick_donation_button_can_be_enabled()
{
    if(parseInt($('#quick-donation-fund-id').val()) > 0 && parseInt(get_quick_donation_amount()) > 0) {
        $('#quick-donation-form-submit').removeAttr('disabled');
        return true;
    }

    $('#quick-donation-form-submit').attr('disabled', true);
    return false;
}
