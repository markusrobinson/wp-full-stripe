jQuery(document).ready(function ($)
{
    var handler = StripeCheckout.configure({
        key: stripekey,
        token: function (token, args)
        {
            var $form = $(document).data('liveForm');

            var name = $("input[name='name']", $form).val();
            var redirectOnSuccess = $("input[name='redirectOnSuccess']", $form).val();
            var redirectPostID = $("input[name='redirectPostID']", $form).val();
            var showBillingAddress = $("input[name='showBillingAddress']", $form).val();

            $form.append("<input type='hidden' name='stripeToken' value='" + token.id + "' />");
            $form.append("<input type='hidden' name='stripeEmail' value='" + token.email + "' />");
            $form.append("<input type='hidden' name='form' value='" + name + "' />");
            $form.append("<input type='hidden' name='doRedirect' value='" + redirectOnSuccess + "' />");
            $form.append("<input type='hidden' name='redirectId' value='" + redirectPostID + "' />");

            //if billing address
            if (showBillingAddress == 1 && args.length > 0)
            {
                $form.append("<input type='hidden' name='billing_name' value='" + args.billing_name + "' />");
                $form.append("<input type='hidden' name='billing_address_country' value='" + args.billing_address_country + "' />");
                $form.append("<input type='hidden' name='billing_address_zip' value='" + args.billing_address_zip + "' />");
                $form.append("<input type='hidden' name='billing_address_state' value='" + args.billing_address_state + "' />");
                $form.append("<input type='hidden' name='billing_address_line1' value='" + args.billing_address_line1 + "' />");
                $form.append("<input type='hidden' name='billing_address_city' value='" + args.billing_address_city + "' />");
            }

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: $form.serialize(),
                cache: false,
                dataType: "json",
                success: function (data)
                {
                    $('#showLoading-' + name).hide();
                    var $err = $(".payment-errors-" + name);

                    if (data.success)
                    {
                        //inform user of success
                        $err.addClass('alert alert-success');
                        $err.html(data.msg);
                        $(document).removeData('liveForm');

                        //server tells us if redirect is required
                        if (data.redirect)
                        {
                            setTimeout(function ()
                            {
                                window.location = data.redirectURL;
                            }, 1500);
                        }
                    }
                    else
                    {
                        // show the errors on the form
                        $err.addClass('alert alert-error');
                        $err.html(data.msg);
                        $err.fadeIn(500).fadeOut(500).fadeIn(500);
                    }
                }

            });
        },
        closed: function ()
        {
            $('.showLoading').hide();
        }
    });

    $('.fullstripe_checkout_form').submit(function (e)
    {
        e.preventDefault();

        var companyName = $("input[name='companyName']", this).val();
        var productDesc = $("input[name='productDesc']", this).val();
        var amount = $("input[name='amount']", this).val();
        var buttonTitle = $("input[name='buttonTitle']", this).val();
        var showBillingAddress = $("input[name='showBillingAddress']", this).val();
        var showRememberMe = $("input[name='showRememberMe']", this).val();
        var image = $("input[name='image']", this).val();
        var currency = $("input[name='currency']", this).val();
        var name = $("input[name='name']", this).val();

        $(document).data('liveForm', $(this));

        $('#showLoading-' + name).show();
        var $err = $(".payment-errors-" + name);
        $err.removeClass('alert alert-error alert-success');
        $err.html('');

        handler.open({
            name: companyName,
            description: productDesc,
            amount: amount,
            panelLabel: buttonTitle,
            billingAddress: (showBillingAddress == 1),
            allowRememberMe: (showRememberMe == 1),
            image: image,
            currency: currency
        });

        return false;
    });

});