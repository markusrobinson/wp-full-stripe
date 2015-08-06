<?php

require_once('wp-full-stripe-logger-configurator.php');

//deals with customer front-end input i.e. payment forms submission
class MM_WPFS_Customer
{
    private $stripe = null;
    private $log;

    public function __construct()
    {
        $this->stripe = new MM_WPFS_Stripe();
        $this->db = new MM_WPFS_Database();
        $this->hooks();

        $this->log = Logger::getLogger("WPFS");
    }

    private function hooks()
    {
        add_action('wp_ajax_wp_full_stripe_payment_charge', array($this, 'fullstripe_payment_charge'));
        add_action('wp_ajax_nopriv_wp_full_stripe_payment_charge', array($this, 'fullstripe_payment_charge'));
        add_action('wp_ajax_wp_full_stripe_subscription_charge', array($this, 'fullstripe_subscription_charge'));
        add_action('wp_ajax_nopriv_wp_full_stripe_subscription_charge', array($this, 'fullstripe_subscription_charge'));
        add_action('wp_ajax_wp_full_stripe_check_coupon', array($this, 'fullstripe_check_coupon'));
        add_action('wp_ajax_nopriv_wp_full_stripe_check_coupon', array($this, 'fullstripe_check_coupon'));
        add_action('wp_ajax_fullstripe_checkout_form_charge', array($this, 'fullstripe_checkout_charge'));
        add_action('wp_ajax_nopriv_fullstripe_checkout_form_charge', array($this, 'fullstripe_checkout_charge'));
    }

    function fullstripe_payment_charge()
    {

        //get POST data from form
        $valid = true;
        $card = $_POST['stripeToken'];
        $name = sanitize_text_field($_POST['fullstripe_name']);
        $amount = $_POST['amount'];
        $formName = $_POST['formName'];
        $isCustom = $_POST['isCustom'];
        $doRedirect = $_POST['formDoRedirect'];
        $redirectPostID = $_POST['formRedirectPostID'];
        $showAddress = $_POST['showAddress'];
        $sendReceipt = $_POST['sendEmailReceipt'];
        $options = get_option('fullstripe_options');

        if ($isCustom == 1)
        {
            $amount = $_POST['fullstripe_custom_amount'];
            if (!is_numeric($amount))
            {
                $valid = false;
                $return = array('success' => false, 'msg' => __('The payment amount is invalid, please only use numbers and a decimal point', 'wp-full-stripe'));
            }
            else
            {
                $amount = $amount * 100; //Stripe expects amounts in cents/pence
            }
        }

        $address1 = isset($_POST['fullstripe_address_line1']) ? sanitize_text_field($_POST['fullstripe_address_line1']) : '';
        $address2 = isset($_POST['fullstripe_address_line2']) ? sanitize_text_field($_POST['fullstripe_address_line2']) : '';
        $city = isset($_POST['fullstripe_address_city']) ? sanitize_text_field($_POST['fullstripe_address_city']) : '';
        $state = isset($_POST['fullstripe_address_state']) ? sanitize_text_field($_POST['fullstripe_address_state']) : '';
        $zip = isset($_POST['fullstripe_address_zip']) ? sanitize_text_field($_POST['fullstripe_address_zip']) : '';

        if ($showAddress == 1)
        {
            if ($address1 == '' || $city == '' || $zip == '')
            {
                $valid = false;
                $return = array('success' => false, 'msg' => __('Please enter a valid billing address', 'wp-full-stripe'));
            }
        }

        $email = '';
        if (isset($_POST['fullstripe_email']))
        {
           $email = $_POST['fullstripe_email'];
           if (!filter_var($email, FILTER_VALIDATE_EMAIL))
           {
               $valid = false;
               $return = array('success' => false, 'msg' => __('Please enter a valid email address', 'wp-full-stripe'));
           }
        }
        else
        {
            $valid = false;
            $return = array('success' => false, 'msg' => __('Please enter a valid email address', 'wp-full-stripe'));
        }

        if ($valid)
        {
            $customInputs = isset($_POST['customInputs']) ? $_POST['customInputs'] : null;
            $customInputValues = isset($_POST['fullstripe_custom_input']) ? $_POST['fullstripe_custom_input'] : array();
            $description = "Payment from $name on form: $formName";
            $metadata = array(
                'customer_name' => $name,
                'customer_email' => $email,
                'billing_address_line1' => $address1,
                'billing_address_line2' => $address2,
                'billing_address_city' => $city,
                'billing_address_state' => $state,
                'billing_address_zip' => $zip
            );

            try
            {
                //check email
                $sendPluginEmail = true;
                if ($options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 && isset($_POST['fullstripe_email']))
                {
                    $sendPluginEmail = false;
                }

                do_action('fullstripe_before_payment_charge', $amount);
                //create/get customer object
                $stripeCustomer = $this->create_or_get_customer($card, $email, $metadata, ($options['apiMode'] === 'live'));
                //try the charge
                $metadata = $this->add_custom_inputs($metadata, $customInputs,$customInputValues);
                $result = $this->stripe->charge_customer($stripeCustomer->id, $amount, $description, $metadata,($sendPluginEmail==false && $sendReceipt==true ? $email : null));
                $result['wpfs_form'] = $formName;
                do_action('fullstripe_after_payment_charge', $result);

                //save the payment
                $address = array('line1' => $address1, 'line2' => $address2, 'city' => $city, 'state' => $state, 'zip' => $zip);
                $this->db->fullstripe_insert_payment($result, $address, $stripeCustomer->id, $email);

                $return = array('success' => true, 'msg' => 'Payment Successful!');
                if ($doRedirect == 1 && $redirectPostID != 0)
                {
                    $return['redirect'] = true;
                    $return['redirectURL'] = get_page_link($redirectPostID);
                }

                //send email receipt (it is better if done in a background thread...)
                if ($sendPluginEmail && $sendReceipt == 1 && isset($_POST['fullstripe_email']))
                {
                    $this->fullstripe_payment_send_email_receipt($email, $amount, $name, $address);
                }

            }
            catch (Exception $e)
            {
                $this->log->error('Error during Payment Charge: ', $e);
                //show notification of error
                $return = array('success' => false, 'msg' => __('There was an error processing your payment: ', 'wp-full-stripe') . $e->getMessage());
            }
        }

        //correct way to return JS results in wordpress
        header("Content-Type: application/json");
        echo json_encode(apply_filters('fullstripe_payment_charge_return_message', $return));
        exit;
    }


