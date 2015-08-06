<?php
$recipients = MM_WPFS::getInstance()->get_recipients();

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'recipients';
?>
<div class="wrap">
    <h2> <?php echo __('Full Stripe Transfers', 'wp-full-stripe'); ?> </h2>
    <div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>
    <p class="alert alert-info">Please note that Bank Transfers are only supported for US based Stripe accounts. The plugin will be updated to support other countries transfers once Stripe supports them.</p>
    <h2 class="nav-tab-wrapper">
        <a href="?page=fullstripe-transfers&tab=recipients" class="nav-tab <?php echo $active_tab == 'recipients' ? 'nav-tab-active' : ''; ?>">Recipients</a>
        <a href="?page=fullstripe-transfers&tab=transfers" class="nav-tab <?php echo $active_tab == 'transfers' ? 'nav-tab-active' : ''; ?>">Transfers</a>
        <a href="?page=fullstripe-transfers&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>">Create New Recipient</a>
    </h2>

    <div class="tab-content">
    <?php if ($active_tab == 'recipients'): ?>
        <div class="" id="recipients">
            <h2>Your Recipients</h2>
            <?php if (count($recipients) === 0): ?>
                <p class="alert alert-info">You have created no recipients yet. click "Create New Recipient" to get started.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed">
                    <thead>
                    <tr>
                        <th>Stripe ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                    </tr>
                    </thead>
                    <tbody id="recipientsTable">
                    <?php foreach ($recipients['data'] as $rp): ?>
                        <?php
                            $stripeLink = 'https://dashboard.stripe.com/';
                            if (!$rp->livemode) $stripeLink .= 'test/';
                            $stripeLink .= 'recipients/' . $rp->id;
                        ?>
                        <tr>
                            <td><a href="<?php echo $stripeLink; ?>"><?php echo $rp->id; ?></a></td>
                            <td><?php echo $rp->name; ?></td>
                            <td><?php echo $rp->type  ?></td>
                            <td><?php if (isset($rp->email)) echo $rp->email; else echo "Not Supplied"; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php elseif ($active_tab == 'transfers'): ?>
        <div class="" id="transfers">
            <form class="form-horizontal" action="" method="POST" id="create-transfer-form">
                <p class="transfer-tips"></p>
                <input type="hidden" name="action" value="wp_full_stripe_create_transfer"/>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label class="control-label">Transfer Amount: </label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="transfer_amount" id="transfer_amount">
                            <p class="description">The amount to transfer, in cents. i.e. for $10.00 enter 1000</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label class="control-label">Statement Descriptor: </label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="transfer_desc" id="transfer_desc">
                            <p class="description">A 15 character descriptor, it will appear on the recipients bank statement</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label class="control-label">Transfer Recipient: </label>
                        </th>
                        <td>
                            <select id="transfer_recipient" name="transfer_recipient">
                                <option value="self">Your own bank account</option>
                                <?php if (count($recipients) != 0): ?>
                                    <?php foreach ($recipients['data'] as $rp): ?>
                                        <option value="<?php echo $rp->id; ?>"><?php echo $rp->name . ' (' . $rp->id . ')'; ?></option>
                                    <?php endforeach; ?>
                                <?php endif;?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button class="button button-primary" type="submit">Initiate Transfer</button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
                <p class="description"><strong>You must have sufficient funds in your Stripe account</strong> otherwise the transfer will fail.  Transfers can take up to 5 business days. Check your Stripe account for confirmation.</p>
            </form>
        </div>
    <?php elseif ($active_tab == 'create'): ?>
        <div class="" id="create">
            <div class="choose-form-buttons">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label class="control-label">Pay To: </label>
                        </th>
                        <td>
                            <label class="radio inline">
                                <input type="radio" name="set_recipient_type" id="set_recipient_bank_account" value="bank" checked="checked"> Bank Account
                            </label> <label class="radio inline">
                                <input type="radio" name="set_recipient_type" id="set_recipient_debit_card" value="card"> Debit Card
                            </label>
                        </td>
                    </tr>
                </table>
                <hr/>
            </div>
            <div id="createRecipientBank">
                <form class="form-horizontal" action="" method="POST" id="create-recipient-form">
                    <p class="tips"></p>
                    <input type="hidden" name="action" value="wp_full_stripe_create_recipient"/>
                    <input type="hidden" data-stripe="country" value="US">
                    <input type="hidden" id="payToType" name="payToType" value="bank">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Full Legal Name: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_name" id="recipient_name">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Recipient Type: </label>
                            </th>
                            <td>
                                <label class="radio inline">
                                    <input type="radio" name="recipient_type" id="typeInd" value="individual" checked> Individual
                                </label> <label class="radio inline">
                                    <input type="radio" name="recipient_type" id="typeCorp" value="corporation"> Corporation
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Tax ID: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_tax_id" id="recipient_tax_id">
                                <p class="description">For individual use SSN, for corporation use EIN.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Bank Routing Number: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" data-stripe="routingNumber">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Bank Account Number: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" data-stripe="accountNumber">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Recipient Email: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_email" id="recipient_email">
                                <p class="description">Useful for searching & viewing on your Stripe dashboard. (optional)</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button class="button button-primary" type="submit">Create Recipient</button>
                        <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                    </p>
                </form>
            </div>
            <div id="createRecipientCard" style="display: none;">
                <form class="form-horizontal" action="" method="POST" id="create-recipient-form-card">
                    <p class="tips"></p>
                    <input type="hidden" name="action" value="wp_full_stripe_create_recipient_card"/>
                    <input type="hidden" data-stripe="country" value="US">
                    <input type="hidden" id="payToType" name="payToType" value="card">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Full Legal Name: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_name_card" id="recipient_name_card">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Recipient Type: </label>
                            </th>
                            <td>
                                <label class="radio inline">
                                    <input type="radio" name="recipient_type_card" id="typeIndC" value="individual" checked> Individual
                                </label> <label class="radio inline">
                                    <input type="radio" name="recipient_type_card" id="typeCorpC" value="corporation"> Corporation
                                </label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Tax ID: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_tax_id_card" id="recipient_tax_id_card">
                                <p class="description">For individual use SSN, for corporation use EIN.</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Card Number: </label>
                            </th>
                            <td>
                                <input type="text" autocomplete="off" data-stripe="number">
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Card CVV: </label>
                            </th>
                            <td>
                                <input type="password" autocomplete="off" size="8" data-stripe="cvc"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Card Expiry Month: </label>
                            </th>
                            <td>
                                <input type="text" size="4" data-stripe="exp-month"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Card Expiry Year: </label>
                            </th>
                            <td>
                                <input type="text" size="8" data-stripe="exp-year"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label class="control-label">Recipient Email: </label>
                            </th>
                            <td>
                                <input type="text" class="regular-text" name="recipient_email_card" id="recipient_email_card">
                                <p class="description">Useful for searching & viewing on your Stripe dashboard. (optional)</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button class="button button-primary" type="submit">Create Recipient</button>
                        <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                    </p>
                </form>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>