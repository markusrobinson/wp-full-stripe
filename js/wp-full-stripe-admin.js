/*
 Plugin Name: WP Full Stripe
 Plugin URI: http://mammothology.com/products/view/wp-full-stripe
 Description: Complete Stripe payments integration for Wordpress
 Author: Mammothology
 Version: 1.0
 Author URI: http://mammothology.com
 */

Stripe.setPublishableKey(stripekey);

jQuery(document).ready(function ($)
{
    var $loading = $(".showLoading");
    var $update = $("#updateDiv");
    $loading.hide();
    $update.hide();

    function resetForm($form)
    {
        $form.find('input:text, input:password, input:file, select, textarea').val('');
        $form.find('input:radio, input:checkbox')
            .removeAttr('checked').removeAttr('selected');
    }

    function validField(field, fieldName, errorField)
    {
        var valid = true;
        if (field.val() === "")
        {
            showError(fieldName + " must contain a value");
            valid = false;
        }
        return valid;
    }

    function showError(message)
    {
        showMessage('error', 'updated', message);
    }

    function showUpdate(message)
    {
        showMessage('updated', 'error', message);
    }

    function showMessage(addClass, removeClass, message)
    {
        $update.removeClass(removeClass);
        $update.addClass(addClass);
        $update.html("<p>" + message + "</p>");
        $update.show();
        document.body.scrollTop = document.documentElement.scrollTop = 0;
    }

    function clearUpdateAndError()
    {
        $update.html("");
        $update.removeClass('error');
        $update.removeClass('update');
        $update.hide();
    }

    //for uploading images using WordPress media library
    var custom_uploader;
    function uploadImage(inputID)
    {
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader)
        {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title:'Choose Image',
            button:{
                text:'Choose Image'
            },
            multiple:false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function ()
        {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $(inputID).val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();
    }

    // called on form submit when we know includeCustomFields = 1
    function processCustomFields(form)
    {
        var valid = true;
        var count = $('#customInputNumberSelect').val();
        var customValues = '';
        for (var i = 1 ; i <= count; i++)
        {
            // first validate the field
            var field = '#form_custom_input_label_' + i;
            valid = validField($(field), 'Custom Input Label ' + i, $update);
            if (!valid) return false;
            // save the value, stripping all single & double quotes
            customValues += $(field).val().replace(/['"]+/g, '');
            if (i < count)
                customValues += '{{';
        }

        // now append to the form
        form.append('<input type="hidden" name="customInputs" value="' + customValues + '"/>');

        return valid;
    }

    function do_ajax_post(ajaxUrl, form, successMessage, doRedirect)
    {
        $loading.show();
        // Disable the submit button
        form.find('button').prop('disabled', true);

        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: form.serialize(),
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $loading.hide();
                document.body.scrollTop = document.documentElement.scrollTop = 0;

                if (data.success)
                {
                    showUpdate(successMessage);
                    form.find('button').prop('disabled', false);
                    resetForm(form);

                    if (doRedirect)
                    {
                        setTimeout(function ()
                        {
                            window.location = data.redirectURL;
                        }, 1000);
                    }
                }
                else
                {
                    // re-enable the submit button
                    form.find('button').prop('disabled', false);
                    // show the errors on the form
                    showError(data.msg);
                }
            }
        });
    }


    $('#create-subscription-plan').submit(function (e)
    {
        clearUpdateAndError();

        var valid = validField($('#sub_id'), 'ID', $update);
        valid = valid && validField($('#sub_name'), 'Name', $update);
        valid = valid && validField($('#sub_amount'), 'Amount', $update);
        valid = valid && validField($('#sub_trial'), 'Trial Days', $update);

        if (valid)
        {
            var $form = $(this);
            do_ajax_post(admin_ajaxurl, $form, "Plan created.", false);
        }

        return false;

    });

    $('#create-subscription-form').submit(function (e)
    {
        clearUpdateAndError();

        //get the checked plans
        var checkedVals = $('.plan_checkbox:checkbox:checked').map(function ()
        {
            return this.value;
        }).get();
        var plans = checkedVals.join(",");

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validField($('#form_title'), 'Form Title', $update);

        if (valid && checkedVals.length === 0)
        {
            showError("You must check at least one subscription plan");
            valid = false;
        }

        var includeCustom = $('input[name=form_include_custom_input]:checked', '#create-subscription-form').val();
        if (includeCustom == 1)
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.

        if (valid)
        {
            var $form = $(this);
            //create a plans field for all the checked plans
            $form.append("<input type='hidden' name='selected_plans' value='" + plans + "' />");
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Subscription form created.", true);
        }

        return false;
    });

    $('#edit-subscription-form').submit(function (e)
    {
        clearUpdateAndError();

        //get the checked plans
        var checkedVals = $('.plan_checkbox:checkbox:checked').map(function ()
        {
            return this.value;
        }).get();
        var plans = checkedVals.join(",");

        var includeCustom = $('input[name=form_include_custom_input]:checked', '#edit-subscription-form').val();

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validField($('#form_title'), 'Form Title', $update);

        if (valid && checkedVals.length === 0)
        {
            showError("You must check at least one subscription plan");
            valid = false;
        }

        if (includeCustom == 1)
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.

        if (valid)
        {
            var $form = $(this);
            //create a plans field for all the checked plans
            $form.append("<input type='hidden' name='selected_plans' value='" + plans + "' />");
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Subscription form updated.", true);
        }

        return false;
    });

    $('#create-payment-form').submit(function (e)
    {
        clearUpdateAndError();

        var customAmount = $('input[name=form_custom]:checked', '#create-payment-form').val();
        var includeCustom = $('input[name=form_include_custom_input]:checked', '#create-payment-form').val();

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validField($('#form_title'), 'Form Title', $update);
        if (customAmount == 0)
            valid = valid && validField($('#form_amount'), 'Amount', $update);
        if (includeCustom == 1)
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.

        if (valid)
        {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Payment form created.", true);
        }

        return false;
    });

    $('#edit-payment-form').submit(function (e)
    {
        clearUpdateAndError();

        var customAmount = $('input[name=form_custom]:checked', '#edit-payment-form').val();
        var includeCustom = $('input[name=form_include_custom_input]:checked', '#edit-payment-form').val();

        var valid = validField($('#form_name'), 'Name', $update);
        valid = valid && validField($('#form_title'), 'Form Title', $update);
        if (customAmount == 0)
            valid = valid && validField($('#form_amount'), 'Amount', $update);
        if (includeCustom == 1)
            valid = valid && processCustomFields($(this)); //NOTE: must do this last as it appends a hidden input.

        if (valid)
        {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Payment form updated.", true);
        }

        return false;
    });

    $('#create-checkout-form').submit(function (e)
    {
        clearUpdateAndError();

        var valid = validField($('#form_name_ck'), 'Name', $update);
        valid = valid && validField($('#company_name_ck'), 'Company Name', $update);
        valid = valid && validField($('#form_amount_ck'), 'Amount', $update);

        if (valid)
        {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Checkout form created.", true);
        }

        return false;
    });

    $('#edit-checkout-form').submit(function (e)
    {
        clearUpdateAndError();

        var valid = validField($('#form_name_ck'), 'Name', $update);
        valid = valid && validField($('#company_name_ck'), 'Company Name', $update);
        valid = valid && validField($('#form_amount_ck'), 'Amount', $update);

        if (valid)
        {
            $loading.show();
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Checkout form updated.", true);
        }

        return false;
    });

    //upload checkout form images
    $('#upload_image_button').click(function (e)
    {
        e.preventDefault();
        uploadImage('#form_checkout_image');
    });

    $('#settings-form').submit(function (e)
    {
        clearUpdateAndError();
        var $form = $(this);
        //post form via ajax
        do_ajax_post(admin_ajaxurl, $form, "Settings updated.", true);
        return false;
    });

    //The forms delete button
    $('button.delete').click(function ()
    {
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
        var action = '';
        if (type === 'paymentForm')
            action = 'wp_full_stripe_delete_payment_form';
        else if (type === 'subscriptionForm')
            action = 'wp_full_stripe_delete_subscription_form';
        else if (type === 'checkoutForm')
            action = 'wp_full_stripe_delete_checkout_form';
        else if (type === 'subscriber')
            action = 'wp_full_stripe_delete_subscriber';
        else if (type === 'payment')
            action = 'wp_full_stripe_delete_payment';

        var row = $(this).parents('tr:first');

        $loading.show();

        $.ajax({
            type: "POST",
            url: admin_ajaxurl,
            data: {id: id, action: action},
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $loading.hide();

                if (data.success)
                {
                    $(row).remove();
                    showUpdate("Record deleted.");
                }
            }
        });

        return false;

    });

    $('#create-recipient-form').submit(function (e)
    {
        e.preventDefault();
        $update.removeClass('error');
        $update.text("");

        var $form = $(this);

        var valid = validField($('#recipient_name'), 'Recipient Name', $update);

        if (valid)
        {
            $loading.show();
            // Disable the submit button
            $form.find('button').prop('disabled', true);
            $(document).data('formSubmit', $form);
            Stripe.bankAccount.createToken($form, stripeResponseHandler);
        }
        return false;
    });

    $('#create-recipient-form-card').submit(function (e)
    {
        e.preventDefault();
        $update.removeClass('error');
        $update.text("");

        var $form = $(this);

        var valid = validField($('#recipient_name_card'), 'Recipient Name', $update);

        if (valid)
        {
            $loading.show();
            // Disable the submit button
            $form.find('button').prop('disabled', true);
            // get the pay to type to know what kind of token to create
            $(document).data('formSubmit', $form);
            Stripe.createToken($form, stripeResponseHandler);
        }
        return false;
    });

    $('#create-transfer-form').submit(function (e)
    {
        clearUpdateAndError();

        var valid = validField($('#transfer_amount'), 'Transfer Amount', $update);

        if (valid)
        {
            var $form = $(this);
            //post form via ajax
            do_ajax_post(admin_ajaxurl, $form, "Transfer initiated.", false);
        }
        return false;

    });

    /////////////////////////

    var stripeResponseHandler = function (status, response)
    {
        var $form = $(document).data('formSubmit');

        if (response.error)
        {
            // Show the errors
            showError(response.error.message);
            $form.find('button').prop('disabled', false);
            $loading.hide();
        }
        else
        {
            // token contains bank account
            var token = response.id;
            $form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");

            //post payment via ajax
            $.ajax({
                type: "POST",
                url: admin_ajaxurl,
                data: $form.serialize(),
                cache: false,
                dataType: "json",
                success: function (data)
                {
                    $loading.hide();

                    if (data.success)
                    {
                        //clear form fields
                        $form.find('input:text, input:password').val('');
                        //inform user of success
                        showUpdate(data.msg);
                        $form.find('button').prop('disabled', false);
                    }
                    else
                    {
                        // re-enable the submit button
                        $form.find('button').prop('disabled', false);
                        // show the errors on the form
                        showError(data.msg);
                    }
                }
            });
        }
    };

    /////////////////////

    $('#customInputNumberSelect').change(function()
    {
        var val = $(this).val();
        var $c2 = $('.ci2');
        var $c3 = $('.ci3');
        var $c4 = $('.ci4');
        var $c5 = $('.ci5');
        if (val == 1)
        {
            $c2.hide();$c3.hide();$c4.hide();$c5.hide();
        }
        else if (val == 2)
        {
            $c2.show();$c3.hide();$c4.hide();$c5.hide();
        }
        else if (val == 3)
        {
            $c2.show();$c3.show();$c4.hide();$c5.hide();
        }
        else if (val == 4)
        {
            $c2.show();$c3.show();$c4.show();$c5.hide();
        }
        else if (val == 5)
        {
            $c2.show();$c3.show();$c4.show();$c5.show();
        }
    }).change();

    //payment type toggle
    $('#set_custom_amount').click(function ()
    {
        $('#form_amount').prop('disabled', true);
    });
    $('#set_specific_amount').click(function ()
    {
        $('#form_amount').prop('disabled', false);
    });

    $('#do_redirect_no').click(function ()
    {
        $('#form_redirect_post_id').prop('disabled', true);
    });
    $('#do_redirect_yes').click(function ()
    {
        $('#form_redirect_post_id').prop('disabled', false);
    });
    $('#do_redirect_no_ck').click(function ()
    {
        $('#form_redirect_post_id_ck').prop('disabled', true);
    });
    $('#do_redirect_yes_ck').click(function ()
    {
        $('#form_redirect_post_id_ck').prop('disabled', false);
    });
    //form type toggle
    $('#set_payment_form_type_payment').click(function ()
    {
        $("#createCheckoutFormSection").hide();
        $("#createPaymentFormSection").show();
    });
    $('#set_payment_form_type_checkout').click(function ()
    {
        $("#createCheckoutFormSection").show();
        $("#createPaymentFormSection").hide();
    });

    $('#set_recipient_bank_account').click(function ()
    {
        $("#createRecipientCard").hide();
        $("#createRecipientBank").show();
    });
    $('#set_recipient_debit_card').click(function ()
    {
        $("#createRecipientCard").show();
        $("#createRecipientBank").hide();
    });
    // custom inputs
    $('#noinclude_custom_input').click(function ()
    {
        $('#customInputSection').hide();
    });
    $('#include_custom_input').click(function ()
    {
        $('#customInputSection').show();
    });
});