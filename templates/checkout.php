<?php
$donations_cart_total = get_donations_cart_total();
$donations_cart_total_pence = get_donations_cart_total(2, '', '');
$countries = json_decode(file_get_contents(BLACKBAUD_DONATIONS__PLUGIN_DIR . '/countries.json'));
$further_donation_funds = Blackbaud_donations::get_further_donation_funds_list();
?>

<?php
if ($donations_cart_total > 0): ?>
<section class="checkout-page-holder">

    <div class="container">

<!--    CHECKOUT PLEASE WAIT -->
        <div id="et-checkout-progress">
            <div class="et-checkout-progress__holder">
                <div class="et-checkout-progress__steps">
                    <div class="et-checkout-progress__step et-checkout-progress__step--one"><p><strong>Step 1:</strong> Add Your Information</p></div>
                    <div class="et-checkout-progress__step et-checkout-progress__step--two"><p><strong>Step 2:</strong> Make Payment</p></div>
                    <div class="et-checkout-progress__step et-checkout-progress__step--three"><p><strong>Step 3:</strong> Wait for Confirmation</p></div>   
                </div>
                <div id="et-checkout-progress__showmessage">
                    <div class="et-checkout__wait-message-image"><img src="<?PHP echo plugin_dir_url( __DIR__ ) ?>images/Typing-1s-200px_og.gif" alt="Please wait image"></div>
                    <div class="et-checkout__wait-message-text">
                        <h2>Please wait while we are processing!</h2>
                        <p><strong>Do not close this window</strong> until you are re-directed to our thank you page.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="et-checkout">

<!--        CHECKOUT SUMMARY -->        
            <div class="et-checkout__summary">

                <div class="et-checkout__summary-body">
                    <h2 class="et-checkout__title">Donation Summary</h2>
                    <div class="et-checkout__selected_items">
                        <div class="et-checkout__items-holder">
                            <?php
                            $donations = Blackbaud_donations::get_donations();
                            if ($donations):
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
                                                    <span><?=BLACKBAUD_DONATIONS__CURRENCY?><span class="fund-id-<?=$donation->fund_id?>-total"><?=number_format($donation->amount * $donation->quantity, 2)?></span></span>
                                                </div>
                                            </div>
                                        </div>


                                    <?php endforeach; ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="et-checkout__summary-total">
                    <div class="et-checkout__summary-final-total">
                        <div class="et-checkout__summary-total-title"><h3>Total Donation</h3></div>
                        <h3 class="et-checkout__summary-total-amount donations-cart-total">&pound;<?=$donations_cart_total?></h3>
                    </div>
                </div>

            </div>

