# Release Notes for IO

## v2.9.0 (2018-05-24) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.1...2.9.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The method **getURLById**, which returns the URL of a category, has been added to the CategoryService.
- The route **io/order/additional_information** has been added in order to add and edit additional order information.

### Fixed

- The canonical URLs stored on the category level were not properly utilised. This has been fixed.
- Due to an error, the function ItemService.getVariationImage() did not return image URLs. This has been fixed.

## v2.8.1 (2018-05-16) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.0...2.8.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, addresses could not be created or edited, if no federal states were available for the selected country of delivery. This has been fixed.

## v2.8.0 (2018-05-08) <a href="https://github.com/plentymarkets/plugin-io/compare/2.7.0...2.8.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- A new service (TagService) has been added in order to retrieve the name of a tag via its ID.
- The facet type **price** has been added.
- The option to include trailing slashes is now considered when generating URLs.

### Fixed

- Due to an error, data from the Ceres GlobalContext could not be loaded if accessed via a route of another plugin. This has been fixed.
- When using Ceres and IO as a client that is not the main client, category details of the main client were loaded under certain circumstances. This has been fixed.

## v2.7.0 (2018-04-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.6.0...2.7.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- For items without image, the preconfigured placeholder image is now displayed in the online store.
- Order properties of the type **file** can now be processed.

### Fixed

- Due to an error the shopping cart did not display graduated prices. This has been fixed.
- Returns did not adopt the lock state from the original order. This has been fixed.
- Due to an error the data was not validated by the server when address data was saved or edited. This has been fixed.
- Due to an error, the order confirmation always displayed the order status, the shipping service provider and the payment method in the system language. This has been fixed.
- Due to an error customer class-dependent graduated rebates on gross item value were not considered in the order creation. This has been fixed.
- Due to an error, a failed login did not issue a notification. This has been fixed.

## v2.6.0 (2018-04-03) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.2...2.6.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- IO is now able to react to the generation of the sitemap and can provide its own patterns for the creation of URLs.

### Fixed

- Due to an error graduated prices were not displayed in the shopping cart. This has been fixed.
- The default country of delivery is now selected as the active country of delivery after logging out.
- After a guest purchase the email address is now deleted from the session, so that it has to be entered again for a new order process.
- Under certain circumstances the button for changing payment methods was not displayed on the order confirmation page. This has been fixed.
- Due to an error a purchase via Paypal redirected to a 404 page instead of the order confirmation page. This has been fixed.

## v2.5.2 (2018-03-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.1...2.5.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error items could not be correctly sorted by name. This has been fixed.

## v2.5.1 (2018-03-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.0...2.5.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the shopping cart could not be refreshed if changes had been made to it. This has been fixed.

## v2.5.0 (2018-03-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.4.0...2.5.0" target="_blank"><b>Overview of all changes</b></a>

- Context classes, which provide data to related Twig templates, have been added.
- New service classes have been added in order to facilitate the use of ElasticSearch.

## v2.4.0 (2018-03-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.2...2.4.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- A new helper class has been added in order to facilitate the access to plugin configurations.

### Changed

- In order to improve the performance, global services in Twig are only instantiated when they are required.

### Fixed

- Due to an error filters yielded empty facets and the configuration **Minimum number of hits** was not considered. This has been fixed.

## v2.3.2 (2018-02-28) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.1...2.3.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The subject of the "Forgot password" email is now received via the REST call parameter "subject" and is sent as a translated version if the parameter is a valid translation key.

## v2.3.1 (2018-02-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.0...2.3.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Coupon discounts are now displayed on the order confirmation page and in the order details in the My account section.
- After the creation of a return, the return confirmation page will now be displayed again. (The route in IO config has to be active.)
- The page for the creation of returns now only displays items that can be returned. (No shipping costs, coupon positions, etc.)
- Due to an error particular attributes were not displayed in the variation selection. This has been fixed.
- Due to an error the display of gross/net prices for shipping costs was not refreshed correctly. This has been fixed.
- Errors in the shipping cost calculation didn't yield error messages. This has been fixed.
- The last seen list no longer displays random items if no item has been previously viewed in the store.

