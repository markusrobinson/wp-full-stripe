<form class="form-horizontal" action="" method="POST" id="payment-form">
    <fieldset>
        <div id="legend">
                <span class="fullstripe-form-title">
                    <?php echo $formData->formTitle; ?>
                </span>
            <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." id="showLoading"/>
        </div>
        <input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
        <input type="hidden" name="amount" value="<?php echo $formData->amount; ?>"/>
        <input type="hidden" name="formName" value="<?php echo $formData->name; ?>"/>
        <input type="hidden" name="isCustom" value="<?php echo $formData->customAmount; ?>"/>
        <input type="hidden" name="formDoRedirect" value="<?php echo $formData->redirectOnSuccess; ?>"/>
        <input type="hidden" name="formRedirectPostID" value="<?php echo $formData->redirectPostID; ?>"/>
        <input type="hidden" name="showAddress" value="<?php echo $formData->showAddress; ?>"/>
        <input type="hidden" name="sendEmailReceipt" value="<?php echo $formData->sendEmailReceipt; ?>"/>
        <?php if ($formData->showCustomInput == 1 && $formData->customInputs): ?>
            <input type="hidden" name="customInputs" value="<?php echo $formData->customInputs; ?>"/>
        <?php endif; ?>
        <p class="payment-errors"></p>
        <!-- Name -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Card Holder's Name</label>
            <div class="controls">
                <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_name" id="fullstripe_name"  data-stripe="name">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Email Address</label>
            <div class="controls">
                <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_email" id="fullstripe_email">
            </div>
        </div>
        <?php if ( $formData->showCustomInput == 1 ): ?>
            <?php
                $customInputs = array();
                 if ($formData->customInputs != NULL)
                {
                    $customInputs = explode('{{', $formData->customInputs);
                }
            ?>

            <?php if ($formData->customInputs == NULL): ?>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php echo $formData->customInputTitle; ?></label>
                <div class="controls">
                    <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input" id="fullstripe_custom_input">
                </div>
            </div>
            <?php endif; ?>

            <?php foreach ($customInputs as $i => $label): ?>
                <div class="control-group">
                    <label class="control-label fullstripe-form-label"><?php echo $label; ?></label>
                    <div class="controls">
                        <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input[]" id="fullstripe_custom_input_<?php echo $i+1; ?>">
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
        <?php if ( $formData->customAmount == 1 ): ?>
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Payment Amount</label>
            <div class="controls">
                <input type="text" style="width: 60px;" name="fullstripe_custom_amount" id="fullstripe_custom_amount" class="fullstripe-form-input"><br/>
            </div>
        </div>
        <?php endif; ?>
        <?php if ( $formData->showAddress == 1 ): ?>
            <div class="control-group">
                <label class="control-label fullstripe-form-label">Billing Address Street</label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_line1" id="fullstripe_address_line1" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label">Billing Address Line 2</label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_line2" id="fullstripe_address_line2" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label">City</label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_city" id="fullstripe_address_city" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php echo $localeState; ?></label>
                <div class="controls">
                    <input type="text" style="width: 60px;"  name="fullstripe_address_state" id="fullstripe_address_state" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php echo $localeZip; ?></label>
                <div class="controls">
                    <input type="text" style="width: 60px;"  name="fullstripe_address_zip" id="fullstripe_address_zip" class="fullstripe-form-input"><br/>
                </div>
            </div>
        <?php endif; ?>
        <!-- Card Number -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Card Number</label>
            <div class="controls">
                <input type="text" autocomplete="off" class="input-xlarge fullstripe-form-input" size="20" data-stripe="number">
            </div>
        </div>
        <!-- Expiry-->
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Card Expiry Date</label>
            <div class="controls">
                <input type="text" style="width: 60px;" size="2" data-stripe="exp-month" class="fullstripe-form-input"/>
                <span> / </span>
                <input type="text" style="width: 60px;" size="4" data-stripe="exp-year" class="fullstripe-form-input"/>
            </div>
        </div>
        <!-- CVV -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label">Card CVV</label>
            <div class="controls">
                <input type="password" autocomplete="off" class="input-mini fullstripe-form-input" size="4" data-stripe="cvc"/>
            </div>
        </div>
        <!-- Submit -->
        <?php if ( $formData->customAmount == 0 ): ?>
        <div class="control-group">
            <div class="controls">
                <button type="submit"><?php echo $formData->buttonTitle; ?> <?php if ($formData->showButtonAmount == 1) {echo $currencySymbol . sprintf('%0.2f', $formData->amount / 100.0);}  ?></button>
            </div>
        </div>
        <?php else: ?>
        <div class="control-group">
            <div class="controls">
                <button type="submit"><?php echo $formData->buttonTitle; ?></button>
            </div>
        </div>
        <?php endif; ?>
    </fieldset>
</form>