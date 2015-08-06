<form class="fullstripe_checkout_form" action="" method="POST">
    <input type="hidden" name="action" value="fullstripe_checkout_form_charge"/>
    <input type="hidden" name="companyName" value="<?php echo $formData["companyName"]; ?>"/>
    <input type="hidden" name="productDesc" value="<?php echo $formData["productDesc"]; ?>"/>
    <input type="hidden" name="amount" value="<?php echo $formData["amount"]; ?>"/>
    <input type="hidden" name="buttonTitle" value="<?php echo $formData["buttonTitle"]; ?>"/>
    <input type="hidden" name="sendEmailReceipt" value="<?php echo $formData["sendEmailReceipt"]; ?>"/>
    <input type="hidden" name="showBillingAddress" value="<?php echo $formData["showBillingAddress"]; ?>"/>
    <input type="hidden" name="showRememberMe" value="<?php echo $formData["showRememberMe"]; ?>"/>
    <input type="hidden" name="image" value="<?php echo $formData["image"]; ?>"/>
    <input type="hidden" name="currency" value="<?php echo $formData["currency"]; ?>"/>
    <input type="hidden" name="name" value="<?php echo $formData["name"]; ?>"/>
    <input type="hidden" name="redirectOnSuccess" value="<?php echo $formData["redirectOnSuccess"]; ?>"/>
    <input type="hidden" name="redirectPostID" value="<?php echo $formData["redirectPostID"]; ?>"/>
    <p class="payment-errors-<?php echo $formData['name']; ?>"></p>
    <button class="fullstripe_checkout_button <?php echo ($formData["disableStyling"] == '0') ? 'stripe-button-el' : '' ?> " type="submit">
        <span class="fullstripe_checkout_button_text" <?php echo ($formData["disableStyling"] == '0') ? 'style="display: block; min-height: 30px;"' : '' ?> ><?php echo $formData["openButtonTitle"]; ?></span>
    </button>
    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading" id="showLoading-<?php echo $formData['name']; ?>" style="display: none;"/>
</form>