## v2.3.0 (2018-02-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.2...2.3.0" target="_blank"><b>Overview of all changes</b></a>

### Changed

- The filter `itemName` is now able to display the variation name or a combination of item name and variation name in accordance with the Ceres configuration.

### Fixed

- Due to an error item URLs weren't generated correctly. This has been fixed.

## v2.2.2 (2018-02-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.1...2.2.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, the item view occasionally displayed a 404 page if the URL was entered without Variation ID. This has been fixed by taking the configuration value **Show variations by type** into account in the item view as well.

## v2.2.1 (2018-02-07) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.0...2.2.1" target="_blank"><b>Overview of all changes</b></a>

### Changed

- The sorting order of search results has been improved.
- The list of active languages will no longer be loaded from the `WebstoreConfigurationRepositoryContract`. This list will now be loaded from the configuration of the respective template plugin instead.

### Fixed

- Due to an error the prices of cross selling items weren't calculated correctly. This has been fixed.

## v2.2.0 (2018-02-05) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.5...2.2.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- `IO.Resources.Import` can now receive parameters. For example, when generating and integrating a script own values saved in the plugin configuration can now be transferred and taken into account when rendering the script.
- The content of **.properties** files can now be loaded.

### Fixed

- Due to an error the error page was transmitted with a 200 status code. This has been fixed.
- Due to an error the relevance of an item wasn't correctly taken into account when searching for items and sorting items by relevance. This has been fixed.

## v2.1.5 (2018-02-02) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.4...2.1.5" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the pagination wasn't displayed correctly when using the setting Show varations by type: Dynamically. This has been fixed.
- Due to an error item data was not displayed in a consistent way. This has been fixed.
- Due to an error surcharges for order properties weren't calculated correctly. This has been fixed.

## v2.1.4 (2018-01-29) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.3...2.1.4" target="_blank"><b>Overview of all changes</b></a>

- Due to an error URLs without the **Variation ID** parameter weren't displayed correctly. This has been fixed.

## v2.1.3 (2018-01-23) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.2...2.1.3" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the 404 page wasn't displayed correctly. This has been fixed.
- Due to an error unneccessary item requests were executed. This has been fixed.

## v2.1.2 (2018-01-22) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.1...2.1.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- A security prompt has been added which prevents customers from returning items multiple times.

### Fixed

- Due to an error too many items have been displayed in the wish list. This has been fixed.

## v2.1.1 (2018-01-09) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.0...2.1.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- When ordering as a guest, the address will now be removed from the session after placing the order.
- Due to an error, wrong item URLs have been generated when only one language has been activated for the online store. This has been fixed.

## v2.1.0 (2018-01-04) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.3...2.1.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- URLs for items and categories can now be generated in the respective language.

### Fixed

- Due to an error, readable URLs for new items could not be generated. This has been fixed.

## v2.0.3 (2017-12-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.2...2.0.3" target="_blank"><b>Overview of all changes</b></a>

### Added

- Translatable error message for registration in case the email address already exists.

### Fixed

- Delivery address can now be set back to "Delivery address equals invoice address".
- Fixed error for item visibility in spite of link to customer class.

## v2.0.2 (2017-12-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.1...2.0.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- The additional flag `isSelectable` is sent when loading payment methods.

### Fixed

- Order referrers will now be taken into consideration when loading items or calculating prices.
- Various errors concerning the handling of coupon codes have been fixed.

## v2.0.1 (2017-12-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.0...2.0.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the default homepage wasn't displayed correctly. This has been fixed.