    function fullstripe_subscription_charge()
    {
        $card = $_POST['stripeToken'];
        $name = sanitize_text_field($_POST['fullstripe_name']);
        $planID = $_POST['fullstripe_plan'];
        $customInputs = isset($_POST['customInputs']) ? $_POST['customInputs'] : null;
        $customInputValues = isset($_POST['fullstripe_custom_input']) ? $_POST['fullstripe_custom_input'] : array();
        $couponCode = isset($_POST['fullstripe_coupon_input']) ? $_POST['fullstripe_coupon_input'] : '';
        $doRedirect = $_POST['formDoRedirect'];
        $redirectPostID = $_POST['formRedirectPostID'];
        $address1 = isset($_POST['fullstripe_address_line1']) ? sanitize_text_field($_POST['fullstripe_address_line1']) : '';
        $address2 = isset($_POST['fullstripe_address_line2']) ? sanitize_text_field($_POST['fullstripe_address_line2']) : '';
        $city = isset($_POST['fullstripe_address_city']) ? sanitize_text_field($_POST['fullstripe_address_city']) : '';
        $state = isset($_POST['fullstripe_address_state']) ? sanitize_text_field($_POST['fullstripe_address_state']) : '';
        $zip = isset($_POST['fullstripe_address_zip']) ? sanitize_text_field($_POST['fullstripe_address_zip']) : '';
        $setupFee = $_POST['fullstripe_setupFee'];
        $sendReceipt = $_POST['sendEmailReceipt'];
        $options = get_option('fullstripe_options');

        //validation
        $valid = true;
        $email = '';
        if (isset($_POST['fullstripe_email']))
        {
            $email = $_POST['fullstripe_email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $valid = false;
                $return = array('success' => false, 'msg' => __('Please enter a valid email address', 'wp-full-stripe'));
            }
        }
        else
        {
            $valid = false;
            $return = array('success' => false, 'msg' => __('Please enter a valid email address', 'wp-full-stripe'));
        }

        if ($valid)
        {
            $description =  "Subscriber: " . $name;
            $metadata = array(
                'customer_name' => $name,
                'customer_email' => $email,
                'billing_address_line1' => $address1,
                'billing_address_line2' => $address2,
                'billing_address_city' => $city,
                'billing_address_state' => $state,
                'billing_address_zip' => $zip,
            );
            $metadata = $this->add_custom_inputs($metadata, $customInputs, $customInputValues);

            try
            {
                $sendPluginEmail = true;
                if ($options['receiptEmailType'] == 'stripe' && $sendReceipt == 1)
                {
                    $sendPluginEmail = false;
                }

                // Check if we already have a customer created from a previous time
                $stripeCustomer = $this->db->find_existing_stripe_customer_by_email($email, ($options['apiMode'] === 'live'));
                do_action('fullstripe_before_subscription_charge', $planID);

                $address = array('line1' => $address1, 'line2' => $address2, 'city' => $city, 'state' => $state, 'zip' => $zip);

                if ($stripeCustomer && $stripeCustomer['stripeCustomerID'])
                {
                    $this->stripe->subscribe_existing($stripeCustomer['stripeCustomerID'], $planID, $card, $couponCode, $setupFee, $metadata);
                    $customer = $this->include_customer_subscription($this->stripe->retrieve_customer($stripeCustomer['stripeCustomerID']));

                    // check because payments also create stripe customer objects
                    if ($stripeCustomer['is_subscriber'])
                    {
                        $this->db->update_subscriber($stripeCustomer['subscriberID'],
                            array(
                                'planID' => $planID,
                                'addressLine1' => $address1,
                                'addressLine2' => $address2,
                                'addressCity' => $city,
                                'addressState' => $state,
                                'addressZip' => $zip
                            ));
                    }
                    else // stripe customer was a payment but now a subscriber
                    {
                        $this->db->fullstripe_insert_subscriber($customer, $name, $address);
                    }
                }
                else // new subscriber
                {
                    $customer = $this->include_customer_subscription($this->stripe->subscribe($planID, $card, $email, $description, $couponCode, $setupFee, $metadata));
                    $this->db->fullstripe_insert_subscriber($customer, $name, $address);
                }

                // Do our after subscription action with the Stripe customer so other plugins can hook in
                do_action('fullstripe_after_subscription_charge', $customer);

                $return = array('success' => true, 'msg' => 'Payment Successful. Thanks for subscribing!');
                if ($doRedirect == 1 && $redirectPostID != 0)
                {
                    $return['redirect'] = true;
                    $return['redirectURL'] = get_page_link($redirectPostID);
                }

                $plan = $this->stripe->retrieve_plan($planID);

                //send email receipt (it is better if done in a background thread...)
                if ($sendPluginEmail && $sendReceipt == 1 )
                {
                    $this->fullstripe_subscription_send_email_receipt($email, $setupFee, $plan['name'], $plan['amount'], $name, $address);
                }
            }
            catch (Exception $e)
            {
                $this->log->error('Error during Subscription Charge: ', $e);
                //show notification of error
                $return = array('success' => false, 'msg' => __('There was an error processing your payment: ', 'wp-full-stripe') . $e->getMessage());
            }
        }

        header("Content-Type: application/json");
        echo json_encode(apply_filters('fullstripe_subscription_charge_return_message', $return));
        exit;
    }

