
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
money_mask = "########0.00";

$( document ).ready(function()
{
    $('.fund-money-format').mask(money_mask, {reverse: true});

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
                $('#donations-giftaid-total').html(response.giftaid_total);
                $('#donate-now').attr('data-amount', response.total_pence.replace('&pound;', '').replace('$', ''));
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
            $('#donate-now').attr('data-amount', response.total_pence.replace('&pound;', '').replace('$', ''));
            $('#donate-now').attr('data-amount-formatted', response.total.replace('&pound;', '').replace('$', ''));
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
                $('#donate-now').attr('data-amount', response.total_pence.replace('&pound;', '').replace('$', ''));
                $('#donate-now').attr('data-amount-formatted', response.total.replace('&pound;', '').replace('$', ''));
                jQuery( document.body ).trigger( 'post-load' );
            });
        }
    });

    $(document).on('click', ".et-appeal__item", function (e) {
        //$('.et-appeal__item').removeClass('active');
        $('#appeal-donation-other-amount').removeClass('active');
        //$(this).addClass('active');
        $('#appeal-donation-other-amount').val('');
    });

    $(document).on('click', "#appeal-donation-other-amount", function (e) {
        // $('.et-appeal__item').removeClass('active');
        $(this).addClass('active');
        $('.appeal-donation-amount').prop('checked', false);

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
            //$("input[name='quick_donation[amount]']:checked").val('');
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


// jQuery Mask Plugin v1.14.16
// github.com/igorescobar/jQuery-Mask-Plugin
var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.findInternal=function(a,n,f){a instanceof String&&(a=String(a));for(var p=a.length,k=0;k<p;k++){var b=a[k];if(n.call(f,b,k,a))return{i:k,v:b}}return{i:-1,v:void 0}};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.SIMPLE_FROUND_POLYFILL=!1;
$jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(a,n,f){a!=Array.prototype&&a!=Object.prototype&&(a[n]=f.value)};$jscomp.getGlobal=function(a){return"undefined"!=typeof window&&window===a?a:"undefined"!=typeof global&&null!=global?global:a};$jscomp.global=$jscomp.getGlobal(this);
$jscomp.polyfill=function(a,n,f,p){if(n){f=$jscomp.global;a=a.split(".");for(p=0;p<a.length-1;p++){var k=a[p];k in f||(f[k]={});f=f[k]}a=a[a.length-1];p=f[a];n=n(p);n!=p&&null!=n&&$jscomp.defineProperty(f,a,{configurable:!0,writable:!0,value:n})}};$jscomp.polyfill("Array.prototype.find",function(a){return a?a:function(a,f){return $jscomp.findInternal(this,a,f).v}},"es6","es3");
(function(a,n,f){"function"===typeof define&&define.amd?define(["jquery"],a):"object"===typeof exports&&"undefined"===typeof Meteor?module.exports=a(require("jquery")):a(n||f)})(function(a){var n=function(b,d,e){var c={invalid:[],getCaret:function(){try{var a=0,r=b.get(0),h=document.selection,d=r.selectionStart;if(h&&-1===navigator.appVersion.indexOf("MSIE 10")){var e=h.createRange();e.moveStart("character",-c.val().length);a=e.text.length}else if(d||"0"===d)a=d;return a}catch(C){}},setCaret:function(a){try{if(b.is(":focus")){var c=
        b.get(0);if(c.setSelectionRange)c.setSelectionRange(a,a);else{var g=c.createTextRange();g.collapse(!0);g.moveEnd("character",a);g.moveStart("character",a);g.select()}}}catch(B){}},events:function(){b.on("keydown.mask",function(a){b.data("mask-keycode",a.keyCode||a.which);b.data("mask-previus-value",b.val());b.data("mask-previus-caret-pos",c.getCaret());c.maskDigitPosMapOld=c.maskDigitPosMap}).on(a.jMaskGlobals.useInput?"input.mask":"keyup.mask",c.behaviour).on("paste.mask drop.mask",function(){setTimeout(function(){b.keydown().keyup()},
        100)}).on("change.mask",function(){b.data("changed",!0)}).on("blur.mask",function(){f===c.val()||b.data("changed")||b.trigger("change");b.data("changed",!1)}).on("blur.mask",function(){f=c.val()}).on("focus.mask",function(b){!0===e.selectOnFocus&&a(b.target).select()}).on("focusout.mask",function(){e.clearIfNotMatch&&!k.test(c.val())&&c.val("")})},getRegexMask:function(){for(var a=[],b,c,e,t,f=0;f<d.length;f++)(b=l.translation[d.charAt(f)])?(c=b.pattern.toString().replace(/.{1}$|^.{1}/g,""),e=b.optional,
        (b=b.recursive)?(a.push(d.charAt(f)),t={digit:d.charAt(f),pattern:c}):a.push(e||b?c+"?":c)):a.push(d.charAt(f).replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&"));a=a.join("");t&&(a=a.replace(new RegExp("("+t.digit+"(.*"+t.digit+")?)"),"($1)?").replace(new RegExp(t.digit,"g"),t.pattern));return new RegExp(a)},destroyEvents:function(){b.off("input keydown keyup paste drop blur focusout ".split(" ").join(".mask "))},val:function(a){var c=b.is("input")?"val":"text";if(0<arguments.length){if(b[c]()!==a)b[c](a);
        c=b}else c=b[c]();return c},calculateCaretPosition:function(a){var d=c.getMasked(),h=c.getCaret();if(a!==d){var e=b.data("mask-previus-caret-pos")||0;d=d.length;var g=a.length,f=a=0,l=0,k=0,m;for(m=h;m<d&&c.maskDigitPosMap[m];m++)f++;for(m=h-1;0<=m&&c.maskDigitPosMap[m];m--)a++;for(m=h-1;0<=m;m--)c.maskDigitPosMap[m]&&l++;for(m=e-1;0<=m;m--)c.maskDigitPosMapOld[m]&&k++;h>g?h=10*d:e>=h&&e!==g?c.maskDigitPosMapOld[h]||(e=h,h=h-(k-l)-a,c.maskDigitPosMap[h]&&(h=e)):h>e&&(h=h+(l-k)+f)}return h},behaviour:function(d){d=
        d||window.event;c.invalid=[];var e=b.data("mask-keycode");if(-1===a.inArray(e,l.byPassKeys)){e=c.getMasked();var h=c.getCaret(),g=b.data("mask-previus-value")||"";setTimeout(function(){c.setCaret(c.calculateCaretPosition(g))},a.jMaskGlobals.keyStrokeCompensation);c.val(e);c.setCaret(h);return c.callbacks(d)}},getMasked:function(a,b){var h=[],f=void 0===b?c.val():b+"",g=0,k=d.length,n=0,p=f.length,m=1,r="push",u=-1,w=0;b=[];if(e.reverse){r="unshift";m=-1;var x=0;g=k-1;n=p-1;var A=function(){return-1<
        g&&-1<n}}else x=k-1,A=function(){return g<k&&n<p};for(var z;A();){var y=d.charAt(g),v=f.charAt(n),q=l.translation[y];if(q)v.match(q.pattern)?(h[r](v),q.recursive&&(-1===u?u=g:g===x&&g!==u&&(g=u-m),x===u&&(g-=m)),g+=m):v===z?(w--,z=void 0):q.optional?(g+=m,n-=m):q.fallback?(h[r](q.fallback),g+=m,n-=m):c.invalid.push({p:n,v:v,e:q.pattern}),n+=m;else{if(!a)h[r](y);v===y?(b.push(n),n+=m):(z=y,b.push(n+w),w++);g+=m}}a=d.charAt(x);k!==p+1||l.translation[a]||h.push(a);h=h.join("");c.mapMaskdigitPositions(h,
        b,p);return h},mapMaskdigitPositions:function(a,b,d){a=e.reverse?a.length-d:0;c.maskDigitPosMap={};for(d=0;d<b.length;d++)c.maskDigitPosMap[b[d]+a]=1},callbacks:function(a){var g=c.val(),h=g!==f,k=[g,a,b,e],l=function(a,b,c){"function"===typeof e[a]&&b&&e[a].apply(this,c)};l("onChange",!0===h,k);l("onKeyPress",!0===h,k);l("onComplete",g.length===d.length,k);l("onInvalid",0<c.invalid.length,[g,a,b,c.invalid,e])}};b=a(b);var l=this,f=c.val(),k;d="function"===typeof d?d(c.val(),void 0,b,e):d;l.mask=
    d;l.options=e;l.remove=function(){var a=c.getCaret();l.options.placeholder&&b.removeAttr("placeholder");b.data("mask-maxlength")&&b.removeAttr("maxlength");c.destroyEvents();c.val(l.getCleanVal());c.setCaret(a);return b};l.getCleanVal=function(){return c.getMasked(!0)};l.getMaskedVal=function(a){return c.getMasked(!1,a)};l.init=function(g){g=g||!1;e=e||{};l.clearIfNotMatch=a.jMaskGlobals.clearIfNotMatch;l.byPassKeys=a.jMaskGlobals.byPassKeys;l.translation=a.extend({},a.jMaskGlobals.translation,e.translation);
    l=a.extend(!0,{},l,e);k=c.getRegexMask();if(g)c.events(),c.val(c.getMasked());else{e.placeholder&&b.attr("placeholder",e.placeholder);b.data("mask")&&b.attr("autocomplete","off");g=0;for(var f=!0;g<d.length;g++){var h=l.translation[d.charAt(g)];if(h&&h.recursive){f=!1;break}}f&&b.attr("maxlength",d.length).data("mask-maxlength",!0);c.destroyEvents();c.events();g=c.getCaret();c.val(c.getMasked());c.setCaret(g)}};l.init(!b.is("input"))};a.maskWatchers={};var f=function(){var b=a(this),d={},e=b.attr("data-mask");
    b.attr("data-mask-reverse")&&(d.reverse=!0);b.attr("data-mask-clearifnotmatch")&&(d.clearIfNotMatch=!0);"true"===b.attr("data-mask-selectonfocus")&&(d.selectOnFocus=!0);if(p(b,e,d))return b.data("mask",new n(this,e,d))},p=function(b,d,e){e=e||{};var c=a(b).data("mask"),f=JSON.stringify;b=a(b).val()||a(b).text();try{return"function"===typeof d&&(d=d(b)),"object"!==typeof c||f(c.options)!==f(e)||c.mask!==d}catch(w){}},k=function(a){var b=document.createElement("div");a="on"+a;var e=a in b;e||(b.setAttribute(a,
    "return;"),e="function"===typeof b[a]);return e};a.fn.mask=function(b,d){d=d||{};var e=this.selector,c=a.jMaskGlobals,f=c.watchInterval;c=d.watchInputs||c.watchInputs;var k=function(){if(p(this,b,d))return a(this).data("mask",new n(this,b,d))};a(this).each(k);e&&""!==e&&c&&(clearInterval(a.maskWatchers[e]),a.maskWatchers[e]=setInterval(function(){a(document).find(e).each(k)},f));return this};a.fn.masked=function(a){return this.data("mask").getMaskedVal(a)};a.fn.unmask=function(){clearInterval(a.maskWatchers[this.selector]);
    delete a.maskWatchers[this.selector];return this.each(function(){var b=a(this).data("mask");b&&b.remove().removeData("mask")})};a.fn.cleanVal=function(){return this.data("mask").getCleanVal()};a.applyDataMask=function(b){b=b||a.jMaskGlobals.maskElements;(b instanceof a?b:a(b)).filter(a.jMaskGlobals.dataMaskAttr).each(f)};k={maskElements:"input,td,span,div",dataMaskAttr:"*[data-mask]",dataMask:!0,watchInterval:300,watchInputs:!0,keyStrokeCompensation:10,useInput:!/Chrome\/[2-4][0-9]|SamsungBrowser/.test(window.navigator.userAgent)&&
    k("input"),watchDataMask:!1,byPassKeys:[9,16,17,18,36,37,38,39,40,91],translation:{0:{pattern:/\d/},9:{pattern:/\d/,optional:!0},"#":{pattern:/\d/,recursive:!0},A:{pattern:/[a-zA-Z0-9]/},S:{pattern:/[a-zA-Z]/}}};a.jMaskGlobals=a.jMaskGlobals||{};k=a.jMaskGlobals=a.extend(!0,{},k,a.jMaskGlobals);k.dataMask&&a.applyDataMask();setInterval(function(){a.jMaskGlobals.watchDataMask&&a.applyDataMask()},k.watchInterval)},window.jQuery,window.Zepto);