<!--        CHECKOUT FORM --> 
            <form method="post" action="" id="checkout-form" class="needs-validation" novalidate>
            <div class="et-checkout__form">

                <h2 class="et-checkout__title" id="your-information">Your Information</h2>

                <div class="et-checkout__response-holder">                                        
                    <p style="color:red; display:none; font-weight: bold" id="checkout-errors"></p>
                    <p class="et-checkout__response-message"></p>
                </div>  

                <div class="et-checkout__form-personal-details">

                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <select class="form-select" id="title" required>
                    <option selected value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Miss">Miss</option>
                    <option value="Ms">Ms</option>
                    <option value="Dr">Dr</option>
                    <option value="Prof">Prof</option>
                    </select>
                    <div class="invalid-feedback">
                    Please select a valid state.
                    </div>
                
                </div>
                <div class="mb-3">
                    <label for="firstname" class="form-label">First Name <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="firstname" type="text" class="form-control md3" placeholder="Your first name" maxlength="40" pattern="[A-Za-z]{1,32}" required />
                    <div class="invalid-feedback">
                    Please use the correct format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="lastname" type="text" class="form-control md3" placeholder="Your last name" maxlength="40" pattern="[A-Za-z]{1,32}" required/>
                    <div class="invalid-feedback">
                    Please use the correct format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email"  class="form-label">Email <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="email" type="email" class="form-control md3" placeholder="Your email" maxlength="150" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,}" required/>
                    <div class="invalid-feedback">
                    Please use the correct email format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Contact Number <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="phone" type="tel" class="form-control md3" minlength="9" maxlength="14" pattern="[0-9\s\-\+]+" required/>
                    <div class="invalid-feedback">
                    Please use the correct format: Only numbers, "-" or "+" allowed
                    </div>
                </div>

                <div class="content et-checkout__address-search">

                    <h3>Address Search</h3>                                
                    <div class="fieldWrap">
                    <input type="text" name="search" class="searchInput" id="searchBox" placeholder="Enter Address" aria-label="Enter Address" onchange="showClear(); return false;" onkeypress="return enterSearch(event);">
                    <div class="et-checkout__address-search-buttons">
                    <button onClick="findAddress(); return false;" aria-label="Search address">Search</button>
                    <button class="clear" id="clearButton" onClick="clearSearch(); return false;">Clear</button>
                    </div>                           
                    </div>

                    <div class="fieldWrap">
                    <div class="error" id="errorMessage"></div>
                    </div>

                    <div class="fieldWrap">
                    <div id="result"></div>
                    </div>

                    <div class="seperator" id="seperator"></div>

                    <div class="fieldWrap">
                    <div class="outputArea" id="output"></div>
                    </div>

                </div>


                <div class="mb-3">
                    <label for="address" class="form-label">Your Address <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="address" type="text" class="form-control md3" maxlength="32" pattern="[a-zA-Z0-9\s.]+"required />
                    <div class="invalid-feedback">
                    Please use the correct format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="city" type="text" class="form-control md3" maxlength="50"  pattern="[A-Za-z\s.\]+" required/>
                    <div class="invalid-feedback">
                    Please use the correct format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="postcode" class="form-label">Postcode <span class="et-checkout__form--required">(Required)</span></label>
                    <input id="postcode" type="text" class="form-control md3" maxlength="15" pattern="[a-zA-Z0-9\s]+" required/>
                    <div class="invalid-feedback">
                    Please use the correct format
                    </div>
                </div>
                <div class="mb-3">
                    <label for="country" class="form-label">Country <span class="et-checkout__form--required">(Required)</span></label>
                    <select id="country" name="country" class="form-select" required >
                    <option value="">Select country</option>
                    <option value="UK">United Kingdom</option>
                    <?php foreach($countries[0]->value as $country): ?>
                    <option value="<?=$country->abbreviation?>"><?=$country->name?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <!--                    <div class="mb-3">-->
                <!--                        <div class="et-checkout__form-personal-details">-->
                <!--                            <h3>Address search</h3>-->
                <!--                        </div>-->
                <!--                    </div>-->
                <div class="mb-3">
                    <label for="note" class="form-label">Add a Note <small>(Max 250 Characters)</small></label>
                    <textarea id="note" class="form-control md3" cols="30" rows="5" maxlength="250"></textarea>
                </div>

                </div>


                <div class="et-checkout__gift-aid et-checkout--grey" id="uk-gift-aid-declaration">

                <div class="et-checkout__gift-aid-head">
                    <div class="et-checkout__gift-aid-sig">
                    <img src="<?PHP echo plugin_dir_url( __FILE__ ).'../images/signature.png'?>" alt="">
                    </div>
                    <h2>Increase the value of your donation by <strong style="text-decoration:underline;" id="donations-giftaid-total">&pound;<?=get_donations_giftaid_total($donations_cart_total)?></strong></h2>
                </div>
                <div class="et-checkout__gift-aid-desc">
                    <div class="et-checkout__gift-aid-lead">
                    <h3>Boost your donation by 25p of Gift Aid for every &pound;1 you donate, at no extra cost to you.</h3>
                    </div>
                    <div class="et-checkout__gift-aid-smallprint">
                    <p>If you are a UK taxpayer, you can increase your donation's value by 25% without any additional cost
                    to you by adding Gift Aid. The charity will reclaim 25p of tax on every &pound;1 you've given, including any
                    gifts from the last four tax years, this gift, and all future gifts. If the Gift Aid claimed on all your
                    donations in a given tax year is more than your income tax, capital gains tax, or both, you are responsible
                    for paying the difference. The Gift Aid amount claimed will be used towards: the costs of the charity;
                    fundraising; administrative costs as well as our 'Where Most Needed' fund to save and transform more lives.
                    </p>
                    <p> <a href="https://www.gov.uk/donating-to-charity/gift-aid" target="_blank">Find out more about Gift Aid.</a></p>
                    </div>
                    <div>
                        <input class="form-check-input" type="checkbox" id="giftaid" name="giftaid" value="1">
                        <label class="form-check-label" for="giftaid" > Yes, please add my Gift Aid. </label>
                    </div>
                </div>
                </div>

                <div class="et-checkout__subscribe  et-checkout--grey">
                    <h2>Get Updates on Our Projects</h2>
                    <p>We would like to contact you from time to time to give you updates on our projects and events and how you can support us in the future.
                    Please opt in to receiving these via the options below. You can withdraw your consent at any time and visit our <a href="">Privacy notice here.</a>
                    </p>
                    <div class="et-checkout__subscribe-inputs">
                        <div>
                            <input class="form-check-input" type="checkbox" id="email-marketing-optin" name="email_marketing_optin" value="1">
                            <label class="form-check-label" for="email-marketing-optin">Email </label>
                        </div>
                        <div>
                            <input class="form-check-input" type="checkbox" id="phone-marketing-optin" name="phone_marketing_optin" value="1">
                            <label class="form-check-label" for="phone-marketing-optin">Phone </label>
                        </div>
                        <div>
                            <input class="form-check-input" type="checkbox" id="address-marketing-optin" name="address_marketing_optin" value="1">
                            <label class="form-check-label" for="address-marketing-optin">Mail </label>
                        </div>
                    </div>
                </div>
                <div class="et-checkout__proceed d-grid" >
                    <button class="btn btn-primary" type="submit" id="donate-now" data-amount="<?=$donations_cart_total_pence?>" data-amount-formatted="<?=$donations_cart_total?>">Proceed to Payment</button>
                </div>
            </div>
            </form>
        </div>
    </div>