    // In later versions of the Stripe API, the subscription property is removed so we must create it ourselves for compatibility
    private function include_customer_subscription($customer)
    {
        // the value is already set meaning user has Stripe API version 2013-02-13 or older
        if (isset($customer->subscription)) return $customer;

        //  get the first item from the subscriptions data as the most recently added
        $customer->subscription = $customer->subscriptions->data[0];

        return $customer;
    }

    function fullstripe_checkout_charge()
    {

        //get POST data from form
        $token = $_POST['stripeToken'];
        $name = sanitize_text_field($_POST['name']);
        $email = $_POST['stripeEmail'];
        $form = $_POST['form'];
        $doRedirect = $_POST['doRedirect'];
        $redirectPostID = $_POST['redirectId'];
        $showBillingAddress = $_POST['showBillingAddress'];
        $sendReceipt = $_POST['sendEmailReceipt'];
        //get form
        $formData = $this->db->get_checkout_form_by_name($form);
        $amount = $formData["amount"];
        $description = "Payment for " . $formData["productDesc"];
        $options = get_option('fullstripe_options');

        try
        {

            //check email
            $sendPluginEmail = true;
            if ($options['receiptEmailType'] == 'stripe' && $sendReceipt == 1 && isset($_POST['stripeEmail']))
            {
                $sendPluginEmail = false;
            }

            do_action('fullstripe_before_checkout_payment_charge', $amount);
            //create/get customer object
            $stripeCustomer = $this->create_or_get_customer($token, $email, null, ($options['apiMode'] === 'live'));
            //try the charge
            $result = $this->stripe->charge_customer($stripeCustomer->id, $amount, $description);
            $result['wpfs_form'] = $formData["name"];
            do_action('fullstripe_after_checkout_payment_charge', $result);

            //save the payment
            $address1 = '';
            $address2 = '';
            $city = '';
            $state = '';
            $zip = '';

            if ($showBillingAddress == 1) {
                $address1 = $result["source"]["address_line1"] != null ? $result["source"]["address_line1"] : '';
                $address2 = $result["source"]["address_line2"] != null ? $result["source"]["address_line2"] : '';
                $city = $result["source"]["address_city"] != null ? $result["source"]["address_city"] : '';
                $state = $result["source"]["address_state"] != null ? $result["source"]["address_state"] : '';
                $zip = $result["source"]["address_zip"] != null ? $result["source"]["address_zip"] : '';
            }

            $address = array('line1' => $address1, 'line2' => $address2, 'city' => $city, 'state' => $state, 'zip' => $zip);
            $this->db->fullstripe_insert_payment($result, $address, $stripeCustomer->id, $email);

            $return = array('success' => true, 'msg' => 'Payment Successful!');
            if ($doRedirect == 1 && $redirectPostID != 0)
            {
                $return['redirect'] = true;
                $return['redirectURL'] = get_page_link($redirectPostID);
            }

            //send email receipt (it is better if done in a background thread...)
            if ($sendPluginEmail && $sendReceipt == 1 && isset($_POST['stripeEmail']))
            {
                $this->fullstripe_payment_send_email_receipt($email, $amount, $name, $address);
            }

        }
        catch (Exception $e)
        {
            $this->log->error('Error during Chechout Charge: ', $e);
            //show notification of error
            $return = array('success' => false, 'msg' => __('There was an error processing your payment: ', 'wp-full-stripe') . $e->getMessage());
        }

        header("Content-Type: application/json");
        echo json_encode(apply_filters('fullstripe_checkout_charge_return_message', $return));
        exit;
    }