## v2.0.0 (2017-11-30) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.2...2.0.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- The Twig functions `get_additional_styles()` und `get_additional_scripts()` allow external plugins get styles and scripts and output them at the respective location.
- A new REST route `io/checkout/paymentId` for setting the payment method has been added.
- A new REST route `io/checkout/shippingId` for seeting the shipping method has been added.
- An **Account** will be created in plentymarkets when a B2B customer signs up in the online store.
- A middleware has been added for reacting to changes of the currency in the online store.
- Prices will now be converted when the currency is changed.
- The logic for calculating order sums has been added (previously this logic was contained in a Twig macro in Ceres).
- A customer that ordered as a guest may now change the payment method on the order confirmation page if enabled.
- A customer that ordered as a guest can now pay an order subsequently, e.g. when the payment method changes.
- An error message has been added that will be displayed when an error occurs during adding items to the shopping cart.

### Fixed

- Due to an error the **My Account** area could not be loaded when loading the orders of a customer.
- Due to an error the route `/wishlist` for the wish list hasn't been active even though it has been activated in the configuration. This has been fixed.
- Due to an error prices with different VAT rated haven't been displayed correctly. This has been fixed.
- Multiple events are now triggered after loggint out of the online store for, e.g. updating the shopping cart.
- An order for which returns are not allowed cannot be accessed directly using the `/returns` route anymore.

## v1.7.2 (2017-11-22) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.1...1.7.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, shipping costs weren't displayed correctly on the order detail page and on the order confirmation page. This has been fixed.
- Due to an error, additional item data wasn't displayed in the shopping cart when having more than 10 items in the shopping cart. This has been fixed.

## v1.7.1 (2017-11-17) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.0...1.7.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The position of a sales price is now taken into account in the front end to ensure the correct display of prices in the online store.
- The minimum order quantity saved for a customer class is now also taken into account.
- Variations that are not linked to the current customer class of the customer, will not be displayed in the variation selection of the single item view.

## v1.7.0 (2017-11-08) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.2...1.7.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- Customer classes are now taken into consideration when displaying item data in the online store.
- Plugins can now add new values to extend the item sorting in the online store. For further information about this, refer to <a href="https://developers.plentymarkets.com/dev-doc/cookbook#item-sorting" target="_blank">plentyDevelopers</a>.

### Fixed

- The variation setting for unite prices **Show unit price** is now taken into account. When deactivating this setting, the unit price is not displayed in the online store.

## v1.6.2 (2017-10-25) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.1...1.6.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- Addresses can be saved as a "DHL Packstation" or post office.
- In the Customer Service, the function `hasReturns` was added to show if the customer has any returns.

## v1.6.1 (2017-10-19) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.0...1.6.1" target="_blank"><b>Overview of all changes</b></a>


### Changed

- The setting **Allow returns** is now carried out in the configuration of the plugin **Ceres**.

### Fixed

- Due to an error, the order overview could not be loaded when an order with an old shipping profile was saved. This has been fixed.

## v1.6.0 (2017-10-16) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.1...1.6.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- Graduated prices have been integrated.

#### Fixed

- Due to an error the wrong payment method has been saved for an order when paying the order with a payment method using the express checkout. This has been fixed.
- When updating an address, the `FrontendCustomerAddressChanged`event is triggered.
- When creating a return, a new date will be created instead of using the order date for the return.

## v1.5.1 (2017-10-05) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.0...1.5.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The contact card route is now always correctly available once activated in the IO configuration.

## v1.5.0 (2017-09-28) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.7...1.5.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- The logic for returning items of an order has been added.
- A method has been added in the `RegisterController`. This allows the use of the Ceres checkout with the old **order process** and the **individual shopping cart** of Callisto.

### Fixed

- Due to an error, the order overview could not be loaded when an order with an old payment method was saved. This has been fixed.
- Due to a randomly occurring error, the checkout could not be opened when ordering as a guest. This has been fixed.

## v1.4.7 (2017-09-20) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.6...1.4.7" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the unit price wasn’t displayed correctly. This has been fixed.
- Due to an error the payment method wasn’t always selected correctly in the checkout. This has been fixed.
- Due to an error the addresses weren’t always selected correctly in the checkout. This has been fixed.

## v1.4.6 (2017-09-13) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.5...1.4.6" target="_blank"><b>Overview of all changes</b></a>

### Added