</section>
<?php else: ?>
        <div class="et-checkout--error"><p>Please add some donations to continue with checkout.</p></div>
<?php endif; ?>

<script>
    // Function to track country changes
function trackChanges() {
  var selectElement = document.getElementById('country');
  
  selectElement.addEventListener('change', function() {
    var selectedValue = selectElement.value;
    console.log('Selected country:', selectedValue);
    // You can perform further actions with the selected value here
  });
}

    document.addEventListener('DOMContentLoaded', function() {
        // change currency once the user select usa as country 

   
        trackChanges()


        // create the transaction object
        transactionData = {
            key: '<?=PAYMENTS_PUBLIC_KEY?>',
            payment_configuration_id: '<?=PAYMENTS_CONFIG_ID?>',
            transaction_type: 'card_not_present'
        };

        // load the payment form in the background
        Blackbaud_Init(transactionData);


        document.getElementById('donate-now').addEventListener('click', function(e) {
            e.preventDefault();

            
            $('#checkout-errors').html("");
            let form_error = 0;

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            let forms = document.querySelectorAll('.needs-validation')
            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                if (!form.checkValidity()) {
                    form_error++; 
                }
                form.classList.add('was-validated')
                
            })
             
            // validate data has been entered
            if( form_error >= 1
            //document.getElementById('firstname').value === '' || 
            //document.getElementById('lastname').value === '' || 
            //document.getElementById('email').value === '' || 
            //document.getElementById('phone').value === '' || 
            //document.getElementById('address').value === '' || 
            //document.getElementById('city').value === '' || 
            //document.getElementById('postcode').value === '' || 
            //document.getElementById('country').value === ''
            ) {
                location.hash = "#your-information";
                $(".et-checkout-progress__step--one").removeClass("et-checkout-progress__step--done"); 
                $('#checkout-errors').html("Please ensure all fields have been completed").show();
                console.log("error");
                return;   
            }
            $(".et-checkout-progress__step--one").addClass("et-checkout-progress__step--done");
            var giftaid = document.getElementById('giftaid').value;

            // console.log($('#donate-now').attr('data-amount-formatted'));

            // append any donor-entered information to the transaction object
            transactionData.amount = $('#donate-now').attr('data-amount-formatted');
            transactionData.cardholder = document.getElementById('firstname').value + ' ' + document.getElementById('lastname').value;
            transactionData.billing_address_first_name = document.getElementById('firstname').value;
            transactionData.billing_address_last_name = document.getElementById('lastname').value;
            transactionData.billing_address_email = document.getElementById('email').value;
            transactionData.billing_address_phone = document.getElementById('phone').value;
            transactionData.billing_address_line = document.getElementById('address').value;
            transactionData.billing_address_city = document.getElementById('city').value;
            // transactionData.billing_address_state = document.getElementById('state').value;
            transactionData.billing_address_post_code = document.getElementById('postcode').value;
            transactionData.billing_address_country = document.getElementById('country').value;
            transactionData.primary_color = '#EDB601';
            transactionData.is_email_required = true;
            transactionData.is_name_required = true;
            transactionData.is_name_visible = true;
            transactionData.use_captcha = false;
            transactionData.note = "Donation via website. Reference <?=Blackbaud_donations::get_cart_id(false)?>";

            console.log("Donate Now Clicked");
            Blackbaud_Open(transactionData);

        });

        document.addEventListener('checkoutCancel', function() {
            // window.location = '/';
        });

        document.addEventListener('checkoutComplete', function(e) {
            // console.log($('#donate-now').attr('data-amount'));

            var data = {
                amount: $('#donate-now').attr('data-amount-formatted'),
                authorization_token: e.detail.transactionToken,
                application_fee: 0,
                title: document.getElementById('title').value,
                firstname: document.getElementById('firstname').value,
                lastname: document.getElementById('lastname').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                postcode: document.getElementById('postcode').value,
                country: document.getElementById('country').value,
                email_marketing_optin: get_email_marketing_optin(),
                phone_marketing_optin: get_phone_marketing_optin(),
                address_marketing_optin: get_address_marketing_optin(),
                giftaid: get_giftaid_optin(),
                note: document.getElementById('note').value,
                action: 'complete_checkout_transaction'
            };
            console.log("checkout complete");
            // google analytics data 
            let dataGA = data 
            dataGa.items = JSON.parse('<?php echo json_encode($donations) ?>')
            window.dataLayer.push({event:"checkout-completed-event", data:dataGA})
            $(".et-checkout-progress__step--one").addClass("et-checkout-progress--loading");
            $(".et-checkout-progress__step--two").addClass("et-checkout-progress__step--done");
            $("#et-checkout-progress__showmessage").show();

            jQuery.post(ajax_object.ajax_url, data, function(response) {
                console.log("Sending data to BB");
                console.log(response);
                window.location = '/wp_MDUK/thanks-for-your-donation/';
            });
        });

        document.addEventListener('checkoutError', function(e) {
            // handle Error event
            console.log('error text: ', e.detail.errorText);
            console.log('error code: ', e.detail.errorCode);

            $('#checkout-errors').html(e.detail.errorText).show();
        });
    });

    function get_email_marketing_optin() {
        return $('#email-marketing-optin').is(':checked') ? '1' : '0';
    }

    function get_phone_marketing_optin() {
        return $('#phone-marketing-optin').is(':checked') ? '1' : '0';
    }

    function get_address_marketing_optin() {
        return $('#address-marketing-optin').is(':checked') ? '1' : '0';
    }

    function get_giftaid_optin() {
        return $('#giftaid').is(':checked') ? '1' : '0';
    }

    function showClear() {
        document.getElementById("clearButton").style.display = "block";
    }

    function clearSearch() {
        var input = document.getElementById("searchBox");
        input.value = "";
        document.getElementById("clearButton").style.display = "none";
    }

    function showError(message) {
        var error = document.getElementById("errorMessage");
        error.innerText = message;
        error.style.display = "block";

        setTimeout(function(){
            error.style.display = "none";
        }, 10000)
    }

    function enterSearch(e) {
        if (e.keyCode == 13){
            findAddress();
        }
    }

    function findAddress(SecondFind) {
        var Text = document.getElementById("searchBox").value;

        if (Text === "") {
            showError("Please enter an address");
            return;
        }

        var Container = "";

        if (SecondFind !== undefined){
            Container = SecondFind;
        }

        var Key = "BJ23-BX33-AH88-FB69",
            IsMiddleware = false,
            Origin = "",
            Countries = "GBR",
            Limit = "10",
            Language = "en-gb",
            url = 'https://services.postcodeanywhere.co.uk/Capture/Interactive/Find/v1.10/json3.ws';
        var params = '';
        params += "&Key=" + encodeURIComponent('EM98-AB72-MN31-CU58');
        params += "&Text=" + encodeURIComponent(Text);
        params += "&IsMiddleware=" + encodeURIComponent(IsMiddleware);
        params += "&Container=" + encodeURIComponent(Container);
        params += "&Origin=" + encodeURIComponent(Origin);
        params += "&Countries=" + encodeURIComponent(Countries);
        params += "&Limit=" + encodeURIComponent(Limit);
        params += "&Language=" + encodeURIComponent(Language);
        var http = new XMLHttpRequest();
        http.open('POST', url, true);
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        http.onreadystatechange = function() {
            if (http.readyState == 4 && http.status == 200) {
                var response = JSON.parse(http.responseText);
                if (response.Items.length == 1 && typeof(response.Items[0].Error) != "undefined") {
                    showError(response.Items[0].Description);
                }
                else {
                    if (response.Items.length == 0)
                        showError("Sorry, there were no results");

                    else {
                        var resultBox = document.getElementById("result");

                        if (resultBox.childNodes.length > 0) {
                            var selectBox = document.getElementById("mySelect");
                            selectBox.parentNode.removeChild(selectBox)
                        }

                        var resultArea = document.getElementById("result");
                        var list = document.createElement("select");
                        list.id = "selectList";
                        list.setAttribute("id", "mySelect");
                        resultArea.appendChild(list);

                        var defaultOption = document.createElement("option");
                        defaultOption.text = "Select Address";
                        defaultOption.setAttribute("value", "");
                        defaultOption.setAttribute("selected", "selected");
                        list.appendChild(defaultOption);

                        for (var i = 0; i < response.Items.length; i++){
                            var option = document.createElement("option");
                            option.setAttribute("value", response.Items[i].Id)
                            option.text = response.Items[i].Text + " " + response.Items[i].Description;
                            option.setAttribute("class", response.Items[i].Type)

                            list.appendChild(option);
                        }
                        selectAddress(Key);
                    }
                }
            }
        }
        http.send(params);
    };

    function selectAddress(Key){
        var resultList = document.getElementById("result");

        if (resultList.childNodes.length > 0) {
            var elem = document.getElementById("mySelect");

            //IE fix
            elem.onchange = function (e) {

                var target = e.target[e.target.selectedIndex];

                if (target.text === "Select Address") {
                    return;
                }

                if (target.className === "Address"){
                    retrieveAddress(Key, target.value);
                }

                else {
                    findAddress(target.value)
                }
            };
        }
    };

    function retrieveAddress(Key, Id){
        var Field1Format = "";
        var url = 'https://services.postcodeanywhere.co.uk/Capture/Interactive/Retrieve/v1.00/json3.ws';
        var params = '';
        params += "&Key=" + encodeURIComponent('EM98-AB72-MN31-CU58');
        params += "&Id=" + encodeURIComponent(Id);
        params += "&Field1Format=" + encodeURIComponent(Field1Format);

        var http = new XMLHttpRequest();
        http.open('POST', url, true);
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        http.onreadystatechange = function() {
            if (http.readyState == 4 && http.status == 200) {
                var response = JSON.parse(http.responseText);

                if (response.Items.length == 1 && typeof(response.Items[0].Error) != "undefined") {
                    showError(response.Items[0].Description);
                }
                else {
                    if (response.Items.length == 0)
                        showError("Sorry, there were no results");
                    else {
                        var res = response.Items[0];

                        $('#address').val(res.Line1);
                        $('#city').val(res.City);
                        $('#postcode').val(res.PostalCode);

                        if(res.CountryIso3 === 'GBR') {
                            $('#country').val('UK');
                        } else {
                            $('#country').val(res.CountryIso3);
                        }
                    }
                }
            }
        }
        http.send(params);
    }



</script>

<script src="https://payments.blackbaud.com/Checkout/bbCheckout.2.0.js"></script>