    function fullstripe_check_coupon()
    {
        $code = $_POST['code'];

        try
        {
            $coupon = $this->stripe->get_coupon($code);

            if ($coupon->valid == false)
            {
                $return = array('msg' => "This coupon has expired", 'valid' => false);
            }
            else
            {
                $return = array('msg' => "The coupon has been applied successfully",
                    'coupon' => array('percent_off' => $coupon->percent_off, 'amount_off' => $coupon->amount_off),
                    'valid' => true);
            }
        }
        catch (Exception $e)
        {
            $this->log->error('Error during Coupon Check: ', $e);
            $return = array('msg' => "You have entered an invalid coupon code", 'valid' => false);
        }

        header("Content-Type: application/json");
        echo json_encode($return);
        exit;
    }

    function get_currency_symbol($options)
    {
        $cur = $options['currency'];

        $symbol = strtoupper($cur);

        if ($cur === 'usd') $symbol = '$';
        elseif ($cur === 'eur') $symbol = '€';
        elseif ($cur === 'jpy') $symbol = '¥';
        elseif ($cur === 'gbp') $symbol = '£';
        elseif ($cur === 'aud') $symbol = '$';
        elseif ($cur === 'chf') $symbol = 'Fr';
        elseif ($cur === 'cad') $symbol = '$';
        elseif ($cur === 'mxn') $symbol = '$';
        elseif ($cur === 'sek') $symbol = 'kr';
        elseif ($cur === 'nok') $symbol = 'kr';
        elseif ($cur === 'dkk') $symbol = 'kr';

        return $symbol;
    }