- The search by variation number has been implemented.
- Due to an error the online store wasn’t displayed correctly when the data base table for the wish list was missing. This has been fixed.

## v1.4.5 (2017-09-06)

### Fixed

- Due to an error, the number of items wasn’t displayed correctly in the shopping cart preview. This has been fixed.

## v1.4.4 (2017-08-30)

### Added

- A method has been implemented for sending an email as soon as a customer wants to reset the password.
- A new password for the customer can be saved.

### Fixed

- The variation selection dropdown in the single item view now also displays the attributes of the main variation.

### TODO

- The `password-reset` route must be activated in IO in order to use the **Forgot your password?** feature in Ceres.

## v1.4.3 (2017-08-25)

### Removed

- The unused route `/guest` and `GuestController` have been removed.

## v1.4.2 (2017-08-23)

### Fixed

- When accessing the order overview page with an expired session, a 404 page is shown instead of a twig error.

## v1.4.1 (2017-08-11)

### Added

- The order confirmation link in the order overview of the back end can now also be interpreted.
- `ContactMailService` now accepts a parameter to submit a copy of the contact form to the sender.

### Fixed

- Due to an error, prices of cross-selling items were not displayed. This has been fixed.
- In case of an invalid order confirmation link, a 404 page will be displayed instead of a Twig error.

## v1.4.0 (2017-08-09)

### Added

- The logic and the route `/wish-list` has been added to display a wish list in the online store. **Note:** In order for the migration of the data base table to run correctly, the standard client must be activated and the plugin deployed. After deployment the standard client can be deactivated.
- The logic and the route `/contact` has been added to display a contact page in the online store.
- The `ContactMailService` has been added to process the sending of customer requests via the contact page of the online store.
- A method has been added in the `BasketService` to get the quantity of items in the shopping cart.
- The `NotificationService` has been extended to correctly display error messages in the front-end.
- The link in the order confirmation email now forwards to the order confirmation page of Ceres.

### Fixed

- The language selection in the header of the online store displays languages again.

### Removed

- The logic for item stock has been removed from the `ItemController`. This information is now contained in the `result fields` of ElasticSearch.

## v1.3.2 (2017-07-26)

### Added

- The phone number can now be saved in the `CustomerService`.

### Fixed

- The performance of the order confirmation page has been improved.
- The item images on the order confirmation page are now displayed correctly.

## v1.3.1 (2017-07-21)

### Added

- Order properties of the **Text** type are now processed in the `BasketService` and the `OrderItemBuilder`.
- The route `io/localization/language` has been added. This route can be used to set the language of the online store.

## v1.3.0 (2017-07-13)

### Added

- IO now provides data concerning cross-selling and tags for item lists.
- Templates can now be cached.
- The academic title can now be saved in the `CustomerService`.
- A new event `LocalizationChanged` has been added.
- Multiple conditions for changing the payment method in the **My account** area have been added. The **Allow customer to change the payment method** setting must be activated in the Ceres configuration. Additionally, the order must not be paid yet. The order status must be less than 3.4, or when the order was created the same day the order status must be 5 or less than 3.4.

### Changed

- The online store search will now use the **AND** operator. This replaces the **OR** search that was previously used.
- Editing additional address fields has been optimised in the `CustomerService`.

### Fixed

- Only those item images activated for a client will be displayed in the respective online store.

## v1.2.10 (2017-07-05)

### Added

- The `getCheckoutPaymentDataList` method was added in the `CheckoutService`, to return the `sourceUrl` of a payment plugin.
- It is now possible to set up complex item sorting for the category view and the search by using the recommended sorting options.
- The result of a requested item also contains the formatted item price.

### Changed

- Address fields that are deactivated in the configuration of Ceres but for which validation is activated, will not be validated in the online store anymore.

## v1.2.9 (2017-06-30)

### Fixed

- The translation in the list of payment methods wasn't displayed, when clicking on **Change payment method** in the checkout. This has been fixed.
- In the `TemplateService` the method `isCurrentTemplate` has been added to dynamically request the current template.

