<div class="wrap about-wrap">
    <h1><?php _e('Welcome to WP Full Stripe'); ?></h1>
    <div class="about-text">
        <p>Accept payments and subscriptions from your WordPress website. Created by
            <a href="http://mammothology.com">Mammothology</a></p>
    </div>
    <div class="changelog">
        <h3><?php _e('Help & Support'); ?></h3>
        <div class="feature-section images-stagger-right">
            <p>Check out our <a target="_blank" href="<?php echo admin_url("admin.php?page=fullstripe-help"); ?>">Help section</a> or visit the
                <a target="_blank" href="http://mammothology.com/forums/">support forums</a> if you have a question. You can also subscribe for premium support for FREE by
                <a target="_blank" href="http://eepurl.com/5zJG1">adding your email address to our mailing list.</a></p>
            <a class="button button-primary" target="_blank" href="http://eepurl.com/5zJG1">Subscribe for premium support</a>
            <h4><?php _e('Changelog'); ?></h4>
            <p>Below is a list of the most recent plugin updates. We are committed to continually improving WP Full Stripe.</p>
            <div class="changelog-updates">
                <strong>July 18th 2015</strong>
                <blockquote>
                    <ul>
                        <li>Fixed a bug with Stripe receipt emails on subscription forms.</li>
                    </ul>
                </blockquote>
                <strong>June 23rd 2015</strong>
                <blockquote>
                    <ul>
                        <li>Now you can use plugin email receipts for all form types (payment, checkout, and subscription) !!!</li>
                        <li>New email receipt tokens: customer email, subscription plan name, subscription plan amount, subscription setup fee.</li>
                        <li>Separate email template and subject field for payment forms and subscription forms.</li>
                        <li>Support for all countries supported by Stripe (20 countries currently).</li>
                        <li>Support for all currencies supported by Stripe (138 currencies in total, number varies by country).</li>
                    </ul>
                </blockquote>
                <strong>December 30th 2014</strong>
                <blockquote>
                    <ul>
                        <li>You can now use multiple checkout buttons on the same page!</li>
                        <li>Checkout button styling can now be disabled (useful for theme conflicts).</li>
                        <li>Some minor changes added for future extensions.</li>
                    </ul>
                </blockquote>
                <strong>December 5th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Removing form input placeholders as they conflict with some themes.</li>
                        <li>SSN is no longer a required field for transfer forms.</li>
                        <li>Support for KO Metrics added.</li>
                        <li>Bugfix: settings upgrade properly when installing a new version of the plugin.</li>
                    </ul>
                </blockquote>
                <strong>November 4th 2014 - We're now at version 3.0! Over 1 years worth of regular updates & new features</strong>
                <blockquote>
                    <ul>
                        <li>You can now add up to 5 custom input fields to payment & subscription forms!</li>
                        <li>Subscribers and payment records can now be deleted locally (they remain in your Stripe dashboard).</li>
                        <li>Lots of UI/UX improvements including appropriate table styling and useful redirects.</li>
                        <li>Added livemode status to subscribers.</li>
                        <li>Cardholder name correctly added to payment details.</li>
                    </ul>
                </blockquote>
                <strong>October 16th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Email address is now a required field on payment forms.</li>
                        <li>We now check for existing Stripe Customers before creating new ones.</li>
                        <li>Updated the Stripe PHP Bindings to the latest version.</li>
                        <li>Fixed deprecated warnings on payment and subscription table pages.</li>
                        <li>Fixed a bug with trying to redirect to post ID 0 following payment.</li>
                        <li>Hook and function updates to support upcoming Members add-on.</li>
                    </ul>
                </blockquote>
                <strong>October 7th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Updated bank transfers feature to include ability to transfer to debit cards as well as bank accounts.</li>
                        <li>Fixed a bug with checkout forms not displaying.</li>
                    </ul>
                </blockquote>
                <strong>September 6th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Bugfix: Subscriptions create Stripe customer objects correctly again.</li>
                    </ul>
                 </blockquote>
                <strong>August 29th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Stripe Customer objects are now created for charges, meaning better information about customers in your Stripe dashboard</li>
                        <li>Custom input has been moved from the description field to a charge metadata value</li>
                        <li>Fixed Stripe link on payments history tables</li>
                        <li>Stripe checkout forms now correctly save customer email</li>
                        <li>Locale strings for CAD accounts have been added</li>
                    </ul>
                </blockquote>
                <strong>July 23rd 2014</strong>
                <blockquote>
                    <ul>
                        <li>Hotfix to update transfers parameter due to Stripe API update</li>
                    </ul>
                </blockquote>
                <strong>July 20th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Added option to use Stripe emails for payment receipts</li>
                        <li>Fixed issue with redirect ID field on edit forms</li>
                        <li>Added customer name to metatdata sent to Stripe on successful payment</li>
                    </ul>
                </blockquote>
                <strong>June 23rd 2014</strong>
                <blockquote>
                    <ul>
                        <li>New tabbed design on payment and subscription pages</li>
                        <li>New sortable table for subscriber list</li>
                        <li>Choose to show remember me option on checkout forms</li>
                        <li>Ability to choose custom image for checkout form</li>
                    </ul>
                </blockquote>
                <strong>June 21st 2014</strong>
                <blockquote>
                    <ul>
                        <li>You can now specify setup fees for subscriptions!</li>
                    </ul>
                </blockquote>
                <strong>June 18th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Added ability to customize subscription form button text</li>
                        <li>Currency symbol now shows for plan summary price text</li>
                        <li>Some typos have been fixed & other UI improvements.</li>
                        <li>New About page.</li>
                    </ul>
                </blockquote>
                <strong>May 5th 2014</strong>
                <blockquote>
                    <ul>
                        <li>New system allows selection of different form styles</li>
                        <li>New 'compact' style for payment forms. More to come!</li>
                        <li>Tidy up of form code to allow easier creation of new form styles.</li>
                    </ul>
                </blockquote>
                <strong>Apr 20th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Checkout form now uses currency set in the plugin options</li>
                        <li>Updated currency symbols throughout admin sections</li>
                        <li>Tested to work with latest release, WordPress 3.9</li>
                    </ul>
                </blockquote>
                <strong>Apr 19th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Added address line 2 and state fields to billing address portion of forms</li>
                        <li>Used metadata parameter with Stripe API to better store customer email and address fields</li>
                        <li>Address fields on forms now take locale into account (Zip/Postcode, State/Region etc.)</li>
                        <li>Added new fields to customize email receipts</li>
                    </ul>
                </blockquote>
                <strong>Apr 13th 2014</strong>
                <blockquote>
                    <ul>
                        <li>New form type! Stripe Checkout forms are now supported. These are pre-styled, responsive forms.</li>
                        <li>You can now select to receive a copy of email receipts that are sent after successful payments.</li>
                        <li>More email validation added.</li>
                    </ul>
                </blockquote>
                <strong>Mar 21st 2014</strong>
                <blockquote>
                    <ul>
                        <li>You can now customize payment email receipts in the settings page</li>
                        <li>Stage 1 of major refactor of code, making it easier & faster to provide future updates.</li>
                        <li>Loads more action and filter hooks added to make plugin more extendable.</li>
                    </ul>
                </blockquote>
                <strong>Feb 17th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Subscription forms now show total price at the bottom</li>
                        <li>Coupon codes can now be applied, showing total price to the customer</li>
                    </ul>
                </blockquote>
                <strong>Feb 15th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Added option to send email receipts to your customers after successful payment</li>
                    </ul>
                </blockquote>
                <strong>Jan 26th 2014</strong>
                <blockquote>
                    <ul>
                        <li>Fixed an issue with copy/pasting Stripe API keys sometimes including extra spaces</li>
                    </ul>
                </blockquote>
                <strong>Jan 15th 2014</strong>
                <blockquote>
                    <ul>
                        <li>You can now edit your payment and subscription forms!</li>
                        <li>Improved table added for viewing payments which allows sorting by amount, date and more.</li>
                        <li>General code tidy up. More coming soon.</li>
                    </ul>
                </blockquote>
            </div>
        </div>
    </div>
</div>