    function fullstripe_payment_send_email_receipt($email, $amount, $cardholderName, $billingAddress)
    {

        if (defined('WP_FULL_STRIPE_DEMO_MODE')) {
            return;
        }

        $options = get_option('fullstripe_options');
        $name = get_bloginfo('name');

        //saved in db using htmlentities()
        $subject =  $options['email_receipt_subject'];
        $message = html_entity_decode($options['email_receipt_html']);
        $symbol = $this->get_currency_symbol($options);

        $message = str_replace(
            array(
                "%AMOUNT%",
                "%NAME%",
                "%CUSTOMERNAME%",
                "%CUSTOMER_EMAIL%",
                "%ADDRESS1%",
                "%ADDRESS2%",
                "%CITY%",
                "%STATE%",
                "%ZIP%"),
            array(
                $symbol . sprintf('%0.2f', $amount / 100),
                $name,
                $cardholderName,
                $email,
                $billingAddress['line1'],
                $billingAddress['line2'],
                $billingAddress['city'],
                $billingAddress['state'],
                $billingAddress['zip']),
            $message);

        $this->fullstripe_send_email_receipt($email, $subject, $message);
    }

    function fullstripe_subscription_send_email_receipt($email, $setupFee, $planName, $planAmount, $cardholderName, $billingAddress)
    {
        if (defined('WP_FULL_STRIPE_DEMO_MODE'))
            return;

        $options = get_option('fullstripe_options');
        $name = get_bloginfo('name');

        //saved in db using htmlentities()
        $subject =  $options['subscription_email_receipt_subject'];
        $message = html_entity_decode($options['subscription_email_receipt_html']);
        $symbol = $this->get_currency_symbol($options);

        $message = str_replace(
            array(
                "%SETUP_FEE%",
                "%PLAN_NAME%",
                "%PLAN_AMOUNT%",
                "%AMOUNT%",
                "%NAME%",
                "%CUSTOMERNAME%",
                "%CUSTOMER_EMAIL%",
                "%ADDRESS1%",
                "%ADDRESS2%",
                "%CITY%",
                "%STATE%",
                "%ZIP%"),
            array(
                $symbol . sprintf('%0.2f', $setupFee / 100),
                $planName,
                $symbol . sprintf('%0.2f', $planAmount / 100),
                $symbol . sprintf('%0.2f', $planAmount / 100),
                $name,
                $cardholderName,
                $email,
                $billingAddress['line1'],
                $billingAddress['line2'],
                $billingAddress['city'],
                $billingAddress['state'],
                $billingAddress['zip']),
            $message);

        $this->fullstripe_send_email_receipt($email, $subject, $message);
    }

    function fullstripe_send_email_receipt($email, $subject, $message)
    {
        $options = get_option('fullstripe_options');

        $name = get_bloginfo('name');
        $admin_email = get_bloginfo('admin_email');
        $headers[] = "From: $name <$admin_email>";
        $headers[] = "Content-type: text/html";

        wp_mail($email,
            apply_filters('fullstripe_email_subject_filter', $subject),
            apply_filters('fullstripe_email_message_filter', $message),
            apply_filters('fullstripe_email_headers_filter', $headers));

        if ($options['admin_payment_receipt'] == 1)
        {
            wp_mail($admin_email,
                "COPY: " . apply_filters('fullstripe_email_subject_filter', $subject),
                apply_filters('fullstripe_email_message_filter', $message),
                apply_filters('fullstripe_email_headers_filter', $headers));
        }
    }

    private function create_or_get_customer($card, $email, $metadata, $livemode = true)
    {
        // First check for existing Stripe customer
        $stripeCustomerID = $this->db->get_customer_id_from_payments($email, $livemode);
        if (!$stripeCustomerID)
        {
            // also check the subscribers table for an existing Stripe customer
            $subscriber = $this->db->get_subscriber_by_email($email, $livemode);
            if (!$subscriber)
            {
                return $this->stripe->create_customer($card, $email, $metadata);
            }
            else
            {
                return $this->stripe->update_customer_card($subscriber->stripeCustomerID, $card);
            }
        }
        // update and return existing customer to charge
        return $this->stripe->update_customer_card($stripeCustomerID, $card);

    }

    // insert the inputs into the metadata
    private function add_custom_inputs($metadata, $customInputs, $customInputValues)
    {
        // if not set, it's the old version with just one value
        if ($customInputs == null)
        {
            $metadata['custom_input'] = $customInputValues;
        }
        else
        {
            $labels = explode('{{', $customInputs);
            foreach ($labels as $i => $label)
            {
                $metadata[$label] = $customInputValues[$i];
            }
        }

        return $metadata;
    }

}