## v1.2.8 (2017-06-29)

### Added

- A payment method can be changed subsequently for an order in the **My account** area if this feature is enabled in the payment method.

### Changed

- Variations that are out of stock cannot be added to the shopping cart anymore.
- When selecting a variation that is out of stock the customer will be forwarded to the next variation with stock.

### Fixed

- Due to an error, a deleted address was not removed from the address list. This has been fixed.
- Due to an error the address could not be edited when ordering as a guest. This has been fixed.

## v.1.2.7 (2017-06-21)

### Fixed

- During registration, when the customer enters an invoice address, the entered address is not automatically saved as the delivery address.

## v1.2.6 (2017-06-14)

### Fixed

- Due to an error, the validator for invoice and delivery addresses for the country of delivery **United kingdom** did not work properly. This has been fixed.

## v1.2.5 (2017-06-08)

### Added

- Countries of delivery and online store settings are now loaded from the cache to improve the overall performance.

### Fixed

- Due to an error the default country of delivery has not been set. This has been fixed.

## v1.2.4 (2017-06-02)

### Added

- A Twig filter for sorting an object by a given key has been added.
- Validation of the address form for the delivery country **United Kingdom**

## v1.2.3 (2017-05-19)

### Added

- The date of birth and the VAT number entered during the address input will now be saved with the address.
- Added a twig filter for variation images.
- A corresponding template plugin can now be specified in the configuration of IO.
- Address validation based on the specified template plugin.

### Fixed

- Items will only be returned when item texts have been saved in the selected store language.

## v1.2.2 (2017-05-11)

## Fixed

- Suggested search results created by the auto-complete feature are now taking into account the grouping of variations.

## v1.2.1 (2017-05-08)

## Fixed

- Minor bug fixes and improvements.

## v1.2.0 (2017-04-28)

### Fixed

- Registrations with an email address for which an account already exists are no longer possible.
- Breadcrumbs are now also working correctly in the single item view.

## v1.1.1 (2017-04-24)

### Added

- Logic for the item list of last seen items

### Fixed

- Grouping of variations in the category item list and on the search result page
- Sorting by item name in the category item list and on the search result page

## v1.1.0 (2017-04-12)

### Added

- TemplateService: `isCategoryView` method added to check if current page is category page.
- Support for new category logic in Ceres.

## v.1.0.4

### Fixed

- An error that occurred when opening the order confirmation page has been fixed

## v1.0.3 (2017-03-24)

### Added

- Filter functionality via facets
- Rendered Twig templates can now be retrieved via REST
- New Twig functions: `trimNewLines` and `formatDateTime`
- New method in the **CategoryService**: `getChildren()`
to get all subcategories

### Changed

- Routing was updated and extended: Old store URLs can now be processed and displayed in **Ceres**. The URL structure was optimised from `/{itemName}/{itemId}/{variationId}` to `/{category}/{subcategory}/.../{itemName}-{itemId}-{variationId}`

## v1.0.2 (2017-03-06)

### Fixed

- Fixed an error when accessing the category view and single item view.
- Fixed an error with items showing up in a category which they weren‘t linked with.
- Fixed an error with other plugin routes being overwritten by the 404 route of IO.

## v1.0.1 (2017-02-22)

### Fixed

- Fixed an error that occurred when activating additional store languages. When [adding](https://developers.plentymarkets.com/dev-doc/template-plugins#design-lang) new language files to the `resources/lang` folder and compiling the files with [Gulp](https://developers.plentymarkets.com/dev-doc/template-plugins#gulp-ceres), the template will be displayed in the selected language.

## v1.0.0 (2017-02-20)

### Features
**IO** offers a variety of logic functions for a plentymarkets online store and serves as an interface between plentymarkets and the following online store pages:
- Homepage
- Category view
- Item view
- Shopping cart
- Checkout
- Order confirmation
- Login and registration
- Guest order page
- **My account** page
- static pages (e.g. terms and conditions, legal disclosure etc.)

Furthermore, **IO** allows you to load additional content with the help of template containers.
