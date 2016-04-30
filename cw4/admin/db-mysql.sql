/*
Cartweaver mySQL Database
==========================================================
Application: Cartweaver 4 PHP
Copyright 2002 - 2012, All Rights Reserved
Developer: Application Dynamics, Inc. | Cartweaver.com
Licensing: http://www.cartweaver.com/eula
Support: http://www.cartweaver.com/support
==========================================================
File: db-sql.sql
File Date: 2012-07-03
Description: Creates Cartweaver database with default data
Contents may be replaced with a sql dump of any Cartweaver db

Note: Edit and use at your own risk! All operations are permanent!
==========================================================
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `cw_admin_users`
-- ----------------------------
DROP TABLE IF EXISTS `cw_admin_users`;
CREATE TABLE `cw_admin_users` (
  `admin_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_alias` varchar(255) DEFAULT NULL,
  `admin_username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `admin_password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `admin_access_level` varchar(255) DEFAULT NULL,
  `admin_login_date` datetime DEFAULT NULL,
  `admin_last_login` datetime DEFAULT NULL,
  `admin_user_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`admin_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_admin_users
-- ----------------------------
INSERT INTO `cw_admin_users` VALUES ('1', 'Developer', 'developer', 'admin', 'developer', '2011-12-16 17:23:05', '2011-12-16 16:43:12', 'developer@cartweaver.com');
INSERT INTO `cw_admin_users` VALUES ('2', 'Customer Service', 'service', 'admin', 'service', '2011-04-26 16:09:46', '2010-10-11 13:33:34', 'service@cartweaver.com');
INSERT INTO `cw_admin_users` VALUES ('3', 'Manager', 'manager', 'admin', 'manager', '2011-07-18 10:50:59', '2011-04-26 16:10:06', 'manager@cartweaver.com');
INSERT INTO `cw_admin_users` VALUES ('4', 'Merchant', 'merchant', 'admin', 'merchant', '2010-10-11 13:23:11', '2010-04-23 07:43:30', 'merchant@cartweaver.com');

-- ----------------------------
-- Table structure for `cw_cart`
-- ----------------------------
DROP TABLE IF EXISTS `cw_cart`;
CREATE TABLE `cw_cart` (
  `cart_line_id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_custcart_id` varchar(255) DEFAULT NULL,
  `cart_sku_id` int(11) DEFAULT NULL,
  `cart_sku_unique_id` varchar(255) DEFAULT NULL,
  `cart_sku_qty` int(11) DEFAULT NULL,
  `cart_dateadded` datetime DEFAULT NULL,
  PRIMARY KEY (`cart_line_id`),
  KEY `cart_custcart_id_idx` (`cart_custcart_id`),
  KEY `cart_sku_id_idx` (`cart_sku_id`),
  KEY `cart_sku_unique_id_idx` (`cart_sku_unique_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `cw_categories_primary`
-- ----------------------------
DROP TABLE IF EXISTS `cw_categories_primary`;
CREATE TABLE `cw_categories_primary` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(75) DEFAULT NULL,
  `category_archive` smallint(6) DEFAULT '0',
  `category_sort` float(11,3) DEFAULT '1.000',
  `category_description` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_categories_primary
-- ----------------------------
INSERT INTO `cw_categories_primary` VALUES ('55', 'Electronics', '0', '0.000', '<p>Category Description: text inserted here for each category</p>');
INSERT INTO `cw_categories_primary` VALUES ('56', 'Housewares', '0', '0.000', '<p>Category Description: text inserted here for each category</p>');
INSERT INTO `cw_categories_primary` VALUES ('57', 'Lawn & Garden', '0', '0.000', '<p>Category Description: text inserted here for each category</p>');
INSERT INTO `cw_categories_primary` VALUES ('58', 'Collectibles', '0', '0.000', '<p>Category Description: text inserted here for each category</p>');
INSERT INTO `cw_categories_primary` VALUES ('54', 'Clothing', '0', '0.000', '<p>Category Description: text inserted here for each category</p>');

-- ----------------------------
-- Table structure for `cw_categories_secondary`
-- ----------------------------
DROP TABLE IF EXISTS `cw_categories_secondary`;
CREATE TABLE `cw_categories_secondary` (
  `secondary_id` int(11) NOT NULL AUTO_INCREMENT,
  `secondary_name` varchar(100) DEFAULT NULL,
  `secondary_archive` smallint(6) DEFAULT '0',
  `secondary_sort` float(11,3) DEFAULT '1.000',
  `secondary_description` text,
  PRIMARY KEY (`secondary_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_categories_secondary
-- ----------------------------
INSERT INTO `cw_categories_secondary` VALUES ('70', 'Shirts', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('71', 'Pants & Shorts', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('72', 'Cameras', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('73', 'Televisions', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('74', 'Desktop Computers', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('76', 'Video Cameras', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('75', 'Laptop Computers', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('79', 'Lawn Mowers', '0', '1.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('80', 'Weed Trimmers', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('77', 'Bed & Bath', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');
INSERT INTO `cw_categories_secondary` VALUES ('78', 'Kitchen', '0', '0.000', '<p>Secondary Category Description: insert any content here for each secondary category</p>');

-- ----------------------------
-- Table structure for `cw_config_groups`
-- ----------------------------
DROP TABLE IF EXISTS `cw_config_groups`;
CREATE TABLE `cw_config_groups` (
  `config_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_group_name` varchar(50) DEFAULT NULL,
  `config_group_sort` float(11,3) DEFAULT '1.000',
  `config_group_show_merchant` tinyint(4) DEFAULT '0',
  `config_group_protected` tinyint(4) DEFAULT '1',
  `config_group_description` text,
  PRIMARY KEY (`config_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_config_groups
-- ----------------------------
INSERT INTO `cw_config_groups` VALUES ('3', 'Company Info.', '1.000', '1', '1', 'Enter global settings for company information.');
INSERT INTO `cw_config_groups` VALUES ('5', 'Tax Settings', '1.000', '1', '1', 'Manage global tax system settings.');
INSERT INTO `cw_config_groups` VALUES ('6', 'Shipping Settings', '1.000', '1', '1', 'Manage global shipping system settings.');
INSERT INTO `cw_config_groups` VALUES ('7', 'Admin Controls', '1.000', '0', '1', 'Manage global settings for the admin interface.');
INSERT INTO `cw_config_groups` VALUES ('8', 'Product Display', '1.000', '0', '1', 'Manage global settings for front end display.');
INSERT INTO `cw_config_groups` VALUES ('10', 'Debug Settings', '1.000', '0', '1', 'Select the variable scopes to be shown as part of the debugging output. <br> Warning: Enabling all scopes may cause timeout errors, depending on your server settings.');
INSERT INTO `cw_config_groups` VALUES ('11', 'Discount Settings', '1.000', '1', '1', 'Manage global settings for discount system.');
INSERT INTO `cw_config_groups` VALUES ('24', 'Product Admin Settings', '1.000', '0', '1', 'Manage global settings related to product administration.');
INSERT INTO `cw_config_groups` VALUES ('25', 'Global Settings', '1.000', '0', '1', 'Manage application global settings');
INSERT INTO `cw_config_groups` VALUES ('27', 'Email Settings', '1.000', '0', '1', 'Sitewide email options and message content    (<a href=\"email-sample.php\" target=\"_blank\">View sample email format</a>)');
INSERT INTO `cw_config_groups` VALUES ('29', 'Customer Settings', '1.000', '0', '1', 'Manage options related to customer accounts');
INSERT INTO `cw_config_groups` VALUES ('9', 'Payment Settings', '1.000', '0', '1', 'Manage payment settings and options');
INSERT INTO `cw_config_groups` VALUES ('12', 'Cart Pages', '1.000', '0', '1', 'Manage default pages for Cartweaver functions');
INSERT INTO `cw_config_groups` VALUES ('13', 'Cart Display Settings', '1.000', '0', '1', 'Manage display settings for general cart functions');
INSERT INTO `cw_config_groups` VALUES ('14', 'Image Settings', '1.000', '0', '1', 'Manage global options for product images');
INSERT INTO `cw_config_groups` VALUES ('15', 'Admin Home', '1.000', '0', '1', 'Manage content on admin landing page');
INSERT INTO `cw_config_groups` VALUES ('30', 'Developer Settings', '1.000', '0', '1', 'Manage developer-only options');
INSERT INTO `cw_config_groups` VALUES ('31', 'Download Settings', '1.000', '0', '1', 'Manage global options for file downloads');

-- ----------------------------
-- Table structure for `cw_config_items`
-- ----------------------------
DROP TABLE IF EXISTS `cw_config_items`;
CREATE TABLE `cw_config_items` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_group_id` int(11) DEFAULT '0',
  `config_variable` varchar(50) DEFAULT NULL,
  `config_name` varchar(50) DEFAULT NULL,
  `config_value` text,
  `config_type` varchar(50) DEFAULT NULL,
  `config_description` text,
  `config_possibles` text,
  `config_show_merchant` tinyint(4) DEFAULT '0',
  `config_sort` float(11,3) DEFAULT '1.000',
  `config_size` int(3) NOT NULL DEFAULT '35',
  `config_rows` int(3) NOT NULL DEFAULT '5',
  `config_protected` tinyint(4) DEFAULT '0',
  `config_required` tinyint(1) NOT NULL DEFAULT '0',
  `config_directory` varchar(0) DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  KEY `config_group_id_idx` (`config_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_config_items
-- ----------------------------
INSERT INTO `cw_config_items` VALUES ('8', '3', 'companyName', 'Company Name', 'Our Company', 'text', 'This is the name of the store, used on invoices and in email confirmations', '', '0', '4.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('9', '3', 'companyAddress1', 'Address 1', '123 Our Street', 'text', 'The first line of the company address', '', '0', '5.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('10', '3', 'companyAddress2', 'Address 2', '', 'text', 'The second line of the company address (if necessary)', '', '0', '6.000', '35', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('11', '3', 'companyCity', 'City', 'Our Town', 'text', 'The company\'s city', '', '0', '7.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('12', '3', 'companyState', 'State / Prov', 'ST', 'text', 'The state or province the company is located in', '', '0', '8.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('13', '3', 'companyZip', 'Postal Code', '11111', 'text', 'The company\'s postal or zip code', '', '0', '9.000', '20', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('14', '3', 'companyCountry', 'Country', 'USA', 'text', 'The company\'s country', '', '0', '10.000', '12', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('15', '3', 'companyPhone', 'Phone', '555-555-1234', 'text', 'The company phone number', '', '0', '12.000', '20', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('16', '3', 'companyFax', 'Fax', '', 'text', 'The company fax number', '', '0', '13.000', '20', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('17', '3', 'companyEmail', 'Company Email', 'company@cartweaver.com', 'text', 'This is the company email - all automated emails are sent from this address, and order confirmations are sent to this address', '', '0', '3.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('18', '5', 'taxChargeOnShipping', 'Charge Tax on Shipping', '0', 'boolean', 'Determines whether tax is charged on the shipping total for an order', '', '0', '6.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('19', '5', 'taxDisplayLineItem', 'Display Tax on Each Line Item', 'true', 'boolean', 'Determines whether line item taxes are displayed in the customer\'s shopping cart', '', '0', '7.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('20', '5', 'taxDisplayOnProduct', 'Display Product Price Incl. and Excl. Tax', 'false', 'boolean', 'Determines whether prices including taxes are displayed on the product detail pages', '', '0', '8.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('21', '5', 'taxIDNumber', 'Tax ID Number', '', 'text', 'Tax ID Number, or VAT number (may be displayed on customer invoices)', '', '0', '9.000', '20', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('22', '5', 'taxDisplayID', 'Display TAX ID Number on Invoice', 'true', 'boolean', 'Determines whether the Tax ID Number is displayed on the customer\'s invoice', '', '0', '10.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('25', '6', 'shipEnabled', 'Enable Shipping', 'true', 'boolean', 'Determines if shipping calculations are performed during checkout', '', '0', '1.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('26', '6', 'shipChargeBase', 'Charge Base', 'true', 'boolean', 'Include the shipping method base rate when calculation shipping rates using this method (y/n)', '', '0', '4.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('28', '6', 'shipChargeExtension', 'Charge Location Extension', 'true', 'boolean', 'Include Local Extension percentages when calculating shipping costs (y/n)', '', '0', '5.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('29', '8', 'appEnableBackOrders', 'Allow Backorders', '0', 'boolean', 'Determines if backorders are allowed in the application - if this option is disabled, all products must have a positive stock quantity to be available for purchase', '', '0', '8.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('30', '8', 'appDisplayUpsell', 'Show Related Items', 'true', 'boolean', 'Determines whether related \'upsell\' products are displayed on the product details pages', '', '0', '14.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('190', '8', 'appDisplayFreeShipMessage', 'Show Free Shipping Message', 'true', 'boolean', 'Show a message along with pricing when the item is set to shipping = no', '', '0', '11.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('32', '8', 'appDisplayColumns', 'Number of Product Columns', '3', 'text', 'The number of columns to be displayed on the product results page', '', '0', '1.000', '0', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('33', '8', 'appDisplayPerPage', 'Results per Page', '6', 'text', 'This value should be evenly divisible by the Number of Columns in order to ensure correct display for multi-column results', '', '0', '2.000', '0', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('34', '8', 'appDisplayOptionView', 'Product Option Selection Type', 'select', 'select', 'Determines how product option details are displayed', 'Select Dropdowns|select\r\nTables|table', '0', '10.000', '0', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('36', '10', 'debugApplication', 'Show Application Variables', '0', 'boolean', 'Show application variables when debugging is turned on', 'NULL', '0', '6.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('37', '10', 'debugSession', 'Show Session Variables', 'true', 'boolean', 'Show session variables when debugging is turned on', 'NULL', '0', '13.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('38', '10', 'debugRequest', 'Show Request Variables', 'true', 'boolean', 'Show request variables when debugging is turned on', 'NULL', '0', '11.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('39', '10', 'debugServer', 'Show Server Variables', '0', 'boolean', 'Show server variables when debugging is turned on', 'NULL', '0', '12.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('40', '10', 'debugUrl', 'Show URL Variables', 'true', 'boolean', 'Show URL variables when debugging is turned on', 'NULL', '0', '14.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('41', '10', 'debugLocal', 'Show Local Variables', '0', 'boolean', 'Show local variables when debugging is turned on', 'NULL', '0', '10.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('42', '10', 'debugForm', 'Show Form Variables', '0', 'boolean', 'Show form variables when debugging is turned on', 'NULL', '0', '9.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('43', '10', 'debugCookies', 'Show Cookie Variables', 'true', 'boolean', 'Show cookie variables when debugging is turned on', 'NULL', '0', '8.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('44', '10', 'debugCGI', 'Show CGI Variables', '0', 'boolean', 'Show CGI variables when debugging is turned on', 'NULL', '0', '7.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('45', '10', 'debugDisplayLink', 'Show Debug Link', 'true', 'boolean', 'If debugging is enabled, show a link in the store', '', '0', '5.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('46', '10', 'debugEnabled', 'Enable Debugging', 'true', 'boolean', 'Enable debugging output for displaying system information', 'NULL', '0', '2.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('47', '11', 'discountDisplayLineItem', 'Display Line Item Discount', 'true', 'boolean', 'Displays a line item discount in the shopping cart if checked and if the product is discounted', 'NULL', '0', '2.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('48', '11', 'discountsEnabled', 'Enable Discounts', 'true', 'boolean', 'Enables the discount feature in the store. If unchecked, discounts are disabled', 'NULL', '0', '1.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('50', '11', 'discountDisplayNotes', 'Display Discount Notes on Cart', 'true', 'boolean', 'Show an asterisk in the cart line item and tie it to a discount description below', 'NULL', '0', '3.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('51', '13', 'appDisplayCartImage', 'Show Small Image in Cart', 'true', 'boolean', 'Determines whether the extra small images are displayed in the cart', 'NULL', '0', '2.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('52', '6', 'shipChargeBasedOn', 'Charge Range Based On', 'weight', 'select', 'Determines how shipping ranges are calculated - this value is either ignored (if set to none), or figured by cart subtotal or weight totals.', 'None|none\r\nCart Subtotal|subtotal\r\nCart Weight|weight', '0', '6.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('53', '8', 'appEnableCatsRelated', 'Relate Categories/Secondaries', 'true', 'boolean', 'Check the box if your categories and secondary categories are related - relationships are handled at the product level', 'NULL', '0', '18.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('54', '8', 'appEnableImageZoom', 'Show Expanded Image Popup', 'true', 'boolean', 'Determines whether a popup image is shown on the details page', 'NULL', '0', '13.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('55', '25', 'appVersionNumber', 'Cartweaver Version Number', '4.0.1', 'text', 'The current Cartweaver version number is stored here for reference when installing or upgrading', 'NULL', '0', '12.000', '0', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('56', '30', 'developerEmail', 'Developer Email', '', 'text', 'Debugging and error emails will go to this address - if not defined, emails will go to the CompanyEmail', 'NULL', '0', '1.000', '35', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('57', '10', 'debugHandleErrors', 'Enable Error Handling', 'true', 'boolean', 'Determines whether error handling will be enabled on the site', 'NULL', '0', '1.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('59', '5', 'taxSystem', 'Tax System', 'Groups', 'radio', 'Determine which tax system to use - tax groups or general tax', 'Groups|Groups\r\nGeneral|General', '0', '2.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('60', '5', 'taxChargeBasedOn', 'Charge Tax Based On', 'shipping', 'radio', 'Taxes can be charged based on billing or shipping address, depending on state/country laws', 'Shipping Address|shipping\r\nBilling Address|billing', '0', '4.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('61', '13', 'appActionContinueShopping', 'Continue Shopping', 'Results', 'select', 'Used for the Continue Shopping and Return to Store links.', 'Product Details|Details\r\nProduct Listings|Results\r\nHome Page|Home', '0', '7.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('62', '6', 'shipDisplayInfo', 'Show Customer Shipping Info', 'true', 'boolean', 'Show shipping form fields on the order form, and shipping totals in order confirmation (y/n)', '', '0', '3.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('140', '27', 'mailSmtpPassword', 'SMTP Password', '', 'text', 'The SMTP password (may be left blank)', null, '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('85', '7', 'adminProductPaging', 'Enable Product Paging', 'true', 'boolean', 'Break products list into multiple pages', null, '0', '4.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('155', '7', 'adminErrorHandling', 'Use Admin Error Handling', 'false', 'boolean', 'db', '', '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('129', '10', 'debugPassword', 'Debugging Password', 'cwdebug', 'text', 'Add this password to the url to turn on debugging from any page (&debug=[debugpw])', '', '0', '3.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('127', '25', 'appSiteUrlHttps', 'Site Secure URL', '', 'text', 'The URL for your secure root directory (usually same as Site URL, with https:// prefix). If no SSL is used for the site, use the http prefix here, or leave this blank.', '', '0', '2.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('125', '30', 'appTestModeEnabled', 'TEST MODE ON', '0', 'boolean', 'For development purposes only, bypasses some global warnings and functions to allow for easier setup.', '', '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('124', '14', 'appImageDefault', 'Default Image Filename', 'noimgupload.jpg', 'text', 'To activate the default placeholder image, enter the filename of any image that has been uploaded via the product form.', '', '0', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('126', '25', 'appSiteUrlHttp', 'Site URL', '', 'text', 'The website root directory url, starting with http://, no trailing slash (e.g. http://www.cartweaver.com )', '', '0', '1.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('86', '7', 'adminOrderPaging', 'Enable Order Paging', 'true', 'boolean', 'Break orders list into multiple pages', 'NULL', '0', '3.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('87', '7', 'adminCustomerPaging', 'Enable Customer Paging', 'true', 'boolean', 'Break customer list into multiple pages', 'NULL', '0', '2.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('88', '24', 'adminProductLinksEnabled', 'Show Links to View & Edit Product', 'true', 'boolean', 'Show links in admin lists to view products and categories on the site, and links on site to edit product (if logged in).', 'NULL', '0', '19.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('90', '24', 'adminLabelCategories', 'Categories Label', 'Main Categories', 'text', 'The text label assigned to multiple top level categories (i.e. \'categories, galleries, departments\')', 'NULL', '0', '2.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('91', '24', 'adminLabelCategory', 'Category Label', 'Main Category', 'text', 'The text label for a singular top level category (i.e. \'category, gallery, department\')', '', '0', '3.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('92', '24', 'adminLabelSecondary', 'Secondary Category Label', 'Secondary Category', 'text', 'The text label for a singular secondary category (i.e. \'category, gallery, department\')', null, '0', '7.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('93', '24', 'adminLabelSecondaries', 'Secondary Categories Label', 'Secondary Categories', 'text', 'The text label assigned to multiple secondary categories (i.e. \'categories, galleries, departments\')', null, '0', '4.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('94', '24', 'adminProductAltPriceEnabled', 'Use Alternate (Suggested) Price', 'true', 'boolean', 'Use alternate \'MSRP\' pricing to show full suggested retail price', null, '0', '9.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('95', '24', 'adminLabelProductAltPrice', 'Alternate Price Label', 'MSRP', 'text', 'The text label for the alternate pricing field (default = \'MSRP\')', null, '0', '10.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('96', '24', 'adminProductSpecsEnabled', 'Use Product Additional Info', 'true', 'boolean', 'Show product specs or additional info field on product form', null, '0', '11.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('97', '24', 'adminLabelProductSpecs', 'Addtional Info Label', 'Additional Information', 'text', 'The text label for the additional info field', null, '0', '12.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('98', '24', 'adminProductKeywordsEnabled', 'Use Product Keywords', 'true', 'boolean', 'Show product keywords field on product form - used for enhanced product search', '', '0', '13.000', '0', '0', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('99', '24', 'adminLabelProductKeywords', 'Product Keywords Label', 'Additional Search Terms', 'text', 'The text label for product keywords field', null, '0', '14.000', '35', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('100', '7', 'adminEditorEnabled', 'Use Text Editor (global)', 'true', 'boolean', 'Use the rich text (wysiwyg) editor where specified', null, '0', '6.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('101', '24', 'adminProductImageMaxKB', 'Maximum Image File Size', '2000', 'number', 'The maximum filesize allowed for image uploads', null, '0', '17.000', '5', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('102', '24', 'adminProductImageSelectorThumbsEnabled', 'Enable Image Selector Thumbnails', 'true', 'boolean', 'Enable thumbnail view when selecting from existing images', null, '0', '18.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('103', '24', 'adminProductCustomInfoEnabled', 'Use Product Custom Info', 'true', 'boolean', 'Use \'custom info\' personalization field for products', null, '0', '15.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('104', '24', 'adminProductUpsellByCatEnabled', 'Show Related Products by Category', 'true', 'boolean', 'Filter Related Products selection by category (recommended for large product catalogs for faster performance)', null, '0', '20.000', '0', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('105', '24', 'adminProductDefaultBackOrderText', 'Out of Stock Message Default', '', 'text', 'Default message to show in \'out of stock message\' field in product form (overridden per product)', null, '0', '1.000', '35', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('106', '24', 'adminProductDefaultCustomInfo', 'Custom Info Label Default', '', 'text', 'Default message to show in \'custom info label\' field in product form', null, '0', '16.000', '35', '0', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('109', '15', 'adminWidgetOrders', 'Admin Home: Recent Orders', '5', 'number', 'Number of recent orders to show in the admin home page preview (0 hides this widget)', '', '0', '4.000', '3', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('110', '15', 'adminWidgetProductsBestselling', 'Admin Home: Top Selling Products', '5', 'number', 'Number of top selling products to show in the admin home page preview (0 hides this widget)', '', '0', '7.000', '3', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('111', '15', 'adminWidgetProductsRecent', 'Admin Home: Recent Products', '5', 'number', 'Number of top selling products to show in the admin home page preview (0 hides this widget)', '', '0', '5.000', '3', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('112', '15', 'adminWidgetCustomers', 'Admin Home: Top Customers', '5', 'number', 'Number of top customers to show in the admin home page preview (0 hides this widget)', '', '0', '6.000', '3', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('113', '15', 'adminWidgetSearchProducts', 'Admin Home: Search Products', 'true', 'boolean', 'Show product search on Admin Home page y/n', '', '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('114', '15', 'adminWidgetSearchOrders', 'Admin Home: Search Orders', 'true', 'boolean', 'Show order search on Admin Home page y/n', '', '0', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('115', '15', 'adminWidgetSearchCustomers', 'Admin Home: Search Customers', 'true', 'boolean', 'Show customer search on Admin Home page y/n', null, '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('116', '10', 'debugDisplayExpanded', 'Show Debug Expanded', '0', 'boolean', 'Show debug cfdump output expanded by default', '', '0', '4.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('117', '25', 'globalTimeOffset', 'Server Time Offset (+/-)(hh:mm)', '0', 'number', 'The global time offset, in hours or fractional hours, from server time to displayed store time (if your server is at 1pm, and your site is at 3pm, the value would be +2)', '', '0', '7.000', '5', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('118', '25', 'globalDateMask', 'Date Format', 'Y-m-d', 'select', 'A global format to be applied to all displayed dates sitewide', '\'mm/dd/yyyy\' (07/28/2011)|m/d/Y\r\n\'m/d/yy\' (7/28/11)|j/n/y\r\n\'dd/mm/yyyy\' (28/7/2011)|d/m/Y\r\n\'d/m/yy\' (28/7/11)|n/j/y\r\n\'yyyy-mm-dd\' (2011-07-28)|Y-m-d', '0', '50.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('139', '27', 'mailSmtpUsername', 'SMTP Username', '', 'text', 'The SMTP username (may be left blank)', null, '0', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('130', '13', 'appActionAddToCart', 'Add to Cart Action', 'goto', 'select', 'The response after adding an item to the cart (confirm|goto)', 'Product Details Page|confirm\r\nGo To Cart|goto', '0', '1.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('131', '12', 'appPageResults', 'Results Page (filename)', 'productlist.php', 'text', 'The page displaying product listings', null, '0', '1.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('132', '12', 'appPageDetails', 'Details Page (filename)', 'product.php', 'text', 'The page displaying product details', null, '0', '2.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('133', '12', 'appPageShowCart', 'Cart Page (filename)', 'cart.php', 'text', 'The page displaying cart details', null, '0', '3.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('134', '12', 'appPageCheckout', 'Checkout Page (filename)', 'checkout.php', 'text', 'The page displaying checkout forms (log in / customer info)', null, '0', '4.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('135', '12', 'appPageConfirmOrder', 'Order Confirmation Page (filename)', 'confirm.php', 'text', 'The page displaying order confirmation', null, '0', '5.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('136', '7', 'adminEditorCSS', 'Text Editor CSS File (file path and name)', 'css/cw-styles.css', 'text', 'The css file used to define text styles in scripted text editors (product descriptions), relative to /cw/ directory', null, '0', '10.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('137', '14', 'appImagesDir', 'Images Directory (folder name)', 'images', 'text', 'The folder for CW product images (created automatically if it does not already exist)', null, '0', '1.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('138', '27', 'mailSmtpServer', 'SMTP Email Server', '', 'text', 'The mail server host name (usually \'localhost\' or left blank). If a mail server is entered, you must have PEAR Mail installed.', null, '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('119', '7', 'adminEditorCategoryDescrip', 'Categories: Text Editor', 'true', 'boolean', 'Show rich text editor for category and secondary category descriptions (if no, simple text input shown)', '', '0', '7.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('120', '7', 'adminEditorProductDescrip', 'Products: Text Editor', 'true', 'boolean', 'Show rich text editor for product descriptions (if no, simple textarea shown)', '', '0', '9.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('122', '7', 'adminThemeDirectory', 'Theme Directory', 'neutral', 'select', 'The directory for Cartweaver admin theme stylesheet and graphics', '', '0', '11.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('121', '7', 'adminEditorOptionDescrip', 'Options: Text Editor', 'true', 'boolean', 'Show rich text editor for option descriptions (if no, simple textarea shown)', '', '0', '8.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('142', '9', 'paymentMethods', 'Payment Collection Method', 'cw-auth-account.php', 'checkboxgroup', 'The checkout methods, and corresponding connection files, to use for your processing or payment gateway', 'In-Store Account|cw-auth-account.php\rAuthorize.net|cw-auth-authorizenet.php\rPayPal|cw-auth-paypal.php\rSagePay UK|cw-auth-sagepay.php\rWorldPay|cw-auth-worldpay.php', '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('143', '25', 'globalLocale', 'Default Locale (nationality)', 'English (US),en_US,en_US.UTF8,English_United States.1252', 'select', 'The default nationality or locale for your site, used to automatically set other location-specific settings', 'Chinese (China)|Chinese (China),zh_CN,zh_CN.UTF8,Chinese_PRC.936\r\nChinese (Hong Kong)|Chinese (Hong Kong),zh_HK,zh_HK.UTF8,Chinese_Hong Kong SAR.950\r\nChinese (Taiwan)|Chinese (Taiwan),zh_TW,zh_TW.UTF8,Chinese_Taiwan.950\r\nEnglish (Australian)|English (Australian),en_AU,en_AU.UTF8,English_Australia.1252\r\nEnglish (Canadian)|English (Canadian),en_CA,en_CA.UTF8,English_Canada.1252\r\nEnglish (New Zealand)|English (New Zealand),en_NZ,en_NZ.UTF8,English_New Zealand.1252\r\nEnglish (UK)|English (UK),en_GB,en_GB.UTF8,English_United Kingdom.1252\r\nEnglish (US)|English (US),en_US,en_US.UTF8,English_United States.1252\r\nFrench (Belgian)|French (Belgian),fr_BE,fr_BE.UTF8,French_Belgium.1252\r\nFrench (Canadian)|French (Canadian),fr_CA,fr_CA.UTF8,French_Canada.1252\r\nFrench (Standard)|French (Standard),fr_FR,fr_FR.UTF8,French_France.1252\r\nFrench (Swiss)|French (Swiss),fr_CH,fr_CH.UTF8,French_Switzerland.1252\r\nGerman (Austrian)|German (Austrian),de_AT,de_AT.UTF8,German_Austria.1252\r\nGerman (Standard)|German (Standard),de_DE,de_DE.UTF8,German_Germany.1252\r\nGerman (Swiss)|German (Swiss),de_CH,de_CH.UTF8,German_Switzerland.1252\r\nItalian (Standard)|Italian (Standard),it_IT,it_IT.UTF8,Italian_Italy.1252\r\nItalian (Swiss)|Italian (Swiss),id_CH,id_CH.UTF8,Italian_Switzerland.1252\r\nJapanese|Japanese,jp_JP,jp_JP.UTF8,Japanese_Japan.932\r\nKorean|Korean,ko_KR,ko_KR.UTF8,Korean_Korea.949\r\nNorwegian (Bokmal)|Norwegian (Bokmal),nb_NO,nb_NO.UTF8,Norwegian_Norway (Bokmal).1252\r\nNorwegian (Nynorsk)|Norwegian (Nynorsk),nn_NO,nn_NO.UTF8,Norwegian_Norway (Nynorsk).1252\r\nPortuguese (Brazilian)|Portuguese (Brazilian),pt_BR,pt_BR.UTF8,Portuguese_Brazil.1252\r\nPortuguese (Standard)|Portuguese (Standard),pt_PT,pt_PT.UTF8,Portuguese_Portugal.1252\r\nSpanish (Standard)|Spanish (Standard),es_ES,es_ES.UTF8,Spanish_Spain.1252\r\nSpanish (Modern)|Spanish (Modern),es_ES,es_ES.UTF8,Spanish_Spain.1252\r\nSwedish|Swedish,sv_SE,sv_SE.UTF8,Swedish_Sweden.1252', '0', '60.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('144', '25', 'appDbType', 'Database Type', 'mySQL', 'select', 'The database type for the application', 'mySQL|mySQL\r\nMS SQL|MSSQLServer', '0', '4.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('196', '25', 'appCookieTerm', 'Cookie Expiration Term', '240', 'text', 'Length of time to expire cookies. Options are 0 (no cookies), blank (never expire), or number of hours.', '', '0', '8.000', '7', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('145', '14', 'adminImagesMerchantDeleteOrig', 'Original Images: Merchant Delete', 'true', 'boolean', 'Allow the \'Merchant\' user level to see the \'delete all originals\' button on the Product Images page', '', '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('146', '24', 'adminSkuEditMode', 'SKU display format', 'standard', 'select', 'Select the format for sku display on the product details SKU tab.', 'Standard|standard\r\nList View|list', '0', '5.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('147', '24', 'adminSkuEditModeLink', 'SKU Mode Link: Show Merchant?', 'true', 'boolean', 'Allow the merchant to change SKU view mode?', '', '0', '6.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('148', '24', 'adminProductUpsellThumbsEnabled', 'Enable Related Product Thumbnails', 'true', 'boolean', 'Enable thumbnails in related product tab of product details page', '', '0', '21.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('149', '5', 'taxSystemLabel', 'Tax / VAT Text Label', 'Tax', 'select', 'The name of the tax system in use, Tax(es) or VAT.', 'Tax|Tax\r\nVAT|VAT\r\nGST|GST', '0', '1.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('150', '27', 'mailHeadText', 'Email Heading (Text)', 'Our Cartweaver Site : cartweaver.com', 'textarea', 'Text inserted into all emails sent from the site, at the beginning of the message body.', '', '0', '5.000', '45', '4', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('151', '27', 'mailHeadHtml', 'Email Heading (HTML)', '<h2><strong>Our Cartweaver Site : cartweaver.com</strong></h2>', 'texteditor', 'Formatted text inserted into all HTML-version emails sent from the site, at the beginning of the message body.', '', '0', '6.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('152', '27', 'mailFootText', 'Email Signature (Text)', 'Thank you for choosing Our Site for your online purchase!', 'textarea', 'Text inserted into all emails sent from the site, at the end of the message body.', '', '0', '7.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('153', '27', 'mailFootHtml', 'Email Signature (HTML)', '<p><em><strong>Thank you for choosing <a href=\"http://www.cartweaver.com\">Our Site</a> for your online purchase!</strong></em></p>', 'texteditor', 'Text inserted into all emails sent from the site, at the end of the message body.', '', '0', '8.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('154', '27', 'mailMultipart', 'Mail Format', '1', 'select', 'If Multi-Part is selected, mail will be sent in both html and text format, allowing the recipient\'s email reader to display the preferred format. The Text Only setting will allow for only plain text email to be sent from this site.', 'Multi-Part (html + text)|1,\r\nText Only|0', '0', '4.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('158', '24', 'adminProductDefaultPrice', 'Default Price', '0', 'text', 'The value shown by default in the \'new sku\' form. Can be left blank to force user input via form validation, or set to any numeric value.', '', '0', '8.000', '5', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('161', '8', 'appDisplayProductSort', 'Show Product Sort Links', 'true', 'boolean', 'Show links to sort product listings (results sortable)', '', '0', '4.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('160', '8', 'appDisplayProductQtyType', 'Quantity Selection Type', 'text', 'radio', 'Use a select box or a text input for the quantity.', 'select|select\r\ntext|text', '0', '9.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('162', '8', 'appDisplayProductViews', 'Number of Recently Viewed Items', '5', 'number', 'The number of recently viewed products - set to 0 to turn off recent products view.', '', '0', '16.000', '2', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('163', '13', 'appDisplayCartSku', 'Show SKU Name in Cart', 'true', 'boolean', 'Determines whether the sku name displayed in the cart', '', '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('164', '13', 'appDisplayCartOrder', 'Order of Products in Cart', 'timeAdded', 'select', 'Show items in the cart in order they are added, or by product sort order and name', 'Added to Cart|timeAdded\r\nProduct Name|productName', '0', '6.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('165', '8', 'appDisplayListingAddToCart', 'Add to Cart from Product Listings', 'true', 'boolean', 'Show \'Add to Cart\' button on product listing page (y/n)', '', '0', '7.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('166', '13', 'appDisplayCartCustom', 'Show Custom Values in Cart', 'true', 'boolean', 'Show the custom values for product personalizations in the cart (y/n)', '', '0', '4.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('167', '8', 'appDisplayProductSortType', 'Product Sort Links Type', 'select', 'select', 'The type of sorting display to show on the product listings page.', 'Select List (dropdown)|select\r\nStandard Links|links', '0', '5.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('168', '8', 'appDisplayProductCategories', 'Lookup Product Categories', 'true', 'boolean', 'Get category and secondary for page titles, navigation and other functions based on product ID in url if category IDs are not provided (y/n)', '', '0', '20.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('194', '5', 'taxCalctype', 'Tax Calculation Method', 'localtax', 'select', 'The calculation method to use for retrieving product tax values (localtax = CW extensions). NOTE: some related settings are automatically set and/or disabled when selecting an option other than Local.', 'Local Database|localtax\r\nAvaTax|avatax', '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('185', '27', 'mailDefaultOrderReceivedIntro', 'Order Received Intro', 'Your order has been received, and is being held pending payment. \r\nWe will notify you again when any payments have been processed, and when your order is shipped.', 'textarea', 'Intro text for order confirmation email to customers', '', '0', '9.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('186', '27', 'mailDefaultOrderReceivedEnd', 'Order Received Footer', 'Please save this email for future reference.\r\nWe appreciate your business, and welcome any questions or requests you may have.', 'textarea', 'Footer text for order confirmation notice to customers', '', '0', '10.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('187', '27', 'mailDefaultOrderPaidIntro', 'Order Paid Intro', 'Your payment has been approved. \r\nYou will receive another notice when your order is shipped.', 'textarea', 'Intro text for order paid in full notice to customers', null, '0', '11.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('188', '27', 'mailDefaultOrderPaidEnd', 'Order Paid Footer', 'We appreciate your business, and welcome any questions or requests you may have.', 'textarea', 'Footer text for order paid in full notice to customers', null, '0', '12.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('156', '27', 'mailDefaultOrderShippedIntro', 'Order Shipped Intro', 'Your order has been shipped.', 'textarea', 'Intro text for order shipping status email to customers', '', '0', '13.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('157', '27', 'mailDefaultOrderShippedEnd', 'Order Shipped Footer', 'We appreciate your business, and welcome any questions or requests you may have.', 'textarea', 'Footer text for order shipping status email to customers', '', '0', '14.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('173', '8', 'appDisplayPageLinksMax', 'Max. Paging Links', '5', 'number', 'The maximum number of product paging links to show at once on the product listings page.', '', '0', '6.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('174', '8', 'appDisplayEmptyCategories', 'Show Empty Categories', '0', 'boolean', 'Show categories with no active products in navigation menus and search options (y/n)', '', '0', '19.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('175', '29', 'customerAccountEnabled', 'Enable Customer Account', 'true', 'boolean', 'Enable customer account functions including order and product history, and access to saved information.', '', '0', '1.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('176', '12', 'appPageAccount', 'Account Page (filename)', 'account.php', 'text', 'The page displaying customer account', '', '0', '6.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('177', '13', 'appDisplayCartCustomEdit', 'Edit Custom Values in Cart', 'true', 'boolean', 'Allow customer to edit custom values in the cart view (y/n)', '', '0', '5.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('179', '12', 'appPageSearch', 'Search Page (filename)', 'index.php', 'text', 'The page used for product search system. Leave blank if none exists. Optional  (unlike other page variables).', '', '0', '7.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('180', '27', 'mailDefaultPasswordSentIntro', 'Password Reminder Intro', 'Account Information Request', 'textarea', 'Intro text for password reminder email to customers', '', '0', '15.000', '45', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('181', '27', 'mailDefaultPasswordSendEnd', 'Password Reminder Footer', 'For further assistance with your account, please contact our customer service department.', 'textarea', 'Footer text for password reminder email to customers', '', '0', '16.000', '45', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('182', '3', 'companyURL', 'Company Url', 'http://www.cartweaver.com', 'text', 'The web address as shown in email messages, does not have to be the same as the site home page or other global URL settings.', '', '0', '2.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('189', '29', 'customerAccountRequired', 'Require Customer Account', '0', 'boolean', 'If accounts are enabled, require customer account (unchecked = allow guest checkout)', '', '0', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('191', '8', 'appFreeShipMessage', 'Free Shipping Message Text', 'FREE SHIPPING on this item', 'text', 'The message to show if an item is set to shipping = no, and message is enabled', '', '0', '12.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('192', '6', 'upsAccessLicense', 'UPS Shipping Access License', '', 'text', 'The UPS shipping API Access License Key for live UPS rate lookup', '', '0', '8.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('193', '3', 'companyShipCountry', 'Country Ship Code', 'US', 'text', 'Shipping code for country', '', '0', '11.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('195', '8', 'appSearchMatchType', 'Keyword Search Matching Rule', 'any', 'select', 'Determines default search match to use - can be overridden wherever the search box is displayed, by passing in arguments.match_type.', 'Any Word|any\r\nExact Phrase|phrase\r\nAll Words|all', '0', '17.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('197', '29', 'customerRememberMe', 'Show Remember Me Checkbox', 'true', 'boolean', 'If checked, the \'remember me\' checkbox is shown on the login form, and a cookie is saved with the customer\'s username.', '', '0', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('198', '25', 'appCWContentDir', 'CW Content Directory: CAUTION!', 'cw4/', 'text', 'Path from site root to Cartweaver\'s content directory. Can be used for advanced configuration, or with plugins, etc. Value is usually \'cw4/\' NOTE: should include trailing slash', '', '0', '10.000', '35', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('199', '25', 'appCWAdminDir', 'CW Admin Directory: CAUTION!', 'cw4/admin/', 'text', 'Path from site root to Cartweaver\'s admin directory. Can be used for advanced configuration, or with plugins, etc. Value is usually \'cw4/admin/\' NOTE: should include trailing slash', '', '0', '9.000', '35', '5', '1', '1', '');
INSERT INTO `cw_config_items` VALUES ('200', '7', 'adminRecordsPerPage', 'Records per Page', '20', 'select', 'Number of products, orders and customers to show per page (if paging for that item is enabled)', '10|10\r\n20|20\r\n30|30\r\n40|40\r\n50|50\r\n100|100', '0', '5.000', '0', '0', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('201', '25', 'appCWStoreRoot', 'CW Store Root: CAUTION!', '', 'text', 'Path from site root to main store page, added to prefix of nav urls and form actions. Can be used for advanced configuration, or with plugins, etc. Value is usually empty (\')', '', '0', '11.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('202', '5', 'taxUseDefaultCountry', 'Use Default Country for Tax', 'false', 'boolean', 'If checked, the default country will be used to calculate and show tax totals in the cart, when customer\'s country is not available.', '', '1', '5.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('203', '6', 'shipDisplaySingleMethod', 'Show Single Shipping Option', 'true', 'boolean', 'If enabled, the shipping information step of the checkout process will still be shown when only one shipping method is available. If unchecked, it will be skipped automatically.', '', '1', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('204', '11', 'adminDiscountThumbsEnabled', 'Show Associated Thumbnails', 'true', 'boolean', 'If enabled, thumbnails will be available in discount associated items.', '', '1', '4.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('205', '13', 'appOrderForceConfirm', 'Force Confirmation of All Orders', 'true', 'boolean', 'Force all persisted orders to show confirmation page on return to site. Useful for paypal and other off-site processors, where customer may not always view the confirmation page manually but may return to site with order info saved in a cookie or session variable.', '', '1', '8.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('213', '5', 'avalaraUrl', 'Avalara Tax URL', 'https://development.avalara.net/1.0/', 'text', 'The API url for your Avalara tax account, including the version number and trailing slash (e.g. https://development.avalara.net/1.0/, https://rest.avalara.net/1.0/)', '', '0', '13.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('214', '5', 'avalaraDefaultCode', 'Avalara Tax Default Item Code', 'O9999999', 'text', 'The code to use for items where a tax group code is unavailable.', '', '1', '14.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('215', '5', 'avalaraDefaultShipCode', 'Avalara Tax Default Shipping Code', 'FR020100', 'text', 'The shipping tax code to use for tax charged on shipping, if applicable.', '', '1', '15.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('206', '5', 'avalaraID', 'Avalara - AvaTax Account ID', '', 'text', 'If using the Avalara AvaTax service, this is the API ID provided for your website location.', '', '1', '11.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('207', '5', 'avalaraKey', 'Avalara - AvaTax Access Key', '', 'text', 'If using the Avalara AvaTax service, this is the API Key provided for your website location.', '', '1', '12.000', '45', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('208', '25', 'appInstallationDate', 'Installation Date', '', 'text', 'Blank by default, this is set on the first page request from a newly-installed application. Useful for tracking core application updates. Simply delete the value and save to reset the timestamp on next login.', '', '1', '13.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('209', '25', 'appHttpRedirectEnabled', 'Enable non-https redirection', 'true', 'boolean', 'If enabled, pages other than checkout will be redirected back to \'http\' when requested under \'https\'. See cw-func-init for processing code or to make adjustments.', '', '1', '3.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('210', '30', 'appDataDeleteEnabled', 'Delete Test Data Enabled', 'true', 'boolean', 'If enabled, the developer will see a menu option to Delete Test Data, and the cleanup script will be enabled.', '', '1', '2.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('211', '8', 'appDisplayUpsellColumns', 'Number of Related Item Columns', '4', 'number', 'The number of related items to show per row in the default product details output.', '', '1', '15.000', '2', '5', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('212', '8', 'productPerPageOptions', 'Products Per Page List Options', '6,12,24,48', 'text', 'A list of numeric values to be used for the per page selector - only used if sort type is select list/dropdown', '', '1', '3.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('216', '6', 'upsUserID', 'UPS Shipping User ID', '', 'text', 'The Account User ID for the UPS shipping rates API', '', '1', '9.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('217', '6', 'upsPassword', 'UPS Shipping Password', '', 'text', 'The Account Password for the UPS shipping rates API', '', '1', '10.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('218', '6', 'upsUrl', 'UPS Shipping URL', 'https://onlinetools.ups.com/ups.app/xml/Rate', 'text', 'The URL for UPS API Transactions', 'The URL for UPS API Transactions', '1', '11.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('219', '6', 'shipWeightUOM', 'Shipping Weight Unit of Measure', 'lbs', 'select', 'The Unit of Measure for shipping weight values, used for shipping rate API transactions', 'lbs|lbs\r\nkgs|kgs', '1', '7.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('220', '5', 'taxErrorEmail', 'Tax Transaction Error Email', '', 'text', 'The email address for remote tax system errors. Leave blank to disable. Not used for Local Database calculations.', '', '1', '16.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('221', '5', 'taxSendLookupErrors', 'Tax Error Email on Basic Lookup', 'true', 'boolean', 'If enabled, and a tax error email address is provided, error alerts will be sent for general view cart tax errors. If not checked, error alerts are only sent for actual checkout transactions.', '', '0', '17.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('222', '31', 'appDownloadsDir', 'Downloads Directory (folder name)', '../downloads', 'text', 'The folder for CW download files, relative to the site root (created automatically if it does not already exist). Tip: use ../ prefix to put your files above your site root for security (if using this method, directory must already exist).', null, '0', '4.000', '35', '5', '1', '1', '');
INSERT INTO `cw_config_items` VALUES ('223', '31', 'appDownloadsEnabled', 'Downloads Enabled', 'true', 'boolean', 'If unchecked, all file download functionality is disabled', null, '0', '1.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('224', '31', 'appDownloadsFileTypes', 'Allowed File Types', 'application/zip,application/pdf,audio/mpeg3,application/x-zip-compressed', 'textarea', 'Comma-separated list of allowed', null, '0', '8.000', '35', '3', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('225', '31', 'appDownloadsFileExtDirs', 'Divide Files by Extension', 'true', 'boolean', 'If checked, files will be separated into subdirectories named for the file type', null, '0', '3.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('226', '31', 'appDownloadsMaxKb', 'Max. File Size (kb)', '5000', 'number', 'The maximum filesize allowed for files uploaded through the product admin.', null, '0', '9.000', '7', '5', '1', '1', '');
INSERT INTO `cw_config_items` VALUES ('227', '31', 'appDownloadsLimitDefault', 'Download Limit Default', '3', 'number', 'The default number shown in the download limit on the sku file upload form.', null, '0', '10.000', '5', '1', '1', '1', '');
INSERT INTO `cw_config_items` VALUES ('228', '31', 'appPageDownload', 'Download Page', 'download.php', 'text', 'The name of your site\'s download page, in the site root.', null, '0', '6.000', '35', '5', '1', '1', '');
INSERT INTO `cw_config_items` VALUES ('229', '31', 'appDownloadStatusCodes', 'Order Status Codes', '3,4', 'text', 'List of order status codes allowed to download files (default=3,4/paid,shipped). If blank, no downloads will be available.', null, '0', '7.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('230', '31', 'appDownloadsMaskFilenames', 'Mask Filenames', 'true', 'boolean', 'If checked, files are saved with a unique filename on the server. (The original filename is restored when downloaded by the customer).', null, '0', '2.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('231', '31', 'appDownloadsPath', 'Downloads Path (full path)', null, 'text', 'If provided, overrides appDownloadsDir, forcing downloads to and from an absolute path on the server. Leave blank to use appDownloadsDir instead.', null, '0', '5.000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('232', '27', 'mailSendOrderCustomer', 'Send Customer Order Notification', 'true', 'boolean', 'If checked, email notification will be sent to the customer upon submission of the order, regardless of payment status', '', '0', '4.100', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('233', '27', 'mailSendOrderMerchant', 'Send Merchant Order Notification', 'true', 'boolean', 'If checked, email notification will be sent to the merchant upon submission of the order, regardless of payment status', '', '0', '4.200', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('234', '27', 'mailSendPaymentCustomer', 'Send Customer Payment Notification', 'true', 'boolean', 'If checked, email notification will be sent to the customer upon receipt of payment', '', '0', '4.300', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('235', '27', 'mailSendPaymentMerchant', 'Send Merchant Payment Notification', 'true', 'boolean', 'If checked, email notification will be sent to the merchant upon receipt of payment', '', '0', '4.400', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('236', '27', 'mailSendShipCustomer', 'Send Customer Ship Notification', 'true', 'boolean', 'If checked, email notification will be sent to the customer when the order status is changed to Shipped in the admin', '', '0', '4.5000', '35', '5', '1', '0', '');
INSERT INTO `cw_config_items` VALUES ('237', '13', 'appDisplayCountryType', 'State/Country Selection Type', 'split', 'select', 'Show countries and states/regions in a single list (best for sites with only one or two countries) or as separate selections (for sites with many active countries)', 'Single List|single\r\nSeparate Selections|split', '0', '9.000', '0', '0', '1', '1', null);
INSERT INTO `cw_config_items` VALUES ('238', '5', 'avalaraCompanyCode', 'Avalara - AvaTax Company Code', '', 'text', 'If using the Avalara AvaTax service, this is the company code for your store location.', '', '1', '13.500', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('239', '6', 'fedexAccessKey', 'FedEx Access Key', '', 'text', 'The Access Key for the FedEx shipping rates API', '', '1', '12.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('240', '6', 'fedexPassword', 'FedEx Password', '', 'text', 'The Password for the FedEx shipping rates API', '', '1', '13.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('241', '6', 'fedexAccountNumber', 'FedEx Account Number', '', 'text', 'The Account Number for the FedEx shipping rates API', '', '1', '14.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('242', '6', 'fedexMeterNumber', 'FedEx Meter Number', '', 'text', 'The Meter Number for the FedEx shipping rates API', '', '1', '15.000', '35', '5', '1', '0', null);
INSERT INTO `cw_config_items` VALUES ('243', '6', 'fedexUrl', 'FedEx Shipping URL', 'https://wsbeta.fedex.com:443/web-services', 'text', 'The URL for FedEx API Transactions', 'The URL for FedEx API Transactions', '1', '16.000', '35', '5', '1', '0', null);

-- ----------------------------
-- Table structure for `cw_countries`
-- ----------------------------
DROP TABLE IF EXISTS `cw_countries`;
CREATE TABLE `cw_countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(50) DEFAULT NULL,
  `country_code` varchar(50) DEFAULT NULL,
  `country_sort` float(11,3) DEFAULT '1.000',
  `country_archive` smallint(6) DEFAULT '0',
  `country_default_country` int(11) DEFAULT '0',
  PRIMARY KEY (`country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_countries
-- ----------------------------
INSERT INTO `cw_countries` VALUES ('1', 'United States', 'US', '1.000', '0', '1');
INSERT INTO `cw_countries` VALUES ('7', 'Belgium', 'BEL', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('21', 'Argentina', 'AR', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('23', 'Australia', 'AU', '1.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('38', 'Brazil', 'BR', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('45', 'Canada', 'CA', '1.000', '0', '0');
INSERT INTO `cw_countries` VALUES ('51', 'China', 'CN', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('75', 'France', 'FR', '1.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('80', 'Germany', 'DE', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('96', 'Ireland', 'IE', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('97', 'Israel', 'IL', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('98', 'Italy', 'IT', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('100', 'Japan', 'JP', '1.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('127', 'Mexico', 'MX', '1.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('139', 'New Zealand', 'NZ', '1.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('175', 'Spain', 'ES', '9.000', '1', '0');
INSERT INTO `cw_countries` VALUES ('198', 'United Kingdom', 'GB', '1.000', '1', '0');

-- ----------------------------
-- Table structure for `cw_credit_cards`
-- ----------------------------
DROP TABLE IF EXISTS `cw_credit_cards`;
CREATE TABLE `cw_credit_cards` (
  `creditcard_id` int(11) NOT NULL AUTO_INCREMENT,
  `creditcard_name` varchar(50) DEFAULT NULL,
  `creditcard_code` varchar(50) DEFAULT NULL,
  `creditcard_archive` smallint(6) DEFAULT '0',
  PRIMARY KEY (`creditcard_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_credit_cards
-- ----------------------------
INSERT INTO `cw_credit_cards` VALUES ('2', 'MasterCard', 'mc', '0');
INSERT INTO `cw_credit_cards` VALUES ('3', 'American Express', 'amex', '0');
INSERT INTO `cw_credit_cards` VALUES ('4', 'Discover', 'disc', '0');
INSERT INTO `cw_credit_cards` VALUES ('18', 'Visa', 'visa', '0');
INSERT INTO `cw_credit_cards` VALUES ('21', 'Diners Club', 'diners', '0');
INSERT INTO `cw_credit_cards` VALUES ('24', 'Maestro', 'maestro', '0');

-- ----------------------------
-- Table structure for `cw_customer_stateprov`
-- ----------------------------
DROP TABLE IF EXISTS `cw_customer_stateprov`;
CREATE TABLE `cw_customer_stateprov` (
  `customer_state_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_state_customer_id` varchar(50) NOT NULL,
  `customer_state_stateprov_id` int(11) DEFAULT NULL,
  `customer_state_destination` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`customer_state_id`),
  KEY `customer_state_customer_id_idx` (`customer_state_customer_id`),  
  KEY `customer_state_stateprov_id_idx` (`customer_state_stateprov_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_customer_stateprov
-- ----------------------------
INSERT INTO `cw_customer_stateprov` VALUES ('117', '1', '44', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('118', '1', '44', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('126', 'D3970516-11-10-06', '9', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('127', 'D3970516-11-10-06', '9', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('156', 'F55A7310-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('155', 'F540EA10-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('149', 'F45A2F10-25-09', '3', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('148', 'F45A2F10-25-09', '3', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('150', 'F52B2C10-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('154', 'F540EA10-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('152', 'F5393110-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('151', 'F52B2C10-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('153', 'F5393110-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('157', 'F55A7310-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('158', 'F5606110-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('159', 'F5606110-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('160', 'F5825210-25-09', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('161', 'F5825210-25-09', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('162', 'FB64CA10-25-09', '3', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('163', 'FB64CA10-25-09', '3', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('164', '127B8710-26-09', '9', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('165', '127B8710-26-09', '38', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('166', '139AAD10-26-09', '23', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('167', '139AAD10-26-09', '23', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('168', '17D14A10-26-09', '13', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('169', '17D14A10-26-09', '13', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('170', '56F28C10-01-11', '1', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('171', '56F28C10-01-11', '1', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('172', '5A8B1C10-01-11', '1', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('173', '5A8B1C10-01-11', '1', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('174', 'F2DCF310-09-11', '79', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('175', 'F2DCF310-09-11', '79', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('176', '1ED3DF10-10-11', '14', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('177', '1ED3DF10-10-11', '14', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('178', 'FCFC9810-29-11', '7', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('179', 'FCFC9810-29-11', '7', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('180', '4EBDBF10-21-12', '1', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('181', '4EBDBF10-21-12', '1', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('182', '67A0F910-21-12', '3', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('183', '67A0F910-21-12', '3', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('184', '69E8BF10-21-12', '28', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('185', '69E8BF10-21-12', '28', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('186', '149B4611-28-01', '1', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('187', '149B4611-28-01', '1', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('188', '0BE78A11-02-02', '10', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('189', '0BE78A11-02-02', '10', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('190', '26C95811-07-02', '78', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('191', '26C95811-07-02', '78', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('194', '96C46711-18-08', '48', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('195', '96C46711-18-08', '48', 'ShipTo');
INSERT INTO `cw_customer_stateprov` VALUES ('206', 'FC4E0311-07-11', '1', 'BillTo');
INSERT INTO `cw_customer_stateprov` VALUES ('207', 'FC4E0311-07-11', '1', 'ShipTo');

-- ----------------------------
-- Table structure for `cw_customer_types`
-- ----------------------------
DROP TABLE IF EXISTS `cw_customer_types`;
CREATE TABLE `cw_customer_types` (
  `customer_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_type_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`customer_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_customer_types
-- ----------------------------
INSERT INTO `cw_customer_types` VALUES ('1', 'Retail');
INSERT INTO `cw_customer_types` VALUES ('2', 'Wholesale');

-- ----------------------------
-- Table structure for `cw_customers`
-- ----------------------------
DROP TABLE IF EXISTS `cw_customers`;
CREATE TABLE `cw_customers` (
  `customer_id` varchar(50) NOT NULL,
  `customer_type_id` int(11) DEFAULT NULL,
  `customer_date_added` datetime DEFAULT NULL,
  `customer_date_modified` datetime DEFAULT NULL,
  `customer_first_name` varchar(255) DEFAULT NULL,
  `customer_last_name` varchar(255) DEFAULT NULL,
  `customer_company` varchar(255) DEFAULT NULL,
  `customer_address1` varchar(255) DEFAULT NULL,
  `customer_address2` varchar(255) DEFAULT NULL,
  `customer_city` varchar(255) DEFAULT NULL,
  `customer_zip` varchar(255) DEFAULT NULL,
  `customer_ship_name` varchar(255) DEFAULT NULL,
  `customer_ship_company` varchar(255) DEFAULT NULL,
  `customer_ship_country` varchar(255) DEFAULT NULL,
  `customer_ship_address1` varchar(255) DEFAULT NULL,
  `customer_ship_address2` varchar(255) DEFAULT NULL,
  `customer_ship_city` varchar(255) DEFAULT NULL,
  `customer_ship_zip` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_phone_mobile` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_username` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `customer_password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `customer_guest` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`customer_id`),
  KEY `customer_type_id_idx` (`customer_type_id`)  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_customers
-- ----------------------------
INSERT INTO `cw_customers` VALUES ('F45A2F10-25-09', '1', '2010-09-25 19:22:50', '2011-12-08 23:39:06', 'Bob', 'Buyer', 'Some Company', '4444 My St.', null, 'Sunny City', '30322', 'Bob Buyer', 'Some Company', null, '4444 My St.', null, 'Sunny City', '30322', '555-555-5555', null, 'bobbuyer@somebogusemail.com', 'test123', 'test123', null);
INSERT INTO `cw_customers` VALUES ('96C46711-18-08', '1', '2011-08-18 01:52:20', '2011-11-08 15:04:55', 'Suzie', 'Shopper', '9876', '56789 Soma Street', null, 'Littletown', '99999', 'Suzie Shopper', '9876', null, '56789 Somea Street', null, 'Littletown', '99999', '555-555-0965', '555-555-0965', 'suzshopper@bogusmail.com', 'test12', 'test12', null);
INSERT INTO `cw_customers` VALUES ('FC4E0311-07-11', '1', '2011-11-07 13:03:55', '2011-11-16 11:22:13', 'Wanda', 'Buymore', null, '1234 st', null, 'some town', '99999', 'Wanda Buymore', null, null, '1234 st', null, 'some town', '99999', '555-555-1234', '555-555-9999', 'buymore@bogusenmail.com', 'test1234', 'test1234', '0');

-- ----------------------------
-- Table structure for `cw_discount_amounts`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_amounts`;
CREATE TABLE `cw_discount_amounts` (
  `discount_amount_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_amount_discount_id` int(11) NOT NULL DEFAULT '0',
  `discount_amount_discount` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `discount_amount_minimum_qty` int(11) DEFAULT NULL,
  `discount_amount_minimum_amount` decimal(20,4) DEFAULT NULL,
  `discount_amount_rate_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`discount_amount_id`),
  KEY `discount_amount_discount_id_idx` (`discount_amount_discount_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_amounts
-- ----------------------------
INSERT INTO `cw_discount_amounts` VALUES ('35', '64', '10.0000', '0', '0.0000', '0');
INSERT INTO `cw_discount_amounts` VALUES ('36', '65', '15.0000', '0', '0.0000', '0');
INSERT INTO `cw_discount_amounts` VALUES ('37', '66', '10.0000', '0', '0.0000', '0');

-- ----------------------------
-- Table structure for `cw_discount_apply_types`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_apply_types`;
CREATE TABLE `cw_discount_apply_types` (
  `discount_apply_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_apply_type_description` varchar(100) NOT NULL,
  `discount_apply_type_archive` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`discount_apply_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_apply_types
-- ----------------------------
INSERT INTO `cw_discount_apply_types` VALUES ('1', 'Purchase', '0');

-- ----------------------------
-- Table structure for `cw_discount_categories`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_categories`;
CREATE TABLE `cw_discount_categories` (
  `discount_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount2category_discount_id` int(11) NOT NULL DEFAULT '0',
  `discount2category_category_id` int(11) NOT NULL DEFAULT '0',
  `discount_category_type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`discount_category_id`),
  KEY `discount2category_discount_id_idx` (`discount2category_discount_id`),  
  KEY `discount2category_category_id_idx` (`discount2category_category_id`)  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_categories
-- ----------------------------
INSERT INTO `cw_discount_categories` VALUES ('120', '65', '70', '2');
INSERT INTO `cw_discount_categories` VALUES ('124', '72', '57', '1');
INSERT INTO `cw_discount_categories` VALUES ('125', '72', '79', '2');
INSERT INTO `cw_discount_categories` VALUES ('111', '70', '56', '1');
INSERT INTO `cw_discount_categories` VALUES ('116', '70', '73', '2');
INSERT INTO `cw_discount_categories` VALUES ('115', '70', '70', '2');
INSERT INTO `cw_discount_categories` VALUES ('114', '70', '57', '1');
INSERT INTO `cw_discount_categories` VALUES ('113', '70', '55', '1');
INSERT INTO `cw_discount_categories` VALUES ('112', '70', '80', '2');
INSERT INTO `cw_discount_categories` VALUES ('121', '65', '73', '2');
INSERT INTO `cw_discount_categories` VALUES ('126', '73', '55', '1');
INSERT INTO `cw_discount_categories` VALUES ('127', '78', '57', '1');

-- ----------------------------
-- Table structure for `cw_discount_products`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_products`;
CREATE TABLE `cw_discount_products` (
  `discount_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount2product_discount_id` int(11) NOT NULL DEFAULT '0',
  `discount2product_product_id` int(11) NOT NULL DEFAULT '0',
  `discount_product_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`discount_product_id`),
  KEY `discount2product_discount_id_idx` (`discount2product_discount_id`),  
  KEY `discount2product_product_id_idx` (`discount2product_product_id`)  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_products
-- ----------------------------
INSERT INTO `cw_discount_products` VALUES ('61', '70', '110', '1');
INSERT INTO `cw_discount_products` VALUES ('50', '73', '94', '1');
INSERT INTO `cw_discount_products` VALUES ('49', '73', '103', '1');
INSERT INTO `cw_discount_products` VALUES ('60', '70', '94', '1');
INSERT INTO `cw_discount_products` VALUES ('48', '73', '104', '1');
INSERT INTO `cw_discount_products` VALUES ('63', '70', '95', '1');
INSERT INTO `cw_discount_products` VALUES ('59', '70', '109', '1');
INSERT INTO `cw_discount_products` VALUES ('47', '73', '95', '1');
INSERT INTO `cw_discount_products` VALUES ('62', '70', '93', '1');
INSERT INTO `cw_discount_products` VALUES ('34', '70', '112', '1');
INSERT INTO `cw_discount_products` VALUES ('46', '73', '109', '1');
INSERT INTO `cw_discount_products` VALUES ('45', '73', '105', '1');
INSERT INTO `cw_discount_products` VALUES ('44', '73', '99', '1');
INSERT INTO `cw_discount_products` VALUES ('38', '70', '107', '1');
INSERT INTO `cw_discount_products` VALUES ('39', '70', '111', '1');
INSERT INTO `cw_discount_products` VALUES ('40', '70', '102', '1');
INSERT INTO `cw_discount_products` VALUES ('41', '70', '96', '1');
INSERT INTO `cw_discount_products` VALUES ('42', '70', '103', '1');
INSERT INTO `cw_discount_products` VALUES ('43', '70', '106', '1');
INSERT INTO `cw_discount_products` VALUES ('51', '73', '100', '1');
INSERT INTO `cw_discount_products` VALUES ('70', '78', '109', '1');
INSERT INTO `cw_discount_products` VALUES ('53', '73', '96', '1');
INSERT INTO `cw_discount_products` VALUES ('54', '73', '101', '1');
INSERT INTO `cw_discount_products` VALUES ('56', '73', '108', '1');
INSERT INTO `cw_discount_products` VALUES ('57', '73', '93', '1');
INSERT INTO `cw_discount_products` VALUES ('58', '73', '112', '1');
INSERT INTO `cw_discount_products` VALUES ('65', '70', '104', '1');
INSERT INTO `cw_discount_products` VALUES ('71', '78', '111', '1');
INSERT INTO `cw_discount_products` VALUES ('72', '78', '110', '1');
INSERT INTO `cw_discount_products` VALUES ('73', '78', '112', '1');
INSERT INTO `cw_discount_products` VALUES ('74', '78', '103', '1');
INSERT INTO `cw_discount_products` VALUES ('75', '82', '103', '1');

-- ----------------------------
-- Table structure for `cw_discount_skus`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_skus`;
CREATE TABLE `cw_discount_skus` (
  `discount_sku_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount2sku_discount_id` int(11) NOT NULL DEFAULT '0',
  `discount2sku_sku_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`discount_sku_id`),
  KEY `discount2sku_discount_id_idx` (`discount2sku_discount_id`),  
  KEY `discount2sku_sku_id_idx` (`discount2sku_sku_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_skus
-- ----------------------------
INSERT INTO `cw_discount_skus` VALUES ('139', '70', '176');
INSERT INTO `cw_discount_skus` VALUES ('141', '70', '244');
INSERT INTO `cw_discount_skus` VALUES ('142', '70', '245');
INSERT INTO `cw_discount_skus` VALUES ('138', '70', '218');
INSERT INTO `cw_discount_skus` VALUES ('137', '70', '233');
INSERT INTO `cw_discount_skus` VALUES ('135', '70', '232');

-- ----------------------------
-- Table structure for `cw_discount_types`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_types`;
CREATE TABLE `cw_discount_types` (
  `discount_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_type` varchar(100) DEFAULT NULL,
  `discount_type_description` varchar(100) DEFAULT NULL,
  `discount_type_archive` tinyint(1) DEFAULT '0',
  `discount_type_order` int(2) DEFAULT NULL,
  PRIMARY KEY (`discount_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_types
-- ----------------------------
INSERT INTO `cw_discount_types` VALUES ('1', 'sku_cost', 'Product/SKU Price', '0', '10');
INSERT INTO `cw_discount_types` VALUES ('2', 'sku_ship', 'Product/SKU Shipping', '0', '20');
INSERT INTO `cw_discount_types` VALUES ('3', 'order_total', 'Order Total', '0', '30');
INSERT INTO `cw_discount_types` VALUES ('4', 'ship_total', 'Shipping Total', '0', '40');

-- ----------------------------
-- Table structure for `cw_discount_usage`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discount_usage`;
CREATE TABLE `cw_discount_usage` (
  `discount_usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_usage_customer_id` varchar(50) DEFAULT NULL,
  `discount_usage_datetime` datetime DEFAULT NULL,
  `discount_usage_order_id` varchar(50) DEFAULT NULL,
  `discount_usage_discount_name` varchar(255) DEFAULT NULL,
  `discount_usage_discount_description` text,
  `discount_usage_promocode` varchar(255) DEFAULT NULL,
  `discount_usage_discount_id` int(11) DEFAULT '0',
  PRIMARY KEY (`discount_usage_id`),
  KEY `discount_usage_order_id_idx` (`discount_usage_order_id`)  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discount_usage
-- ----------------------------
INSERT INTO `cw_discount_usage` VALUES ('94', 'F45A2F10-25-09', '2011-12-08 23:39:19', '1112082339-F45A', '', '', '', '84');
INSERT INTO `cw_discount_usage` VALUES ('95', 'F45A2F10-25-09', '2011-12-08 23:39:19', '1112082339-F45A', '10% Off over $100', 'Save 10 percent when you spend $100 or more', '', '71');
INSERT INTO `cw_discount_usage` VALUES ('96', 'F45A2F10-25-09', '2011-12-08 23:39:19', '1112082339-F45A', 'Free Shipping', 'Free shipping on all items for the first 100 customers, when you spend $75 or more', 'FREESHIP', '70');

-- ----------------------------
-- Table structure for `cw_discounts`
-- ----------------------------
DROP TABLE IF EXISTS `cw_discounts`;
CREATE TABLE `cw_discounts` (
  `discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_merchant_id` varchar(50) DEFAULT NULL,
  `discount_name` varchar(100) DEFAULT NULL,
  `discount_amount` float(12,2) DEFAULT NULL,
  `discount_calc` varchar(99) DEFAULT NULL,
  `discount_description` text,
  `discount_show_description` tinyint(1) DEFAULT '1',
  `discount_type` varchar(55) DEFAULT NULL,
  `discount_promotional_code` varchar(255) DEFAULT NULL,
  `discount_start_date` datetime DEFAULT NULL,
  `discount_end_date` datetime DEFAULT NULL,
  `discount_limit` int(11) DEFAULT '0',
  `discount_customer_limit` int(11) DEFAULT '0',
  `discount_global` tinyint(1) DEFAULT NULL,
  `discount_exclusive` tinyint(1) DEFAULT '0',
  `discount_priority` bigint(11) DEFAULT '0',
  `discount_archive` tinyint(4) DEFAULT '0',
  `discount_filter_customer_type` tinyint(1) DEFAULT '0',
  `discount_customer_type` varchar(100) DEFAULT NULL,
  `discount_filter_customer_id` tinyint(1) DEFAULT '0',
  `discount_customer_id` text,
  `discount_filter_cart_total` tinyint(1) DEFAULT '0',
  `discount_cart_total_max` float(12,2) DEFAULT '0.00',
  `discount_cart_total_min` float(12,2) DEFAULT '0.00',
  `discount_filter_item_qty` tinyint(1) DEFAULT '0',
  `discount_item_qty_min` int(8) DEFAULT '0',
  `discount_item_qty_max` int(8) DEFAULT '0',
  `discount_filter_cart_qty` tinyint(1) DEFAULT '0',
  `discount_cart_qty_min` int(8) DEFAULT '0',
  `discount_cart_qty_max` int(8) DEFAULT '0',
  `discount_association_method` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`discount_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_discounts
-- ----------------------------
INSERT INTO `cw_discounts` VALUES ('64', 'save10-spring sale', 'Save $10 when you spend $100 or more', '22.34', 'fixed', 'This is a great discount. Don\'t miss out!', '1', 'sku_cost', 'SAVE10', '2011-06-02 00:00:00', '2011-06-16 00:00:00', '0', '1', '1', null, null, '1', '0', null, '0', null, '0', '0.00', '0.00', '0', null, null, '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('66', 'GlobalDiscount001', 'Global Discount', '10.00', 'percent', '10% OFF everything!', '1', 'sku_cost', '1010', '2006-11-03 00:00:00', null, '0', '0', '1', '0', '1', '1', '0', '0', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('70', 'fallfreeship', 'Free Shipping', '100.00', 'percent', 'Free shipping on all items for the first 100 customers, when you spend $75 or more', '1', 'ship_total', 'FREESHIP', '2011-06-19 00:00:00', null, '100', '1', '0', '0', '0', '0', '0', '0', '0', null, '1', '0.00', '75.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('71', 'Save10', '10% Off over $100', '10.00', 'percent', 'Save 10 percent when you spend $100 or more', '1', 'order_total', null, '2011-06-02 00:00:00', null, '0', '0', '1', '1', '5', '0', '0', '0', '0', null, '1', '1000.00', '100.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('73', 'summerFreeShipCategories', 'Free Shipping on all Clothing and Electronics', '100.00', 'percent', 'Get Free Shipping on all Clothing and Electronics items for a limited time!', '1', 'sku_ship', null, '2011-06-03 00:00:00', null, '0', '0', '0', '1', '10', '1', '0', '0', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'categories');
INSERT INTO `cw_discounts` VALUES ('77', 'GlobalDiscount002', 'Global Ship Discount', '100.00', 'percent', 'Free shipping', '1', 'sku_ship', null, '2011-07-18 00:00:00', null, '0', '0', '1', '0', '0', '1', '0', '0', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('78', '50off', '$50 OFF', '50.00', 'fixed', 'save $50 on any order', '1', 'order_total', '5050', '2011-03-01 00:00:00', '2011-03-30 00:00:00', '0', '0', '1', '0', '0', '1', '0', '0', '0', null, '0', '0.00', '860.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('79', 'GlobalDiscount003', 'Global Discount 25% all orders', '25.00', 'percent', '25% off all orders', '1', 'sku_cost', null, '2011-07-19 00:00:00', null, '0', '0', '1', '0', '0', '1', '0', '0', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('80', '20OFFShipping', '$20 off shipping', '20.00', 'fixed', 'Save up to $20 on shipping', '1', 'ship_total', 'ship20', '2011-07-21 00:00:00', null, '0', '0', '1', '0', '0', '1', '0', '0', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('82', 'QuantityDiscount', 'Quantity Discount', '10.00', 'percent', 'Get discount if you buy 2 or more.', '1', 'sku_cost', null, '2011-08-16 00:00:00', null, '0', '0', '0', '0', '0', '1', '0', '0', '0', null, '0', '0.00', '0.00', '1', '2', '0', '0', '0', '0', 'products');
INSERT INTO `cw_discounts` VALUES ('83', 'CustTypeDiscount', 'Wholesale Discount', '10.00', 'percent', 'Discount placed on products for Wholesale Customers', '0', 'sku_cost', null, '2011-09-12 00:00:00', null, '0', '0', '1', '0', '0', '1', '0', '2', '0', null, '0', '0.00', '0.00', '0', '0', '0', '0', '0', '0', 'products');
-- ----------------------------
-- Table structure for `cw_downloads`
-- ----------------------------
DROP TABLE IF EXISTS `cw_downloads`;
CREATE TABLE `cw_downloads` (
  `dl_id` int(12) NOT NULL AUTO_INCREMENT,
  `dl_sku_id` int(12) DEFAULT NULL,
  `dl_customer_id` varchar(255) DEFAULT NULL,
  `dl_timestamp` datetime DEFAULT NULL,
  `dl_file` varchar(255) DEFAULT NULL,
  `dl_version` varchar(255) DEFAULT NULL,
  `dl_remote_addr` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`dl_id`),
  KEY `dl_sku_id_idx` (`dl_sku_id`),  
  KEY `dl_customer_id_idx` (`dl_customer_id`)  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for `cw_image_types`
-- ----------------------------
DROP TABLE IF EXISTS `cw_image_types`;
CREATE TABLE `cw_image_types` (
  `imagetype_id` int(11) NOT NULL AUTO_INCREMENT,
  `imagetype_name` varchar(100) DEFAULT NULL,
  `imagetype_sortorder` float(11,3) DEFAULT '1.000',
  `imagetype_folder` varchar(50) DEFAULT NULL,
  `imagetype_max_width` int(10) DEFAULT NULL,
  `imagetype_max_height` int(10) DEFAULT NULL,
  `imagetype_crop_width` int(10) DEFAULT NULL,
  `imagetype_crop_height` int(10) DEFAULT NULL,
  `imagetype_upload_group` int(2) DEFAULT '1',
  `imagetype_user_edit` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`imagetype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_image_types
-- ----------------------------
INSERT INTO `cw_image_types` VALUES ('1', 'Thumbnail', '1.000', 'product_thumb', '160', '160', '0', '0', '1', '1');
INSERT INTO `cw_image_types` VALUES ('2', 'Details', '2.000', 'product_full', '420', '420', '0', '0', '1', '1');
INSERT INTO `cw_image_types` VALUES ('3', 'Zoom', '3.000', 'product_expanded', '680', '680', '0', '0', '1', '1');
INSERT INTO `cw_image_types` VALUES ('4', 'Cart', '4.000', 'product_small', '80', '80', '0', '0', '1', '1');
INSERT INTO `cw_image_types` VALUES ('5', 'SquareThumb', '5.000', 'product_thumb_square', '160', '160', '50', '50', '1', '0');

-- ----------------------------
-- Table structure for `cw_option_types`
-- ----------------------------
DROP TABLE IF EXISTS `cw_option_types`;
CREATE TABLE `cw_option_types` (
  `optiontype_id` int(11) NOT NULL AUTO_INCREMENT,
  `optiontype_required` tinyint(4) DEFAULT '1',
  `optiontype_name` varchar(75) DEFAULT NULL,
  `optiontype_archive` smallint(6) DEFAULT '0',
  `optiontype_deleted` smallint(1) DEFAULT '0',
  `optiontype_sort` float(11,3) DEFAULT '1.000',
  `optiontype_text` text,
  PRIMARY KEY (`optiontype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_option_types
-- ----------------------------
INSERT INTO `cw_option_types` VALUES ('29', '1', 'Size', '0', '0', '55.000', 'Choose the size that suits you best. Note: inseam sizes may vary by brand.');
INSERT INTO `cw_option_types` VALUES ('30', '1', 'Color', '0', '0', '99.000', 'This item comes in a variety of eye-pleasing shades. Choose the one that you like best!');
INSERT INTO `cw_option_types` VALUES ('33', '1', 'CPU', '0', '0', '5.000', 'Central Processing Unit');
INSERT INTO `cw_option_types` VALUES ('34', '1', 'RAM', '0', '0', '25.000', 'Random Access Memory');
INSERT INTO `cw_option_types` VALUES ('35', '1', 'Primary Storage', '0', '0', '35.000', 'Hard Drive Speed and Capacity');
INSERT INTO `cw_option_types` VALUES ('36', '1', 'Optical Drives', '0', '0', '40.000', 'CD/DVD disk drives');
INSERT INTO `cw_option_types` VALUES ('37', '1', 'Graphics', '0', '0', '92.000', 'Video Graphics Card');
INSERT INTO `cw_option_types` VALUES ('38', '1', 'Connectivity', '0', '0', '98.000', 'Get Wired!');
INSERT INTO `cw_option_types` VALUES ('39', '1', 'Dimensions', '0', '0', '3.000', 'Choose the size that will work best');
INSERT INTO `cw_option_types` VALUES ('40', '1', 'HP', '0', '0', '1.000', 'Product rated horsepower');
INSERT INTO `cw_option_types` VALUES ('43', '1', 'Gender', '0', '0', '0.000', 'What Gender is best for this product');

-- ----------------------------
-- Table structure for `cw_options`
-- ----------------------------
DROP TABLE IF EXISTS `cw_options`;
CREATE TABLE `cw_options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_type_id` int(11) DEFAULT '0',
  `option_name` varchar(50) DEFAULT NULL,
  `option_sort` float(11,3) DEFAULT '1.000',
  `option_archive` smallint(6) DEFAULT '0',
  `option_text` text,
  PRIMARY KEY (`option_id`),
  KEY `option_type_id_idx` (`option_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_options
-- ----------------------------
INSERT INTO `cw_options` VALUES ('108', '29', 'Small', '2.100', '0', 'Men\'s small');
INSERT INTO `cw_options` VALUES ('109', '29', 'Medium', '2.200', '0', 'Men\'s medium');
INSERT INTO `cw_options` VALUES ('110', '29', 'Large', '2.300', '0', 'Men\'s large');
INSERT INTO `cw_options` VALUES ('111', '29', 'X-Large', '2.400', '0', 'Men\'s extra large');
INSERT INTO `cw_options` VALUES ('112', '30', 'Green', '1.200', '0', 'Green like the grass in spring.');
INSERT INTO `cw_options` VALUES ('113', '30', 'Blue', '1.100', '0', 'Our classic blue.');
INSERT INTO `cw_options` VALUES ('114', '30', 'Yellow', '1.400', '0', 'Mellow yellow is a constant best seller.');
INSERT INTO `cw_options` VALUES ('115', '30', 'Red', '1.300', '0', 'Red means stop - and fun!');
INSERT INTO `cw_options` VALUES ('116', '30', 'Medium Blue', '2.200', '0', 'A little lighter than the classic blue.');
INSERT INTO `cw_options` VALUES ('117', '30', 'Indigo', '2.100', '0', 'Get deep with our unique shade of indigo blue.');
INSERT INTO `cw_options` VALUES ('118', '30', 'Stone Washed Light Blue', '2.300', '0', 'Classic stone wash.');
INSERT INTO `cw_options` VALUES ('119', '29', '34x30', '1.100', '0', '34 inch waist with 30 inch inseam.');
INSERT INTO `cw_options` VALUES ('120', '29', '36x30', '1.300', '0', '36 inch waist with 30 inch inseam.');
INSERT INTO `cw_options` VALUES ('121', '29', '38x30', '1.500', '0', '38 inch waist with 30 inch inseam.');
INSERT INTO `cw_options` VALUES ('127', '33', 'Core Duo E7500', '1.000', '0', 'CoreTM 2 Duo E7500 Processor');
INSERT INTO `cw_options` VALUES ('128', '33', 'Core 2 Duo E7500', '1.000', '0', 'CoreTM 2 Duo E7500 with VT (2.93GHz, 3M, 1066MHz FSB)');
INSERT INTO `cw_options` VALUES ('129', '33', 'Core  i5-750', '1.000', '0', 'CoreTM  i5-750 w/VT Processor');
INSERT INTO `cw_options` VALUES ('130', '34', '4GB', '1.000', '0', '4GB Memory (2DIMMs)');
INSERT INTO `cw_options` VALUES ('131', '34', '6GB', '1.000', '0', '6GB Memory (2DIMMs)');
INSERT INTO `cw_options` VALUES ('132', '34', '8GB', '1.000', '0', '8GB Memory (2DIMMs)');
INSERT INTO `cw_options` VALUES ('133', '35', '250 GB', '1.000', '0', '250 GB 7200RPM SATA Hard Drive');
INSERT INTO `cw_options` VALUES ('134', '35', '500 GB', '1.000', '0', '500 GB 7200RPM SATA Hard Drive');
INSERT INTO `cw_options` VALUES ('135', '35', '1 TB', '1.000', '0', '1 TB 7200RPM SATA Hard Drive');
INSERT INTO `cw_options` VALUES ('136', '36', '16X DVD', '1.000', '0', '<p>16X DVD-ROM Drive</p>');
INSERT INTO `cw_options` VALUES ('137', '36', '16X CD/DVD burner', '15.000', '0', '<p>16X CD/DVD burner (DVD+/-RW) with double layer write capability</p>');
INSERT INTO `cw_options` VALUES ('138', '36', '16X CD/DVD/BlueRay', '99.000', '0', '<p>16X CD/DVD/BlueRay burner</p>');
INSERT INTO `cw_options` VALUES ('139', '37', 'GMA X4500', '1.000', '0', 'Integrated GMA X4500');
INSERT INTO `cw_options` VALUES ('140', '37', '512MB', '1.000', '0', '512MB  G310* (DVI, HDMI, VGA)');
INSERT INTO `cw_options` VALUES ('141', '38', 'Ethernet', '1.000', '0', '10/100/1000 Ethernet LAN on system board');
INSERT INTO `cw_options` VALUES ('142', '38', 'Wireless', '1.000', '0', 'Wireless WLAN 11n PCIe Card');
INSERT INTO `cw_options` VALUES ('143', '30', 'Hot Pink', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('144', '30', 'Mint Green', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('145', '30', 'Orange', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('146', '39', '24X36', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('147', '39', '36X48', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('148', '29', 'Twin', '1.000', '0', 'For Twin Sized Beds');
INSERT INTO `cw_options` VALUES ('149', '29', 'Full', '2.000', '0', 'For Full Sized Beds');
INSERT INTO `cw_options` VALUES ('150', '29', 'Queen', '3.000', '0', 'For Queen Sized Beds');
INSERT INTO `cw_options` VALUES ('151', '29', 'King', '4.000', '0', 'For King Sized Beds');
INSERT INTO `cw_options` VALUES ('152', '30', 'Purple', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('153', '29', '34X32', '1.200', '0', '');
INSERT INTO `cw_options` VALUES ('154', '29', '36X32', '1.400', '0', '');
INSERT INTO `cw_options` VALUES ('155', '29', '38X32', '1.600', '0', '');
INSERT INTO `cw_options` VALUES ('156', '40', '.24 HP', '1.000', '0', '<p>One Quarter Horse Power</p>');
INSERT INTO `cw_options` VALUES ('157', '40', '.5 HP', '2.000', '0', '<p>One Half Horsepower</p>');
INSERT INTO `cw_options` VALUES ('158', '40', '3.5 HP', '3.000', '0', '<p>3 and a half horsepower</p>');
INSERT INTO `cw_options` VALUES ('159', '40', '6 HP', '4.000', '0', '<p>6 horsepower</p>');
INSERT INTO `cw_options` VALUES ('160', '40', '12 HP', '5.000', '0', '<p>12 horsepower</p>');
INSERT INTO `cw_options` VALUES ('166', '39', '33X38', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('168', '43', 'Men\'s', '1.000', '0', '');
INSERT INTO `cw_options` VALUES ('169', '43', 'Women\'s', '1.000', '0', '');

-- ----------------------------
-- Table structure for `cw_order_payments`
-- ----------------------------
DROP TABLE IF EXISTS `cw_order_payments`;
CREATE TABLE `cw_order_payments` (
  `payment_id` int(12) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `payment_amount` float(12,2) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `payment_trans_id` varchar(255) DEFAULT NULL,
  `payment_trans_response` text,
  `payment_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_ID`),
  KEY `order_id_idx` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_order_payments
-- ----------------------------
INSERT INTO `cw_order_payments` VALUES ('1', '1111161120-F45A', 'In-Store Account', 'account', '2027.50', 'approved', 'ACCT:F45A2F10-25-09', 'Order charged to account', '2011-11-16 11:20:40');
INSERT INTO `cw_order_payments` VALUES ('2', '1111161122-FC4E', 'In-Store Account', 'account', '1041.21', 'approved', 'ACCT:FC4E0311-07-11', 'Order charged to account', '2011-11-16 11:22:23');
INSERT INTO `cw_order_payments` VALUES ('3', '1112082339-F45A', 'In-Store Account', 'account', '433.50', 'approved', 'ACCT:F45A2F10-25-09', 'Order charged to account', '2011-12-08 23:39:19');

-- ----------------------------
-- Table structure for `cw_order_sku_data`
-- ----------------------------
DROP TABLE IF EXISTS `cw_order_sku_data`;
CREATE TABLE `cw_order_sku_data` (
  `data_id` int(11) NOT NULL AUTO_INCREMENT,
  `data_sku_id` int(11) DEFAULT NULL,
  `data_cart_id` bigint(20) DEFAULT NULL,
  `data_content` text,
  `data_date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_sku_id_idx` (`data_sku_id`),
  KEY `data_cart_id_idx` (`data_cart_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `cw_order_skus`
-- ----------------------------
DROP TABLE IF EXISTS `cw_order_skus`;
CREATE TABLE `cw_order_skus` (
  `ordersku_id` int(11) NOT NULL AUTO_INCREMENT,
  `ordersku_order_id` varchar(255) NOT NULL,
  `ordersku_sku` int(11) NOT NULL DEFAULT '0',
  `ordersku_quantity` int(11) DEFAULT NULL,
  `ordersku_unit_price` double DEFAULT NULL,
  `ordersku_sku_total` double DEFAULT '0',
  `ordersku_tax_rate` double DEFAULT '0',
  `ordersku_taxrate_id` int(11) DEFAULT NULL,
  `ordersku_unique_id` varchar(255) DEFAULT NULL,
  `ordersku_customval` varchar(255) DEFAULT NULL,
  `ordersku_discount_amount` double DEFAULT NULL,
  PRIMARY KEY (`ordersku_id`),
  KEY `ordersku_order_id_idx` (`ordersku_order_id`),
  KEY `ordersku_unique_id_idx` (`ordersku_unique_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_order_skus
-- ----------------------------
INSERT INTO `cw_order_skus` VALUES ('1', '1111161120-F45A', '188', '1', '1999', '1999', '0', null, '188', '', '0');
INSERT INTO `cw_order_skus` VALUES ('2', '1111161122-FC4E', '240', '1', '899', '899', '0', null, '240', '', '0');
INSERT INTO `cw_order_skus` VALUES ('3', '1111161122-FC4E', '232', '1', '125', '125', '0', null, '232', '', '0');
INSERT INTO `cw_order_skus` VALUES ('4', '1112082339-F45A', '230', '1', '500', '450', '0', null, '230', '', '50');

-- ----------------------------
-- Table structure for `cw_order_status`
-- ----------------------------
DROP TABLE IF EXISTS `cw_order_status`;
CREATE TABLE `cw_order_status` (
  `shipstatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipstatus_name` varchar(70) DEFAULT NULL,
  `shipstatus_sort` float(11,3) DEFAULT '1.000',
  PRIMARY KEY (`shipstatus_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_order_status
-- ----------------------------
INSERT INTO `cw_order_status` VALUES ('1', 'Pending', '1.000');
INSERT INTO `cw_order_status` VALUES ('2', 'Balance Due', '2.000');
INSERT INTO `cw_order_status` VALUES ('3', 'Paid in Full', '3.000');
INSERT INTO `cw_order_status` VALUES ('4', 'Shipped', '4.000');
INSERT INTO `cw_order_status` VALUES ('5', 'Cancelled', '5.000');
INSERT INTO `cw_order_status` VALUES ('6', 'Returned', '6.000');

-- ----------------------------
-- Table structure for `cw_orders`
-- ----------------------------
DROP TABLE IF EXISTS `cw_orders`;
CREATE TABLE `cw_orders` (
  `order_id` varchar(75) NOT NULL,
  `order_date` datetime DEFAULT NULL,
  `order_status` int(11) DEFAULT '0',
  `order_customer_id` varchar(50) NOT NULL,
  `order_checkout_type` varchar(20) DEFAULT NULL,
  `order_tax` double DEFAULT '0',
  `order_shipping` double DEFAULT '0',
  `order_shipping_tax` double DEFAULT '0',
  `order_total` double DEFAULT '0',
  `order_ship_method_id` int(11) DEFAULT '0',
  `order_ship_date` datetime DEFAULT NULL,
  `order_ship_tracking_id` varchar(100) DEFAULT NULL,
  `order_ship_name` text,
  `order_company` varchar(255) DEFAULT NULL,
  `order_address1` varchar(255) DEFAULT NULL,
  `order_address2` varchar(255) DEFAULT NULL,
  `order_city` varchar(255) DEFAULT NULL,
  `order_state` varchar(50) DEFAULT NULL,
  `order_zip` varchar(50) DEFAULT NULL,
  `order_country` varchar(75) DEFAULT NULL,
  `order_notes` text,
  `order_actual_ship_charge` double DEFAULT '0',
  `order_comments` varchar(255) DEFAULT NULL,
  `order_discount_total` double DEFAULT '0',
  `order_ship_discount_total` double DEFAULT NULL,
  `order_return_date` date DEFAULT NULL,
  `order_return_amount` double DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `order_customer_id_idx` (`order_customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_orders
-- ----------------------------
INSERT INTO `cw_orders` VALUES ('1111161120-F45A', '2011-11-16 11:20:40', '3', 'F45A2F10-25-09', 'account', '0', '28.5', '0', '2027.5', '101', null, null, 'Bob Buyer', 'Some Company', '4444 My St.', '', 'Sunny City', 'Arizona', '30322', 'United States', null, '0', '', '0', '0', null, null);
INSERT INTO `cw_orders` VALUES ('1111161122-FC4E', '2011-11-16 11:22:23', '3', 'FC4E0311-07-11', 'account', '0', '17.21', '0', '1041.21', '79', null, null, 'Wanda Buymore', '', '1234 st', '', 'some town', 'Alabama', '99999', 'United States', null, '0', '', '0', '0', null, null);
INSERT INTO `cw_orders` VALUES ('1112082339-F45A', '2011-12-08 23:39:19', '3', 'F45A2F10-25-09', 'account', '0', '28.5', '0', '433.5', '101', null, null, 'Bob Buyer', 'Some Company', '4444 My St.', '', 'Sunny City', 'Arizona', '30322', 'United States', null, '0', '', '95', '0', null, null);

-- ----------------------------
-- Table structure for `cw_product_categories_primary`
-- ----------------------------
DROP TABLE IF EXISTS `cw_product_categories_primary`;
CREATE TABLE `cw_product_categories_primary` (
  `product2category_id` int(11) NOT NULL AUTO_INCREMENT,
  `product2category_product_id` int(11) DEFAULT '0',
  `product2category_category_id` int(11) DEFAULT '0',
  PRIMARY KEY (`product2category_id`),
  KEY `product2category_product_id_idx` (`product2category_product_id`),
  KEY `product2category_category_id_idx` (`product2category_category_id`)  
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_product_categories_primary
-- ----------------------------
INSERT INTO `cw_product_categories_primary` VALUES ('462', '102', '56');
INSERT INTO `cw_product_categories_primary` VALUES ('384', '95', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('380', '96', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('532', '93', '54');
INSERT INTO `cw_product_categories_primary` VALUES ('485', '103', '56');
INSERT INTO `cw_product_categories_primary` VALUES ('498', '104', '56');
INSERT INTO `cw_product_categories_primary` VALUES ('528', '99', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('524', '100', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('443', '101', '56');
INSERT INTO `cw_product_categories_primary` VALUES ('521', '105', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('531', '106', '54');
INSERT INTO `cw_product_categories_primary` VALUES ('419', '107', '54');
INSERT INTO `cw_product_categories_primary` VALUES ('488', '108', '55');
INSERT INTO `cw_product_categories_primary` VALUES ('511', '110', '57');
INSERT INTO `cw_product_categories_primary` VALUES ('438', '111', '57');
INSERT INTO `cw_product_categories_primary` VALUES ('439', '112', '57');
INSERT INTO `cw_product_categories_primary` VALUES ('525', '94', '54');
INSERT INTO `cw_product_categories_primary` VALUES ('530', '109', '55');

-- ----------------------------
-- Table structure for `cw_product_categories_secondary`
-- ----------------------------
DROP TABLE IF EXISTS `cw_product_categories_secondary`;
CREATE TABLE `cw_product_categories_secondary` (
  `product2secondary_id` int(11) NOT NULL AUTO_INCREMENT,
  `product2secondary_product_id` int(11) DEFAULT '0',
  `product2secondary_secondary_id` int(11) DEFAULT '0',
  PRIMARY KEY (`product2secondary_id`),
  KEY `product2secondary_product_id_idx` (`product2secondary_product_id`),
  KEY `product2secondary_secondary_id_idx` (`product2secondary_secondary_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_product_categories_secondary
-- ----------------------------
INSERT INTO `cw_product_categories_secondary` VALUES ('940', '103', '78');
INSERT INTO `cw_product_categories_secondary` VALUES ('984', '94', '71');
INSERT INTO `cw_product_categories_secondary` VALUES ('828', '95', '72');
INSERT INTO `cw_product_categories_secondary` VALUES ('823', '96', '73');
INSERT INTO `cw_product_categories_secondary` VALUES ('913', '102', '77');
INSERT INTO `cw_product_categories_secondary` VALUES ('991', '93', '70');
INSERT INTO `cw_product_categories_secondary` VALUES ('956', '104', '77');
INSERT INTO `cw_product_categories_secondary` VALUES ('987', '99', '74');
INSERT INTO `cw_product_categories_secondary` VALUES ('892', '101', '77');
INSERT INTO `cw_product_categories_secondary` VALUES ('983', '100', '75');
INSERT INTO `cw_product_categories_secondary` VALUES ('980', '105', '76');
INSERT INTO `cw_product_categories_secondary` VALUES ('990', '106', '70');
INSERT INTO `cw_product_categories_secondary` VALUES ('868', '107', '71');
INSERT INTO `cw_product_categories_secondary` VALUES ('943', '108', '73');
INSERT INTO `cw_product_categories_secondary` VALUES ('970', '110', '79');
INSERT INTO `cw_product_categories_secondary` VALUES ('887', '111', '79');
INSERT INTO `cw_product_categories_secondary` VALUES ('888', '112', '80');
INSERT INTO `cw_product_categories_secondary` VALUES ('989', '109', '72');

-- ----------------------------
-- Table structure for `cw_product_images`
-- ----------------------------
DROP TABLE IF EXISTS `cw_product_images`;
CREATE TABLE `cw_product_images` (
  `product_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_image_product_id` int(11) DEFAULT '0',
  `product_image_imagetype_id` int(11) DEFAULT '0',
  `product_image_filename` varchar(255) DEFAULT NULL,
  `product_image_sortorder` float(11,3) DEFAULT '1.000',
  `product_image_caption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_image_id`),
  KEY `product_image_product_id_idx` (`product_image_product_id`),
  KEY `product_image_imagetype_id_idx` (`product_image_imagetype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_product_images
-- ----------------------------
INSERT INTO `cw_product_images` VALUES ('414', '93', '1', 'Sample-Image-1.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('415', '93', '2', 'Sample-Image-1.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('416', '93', '3', 'Sample-Image-1.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('417', '93', '4', 'Sample-Image-1.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('418', '94', '1', 'Sample-Image-2.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('419', '94', '2', 'Sample-Image-2.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('420', '94', '3', 'Sample-Image-2.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('421', '94', '4', 'Sample-Image-2.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('422', '95', '1', 'Sample-Image-3.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('423', '95', '2', 'Sample-Image-3.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('424', '95', '3', 'Sample-Image-3.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('425', '95', '4', 'Sample-Image-3.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('426', '96', '1', 'Sample-Image-4.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('427', '96', '2', 'Sample-Image-4.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('428', '96', '3', 'Sample-Image-4.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('429', '96', '4', 'Sample-Image-4.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('457', '103', '4', 'Sample-Image-5.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('456', '103', '3', 'Sample-Image-5.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('450', '102', '1', 'Sample-Image-6.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('451', '102', '2', 'Sample-Image-6.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('452', '102', '3', 'Sample-Image-6.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('453', '102', '4', 'Sample-Image-6.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('454', '103', '1', 'Sample-Image-5.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('455', '103', '2', 'Sample-Image-5.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('438', '99', '1', 'Sample-Image-7.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('439', '99', '2', 'Sample-Image-7.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('440', '99', '3', 'Sample-Image-7.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('441', '99', '4', 'Sample-Image-7.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('442', '100', '1', 'noimage.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('443', '100', '2', 'noimage.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('444', '100', '3', 'noimage.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('445', '100', '4', 'noimage.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('446', '101', '1', 'Sample-Image-8.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('447', '101', '2', 'Sample-Image-8.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('448', '101', '3', 'Sample-Image-8.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('449', '101', '4', 'Sample-Image-8.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('458', '104', '1', 'Sample-Image-9.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('459', '104', '2', 'Sample-Image-9.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('462', '105', '1', 'Sample-Image-10.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('460', '104', '3', 'Sample-Image-9.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('461', '104', '4', 'Sample-Image-9.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('463', '105', '2', 'Sample-Image-10.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('464', '105', '3', 'Sample-Image-10.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('465', '105', '4', 'Sample-Image-10.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('466', '106', '1', 'Sample-Image-11.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('467', '106', '2', 'Sample-Image-11.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('468', '106', '3', 'Sample-Image-11.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('469', '106', '4', 'Sample-Image-11.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('470', '107', '1', 'Sample-Image-12.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('471', '107', '2', 'Sample-Image-12.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('472', '107', '3', 'Sample-Image-12.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('473', '107', '4', 'Sample-Image-12.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('474', '108', '1', 'Sample-Image-13.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('475', '108', '2', 'Sample-Image-13.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('476', '108', '3', 'Sample-Image-13.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('477', '108', '4', 'Sample-Image-13.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('478', '109', '1', 'Sample-Image-14.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('479', '109', '2', 'Sample-Image-14.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('480', '109', '3', 'Sample-Image-14.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('481', '109', '4', 'Sample-Image-14.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('482', '110', '1', 'Sample-Image-15.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('483', '110', '2', 'Sample-Image-15.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('484', '110', '3', 'Sample-Image-15.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('485', '110', '4', 'Sample-Image-15.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('486', '111', '1', 'Sample-Image-16.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('487', '111', '2', 'Sample-Image-16.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('488', '111', '3', 'Sample-Image-16.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('489', '111', '4', 'Sample-Image-16.jpg', '4.000', null);
INSERT INTO `cw_product_images` VALUES ('490', '112', '1', 'Sample-Image-17.jpg', '1.000', null);
INSERT INTO `cw_product_images` VALUES ('491', '112', '2', 'Sample-Image-17.jpg', '2.000', null);
INSERT INTO `cw_product_images` VALUES ('492', '112', '3', 'Sample-Image-17.jpg', '3.000', null);
INSERT INTO `cw_product_images` VALUES ('493', '112', '4', 'Sample-Image-17.jpg', '4.000', null);

-- ----------------------------
-- Table structure for `cw_product_options`
-- ----------------------------
DROP TABLE IF EXISTS `cw_product_options`;
CREATE TABLE `cw_product_options` (
  `product_options_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_options2prod_id` int(11) NOT NULL DEFAULT '0',
  `product_options2optiontype_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_options_id`),
  KEY `product_options2prod_id_idx` (`product_options2prod_id`),
  KEY `product_options2optiontype_id_idx` (`product_options2optiontype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_product_options
-- ----------------------------
INSERT INTO `cw_product_options` VALUES ('590', '101', '39');
INSERT INTO `cw_product_options` VALUES ('589', '101', '30');
INSERT INTO `cw_product_options` VALUES ('701', '94', '29');
INSERT INTO `cw_product_options` VALUES ('700', '94', '43');
INSERT INTO `cw_product_options` VALUES ('551', '108', '30');
INSERT INTO `cw_product_options` VALUES ('614', '102', '29');
INSERT INTO `cw_product_options` VALUES ('613', '102', '30');
INSERT INTO `cw_product_options` VALUES ('719', '99', '34');
INSERT INTO `cw_product_options` VALUES ('718', '99', '35');
INSERT INTO `cw_product_options` VALUES ('717', '99', '36');
INSERT INTO `cw_product_options` VALUES ('716', '99', '37');
INSERT INTO `cw_product_options` VALUES ('715', '99', '33');
INSERT INTO `cw_product_options` VALUES ('714', '99', '38');
INSERT INTO `cw_product_options` VALUES ('698', '100', '34');
INSERT INTO `cw_product_options` VALUES ('697', '100', '35');
INSERT INTO `cw_product_options` VALUES ('696', '100', '36');
INSERT INTO `cw_product_options` VALUES ('695', '100', '37');
INSERT INTO `cw_product_options` VALUES ('694', '100', '33');
INSERT INTO `cw_product_options` VALUES ('693', '100', '38');
INSERT INTO `cw_product_options` VALUES ('722', '93', '29');
INSERT INTO `cw_product_options` VALUES ('721', '93', '30');
INSERT INTO `cw_product_options` VALUES ('672', '104', '29');
INSERT INTO `cw_product_options` VALUES ('720', '106', '29');
INSERT INTO `cw_product_options` VALUES ('550', '107', '29');
INSERT INTO `cw_product_options` VALUES ('680', '109', '30');
INSERT INTO `cw_product_options` VALUES ('678', '110', '40');
INSERT INTO `cw_product_options` VALUES ('677', '110', '30');
INSERT INTO `cw_product_options` VALUES ('581', '111', '40');
INSERT INTO `cw_product_options` VALUES ('580', '111', '30');
INSERT INTO `cw_product_options` VALUES ('582', '112', '40');
INSERT INTO `cw_product_options` VALUES ('699', '94', '30');

-- ----------------------------
-- Table structure for `cw_product_upsell`
-- ----------------------------
DROP TABLE IF EXISTS `cw_product_upsell`;
CREATE TABLE `cw_product_upsell` (
  `upsell_id` int(11) NOT NULL AUTO_INCREMENT,
  `upsell_product_id` int(11) DEFAULT '0',
  `upsell_2product_id` int(11) DEFAULT '0',
  PRIMARY KEY (`upsell_id`),
  KEY `upsell_product_id_idx` (`upsell_product_id`),
  KEY `upsell_2product_id_idx` (`upsell_2product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_product_upsell
-- ----------------------------
INSERT INTO `cw_product_upsell` VALUES ('181', '93', '95');
INSERT INTO `cw_product_upsell` VALUES ('182', '93', '94');
INSERT INTO `cw_product_upsell` VALUES ('183', '95', '93');
INSERT INTO `cw_product_upsell` VALUES ('184', '93', '96');
INSERT INTO `cw_product_upsell` VALUES ('186', '95', '94');
INSERT INTO `cw_product_upsell` VALUES ('187', '94', '95');
INSERT INTO `cw_product_upsell` VALUES ('188', '97', '95');
INSERT INTO `cw_product_upsell` VALUES ('195', '107', '106');
INSERT INTO `cw_product_upsell` VALUES ('190', '98', '95');
INSERT INTO `cw_product_upsell` VALUES ('194', '106', '93');
INSERT INTO `cw_product_upsell` VALUES ('197', '109', '105');
INSERT INTO `cw_product_upsell` VALUES ('196', '108', '96');
INSERT INTO `cw_product_upsell` VALUES ('198', '109', '95');
INSERT INTO `cw_product_upsell` VALUES ('199', '101', '104');
INSERT INTO `cw_product_upsell` VALUES ('200', '101', '94');
INSERT INTO `cw_product_upsell` VALUES ('201', '101', '107');
INSERT INTO `cw_product_upsell` VALUES ('202', '101', '102');
INSERT INTO `cw_product_upsell` VALUES ('203', '104', '101');
INSERT INTO `cw_product_upsell` VALUES ('204', '102', '101');
INSERT INTO `cw_product_upsell` VALUES ('205', '115', '100');
INSERT INTO `cw_product_upsell` VALUES ('206', '115', '111');
INSERT INTO `cw_product_upsell` VALUES ('207', '115', '110');
INSERT INTO `cw_product_upsell` VALUES ('208', '115', '102');
INSERT INTO `cw_product_upsell` VALUES ('213', '105', '93');
INSERT INTO `cw_product_upsell` VALUES ('214', '105', '99');
INSERT INTO `cw_product_upsell` VALUES ('301', '93', '101');
INSERT INTO `cw_product_upsell` VALUES ('216', '105', '109');
INSERT INTO `cw_product_upsell` VALUES ('217', '105', '95');
INSERT INTO `cw_product_upsell` VALUES ('218', '105', '104');
INSERT INTO `cw_product_upsell` VALUES ('219', '105', '103');
INSERT INTO `cw_product_upsell` VALUES ('220', '105', '94');
INSERT INTO `cw_product_upsell` VALUES ('221', '105', '107');
INSERT INTO `cw_product_upsell` VALUES ('222', '105', '100');
INSERT INTO `cw_product_upsell` VALUES ('223', '105', '111');
INSERT INTO `cw_product_upsell` VALUES ('224', '105', '110');
INSERT INTO `cw_product_upsell` VALUES ('225', '105', '102');
INSERT INTO `cw_product_upsell` VALUES ('226', '105', '96');
INSERT INTO `cw_product_upsell` VALUES ('229', '105', '106');
INSERT INTO `cw_product_upsell` VALUES ('230', '105', '101');
INSERT INTO `cw_product_upsell` VALUES ('231', '105', '108');
INSERT INTO `cw_product_upsell` VALUES ('232', '105', '112');
INSERT INTO `cw_product_upsell` VALUES ('295', '118', '103');
INSERT INTO `cw_product_upsell` VALUES ('234', '99', '105');
INSERT INTO `cw_product_upsell` VALUES ('235', '113', '105');
INSERT INTO `cw_product_upsell` VALUES ('236', '95', '105');
INSERT INTO `cw_product_upsell` VALUES ('237', '104', '105');
INSERT INTO `cw_product_upsell` VALUES ('238', '103', '105');
INSERT INTO `cw_product_upsell` VALUES ('239', '94', '105');
INSERT INTO `cw_product_upsell` VALUES ('240', '107', '105');
INSERT INTO `cw_product_upsell` VALUES ('241', '100', '105');
INSERT INTO `cw_product_upsell` VALUES ('242', '111', '105');
INSERT INTO `cw_product_upsell` VALUES ('243', '110', '105');
INSERT INTO `cw_product_upsell` VALUES ('244', '102', '105');
INSERT INTO `cw_product_upsell` VALUES ('245', '96', '105');
INSERT INTO `cw_product_upsell` VALUES ('246', '115', '105');
INSERT INTO `cw_product_upsell` VALUES ('247', '114', '105');
INSERT INTO `cw_product_upsell` VALUES ('248', '106', '105');
INSERT INTO `cw_product_upsell` VALUES ('249', '101', '105');
INSERT INTO `cw_product_upsell` VALUES ('250', '108', '105');
INSERT INTO `cw_product_upsell` VALUES ('251', '112', '105');
INSERT INTO `cw_product_upsell` VALUES ('252', '110', '93');
INSERT INTO `cw_product_upsell` VALUES ('253', '110', '99');
INSERT INTO `cw_product_upsell` VALUES ('300', '93', '103');
INSERT INTO `cw_product_upsell` VALUES ('255', '110', '109');
INSERT INTO `cw_product_upsell` VALUES ('256', '110', '95');
INSERT INTO `cw_product_upsell` VALUES ('257', '110', '104');
INSERT INTO `cw_product_upsell` VALUES ('258', '110', '103');
INSERT INTO `cw_product_upsell` VALUES ('259', '110', '94');
INSERT INTO `cw_product_upsell` VALUES ('260', '110', '107');
INSERT INTO `cw_product_upsell` VALUES ('261', '110', '100');
INSERT INTO `cw_product_upsell` VALUES ('262', '110', '111');
INSERT INTO `cw_product_upsell` VALUES ('263', '110', '102');
INSERT INTO `cw_product_upsell` VALUES ('264', '110', '96');
INSERT INTO `cw_product_upsell` VALUES ('266', '110', '106');
INSERT INTO `cw_product_upsell` VALUES ('267', '110', '101');
INSERT INTO `cw_product_upsell` VALUES ('268', '110', '108');
INSERT INTO `cw_product_upsell` VALUES ('269', '110', '112');
INSERT INTO `cw_product_upsell` VALUES ('270', '93', '110');
INSERT INTO `cw_product_upsell` VALUES ('271', '99', '110');
INSERT INTO `cw_product_upsell` VALUES ('272', '113', '110');
INSERT INTO `cw_product_upsell` VALUES ('273', '109', '110');
INSERT INTO `cw_product_upsell` VALUES ('274', '95', '110');
INSERT INTO `cw_product_upsell` VALUES ('275', '104', '110');
INSERT INTO `cw_product_upsell` VALUES ('276', '103', '110');
INSERT INTO `cw_product_upsell` VALUES ('277', '94', '110');
INSERT INTO `cw_product_upsell` VALUES ('278', '107', '110');
INSERT INTO `cw_product_upsell` VALUES ('279', '100', '110');
INSERT INTO `cw_product_upsell` VALUES ('280', '111', '110');
INSERT INTO `cw_product_upsell` VALUES ('281', '102', '110');
INSERT INTO `cw_product_upsell` VALUES ('282', '96', '110');
INSERT INTO `cw_product_upsell` VALUES ('283', '114', '110');
INSERT INTO `cw_product_upsell` VALUES ('284', '106', '110');
INSERT INTO `cw_product_upsell` VALUES ('285', '101', '110');
INSERT INTO `cw_product_upsell` VALUES ('286', '108', '110');
INSERT INTO `cw_product_upsell` VALUES ('287', '112', '110');
INSERT INTO `cw_product_upsell` VALUES ('297', '118', '111');
INSERT INTO `cw_product_upsell` VALUES ('289', '116', '107');
INSERT INTO `cw_product_upsell` VALUES ('290', '116', '102');
INSERT INTO `cw_product_upsell` VALUES ('296', '118', '94');
INSERT INTO `cw_product_upsell` VALUES ('294', '99', '93');
INSERT INTO `cw_product_upsell` VALUES ('298', '118', '108');

-- ----------------------------
-- Table structure for `cw_products`
-- ----------------------------
DROP TABLE IF EXISTS `cw_products`;
CREATE TABLE `cw_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_merchant_product_id` varchar(50) NOT NULL,
  `product_name` varchar(125) DEFAULT NULL,
  `product_description` text,
  `product_preview_description` text,
  `product_sort` float(11,3) DEFAULT '1.000',
  `product_on_web` smallint(6) DEFAULT '1',
  `product_archive` smallint(6) DEFAULT '0',
  `product_ship_charge` smallint(6) DEFAULT '0',
  `product_tax_group_id` int(11) DEFAULT '0',
  `product_date_modified` datetime DEFAULT NULL,
  `product_special_description` text,
  `product_keywords` text,
  `product_out_of_stock_message` varchar(255) DEFAULT NULL,
  `product_custom_info_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `product_tax_group_id_idx` (`product_tax_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_products
-- ----------------------------
INSERT INTO `cw_products` VALUES ('93', 'BriteTs-4U', 'Very Colourful T-Shirts', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus molestie nisi vel orci ultricies volutpat. Nullam lacinia mi vel sapien tincidunt ornare. Aliquam tempus lectus eget turpis pulvinar tincidunt. Nullam sit amet consectetur risus.</p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam pharetra, enim quis varius dignissim, erat magna suscipit ipsum, vel pulvinar sapien augue vel enim. Ut ac sagittis enim. Nulla massa odio, accumsan at consequat id, varius a dolor. Nam non nibh ut massa dictum congue.</p>\r\n<p>Duis quis ligula in turpis facilisis ultrices. Morbi porta mi quis ligula malesuada eget posuere risus congue. Nullam cursus eleifend molestie. Mauris eleifend venenatis convallis.</p>\r\n<p>Pellentesque dapibus, odio eu venenatis faucibus, mi justo venenatis metus, quis cursus odio dui id felis. Sed interdum nisi sit amet nisi fermentum volutpat. Ut eget nisl sapien, ut pellentesque turpis. Etiam magna velit, aliquam volutpat pellentesque vitae, varius vitae ipsum. Fusce elementum hendrerit nibh vel tempor.</p>\r\n<p>Ut dignissim sapien vel lectus suscipit dignissim.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus molestie nisi vel orci ultricies volutpat. Nullam lacinia mi vel sapien tincidunt ornare. Aliquam tempus lectus eget turpis pulvinar tincidunt. Nullam sit amet consectetur risus.</p>', '0.000', '1', '0', '0', '14', '2011-12-16 11:26:25', '<p>In hac bhabitasse platea dictumst. Etiam faucibus interdum nisi sed imperdiet. Sed tempus risus risus. Pellentesque vitae arcu odio. Pellentesque purus magna, feugiat et sodales eget, pretium non metus.</p>', 'Bright Cotton T Shirts, Cotton T\'s, Tee Shirt, Tshirt, Teeshirt, Albatross', 'Sorry, Out Of Stock', 'Write Your Message!');
INSERT INTO `cw_products` VALUES ('94', '3DenimJeans', 'Gotsda Blues Jeans', '<p>Pellentesque consectetur nisl et lacus tincidunt vel euismod turpis adipiscing. Maecenas molestie turpis et quam pulvinar eget ornare nisi consectetur.</p>\r\n<p>Aliquam vehicula scelerisque dolor, in imperdiet nisi consequat eget. Mauris et felis semper quam feugiat ultricies in adipiscing risus.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis a arcu magna, et egestas lorem. Vivamus porta arcu vel massa adipiscing egestas.</p>', '0.000', '1', '0', '1', '14', '2011-11-16 11:10:40', '<p>&nbsp;Maecenas molestie turpis et quam pulvinar eget ornare nisi consectetur. Aliquam vehicula scelerisque dolor, in imperdiet nisi consequat eget.</p>', 'Blue Jeans, Denim Jeans', 'So popular we can hardly keep them in stock <br> Get more of these great jeans next week! ', '');
INSERT INTO `cw_products` VALUES ('95', 'DigitalSLRCamera', 'Digital SLR Camera', '<p>Pellentesque consectetur nisl et lacus tincidunt vel euismod turpis  adipiscing. Maecenas molestie turpis et quam pulvinar eget ornare nisi  consectetur.</p>\r\n<p>Aliquam vehicula scelerisque dolor, in imperdiet nisi  consequat eget. Mauris et felis semper quam feugiat ultricies in  adipiscing risus. Proin feugiat arcu quis risus posuere dignissim. Etiam  lacinia justo ac risus pretium cursus.</p>\r\n<ul>\r\n<li>Vestibulum mattis condimentum  tincidunt. </li>\r\n<li>Aliquam convallis, ante eu posuere molestie,</li>\r\n<li>Rrisus ipsum  sollicitudin sem</li>\r\n<li>Semper tempus diam nibh sed lacus. </li>\r\n</ul>\r\n<p>Morbi tincidunt  ullamcorper libero sed dapibus. Pellentesque habitant morbi tristique  senectus et netus et malesuada fames ac turpis egestas. Donec aliquet  odio nulla, eget vulputate purus. Nullam lobortis eleifend mauris, vel  dictum tortor tempus et. Pellentesque eu augue magna, ac gravida dolor.  Ut ipsum eros, mollis pulvinar interdum at, sodales eu tortor.</p>\r\n<p>Aliquam  erat volutpat. Vestibulum et purus sed dui ornare fermentum vitae id  tortor. Ut elementum auctor turpis, ornare feugiat tortor ultricies  interdum.</p>', '<p>Morbi at ipsum ut eros mattis tincidunt. Etiam id arcu sit amet est  mattis porttitor molestie vel mi. Cras ipsum dui, egestas id accumsan a,  consequat in nibh. Suspendisse potenti. Suspendisse non est eget lectus  tempus gravida elementum eu sapien.</p>', '0.000', '1', '0', '1', '10', '2010-06-20 14:45:32', '<p>Quisque rutrum felis nulla, quis viverra sem. Nam nec lorem  leo. Nulla  lectus arcu, hendrerit id tempus nec, mollis non massa.</p>', 'Photography, Hobbies', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('96', 'PlasmaFlatScreen', 'Plasma 50\" TV', '<p>Nunc at varius lectus. Fusce ullamcorper, dui nec volutpat mollis, nulla quam lobortis magna, et fringilla leo diam id ante. Nunc ornare, nulla nec tempor adipiscing, ipsum turpis pulvinar mi, nec iaculis arcu dolor nec orci. Proin velit nisl, semper vitae vestibulum eget, adipiscing quis augue. Praesent gravida, magna vitae commodo elementum, neque ante eleifend urna, tempus luctus turpis quam a risus. Nam nec metus ac arcu tempor accumsan in sed neque. Phasellus posuere, nisi a rhoncus viverra, risus purus rutrum nisl, accumsan laoreet nisl est in purus. </p>\r\n<p><strong>Duis vulputate placerat tellus a laoreet. </strong></p>\r\n<ul>\r\n<li>Aliquam rhoncus malesuada mollis. <br /></li>\r\n<li>Maecenas facilisis consectetur eleifend. <br /></li>\r\n<li>Vivamus magna lacus, dignissim id ornare sit amet, accumsan at tortor. </li>\r\n</ul>\r\n<p>Mauris quam tellus, pellentesque vel pretium et, convallis quis purus. Quisque at porta ipsum. Maecenas laoreet eros id dolor mollis porta.</p>', '<p>Proin at velit urna. Cras molestie, ligula sit amet vestibulum tincidunt, nulla diam imperdiet justo, tincidunt hendrerit nisl massa id risus. Phasellus consequat dapibus blandit. Nam eu quam eget purus fringilla posuere. Cras tempus tempor quam</p>', '0.000', '1', '0', '1', '10', '2010-06-07 14:47:08', '<p>Cras tempus tempor quam, nec posuere arcu malesuada sed.</p>', 'TV, Video, Big Screen', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('102', 'PastelComforters', 'Pastel Comforters', '<p class=\"CWproductDescription\" style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisl enim, sed ornare orci. Donec porta luctus velit vitae mattis. Pellentesque lacus risus, pellentesque et blandit non, mollis nec tellus.</p>\r\n<p class=\"CWproductDescription\" style=\"text-align: left;\">Quisque ac dignissim odio. Cras eros neque, cursus non vulputate ut, lobortis congue purus. Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta. Praesent sodales tortor eu magna adipiscing sed ultrices sapien viverra. Cras ac enim ligula. Pellentesque velit massa, imperdiet eget consequat non, tincidunt ac purus. Pellentesque mollis justo id tellus tristique ultrices. Suspendisse pretium, nisi sit amet varius laoreet, mauris lacus egestas urna, placerat scelerisque quam odio a nisi.</p>', '<p class=\"CWproduct\" style=\"text-align: left;\">Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta.</p>', '0.000', '1', '0', '0', '10', '2011-01-21 09:42:09', '<p>Super soft and long lasting.</p>', '', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('103', 'RainbowBroom', 'FloorBrite Rainbow Broom', '<p style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisl enim, sed ornare orci. Donec porta luctus velit vitae mattis. Pellentesque lacus risus, pellentesque et blandit non, mollis nec tellus. Quisque ac dignissim odio. Cras eros neque, cursus non vulputate ut, lobortis congue purus. Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta. Praesent sodales tortor eu magna adipiscing sed ultrices sapien viverra. Cras ac enim ligula. Pellentesque velit massa, imperdiet eget consequat non, tincidunt ac purus. Pellentesque mollis justo id tellus tristique ultrices. Suspendisse pretium, nisi sit amet varius laoreet, mauris lacus egestas urna, placerat scelerisque quam odio a nisi.</p>', '<p class=\"CWproduct\" style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisl enim, sed ornare orci. Donec porta luctus velit vitae mattis. Pellentesque lacus risus</p>', '0.000', '1', '0', '1', '10', '2011-02-16 10:44:16', '', '', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('104', 'DownPillows', 'Down Feather Pillows', '<p class=\"CWproductDescription\" style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisl enim, sed ornare orci.</p>\r\n<p class=\"CWproductDescription\" style=\"text-align: left;\">&nbsp;</p>\r\n<p class=\"CWproductDescription\" style=\"text-align: left;\">Donec porta luctus velit vitae mattis. Pellentesque lacus risus, pellentesque et blandit non, mollis nec tellus.</p>\r\n<p class=\"CWproductDescription\" style=\"text-align: left;\">Quisque ac dignissim odio. Cras eros neque, cursus non vulputate ut, lobortis congue purus. Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta. Praesent sodales tortor eu magna adipiscing sed ultrices sapien viverra. Cras ac enim ligula. Pellentesque velit massa, imperdiet eget consequat non, tincidunt ac purus. Pellentesque mollis justo id tellus tristique ultrices. Suspendisse pretium, nisi sit amet varius laoreet, mauris lacus egestas urna, placerat scelerisque quam odio a nisi.</p>', '<p class=\"CWproduct\" style=\"text-align: left;\">Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta.</p>', '0.000', '1', '0', '1', '10', '2011-05-15 17:26:45', '<p>Super soft, supportive and so comfy.</p>', '', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('99', 'DeskTop-Basic5000', 'Desktop Basic 5000', '<p style=\"text-align: left;\">Maecenas a lobortis massa. Quisque viverra suscipit tellus, a dapibus enim pulvinar et. Morbi pharetra orci id odio ultrices scelerisque. Duis quis elit eu nisl blandit mollis. Praesent laoreet placerat ipsum at sagittis. Integer at turpis ut dui aliquam semper sed sit amet elit. Fusce non mi et enim aliquet luctus vitae vestibulum eros. </p>\r\n<ul>\r\n<li>\r\n<div style=\"text-align: left;\">Suspendisse vitae velit felis. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Pellentesque malesuada tincidunt lorem id varius. Morbi quis ante ipsum. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Duis facilisis ligula eget purus dictum sed faucibus enim venenatis. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Phasellus hendrerit rhoncus mi, at adipiscing velit laoreet quis. </div>\r\n</li>\r\n</ul>\r\n<p style=\"text-align: left;\">Sed sem arcu, consequat quis varius ac, dictum sit amet lorem. In odio metus, consequat vel condimentum sit amet, posuere condimentum arcu. Vivamus tellus nunc, sodales a fringilla at, pulvinar ut nisi. Nam nibh lectus, posuere quis convallis vel, luctus ut ipsum. Nam eget eros arcu. </p>\r\n<p style=\"text-align: left;\">Donec quis auctor nisl. Cras viverra, enim molestie lacinia gravida, risus dui laoreet mauris, quis ornare mi orci sed tellus.</p>', '<p>Donec condimentum, turpis eu interdum venenatis, libero diam porttitor ipsum, et sodales dui libero at turpis. Nulla mollis, est non vulputate cursus, ipsum nibh sodales diam, ut elementum odio nibh et turpis. Maecenas imperdiet fringilla nisi non accumsan.&nbsp;</p>', '1.000', '1', '0', '1', '3', '2011-12-16 11:15:52', '<p>Donec quis auctor nisl. Cras viverra, enim molestie lacinia gravida, risus dui laoreet mauris, quis ornare mi orci sed tellus.</p>\r\n<p class=\"smallprint\">This is some small print. This is some small print. This is some small print. This is some small print. This is some small print.&nbsp;</p>', '', 'Sorry, Sold Out', '');
INSERT INTO `cw_products` VALUES ('100', 'Laptop-Basic5000', 'Laptop Basic 5000', '<p style=\"text-align: left;\">Maecenas a lobortis massa. Quisque viverra suscipit tellus, a dapibus enim pulvinar et. Morbi pharetra orci id odio ultrices scelerisque. Duis quis elit eu nisl blandit mollis. Praesent laoreet placerat ipsum at sagittis. Integer at turpis ut dui aliquam semper sed sit amet elit. Fusce non mi et enim aliquet luctus vitae vestibulum eros. </p>\r\n<ul>\r\n<li>\r\n<div style=\"text-align: left;\">Suspendisse vitae velit felis. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Pellentesque malesuada tincidunt lorem id varius. Morbi quis ante ipsum. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Duis facilisis ligula eget purus dictum sed faucibus enim venenatis. </div>\r\n</li>\r\n<li>\r\n<div style=\"text-align: left;\">Phasellus hendrerit rhoncus mi, at adipiscing velit laoreet quis. </div>\r\n</li>\r\n</ul>\r\n<p style=\"text-align: left;\">Sed sem arcu, consequat quis varius ac, dictum sit amet lorem. In odio metus, consequat vel condimentum sit amet, posuere condimentum arcu. Vivamus tellus nunc, sodales a fringilla at, pulvinar ut nisi. Nam nibh lectus, posuere quis convallis vel, luctus ut ipsum. Nam eget eros arcu. </p>\r\n<p style=\"text-align: left;\">Donec quis auctor nisl. Cras viverra, enim molestie lacinia gravida, risus dui laoreet mauris, quis ornare mi orci sed tellus.</p>', '<p>Donec condimentum, turpis eu interdum venenatis, libero diam porttitor ipsum, et sodales dui libero at turpis. Nulla mollis, est non vulputate cursus, ipsum nibh sodales diam, ut elementum odio nibh et turpis. Maecenas imperdiet fringilla nisi non accumsan.&nbsp;</p>', '1.000', '1', '0', '1', '3', '2011-11-09 18:17:45', '<p>Donec quis auctor nisl. Cras viverra, enim molestie lacinia gravida, risus dui laoreet mauris, quis ornare mi orci sed tellus.</p>', '', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('101', 'Terrycloth-Towels', 'Terrycloth Bath Towels', '<p class=\"CWproductDescription\" style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisl enim, sed ornare orci. Donec porta luctus velit vitae mattis. Pellentesque lacus risus, pellentesque et blandit non, mollis nec tellus. Quisque ac dignissim odio. Cras eros neque, cursus non vulputate ut, lobortis congue purus. Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta. Praesent sodales tortor eu magna adipiscing sed ultrices sapien viverra. Cras ac enim ligula. Pellentesque velit massa, imperdiet eget consequat non, tincidunt ac purus. Pellentesque mollis justo id tellus tristique ultrices. Suspendisse pretium, nisi sit amet varius laoreet, mauris lacus egestas urna, placerat scelerisque quam odio a nisi.</p>', '<p class=\"CWproduct\" style=\"text-align: left;\">Aenean in nisi id tellus ornare elementum. Duis neque purus, viverra id lacinia sed, aliquam eget enim. Ut ut lobortis leo. Mauris aliquam libero molestie orci feugiat porta.</p>', '0.000', '1', '0', '1', '10', '2010-07-15 13:19:33', '<p>Super soft and long lasting.</p>', '', 'Sorry, Out Of Stock', 'Custom Monogram');
INSERT INTO `cw_products` VALUES ('105', 'DigitalVideoCamera', 'Digital HD Video Camera', '<p style=\"text-align: left;\">Pellentesque consectetur nisl et lacus tincidunt vel euismod turpis adipiscing. Maecenas molestie turpis et quam pulvinar eget ornare nisi consectetur.</p>\r\n<p style=\"text-align: left;\">Aliquam vehicula scelerisque dolor, in imperdiet nisi consequat eget. Mauris et felis semper quam feugiat ultricies in adipiscing risus. Proin feugiat arcu quis risus posuere dignissim. Etiam lacinia justo ac risus pretium cursus.</p>\r\n<ul>\r\n<li style=\"text-align: left;\">Vestibulum mattis condimentum tincidunt.</li>\r\n<li style=\"text-align: left;\">Aliquam convallis, ante eu posuere molestie,</li>\r\n<li style=\"text-align: left;\">Rrisus ipsum sollicitudin sem</li>\r\n<li style=\"text-align: left;\">Semper tempus diam nibh sed lacus.</li>\r\n</ul>\r\n<p style=\"text-align: left;\">Morbi tincidunt ullamcorper libero sed dapibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec aliquet odio nulla, eget vulputate purus. Nullam lobortis eleifend mauris, vel dictum tortor tempus et. Pellentesque eu augue magna, ac gravida dolor. Ut ipsum eros, mollis pulvinar interdum at, sodales eu tortor.</p>', '<p style=\"text-align: left;\">Morbi at ipsum ut eros mattis tincidunt. Etiam id arcu sit amet est mattis porttitor molestie vel mi. Cras ipsum dui, egestas id accumsan a, consequat in nibh. Suspendisse potenti. Suspendisse non est eget lectus tempus gravida elementum eu sapien.</p>', '0.000', '1', '0', '1', '10', '2011-11-08 15:18:26', '<p>Quisque rutrum felis nulla, quis viverra sem. Nam nec lorem leo. Nulla lectus arcu, hendrerit id tempus nec, mollis non massa.</p>', 'Photography, Hobbies, Video', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('106', 'RuggedRugby', 'Rugged Rugby', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus molestie nisi vel orci ultricies volutpat. Nullam lacinia mi vel sapien tincidunt ornare. Aliquam tempus lectus eget turpis pulvinar tincidunt. Nullam sit amet consectetur risus.</p>\r\n<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam pharetra, enim quis varius dignissim, erat magna suscipit ipsum, vel pulvinar sapien augue vel enim. Ut ac sagittis enim. Nulla massa odio, accumsan at consequat id, varius a dolor. Nam non nibh ut massa dictum congue.</p>\r\n<p>Duis quis ligula in turpis facilisis ultrices. Morbi porta mi quis ligula malesuada eget posuere risus congue. Nullam cursus eleifend molestie. Mauris eleifend venenatis convallis.</p>\r\n<p>Pellentesque dapibus, odio eu venenatis faucibus, mi justo venenatis metus, quis cursus odio dui id felis. Sed interdum nisi sit amet nisi fermentum volutpat. Ut eget nisl sapien, ut pellentesque turpis. Etiam magna velit, aliquam volutpat pellentesque vitae, varius vitae ipsum. Fusce elementum hendrerit nibh vel tempor.</p>\r\n<p>Ut dignissim sapien vel lectus suscipit dignissim.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus molestie nisi vel orci ultricies volutpat. Nullam lacinia mi vel sapien tincidunt ornare. Aliquam tempus lectus eget turpis pulvinar tincidunt. Nullam sit amet consectetur risus.</p>', '0.000', '1', '0', '1', '14', '2011-12-16 11:26:10', '<p>In hac habitasse platea dictumst. Etiam faucibus interdum nisi sed imperdiet. Sed tempus risus risus. Pellentesque vitae arcu odio. Pellentesque purus magna, feugiat et sodales eget, pretium non metus.</p>', 'Cotton Rugby Shirts, Cotton Sport Shirts', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('107', 'KomfyKhakis', 'Komfy Khakis', '<p style=\"text-align: left;\">Pellentesque consectetur nisl et lacus tincidunt vel euismod turpis  adipiscing. Maecenas molestie turpis et quam pulvinar eget ornare nisi  consectetur.</p>\r\n<p style=\"text-align: left;\">Aliquam vehicula scelerisque dolor, in imperdiet nisi  consequat eget. Mauris et felis semper quam feugiat ultricies in  adipiscing risus.</p>', '<p style=\"text-align: left;\">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis a arcu  magna, et egestas lorem. Vivamus porta arcu vel massa adipiscing  egestas.</p>', '0.000', '1', '0', '1', '14', '2010-07-05 15:18:50', '<p style=\"text-align: left;\">&nbsp;Maecenas molestie turpis et quam pulvinar eget ornare nisi  consectetur. Aliquam vehicula scelerisque dolor, in imperdiet nisi  consequat eget.</p>', 'Komfy Khakis, Canvas Pants', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('108', 'RetroTV', 'Ultimate Retro TV', '<p>Nunc at varius lectus. Fusce ullamcorper, dui nec volutpat mollis, nulla quam lobortis magna, et fringilla leo diam id ante. Nunc ornare, nulla nec tempor adipiscing, ipsum turpis pulvinar mi, nec iaculis arcu dolor nec orci. Proin velit nisl, semper vitae vestibulum eget, adipiscing quis augue. Praesent gravida, magna vitae commodo elementum, neque ante eleifend urna, tempus luctus turpis quam a risus. Nam nec metus ac arcu tempor accumsan in sed neque. Phasellus posuere, nisi a rhoncus viverra, risus purus rutrum nisl, accumsan laoreet nisl est in purus. </p>\r\n<p><strong>Duis vulputate placerat tellus a laoreet. </strong></p>\r\n<ul>\r\n<li>Aliquam rhoncus malesuada mollis. <br /></li>\r\n<li>Maecenas facilisis consectetur eleifend. <br /></li>\r\n<li>Vivamus magna lacus, dignissim id ornare sit amet, accumsan at tortor. </li>\r\n</ul>\r\n<p>Mauris quam tellus, pellentesque vel pretium et, convallis quis purus. Quisque at porta ipsum. Maecenas laoreet eros id dolor mollis porta.</p>', '<p>Proin at velit urna. Cras molestie, ligula sit amet vestibulum tincidunt, nulla diam imperdiet justo, tincidunt hendrerit nisl massa id risus. Phasellus consequat dapibus blandit. Nam eu quam eget purus fringilla posuere. Cras tempus tempor quam</p>', '0.000', '1', '0', '1', '10', '2011-03-09 23:01:38', '<p>Cras tempus tempor quam, nec posuere arcu malesuada sed.</p>', 'TV, Video, Retro', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('109', 'DigitalPoint-n-ShootCamera', 'Digital Point & Shoot Camera', '<p>Pellentesque consectetur nisl et lacus tincidunt vel euismod turpis adipiscing. Maecenas molestie turpis et quam pulvinar eget ornare nisi consectetur.</p>\r\n<p>Aliquam vehicula scelerisque dolor, in imperdiet nisi consequat eget. Mauris et felis semper quam feugiat ultricies in adipiscing risus. Proin feugiat arcu quis risus posuere dignissim. Etiam lacinia justo ac risus pretium cursus.</p>\r\n<ul>\r\n<li>Vestibulum mattis condimentum tincidunt.</li>\r\n<li>Aliquam convallis, ante eu posuere molestie,</li>\r\n<li>Rrisus ipsum sollicitudin sem</li>\r\n<li>Semper tempus diam nibh sed lacus.</li>\r\n</ul>\r\n<p>Morbi tincidunt ullamcorper libero sed dapibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec aliquet odio nulla, eget vulputate purus. Nullam lobortis eleifend mauris, vel dictum tortor tempus et. Pellentesque eu augue magna, ac gravida dolor. Ut ipsum eros, mollis pulvinar interdum at, sodales eu tortor.</p>\r\n<p>Aliquam erat volutpat. Vestibulum et purus sed dui ornare fermentum vitae id tortor. Ut elementum auctor turpis, ornare feugiat tortor ultricies interdum.</p>', '<p>Morbi at ipsum ut eros mattis tincidunt. Etiam id arcu sit amet est mattis porttitor molestie vel mi. Cras ipsum dui, egestas id accumsan a, consequat in nibh. Suspendisse potenti. Suspendisse non est eget lectus tempus gravida elementum eu sapien.</p>', '0.000', '1', '0', '1', '10', '2011-12-16 11:22:27', '<p>Quisque rutrum felis nulla, quis viverra sem. Nam nec lorem leo. Nulla lectus arcu, hendrerit id tempus nec, mollis non massa.</p>', 'Photography, Hobbies', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('110', 'LawnPower-Ride', 'LawnPower Ride Lawn Mower', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut venenatis auctor pulvinar. Vivamus porta elit at tellus rhoncus ornare.</p>\r\n<ul>\r\n<li>Vivamus porttitor elit et mauris porttitor quis blandit sem viverra.</li>\r\n<li>Vivamus ultricies velit vitae dui vestibulum ultrices. Aenean pretium velit in ipsum sodales fermentum. </li>\r\n<li>Quisque nec justo lectus, sed varius eros. Morbi a erat a lectus semper molestie eget eu libero.&nbsp;</li>\r\n</ul>\r\n<p>Suspendisse metus nulla, aliquet eu sollicitudin non, condimentum quis tellus.</p>\r\n<p><strong>Vivamus sit amet ligula in ligula ultricies pellentesque.</strong></p>\r\n<p>Quisque porttitor mollis mauris, vitae porttitor sapien venenatis et. In eu diam purus. In hac habitasse platea dictumst.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut venenatis auctor pulvinar.</p>', '0.000', '1', '0', '0', '10', '2011-07-20 10:08:02', '<p>Vivamus porttitor elit et mauris porttitor quis blandit sem viverra. Vivamus ultricies velit vitae dui vestibulum ultrices. Aenean pretium velit in ipsum sodales fermentum.</p>\r\n<p>Quisque nec justo lectus, sed varius eros.</p>', 'Yard Tools, Landscaping, power tools', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('111', 'LawnPower-Push', 'LawnPower Push Lawn Mower', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut venenatis auctor pulvinar. Vivamus porta elit at tellus rhoncus ornare.</p>\r\n<ul>\r\n<li>Vivamus porttitor elit et mauris porttitor quis blandit sem viverra.</li>\r\n<li>Vivamus ultricies velit vitae dui vestibulum ultrices. Aenean pretium velit in ipsum sodales fermentum. </li>\r\n<li>Quisque nec justo lectus, sed varius eros. Morbi a erat a lectus semper molestie eget eu libero.&nbsp;</li>\r\n</ul>\r\n<p>Suspendisse metus nulla, aliquet eu sollicitudin non, condimentum quis tellus.</p>\r\n<p><strong>Vivamus sit amet ligula in ligula ultricies pellentesque.</strong></p>\r\n<p>Quisque porttitor mollis mauris, vitae porttitor sapien venenatis et. In eu diam purus. In hac habitasse platea dictumst.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut venenatis auctor pulvinar.</p>', '0.000', '1', '0', '1', '10', '2010-07-06 15:48:16', '<p>Vivamus porttitor elit et mauris porttitor quis blandit sem viverra. Vivamus ultricies velit vitae dui vestibulum ultrices. Aenean pretium velit in ipsum sodales fermentum.</p>\r\n<p>Quisque nec justo lectus, sed varius eros.</p>', 'Yard Tools, Landscaping, power tools', 'Sorry, Out Of Stock', '');
INSERT INTO `cw_products` VALUES ('112', 'WeedAWack', 'WeedAWack Weed Trimmer', '<p>Aliquam tristique, enim ut elementum pulvinar, justo diam pharetra urna, non adipiscing mi tellus in sapien. Nulla a lectus id sem accumsan dictum id et lorem.</p>\r\n<p><strong>Vestibulum metus orci,</strong></p>\r\n<p>consequat eu viverra et, consequat vitae elit. Pellentesque luctus, est vel ullamcorper gravida, ante sem volutpat libero, et lacinia dolor ligula eleifend quam. Aenean hendrerit luctus consequat. Duis pulvinar lorem at nisi malesuada dignissim. Cras vel posuere metus. Proin ante ligula, pharetra non lobortis in, lobortis sed arcu. Phasellus ac dolor nec tellus dictum pharetra.</p>\r\n<ol>\r\n<li>Aliquam auctor convallis euismod. </li>\r\n<li>Curabitur et leo at mi porta mattis a sed nunc.&nbsp;</li>\r\n</ol>\r\n<p>Nulla sed ligula libero. Nulla eu quam neque. Integer ultricies, lectus id condimentum ullamcorper, dui erat vestibulum mauris, eget ornare neque mi id tortor. Cras lobortis vehicula commodo. Morbi sodales ornare semper. In placerat gravida sem, sed pulvinar arcu cursus eget.</p>\r\n<p><strong>Donec eget</strong></p>\r\n<p>Donec eget risus ac sapien vestibulum euismod. Quisque quis eros urna, vel posuere lectus</p>', '<p>Curabitur et leo at mi porta mattis a sed nunc. Nulla sed ligula libero. Nulla eu quam neque. Integer ultricies, lectus id condimentum ullamcorper, dui erat vestibulum mauris, eget ornare neque mi id tortor. Cras lobortis vehicula commodo. Morbi sodales ornare semper. In placerat gravida sem,</p>', '0.000', '1', '0', '1', '10', '2010-07-06 15:56:24', '<p>Curabitur et leo at mi porta mattis a sed nunc. Nulla sed ligula libero. Nulla eu quam neque. Integer ultricies, lectus id condimentum ullamcorper,</p>', 'Weed Eater, Edge Trimmer, Fish-line Trimmer', 'Sorry, Out Of Stock', '');

-- ----------------------------
-- Table structure for `cw_ship_method_countries`
-- ----------------------------
DROP TABLE IF EXISTS `cw_ship_method_countries`;
CREATE TABLE `cw_ship_method_countries` (
  `ship_method_country_id` int(11) NOT NULL AUTO_INCREMENT,
  `ship_method_country_method_id` int(11) DEFAULT '0',
  `ship_method_country_country_id` int(11) DEFAULT '0',
  PRIMARY KEY (`ship_method_country_id`),
  KEY `ship_method_country_method_id_idx` (`ship_method_country_method_id`),
  KEY `ship_method_country_country_id_idx` (`ship_method_country_country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_ship_method_countries
-- ----------------------------
INSERT INTO `cw_ship_method_countries` VALUES ('1', '35', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('4', '76', '2');
INSERT INTO `cw_ship_method_countries` VALUES ('7', '65', '3');
INSERT INTO `cw_ship_method_countries` VALUES ('9', '74', '5');
INSERT INTO `cw_ship_method_countries` VALUES ('30', '96', '5');
INSERT INTO `cw_ship_method_countries` VALUES ('13', '79', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('16', '82', '2');
INSERT INTO `cw_ship_method_countries` VALUES ('34', '100', '12');
INSERT INTO `cw_ship_method_countries` VALUES ('32', '98', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('33', '99', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('35', '101', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('36', '102', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('37', '103', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('38', '104', '1');
INSERT INTO `cw_ship_method_countries` VALUES ('39', '105', '1');

-- ----------------------------
-- Table structure for `cw_ship_methods`
-- ----------------------------
DROP TABLE IF EXISTS `cw_ship_methods`;
CREATE TABLE `cw_ship_methods` (
  `ship_method_id` int(11) NOT NULL AUTO_INCREMENT,
  `ship_method_name` varchar(100) NOT NULL DEFAULT '',
  `ship_method_rate` double DEFAULT NULL,
  `ship_method_sort` float(11,3) NOT NULL DEFAULT '1.000',
  `ship_method_archive` smallint(6) DEFAULT '0',
  `ship_method_calctype` varchar(55) DEFAULT NULL,
  PRIMARY KEY (`ship_method_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_ship_methods
-- ----------------------------
INSERT INTO `cw_ship_methods` VALUES ('35', 'UPS Ground', '0', '1.000', '1', 'upsgroundcalc');
INSERT INTO `cw_ship_methods` VALUES ('65', 'Canadian UPS', '5', '4.000', '0', null);
INSERT INTO `cw_ship_methods` VALUES ('66', 'International UPS', '28', '5.000', '0', null);
INSERT INTO `cw_ship_methods` VALUES ('73', 'USPS', '4', '6.000', '0', null);
INSERT INTO `cw_ship_methods` VALUES ('74', 'Securicor', '15', '5.000', '1', null);
INSERT INTO `cw_ship_methods` VALUES ('76', 'USA Territories UPS', '14.5', '0.000', '0', null);
INSERT INTO `cw_ship_methods` VALUES ('79', 'UPS Ground', '5', '1.000', '0', 'localcalc');
INSERT INTO `cw_ship_methods` VALUES ('82', 'Mountain Bike', '123.4', '0.000', '1', null);
INSERT INTO `cw_ship_methods` VALUES ('96', 'DHL', '5', '1.000', '0', null);
INSERT INTO `cw_ship_methods` VALUES ('98', 'UPS 3-Day Select', '0', '1.500', '1', 'ups3daycalc');
INSERT INTO `cw_ship_methods` VALUES ('99', 'UPS Next Day Air', '0', '1.800', '1', 'upsnextdaycalc');
INSERT INTO `cw_ship_methods` VALUES ('100', 'Ireland Courier', '0', '0.000', '0', 'localcalc');
INSERT INTO `cw_ship_methods` VALUES ('101', 'UPS Two Day', '6.5', '2.000', '1', 'localcalc');
INSERT INTO `cw_ship_methods` VALUES ('102', 'UPS Next Day Air', '8', '3.000', '0', 'localcalc');
INSERT INTO `cw_ship_methods` VALUES ('103', 'FedEx Ground', '0', '4.000', '1', 'fedexgroundcalc');
INSERT INTO `cw_ship_methods` VALUES ('104', 'FedEx Standard Overnight', '0', '4.200', '1', 'fedexstandardovernightcalc');
INSERT INTO `cw_ship_methods` VALUES ('105', 'FedEx Two Day', '0', '4.400', '1', 'fedex2daycalc');

-- ----------------------------
-- Table structure for `cw_ship_ranges`
-- ----------------------------
DROP TABLE IF EXISTS `cw_ship_ranges`;
CREATE TABLE `cw_ship_ranges` (
  `ship_range_id` int(11) NOT NULL AUTO_INCREMENT,
  `ship_range_method_id` int(11) DEFAULT '0',
  `ship_range_from` double DEFAULT '0',
  `ship_range_to` double DEFAULT '0',
  `ship_range_amount` double DEFAULT '0',
  PRIMARY KEY (`ship_range_id`),
  KEY `ship_range_method_id_idx` (`ship_range_method_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_ship_ranges
-- ----------------------------
INSERT INTO `cw_ship_ranges` VALUES ('13', '65', '0', '5', '19');
INSERT INTO `cw_ship_ranges` VALUES ('14', '65', '5.01', '10', '29');
INSERT INTO `cw_ship_ranges` VALUES ('15', '65', '10.01', '20', '39');
INSERT INTO `cw_ship_ranges` VALUES ('16', '65', '20.01', '10000000', '49');
INSERT INTO `cw_ship_ranges` VALUES ('17', '66', '0', '5', '21');
INSERT INTO `cw_ship_ranges` VALUES ('18', '66', '5.01', '10', '31');
INSERT INTO `cw_ship_ranges` VALUES ('19', '66', '10.01', '20', '41');
INSERT INTO `cw_ship_ranges` VALUES ('20', '66', '20.01', '10000000', '51');
INSERT INTO `cw_ship_ranges` VALUES ('56', '79', '0', '100', '9.99');
INSERT INTO `cw_ship_ranges` VALUES ('64', '96', '0', '21', '7.5');
INSERT INTO `cw_ship_ranges` VALUES ('57', '79', '100.01', '200', '22.99');
INSERT INTO `cw_ship_ranges` VALUES ('58', '79', '200.01', '300', '34.99');
INSERT INTO `cw_ship_ranges` VALUES ('70', '35', '30.01', '50', '18.99');
INSERT INTO `cw_ship_ranges` VALUES ('69', '35', '20.01', '30', '15.99');
INSERT INTO `cw_ship_ranges` VALUES ('65', '96', '25.01', '99', '12.5');
INSERT INTO `cw_ship_ranges` VALUES ('66', '96', '99.01', '999999', '17.5');
INSERT INTO `cw_ship_ranges` VALUES ('68', '35', '0', '20', '5.99');
INSERT INTO `cw_ship_ranges` VALUES ('71', '35', '50.01', '9999999', '20');
INSERT INTO `cw_ship_ranges` VALUES ('72', '35', '200.01', '500', '55');
INSERT INTO `cw_ship_ranges` VALUES ('73', '79', '300.01', '420', '50');
INSERT INTO `cw_ship_ranges` VALUES ('74', '79', '420.01', '100000000', '99.99');
INSERT INTO `cw_ship_ranges` VALUES ('77', '100', '0', '99999', '34');
INSERT INTO `cw_ship_ranges` VALUES ('78', '101', '0', '100', '22');
INSERT INTO `cw_ship_ranges` VALUES ('79', '101', '100.01', '200', '33');
INSERT INTO `cw_ship_ranges` VALUES ('80', '101', '200.01', '300', '45');
INSERT INTO `cw_ship_ranges` VALUES ('81', '101', '300.01', '400', '60');
INSERT INTO `cw_ship_ranges` VALUES ('82', '102', '0', '100', '35');
INSERT INTO `cw_ship_ranges` VALUES ('83', '102', '100.01', '200', '55');
INSERT INTO `cw_ship_ranges` VALUES ('84', '102', '200.01', '300', '75');
INSERT INTO `cw_ship_ranges` VALUES ('85', '102', '300.01', '400', '95');
INSERT INTO `cw_ship_ranges` VALUES ('86', '101', '400.01', '100000000', '120');
INSERT INTO `cw_ship_ranges` VALUES ('87', '102', '400.01', '100000000', '125');

-- ----------------------------
-- Table structure for `cw_sku_options`
-- ----------------------------
DROP TABLE IF EXISTS `cw_sku_options`;
CREATE TABLE `cw_sku_options` (
  `sku_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_option2sku_id` int(11) NOT NULL DEFAULT '0',
  `sku_option2option_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sku_option_id`),
  KEY `sku_option2sku_id_idx` (`sku_option2sku_id`),
  KEY `sku_option2option_id_idx` (`sku_option2option_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_sku_options
-- ----------------------------
INSERT INTO `cw_sku_options` VALUES ('2335', '163', '109');
INSERT INTO `cw_sku_options` VALUES ('2334', '163', '114');
INSERT INTO `cw_sku_options` VALUES ('2323', '212', '150');
INSERT INTO `cw_sku_options` VALUES ('2333', '164', '114');
INSERT INTO `cw_sku_options` VALUES ('2345', '158', '113');
INSERT INTO `cw_sku_options` VALUES ('2343', '159', '109');
INSERT INTO `cw_sku_options` VALUES ('2342', '159', '113');
INSERT INTO `cw_sku_options` VALUES ('2341', '160', '113');
INSERT INTO `cw_sku_options` VALUES ('2339', '161', '111');
INSERT INTO `cw_sku_options` VALUES ('2338', '161', '113');
INSERT INTO `cw_sku_options` VALUES ('2337', '162', '114');
INSERT INTO `cw_sku_options` VALUES ('2336', '162', '108');
INSERT INTO `cw_sku_options` VALUES ('2344', '158', '108');
INSERT INTO `cw_sku_options` VALUES ('2332', '164', '110');
INSERT INTO `cw_sku_options` VALUES ('2340', '160', '110');
INSERT INTO `cw_sku_options` VALUES ('2331', '165', '109');
INSERT INTO `cw_sku_options` VALUES ('2330', '165', '115');
INSERT INTO `cw_sku_options` VALUES ('2557', '167', '168');
INSERT INTO `cw_sku_options` VALUES ('2556', '167', '119');
INSERT INTO `cw_sku_options` VALUES ('2560', '168', '120');
INSERT INTO `cw_sku_options` VALUES ('2559', '168', '117');
INSERT INTO `cw_sku_options` VALUES ('2563', '169', '121');
INSERT INTO `cw_sku_options` VALUES ('2562', '169', '117');
INSERT INTO `cw_sku_options` VALUES ('2566', '170', '168');
INSERT INTO `cw_sku_options` VALUES ('2565', '170', '118');
INSERT INTO `cw_sku_options` VALUES ('2569', '171', '168');
INSERT INTO `cw_sku_options` VALUES ('2568', '171', '118');
INSERT INTO `cw_sku_options` VALUES ('2572', '172', '121');
INSERT INTO `cw_sku_options` VALUES ('2571', '172', '168');
INSERT INTO `cw_sku_options` VALUES ('2575', '173', '116');
INSERT INTO `cw_sku_options` VALUES ('2574', '173', '119');
INSERT INTO `cw_sku_options` VALUES ('2578', '174', '116');
INSERT INTO `cw_sku_options` VALUES ('2577', '174', '120');
INSERT INTO `cw_sku_options` VALUES ('2581', '175', '116');
INSERT INTO `cw_sku_options` VALUES ('2580', '175', '168');
INSERT INTO `cw_sku_options` VALUES ('2315', '209', '151');
INSERT INTO `cw_sku_options` VALUES ('2321', '208', '150');
INSERT INTO `cw_sku_options` VALUES ('2320', '208', '114');
INSERT INTO `cw_sku_options` VALUES ('2609', '154', '112');
INSERT INTO `cw_sku_options` VALUES ('2608', '154', '108');
INSERT INTO `cw_sku_options` VALUES ('2313', '205', '113');
INSERT INTO `cw_sku_options` VALUES ('2312', '205', '151');
INSERT INTO `cw_sku_options` VALUES ('2319', '204', '113');
INSERT INTO `cw_sku_options` VALUES ('2318', '204', '150');
INSERT INTO `cw_sku_options` VALUES ('2325', '203', '149');
INSERT INTO `cw_sku_options` VALUES ('2324', '203', '113');
INSERT INTO `cw_sku_options` VALUES ('2309', '202', '113');
INSERT INTO `cw_sku_options` VALUES ('2308', '202', '148');
INSERT INTO `cw_sku_options` VALUES ('2512', '184', '133');
INSERT INTO `cw_sku_options` VALUES ('2511', '184', '127');
INSERT INTO `cw_sku_options` VALUES ('2510', '184', '136');
INSERT INTO `cw_sku_options` VALUES ('2509', '184', '130');
INSERT INTO `cw_sku_options` VALUES ('2508', '184', '142');
INSERT INTO `cw_sku_options` VALUES ('2507', '184', '139');
INSERT INTO `cw_sku_options` VALUES ('2518', '185', '142');
INSERT INTO `cw_sku_options` VALUES ('2517', '185', '131');
INSERT INTO `cw_sku_options` VALUES ('2516', '185', '128');
INSERT INTO `cw_sku_options` VALUES ('2515', '185', '134');
INSERT INTO `cw_sku_options` VALUES ('2514', '185', '139');
INSERT INTO `cw_sku_options` VALUES ('2513', '185', '137');
INSERT INTO `cw_sku_options` VALUES ('2524', '186', '138');
INSERT INTO `cw_sku_options` VALUES ('2523', '186', '135');
INSERT INTO `cw_sku_options` VALUES ('2522', '186', '129');
INSERT INTO `cw_sku_options` VALUES ('2521', '186', '142');
INSERT INTO `cw_sku_options` VALUES ('2520', '186', '130');
INSERT INTO `cw_sku_options` VALUES ('2519', '186', '140');
INSERT INTO `cw_sku_options` VALUES ('1732', '187', '142');
INSERT INTO `cw_sku_options` VALUES ('1731', '187', '133');
INSERT INTO `cw_sku_options` VALUES ('1730', '187', '127');
INSERT INTO `cw_sku_options` VALUES ('1729', '187', '136');
INSERT INTO `cw_sku_options` VALUES ('1728', '187', '130');
INSERT INTO `cw_sku_options` VALUES ('1727', '187', '139');
INSERT INTO `cw_sku_options` VALUES ('1744', '188', '137');
INSERT INTO `cw_sku_options` VALUES ('1743', '188', '142');
INSERT INTO `cw_sku_options` VALUES ('1742', '188', '140');
INSERT INTO `cw_sku_options` VALUES ('1741', '188', '128');
INSERT INTO `cw_sku_options` VALUES ('1740', '188', '134');
INSERT INTO `cw_sku_options` VALUES ('1739', '188', '131');
INSERT INTO `cw_sku_options` VALUES ('2601', '194', '146');
INSERT INTO `cw_sku_options` VALUES ('2600', '194', '143');
INSERT INTO `cw_sku_options` VALUES ('2605', '193', '146');
INSERT INTO `cw_sku_options` VALUES ('2604', '193', '145');
INSERT INTO `cw_sku_options` VALUES ('2589', '192', '113');
INSERT INTO `cw_sku_options` VALUES ('2588', '192', '146');
INSERT INTO `cw_sku_options` VALUES ('2599', '195', '144');
INSERT INTO `cw_sku_options` VALUES ('2598', '195', '146');
INSERT INTO `cw_sku_options` VALUES ('2603', '196', '114');
INSERT INTO `cw_sku_options` VALUES ('2602', '196', '146');
INSERT INTO `cw_sku_options` VALUES ('2597', '197', '147');
INSERT INTO `cw_sku_options` VALUES ('2596', '197', '113');
INSERT INTO `cw_sku_options` VALUES ('2595', '198', '147');
INSERT INTO `cw_sku_options` VALUES ('2594', '198', '145');
INSERT INTO `cw_sku_options` VALUES ('2593', '199', '147');
INSERT INTO `cw_sku_options` VALUES ('2592', '199', '143');
INSERT INTO `cw_sku_options` VALUES ('2591', '200', '147');
INSERT INTO `cw_sku_options` VALUES ('2311', '210', '152');
INSERT INTO `cw_sku_options` VALUES ('2310', '210', '148');
INSERT INTO `cw_sku_options` VALUES ('2314', '209', '114');
INSERT INTO `cw_sku_options` VALUES ('2590', '200', '144');
INSERT INTO `cw_sku_options` VALUES ('2322', '212', '152');
INSERT INTO `cw_sku_options` VALUES ('2317', '213', '151');
INSERT INTO `cw_sku_options` VALUES ('2316', '213', '152');
INSERT INTO `cw_sku_options` VALUES ('1857', '217', '150');
INSERT INTO `cw_sku_options` VALUES ('1858', '216', '151');
INSERT INTO `cw_sku_options` VALUES ('1864', '219', '108');
INSERT INTO `cw_sku_options` VALUES ('1865', '220', '109');
INSERT INTO `cw_sku_options` VALUES ('1866', '221', '110');
INSERT INTO `cw_sku_options` VALUES ('1868', '222', '111');
INSERT INTO `cw_sku_options` VALUES ('1869', '223', '119');
INSERT INTO `cw_sku_options` VALUES ('1871', '224', '153');
INSERT INTO `cw_sku_options` VALUES ('1873', '225', '120');
INSERT INTO `cw_sku_options` VALUES ('1874', '226', '154');
INSERT INTO `cw_sku_options` VALUES ('1875', '227', '121');
INSERT INTO `cw_sku_options` VALUES ('1876', '228', '155');
INSERT INTO `cw_sku_options` VALUES ('1877', '229', '114');
INSERT INTO `cw_sku_options` VALUES ('1878', '230', '115');
INSERT INTO `cw_sku_options` VALUES ('1879', '231', '113');
INSERT INTO `cw_sku_options` VALUES ('2327', '232', '113');
INSERT INTO `cw_sku_options` VALUES ('2350', '233', '115');
INSERT INTO `cw_sku_options` VALUES ('1903', '239', '159');
INSERT INTO `cw_sku_options` VALUES ('1902', '239', '115');
INSERT INTO `cw_sku_options` VALUES ('1905', '240', '114');
INSERT INTO `cw_sku_options` VALUES ('1904', '240', '159');
INSERT INTO `cw_sku_options` VALUES ('1907', '241', '115');
INSERT INTO `cw_sku_options` VALUES ('1906', '241', '160');
INSERT INTO `cw_sku_options` VALUES ('1909', '242', '114');
INSERT INTO `cw_sku_options` VALUES ('1908', '242', '160');
INSERT INTO `cw_sku_options` VALUES ('1917', '243', '115');
INSERT INTO `cw_sku_options` VALUES ('1916', '243', '158');
INSERT INTO `cw_sku_options` VALUES ('1915', '244', '158');
INSERT INTO `cw_sku_options` VALUES ('1914', '244', '113');
INSERT INTO `cw_sku_options` VALUES ('1918', '245', '159');
INSERT INTO `cw_sku_options` VALUES ('1919', '245', '115');
INSERT INTO `cw_sku_options` VALUES ('1923', '246', '113');
INSERT INTO `cw_sku_options` VALUES ('1922', '246', '159');
INSERT INTO `cw_sku_options` VALUES ('1924', '247', '156');
INSERT INTO `cw_sku_options` VALUES ('1925', '248', '157');
INSERT INTO `cw_sku_options` VALUES ('2555', '167', '117');
INSERT INTO `cw_sku_options` VALUES ('2558', '168', '169');
INSERT INTO `cw_sku_options` VALUES ('2561', '169', '168');
INSERT INTO `cw_sku_options` VALUES ('2564', '170', '119');
INSERT INTO `cw_sku_options` VALUES ('2567', '171', '120');
INSERT INTO `cw_sku_options` VALUES ('2570', '172', '118');
INSERT INTO `cw_sku_options` VALUES ('2573', '173', '168');
INSERT INTO `cw_sku_options` VALUES ('2576', '174', '168');
INSERT INTO `cw_sku_options` VALUES ('2579', '175', '121');
INSERT INTO `cw_sku_options` VALUES ('2582', '259', '141');
INSERT INTO `cw_sku_options` VALUES ('2583', '259', '135');
INSERT INTO `cw_sku_options` VALUES ('2584', '259', '129');
INSERT INTO `cw_sku_options` VALUES ('2585', '259', '137');
INSERT INTO `cw_sku_options` VALUES ('2586', '259', '130');
INSERT INTO `cw_sku_options` VALUES ('2587', '259', '140');
INSERT INTO `cw_sku_options` VALUES ('2610', '156', '110');
INSERT INTO `cw_sku_options` VALUES ('2611', '156', '112');
INSERT INTO `cw_sku_options` VALUES ('2612', '155', '109');
INSERT INTO `cw_sku_options` VALUES ('2613', '155', '112');
INSERT INTO `cw_sku_options` VALUES ('2614', '166', '115');
INSERT INTO `cw_sku_options` VALUES ('2615', '166', '110');

-- ----------------------------
-- Table structure for `cw_skus`
-- ----------------------------
DROP TABLE IF EXISTS `cw_skus`;
CREATE TABLE `cw_skus` (
  `sku_id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_merchant_sku_id` varchar(50) DEFAULT NULL,
  `sku_product_id` int(11) NOT NULL DEFAULT '0',
  `sku_price` double DEFAULT NULL,
  `sku_weight` double DEFAULT '0',
  `sku_stock` int(11) DEFAULT '0',
  `sku_on_web` smallint(6) DEFAULT '1',
  `sku_sort` float(11,3) DEFAULT '1.000',
  `sku_alt_price` double DEFAULT NULL,
  `sku_ship_base` double DEFAULT NULL,
  `sku_download_file` varchar(255) DEFAULT NULL,
  `sku_download_id` varchar(255) DEFAULT NULL,
  `sku_download_version` varchar(50) DEFAULT NULL,
  `sku_download_limit` int(7) DEFAULT '0',
  PRIMARY KEY (`sku_id`),
  KEY `sku_product_id_idx` (`sku_product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_skus
-- ----------------------------
INSERT INTO `cw_skus` VALUES ('154', 'BriteT-Smll-Green', '93', '22.22', '0.5', '0', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('155', 'BriteT-mMedm-Green', '93', '22.5', '0.5', '70', '1', '0.000', '25.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('156', 'BriteT-Lrg-Green', '93', '22.5', '0.5', '63', '1', '0.000', '25.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('158', 'BriteT-Smll-Blue', '93', '22.5', '0.5', '0', '1', '0.000', '25.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('159', 'BriteT-Medm-Blue', '93', '22.5', '0.5', '76', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('160', 'BriteT-Lrg-Blue', '93', '22.5', '0.5', '16', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('161', 'BriteT-XLrg-Blue', '93', '24.5', '0.5', '21', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('162', 'BriteT-Smll-Yellow', '93', '22.5', '0.5', '0', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('163', 'BriteT-Medm-Yellow', '93', '22.5', '0.5', '40', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('164', 'BriteT-Lrg-Yellow', '93', '22.5', '0.5', '58', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('165', 'BriteT-Medm-Red', '93', '22.5', '0.5', '0', '1', '0.000', '20.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('166', 'BriteT-Lrg-Red', '93', '22.5', '0.5', '14', '1', '0.000', '25.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('167', 'Jeans-Ind-Small', '94', '33.33', '3.3', '0', '0', '0.000', '33.33', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('168', 'Jeans-Ind-Med', '94', '34', '1.2', '36', '1', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('169', 'Jeans-Ind-Large', '94', '34', '1.2', '43', '0', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('170', 'Jeans-Stone-Small', '94', '34', '1.2', '44', '1', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('171', 'Jeans-Stone-Med', '94', '34', '1.2', '0', '0', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('172', 'Jeans-Stone-Large', '94', '34', '1.2', '89', '1', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('173', 'Jeans-MediumBlue-Small', '94', '34', '1.2', '59', '1', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('174', 'Jeans-MediumBlue-Med', '94', '34', '1.2', '76', '0', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('175', 'Jeans-MediumBlue-Large', '94', '34', '1.2', '62', '1', '0.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('176', 'SLR-Digital', '95', '869', '4', '492', '1', '0.000', '926', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('177', 'BigScreenPlasma', '96', '1250', '80', '471', '1', '0.000', '1450', '55', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('202', 'PastelComfort-blu-T', '102', '35', '9999', '463', '1', '1.000', '42', '7.77', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('203', 'PastelComfort-blu-F', '102', '38', '7', '467', '1', '1.200', '48', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('204', 'PastelComfort-blu-Q', '102', '39', '7.5', '479', '1', '1.300', '49', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('205', 'PastelComfort-blu-K', '102', '56', '8', '462', '1', '1.400', '60', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('206', 'PastelComfort-Yellow-T', '102', '35', '6', '475', '1', '2.000', '42', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('207', 'PastelComfort-Yellow-F ', '102', '38', '7', '486', '1', '2.200', '48', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('184', 'Basic-Home-System', '99', '899.22', '50', '0', '1', '1.000', '1045', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('185', 'Advanced-Home-System', '99', '999', '45', '0', '1', '2.000', '1100', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('186', 'PowerUser-System', '99', '1155', '45', '0', '1', '3.000', '1400', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('187', 'Basic-Home-User', '100', '990', '5', '971', '1', '1.000', '1045', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('188', 'Advanced-System', '100', '1999', '5', '53', '1', '2.000', '1400', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('192', 'BrightTerryTowels-Sml-blu', '101', '12', '1', '1000', '1', '1.000', '14', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('193', 'BrightTerryTowels-Sml-orng', '101', '12', '1', '1000', '1', '1.000', '14', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('194', 'BrightTerryTowels-Sml-pnk', '101', '12', '1', '1000', '1', '1.000', '14', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('195', 'BrightTerryTowels-Sml-mint', '101', '12', '1', '1000', '1', '1.000', '14', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('196', 'BrightTerryTowels-Sml-yellow', '101', '12', '1', '1000', '1', '1.000', '14', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('197', 'BrightTerryTowels-Lrg-blu ', '101', '14', '1', '1000', '1', '1.000', '16', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('198', 'BrightTerryTowels-Lrg-orng ', '101', '14', '1', '1000', '1', '1.000', '16', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('199', 'BrightTerryTowels-Lrg-pnk ', '101', '14', '1', '1000', '1', '1.000', '16', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('200', 'BrightTerryTowels-Lrg-mint ', '101', '14', '1', '1000', '1', '1.000', '16', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('201', 'BrightTerryTowels-Lrg-yellow ', '101', '14', '1', '971', '1', '1.000', '16', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('208', 'PastelComfort-Yellow-Q ', '102', '39', '7.5', '500', '1', '2.300', '49', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('209', 'PastelComfort-Yellow-K', '102', '56', '8', '480', '1', '2.400', '60', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('210', 'PastelComfort-prpl-T ', '102', '35', '878', '489', '1', '3.100', '42', '7.77', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('211', 'PastelComfort-prpl-F ', '102', '38', '7', '467', '1', '3.200', '48', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('212', 'PastelComfort-prpl-Q ', '102', '39', '7.5', '493', '1', '3.300', '49', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('213', 'PastelComfort-prpl-K ', '102', '56', '8', '480', '1', '3.400', '60', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('214', 'RBBroom', '103', '28', '4', '970', '1', '0.000', '41', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('217', 'DownPillowKing', '104', '28', '3', '961', '1', '1.000', '32', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('216', 'DownPillowQueen', '104', '29', '3', '986', '1', '2.000', '36', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('218', 'DigitalVideoHD', '105', '425', '2', '822', '1', '0.000', '478', '9.12', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('219', 'Rugby-Small', '106', '22', '1', '477', '1', '1.000', '24', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('220', 'Rugby-Med', '106', '24', '1', '456', '1', '2.000', '26', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('221', 'Rugby-Large', '106', '24', '1', '489', '1', '3.000', '26', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('222', 'Rugby-X-Large', '106', '25', '1', '488', '1', '4.000', '28', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('223', 'KomfyKhakis-34X30', '107', '34', '2', '480', '1', '1.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('224', 'KomfyKhakis-34X32', '107', '34', '2', '466', '1', '2.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('225', 'KomfyKhakis-36X30', '107', '34', '2', '457', '1', '3.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('226', 'KomfyKhakis-36X32', '107', '34', '2', '488', '1', '4.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('227', 'KomfyKhakis-38X30', '107', '34', '2', '494', '1', '5.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('228', 'KomfyKhakis-38X32', '107', '34', '2', '489', '1', '6.000', '38', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('229', 'RetroTV-Yellow', '108', '500', '35', '462', '1', '1.000', '525', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('230', 'RetroTV-Red', '108', '500', '35', '478', '1', '2.000', '525', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('231', 'RetroTV-Blue', '108', '500', '35', '491', '1', '3.000', '525', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('232', 'DigitalPoint-n-Shoot-Blue', '109', '125', '11', '449', '1', '1.000', '139.99', '2.22', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('233', 'DigitalPoint-n-Shoot-Red', '109', '125', '1', '259', '1', '2.000', '139.99', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('239', 'LawnPower Riding-6r', '110', '899', '120', '94', '1', '1.000', '999', '56', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('240', 'SKU: LawnPower Riding-6y', '110', '899', '120', '94', '1', '2.000', '999', '56', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('241', 'SKU: LawnPower Riding-12r', '110', '999', '148', '66', '1', '3.000', '1200', '66', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('242', 'SKU: LawnPower Riding-12y', '110', '999', '148', '92', '1', '3.000', '1200', '66', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('243', 'LawnPower-Push-3.5r', '111', '389', '60', '476', '1', '1.000', '425', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('244', 'LawnPower-Push-3.5b', '111', '389', '60', '482', '1', '2.000', '425', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('245', 'LawnPower-Push-6r', '111', '412', '65', '492', '1', '3.000', '445', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('246', 'LawnPower-Push-6b', '111', '412', '65', '493', '1', '4.000', '445', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('247', 'WeedAWack-.25', '112', '120', '8', '492', '1', '0.000', '135', '0', '', '', '', '0');
INSERT INTO `cw_skus` VALUES ('248', 'WeedAWack-.5', '112', '130', '9', '465', '1', '0.000', '155', '0', '', '', '', '0');


-- ----------------------------
-- Table structure for `cw_stateprov`
-- ----------------------------
DROP TABLE IF EXISTS `cw_stateprov`;
CREATE TABLE `cw_stateprov` (
  `stateprov_id` int(11) NOT NULL AUTO_INCREMENT,
  `stateprov_code` varchar(50) DEFAULT NULL,
  `stateprov_name` varchar(255) DEFAULT NULL,
  `stateprov_country_id` int(11) DEFAULT '0',
  `stateprov_tax` double DEFAULT '0',
  `stateprov_ship_ext` double DEFAULT '0',
  `stateprov_archive` smallint(6) DEFAULT '0',
  PRIMARY KEY (`stateprov_id`),
  KEY `stateprov_country_id_idx` (`stateprov_country_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_stateprov
-- ----------------------------
INSERT INTO `cw_stateprov` VALUES ('1', 'AL', 'Alabama', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2', 'AK', 'Alaska', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3', 'AZ', 'Arizona', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4', 'AR', 'Arkansas', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('5', 'CA', 'California', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('6', 'CO', 'Colorado', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('7', 'CT', 'Connecticut', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('8', 'DE', 'Delaware', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('9', 'FL', 'Florida', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('10', 'GA', 'Georgia', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('11', 'HI', 'Hawaii', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('12', 'ID', 'Idaho', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('13', 'IL', 'Illinois', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('14', 'IN', 'Indiana', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('15', 'IA', 'Iowa', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('16', 'KS', 'Kansas', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('17', 'KY', 'Kentucky', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('18', 'LA', 'Louisiana', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('19', 'ME', 'Maine', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('20', 'MD', 'Maryland', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('21', 'MA', 'Massachusetts', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('22', 'MI', 'Michigan', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('23', 'MN', 'Minnesota', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('24', 'MS', 'Mississippi', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('25', 'MO', 'Missouri', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('26', 'MT', 'Montana', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('27', 'NE', 'Nebraska', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('28', 'NV', 'Nevada', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('29', 'NH', 'New Hampshire', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('30', 'NJ', 'New Jersey', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('31', 'NM', 'New Mexico', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('32', 'NY', 'New York', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('33', 'NC', 'North Carolina', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('34', 'ND', 'North Dakota', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('35', 'OH', 'Ohio', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('36', 'OK', 'Oklahoma', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('37', 'OR', 'Oregon', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('38', 'PA', 'Pennsylvania', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('39', 'RI', 'Rhode Island', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('40', 'SC', 'South Carolina', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('41', 'SD', 'South Dakota', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('42', 'TN', 'Tennessee', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('43', 'TX', 'Texas', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('44', 'UT', 'Utah', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('45', 'VT', 'Vermont', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('46', 'VA', 'Virginia', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('47', 'WA', 'Washington', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('48', 'WV', 'West Virginia', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('49', 'WI', 'Wisconsin', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('50', 'WY', 'Wyoming', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('161', 'None', 'None', '14', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('70', 'None', 'None', '4', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('72', 'None', 'None', '6', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('73', 'None', 'None', '7', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('75', 'DC', 'District of Columbia', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('78', 'AA', 'Armed Forces America', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('79', 'AE', 'Armed Forces Europe', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('80', 'AP', 'Armed Forces Pacific', '1', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('81', 'None', 'None', '9', '0', '0', '1');
INSERT INTO `cw_stateprov` VALUES ('83', 'AB', 'Alberta', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('84', 'BC', 'British Columbia', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('85', 'MB', 'Manitoba', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('86', 'NB', 'New Brunswick', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('88', 'NL', 'Newfoundland and Labrador', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('89', 'NS', 'Nova Scotia', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('90', 'ON', 'Ontario', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('91', 'PE', 'Prince Edward Island', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('92', 'QC', 'Quebec', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('93', 'SK', 'Saskatchewan', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('94', 'NT', 'Northwest Territories', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('95', 'NU', 'Nunavut', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('96', 'YT', 'Yukon', '9', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('348', 'AU-ACT', 'Australian Capital Territory', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('349', 'AU-NSW', 'New South Wales', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('350', 'AU-NT', 'Northern Territory', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('351', 'AU-QLD', 'Queensland', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('352', 'AU-SA', 'South Australia', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('353', 'AU-TAS', 'Tasmania', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('354', 'AU-VIC', 'Victoria', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('355', 'AU-WA', 'Western Australia', '23', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('773', 'CA-AB', 'Alberta', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('774', 'CA-BC', 'British Columbia', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('775', 'CA-MB', 'Manitoba', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('776', 'CA-NB', 'New Brunswick', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('777', 'CA-NL', 'Newfoundland and Labrador', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('778', 'CA-NT', 'Northwest Territories', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('779', 'CA-NS', 'Nova Scotia', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('780', 'CA-NU', 'Nunavut', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('781', 'CA-ON', 'Ontario', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('782', 'CA-PE', 'Prince Edward Island', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('783', 'CA-QC', 'Quebec', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('784', 'CA-SK', 'Saskatchewan', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('785', 'CA-YT', 'Yukon Territory', '45', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('860', 'CN-34', 'Anhui', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('861', 'CN-92', 'Aomen (zh) ***', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('862', 'CN-11', 'Beijing', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('863', 'CN-50', 'Chongqing', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('864', 'CN-35', 'Fujian', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('865', 'CN-62', 'Gansu', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('866', 'CN-44', 'Guangdong', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('867', 'CN-45', 'Guangxi', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('868', 'CN-52', 'Guizhou', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('869', 'CN-46', 'Hainan', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('870', 'CN-13', 'Hebei', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('871', 'CN-23', 'Heilongjiang', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('872', 'CN-41', 'Henan', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('873', 'CN-42', 'Hubei', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('874', 'CN-43', 'Hunan', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('875', 'CN-32', 'Jiangsu', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('876', 'CN-36', 'Jiangxi', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('877', 'CN-22', 'Jilin', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('878', 'CN-21', 'Liaoning', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('879', 'CN-15', 'Nei Mongol (mn)', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('880', 'CN-64', 'Ningxia', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('881', 'CN-63', 'Qinghai', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('882', 'CN-61', 'Shaanxi', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('883', 'CN-37', 'Shandong', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('884', 'CN-31', 'Shanghai', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('885', 'CN-14', 'Shanxi', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('886', 'CN-51', 'Sichuan', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('887', 'CN-71', 'Taiwan *', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('888', 'CN-12', 'Tianjin', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('889', 'CN-91', 'Xianggang (zh) **', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('890', 'CN-65', 'Xinjiang', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('891', 'CN-54', 'Xizang', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('892', 'CN-53', 'Yunnan', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('893', 'CN-33', 'Zhejiang', '51', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1219', 'FR-01', 'Ain', '75', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1711', 'IE-CW', 'Carlow', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1712', 'IE-CN', 'Cavan', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1713', 'IE-CE', 'Clare', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1714', 'IE-C', 'Cork', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1715', 'IE-DL', 'Donegal', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1716', 'IE-D', 'Dublin', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1717', 'IE-G', 'Galway', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1718', 'IE-KY', 'Kerry', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1719', 'IE-KE', 'Kildare', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1720', 'IE-KK', 'Kilkenny', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1721', 'IE-LS', 'Laois', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1722', 'IE-LM', 'Leitrim', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1723', 'IE-LK', 'Limerick', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1724', 'IE-LD', 'Longford', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1725', 'IE-LH', 'Louth', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1726', 'IE-MO', 'Mayo', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1727', 'IE-MH', 'Meath', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1728', 'IE-MN', 'Monaghan', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1729', 'IE-OY', 'Offaly', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1730', 'IE-RN', 'Roscommon', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1731', 'IE-SO', 'Sligo', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1732', 'IE-TA', 'Tipperary', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1733', 'IE-WD', 'Waterford', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1734', 'IE-WH', 'Westmeath', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1735', 'IE-WX', 'Wexford', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1736', 'IE-WW', 'Wicklow', '96', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1737', 'IL-D', 'HaDarom', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1738', 'IL-HA', 'Haifa', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1739', 'IL-M', 'HaMerkaz', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1740', 'IL-Z', 'HaZafon', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1741', 'IL-TA', 'Tel-Aviv', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1742', 'IL-JM', 'Yerushalayim', '97', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1743', 'IT-AG', 'Agrigento', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1744', 'IT-AL', 'Alessandria', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1745', 'IT-AN', 'Ancona', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1746', 'IT-AO', 'Aosta', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1747', 'IT-AR', 'Arezzo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1748', 'IT-AP', 'Ascoli Piceno', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1749', 'IT-AT', 'Asti', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1750', 'IT-AV', 'Avellino', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1751', 'IT-BA', 'Bari', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1752', 'IT-BT', 'Barletta-Andria-Trani', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1753', 'IT-BL', 'Belluno', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1754', 'IT-BN', 'Benevento', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1755', 'IT-BG', 'Bergamo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1756', 'IT-BI', 'Biella', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1757', 'IT-BO', 'Bologna', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1758', 'IT-BZ', 'Bolzano', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1759', 'IT-BS', 'Brescia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1760', 'IT-BR', 'Brindisi', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1761', 'IT-CA', 'Cagliari', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1762', 'IT-CL', 'Caltanissetta', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1763', 'IT-CB', 'Campobasso', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1764', 'IT-CI', 'Carbonia-Iglesias', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1765', 'IT-CE', 'Caserta', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1766', 'IT-CT', 'Catania', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1767', 'IT-CZ', 'Catanzaro', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1768', 'IT-CH', 'Chieti', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1769', 'IT-CO', 'Como', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1770', 'IT-CS', 'Cosenza', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1771', 'IT-CR', 'Cremona', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1772', 'IT-KR', 'Crotone', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1773', 'IT-CN', 'Cuneo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1774', 'IT-EN', 'Enna', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1775', 'IT-FM', 'Fermo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1776', 'IT-FE', 'Ferrara', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1777', 'IT-FI', 'Firenze', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1778', 'IT-FG', 'Foggia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1779', 'IT-FC', 'Forli-Cesena', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1780', 'IT-FR', 'Frosinone', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1781', 'IT-GE', 'Genova', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1782', 'IT-GO', 'Gorizia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1783', 'IT-GR', 'Grosseto', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1784', 'IT-IM', 'Imperia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1785', 'IT-IS', 'Isernia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1786', 'IT-AQ', 'L\'Aquila', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1787', 'IT-SP', 'La Spezia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1788', 'IT-LT', 'Latina', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1789', 'IT-LE', 'Lecce', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1790', 'IT-LC', 'Lecco', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1791', 'IT-LI', 'Livorno', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1792', 'IT-LO', 'Lodi', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1793', 'IT-LU', 'Lucca', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1794', 'IT-MC', 'Macerata', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1795', 'IT-MN', 'Mantova', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1796', 'IT-MS', 'Massa-Carrara', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1797', 'IT-MT', 'Matera', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1798', 'IT-MA', 'Medio Campidano', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1799', 'IT-ME', 'Messina', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1800', 'IT-MI', 'Milano', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1801', 'IT-MO', 'Modena', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1802', 'IT-MB', 'Monza e Brianza', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1803', 'IT-NA', 'Napoli', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1804', 'IT-NO', 'Novara', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1805', 'IT-NU', 'Nuoro', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1806', 'IT-OG', 'Ogliastra', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1807', 'IT-OL', 'Olbia-Tempio', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1808', 'IT-OR', 'Oristano', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1809', 'IT-PD', 'Padova', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1810', 'IT-PA', 'Palermo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1811', 'IT-PR', 'Parma', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1812', 'IT-PV', 'Pavia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1813', 'IT-PG', 'Perugia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1814', 'IT-PS', 'Pesaro e Urbino', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1815', 'IT-PE', 'Pescara', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1816', 'IT-PC', 'Piacenza', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1817', 'IT-PI', 'Pisa', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1818', 'IT-PT', 'Pistoia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1819', 'IT-PN', 'Pordenone', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1820', 'IT-PZ', 'Potenza', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1821', 'IT-PO', 'Prato', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1822', 'IT-RG', 'Ragusa', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1823', 'IT-RA', 'Ravenna', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1824', 'IT-RC', 'Reggio Calabria', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1825', 'IT-RE', 'Reggio Emilia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1826', 'IT-RI', 'Rieti', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1827', 'IT-RN', 'Rimini', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1828', 'IT-RM', 'Roma', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1829', 'IT-RO', 'Rovigo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1830', 'IT-SA', 'Salerno', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1831', 'IT-SS', 'Sassari', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1832', 'IT-SV', 'Savona', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1833', 'IT-SI', 'Siena', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1834', 'IT-SR', 'Siracusa', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1835', 'IT-SO', 'Sondrio', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1836', 'IT-TA', 'Taranto', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1837', 'IT-TE', 'Teramo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1838', 'IT-TR', 'Terni', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1839', 'IT-TO', 'Torino', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1840', 'IT-TP', 'Trapani', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1841', 'IT-TN', 'Trento', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1842', 'IT-TV', 'Treviso', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1843', 'IT-TS', 'Trieste', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1844', 'IT-UD', 'Udine', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1845', 'IT-VA', 'Varese', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1846', 'IT-VE', 'Venezia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1847', 'IT-VB', 'Verbano-Cusio-Ossola', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1848', 'IT-VC', 'Vercelli', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1849', 'IT-VR', 'Verona', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1850', 'IT-VV', 'Vibo Valentia', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1851', 'IT-VI', 'Vicenza', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('1852', 'IT-VT', 'Viterbo', '98', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2570', 'NZ-AUK', 'Auckland', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2571', 'NZ-BOP', 'Bay of Plenty', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2572', 'NZ-CAN', 'Canterbury', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2573', 'NZ-X1~', 'Chatham Islands', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2574', 'NZ-GIS', 'Gisborne', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2575', 'NZ-HKB', 'Hawkes\'s Bay', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2576', 'NZ-MWT', 'Manawatu-Wanganui', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2577', 'NZ-MBH', 'Marlborough', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2578', 'NZ-NSN', 'Nelson', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2579', 'NZ-NTL', 'Northland', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2580', 'NZ-OTA', 'Otago', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2581', 'NZ-STL', 'Southland', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2582', 'NZ-TKI', 'Taranaki', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2583', 'NZ-TAS', 'Tasman', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2584', 'NZ-WKO', 'Waikato', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2585', 'NZ-WGN', 'Wellington', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('2586', 'NZ-WTC', 'West Coast', '139', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3986', 'GB-ABE', 'Aberdeen City', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3987', 'GB-ABD', 'Aberdeenshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3988', 'GB-ANS', 'Angus', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3989', 'GB-ANT', 'Antrim', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3990', 'GB-ARD', 'Ards', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3991', 'GB-AGB', 'Argyll and Bute', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3992', 'GB-ARM', 'Armagh', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3993', 'GB-BLA', 'Ballymena', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3994', 'GB-BLY', 'Ballymoney', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3995', 'GB-BNB', 'Banbridge', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3996', 'GB-BDG', 'Barking and Dagenham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3997', 'GB-BNE', 'Barnet', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3998', 'GB-BNS', 'Barnsley', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('3999', 'GB-BAS', 'Bath and North East Somerset', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4000', 'GB-BDF', 'Bedfordshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4001', 'GB-BFS', 'Belfast', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4002', 'GB-BEX', 'Bexley', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4003', 'GB-BIR', 'Birmingham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4004', 'GB-BBD', 'Blackburn with Darwen', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4005', 'GB-BPL', 'Blackpool', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4006', 'GB-BGW', 'Blaenau Gwent', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4007', 'GB-BOL', 'Bolton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4008', 'GB-BMH', 'Bournemouth', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4009', 'GB-BRC', 'Bracknell Forest', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4010', 'GB-BRD', 'Bradford', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4011', 'GB-BEN', 'Brent', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4012', 'GB-BGE', 'Bridgend [Pen-y-bont ar Ogwr GB-POG]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4013', 'GB-BNH', 'Brighton and Hove', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4014', 'GB-BST', 'Bristol, City of', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4015', 'GB-BRY', 'Bromley', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4016', 'GB-BKM', 'Buckinghamshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4017', 'GB-BUR', 'Bury', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4018', 'GB-CAY', 'Caerphilly [Caerffili GB-CAF]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4019', 'GB-CLD', 'Calderdale', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4020', 'GB-CAM', 'Cambridgeshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4021', 'GB-CMD', 'Camden', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4022', 'GB-CRF', 'Cardiff [Caerdydd GB-CRD]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4023', 'GB-CMN', 'Carmarthenshire [Sir Gaerfyrddin GB-GFY]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4024', 'GB-CKF', 'Carrickfergus', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4025', 'GB-CSR', 'Castlereagh', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4026', 'GB-CGN', 'Ceredigion [Sir Ceredigion]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4027', 'GB-CHS', 'Cheshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4028', 'GB-CLK', 'Clackmannanshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4029', 'GB-CLR', 'Coleraine', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4030', 'GB-CWY', 'Conwy', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4031', 'GB-CKT', 'Cookstown', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4032', 'GB-CON', 'Cornwall', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4033', 'GB-COV', 'Coventry', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4034', 'GB-CGV', 'Craigavon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4035', 'GB-CRY', 'Croydon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4036', 'GB-CMA', 'Cumbria', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4037', 'GB-DAL', 'Darlington', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4038', 'GB-DEN', 'Denbighshire [Sir Ddinbych GB-DDB]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4039', 'GB-DER', 'Derby', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4040', 'GB-DBY', 'Derbyshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4041', 'GB-DRY', 'Derry', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4042', 'GB-DEV', 'Devon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4043', 'GB-DNC', 'Doncaster', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4044', 'GB-DOR', 'Dorset', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4045', 'GB-DOW', 'Down', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4046', 'GB-DUD', 'Dudley', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4047', 'GB-DGY', 'Dumfries and Galloway', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4048', 'GB-DND', 'Dundee City', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4049', 'GB-DGN', 'Dungannon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4050', 'GB-DUR', 'Durham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4051', 'GB-EAL', 'Ealing', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4052', 'GB-EAY', 'East Ayrshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4053', 'GB-EDU', 'East Dunbartonshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4054', 'GB-ELN', 'East Lothian', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4055', 'GB-ERW', 'East Renfrewshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4056', 'GB-ERY', 'East Riding of Yorkshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4057', 'GB-ESX', 'East Sussex', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4058', 'GB-EDH', 'Edinburgh, City of', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4059', 'GB-ELS', 'Eilean Siar', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4060', 'GB-ENF', 'Enfield', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4061', 'GB-ESS', 'Essex', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4062', 'GB-FAL', 'Falkirk', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4063', 'GB-FER', 'Fermanagh', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4064', 'GB-FIF', 'Fife', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4065', 'GB-FLN', 'Flintshire [Sir y Fflint GB-FFL]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4066', 'GB-GAT', 'Gateshead', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4067', 'GB-GLG', 'Glasgow City', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4068', 'GB-GLS', 'Gloucestershire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4069', 'GB-GRE', 'Greenwich', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4070', 'GB-GWN', 'Gwynedd', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4071', 'GB-HCK', 'Hackney', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4072', 'GB-HAL', 'Halton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4073', 'GB-HMF', 'Hammersmith and Fulham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4074', 'GB-HAM', 'Hampshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4075', 'GB-HRY', 'Haringey', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4076', 'GB-HRW', 'Harrow', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4077', 'GB-HPL', 'Hartlepool', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4078', 'GB-HAV', 'Havering', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4079', 'GB-HEF', 'Herefordshire, County of', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4080', 'GB-HRT', 'Hertfordshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4081', 'GB-HLD', 'Highland', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4082', 'GB-HIL', 'Hillingdon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4083', 'GB-HNS', 'Hounslow', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4084', 'GB-IVC', 'Inverclyde', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4085', 'GB-AGY', 'Isle of Anglesey [Sir Ynys MÃƒÂ´n GB-YNM]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4086', 'GB-IOW', 'Isle of Wight', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4087', 'GB-IOS', 'Isles of Scilly', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4088', 'GB-ISL', 'Islington', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4089', 'GB-KEC', 'Kensington and Chelsea', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4090', 'GB-KEN', 'Kent', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4091', 'GB-KHL', 'Kingston upon Hull, City of', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4092', 'GB-KTT', 'Kingston upon Thames', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4093', 'GB-KIR', 'Kirklees', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4094', 'GB-KWL', 'Knowsley', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4095', 'GB-LBH', 'Lambeth', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4096', 'GB-LAN', 'Lancashire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4097', 'GB-LRN', 'Larne', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4098', 'GB-LDS', 'Leeds', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4099', 'GB-LCE', 'Leicester', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4100', 'GB-LEC', 'Leicestershire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4101', 'GB-LEW', 'Lewisham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4102', 'GB-LMV', 'Limavady', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4103', 'GB-LIN', 'Lincolnshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4104', 'GB-LSB', 'Lisburn', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4105', 'GB-LIV', 'Liverpool', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4106', 'GB-LND', 'London, City of', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4107', 'GB-LUT', 'Luton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4108', 'GB-MFT', 'Magherafelt', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4109', 'GB-MAN', 'Manchester', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4110', 'GB-MDW', 'Medway', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4111', 'GB-MTY', 'Merthyr Tydfil [Merthyr Tudful GB-MTU]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4112', 'GB-MRT', 'Merton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4113', 'GB-MDB', 'Middlesbrough', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4114', 'GB-MLN', 'Midlothian', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4115', 'GB-MIK', 'Milton Keynes', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4116', 'GB-MON', 'Monmouthshire [Sir Fynwy GB-FYN]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4117', 'GB-MRY', 'Moray', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4118', 'GB-MYL', 'Moyle', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4119', 'GB-NTL', 'Neath Port Talbot [Castell-nedd Port Talbot GB-CTL]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4120', 'GB-NET', 'Newcastle upon Tyne', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4121', 'GB-NWM', 'Newham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4122', 'GB-NWP', 'Newport [Casnewydd GB-CNW]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4123', 'GB-NYM', 'Newry and Mourne', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4124', 'GB-NTA', 'Newtownabbey', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4125', 'GB-NFK', 'Norfolk', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4126', 'GB-NAY', 'North Ayrshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4127', 'GB-NDN', 'North Down', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4128', 'GB-NEL', 'North East Lincolnshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4129', 'GB-NLK', 'North Lanarkshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4130', 'GB-NLN', 'North Lincolnshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4131', 'GB-NSM', 'North Somerset', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4132', 'GB-NTY', 'North Tyneside', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4133', 'GB-NYK', 'North Yorkshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4134', 'GB-NTH', 'Northamptonshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4135', 'GB-NBL', 'Northumberland', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4136', 'GB-NGM', 'Nottingham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4137', 'GB-NTT', 'Nottinghamshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4138', 'GB-OLD', 'Oldham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4139', 'GB-OMH', 'Omagh', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4140', 'GB-ORK', 'Orkney Islands', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4141', 'GB-OXF', 'Oxfordshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4142', 'GB-PEM', 'Pembrokeshire [Sir Benfro GB-BNF]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4143', 'GB-PKN', 'Perth and Kinross', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4144', 'GB-PTE', 'Peterborough', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4145', 'GB-PLY', 'Plymouth', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4146', 'GB-POL', 'Poole', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4147', 'GB-POR', 'Portsmouth', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4148', 'GB-POW', 'Powys', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4149', 'GB-RDG', 'Reading', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4150', 'GB-RDB', 'Redbridge', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4151', 'GB-RCC', 'Redcar and Cleveland', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4152', 'GB-RFW', 'Renfrewshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4153', 'GB-RCT', 'Rhondda, Cynon, Taff [Rhondda, Cynon,Taf]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4154', 'GB-RIC', 'Richmond upon Thames', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4155', 'GB-RCH', 'Rochdale', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4156', 'GB-ROT', 'Rotherham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4157', 'GB-RUT', 'Rutland', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4158', 'GB-SLF', 'Salford', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4159', 'GB-SAW', 'Sandwell', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4160', 'GB-SCB', 'Scottish Borders, The', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4161', 'GB-SFT', 'Sefton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4162', 'GB-SHF', 'Sheffield', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4163', 'GB-ZET', 'Shetland Islands', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4164', 'GB-SHR', 'Shropshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4165', 'GB-SLG', 'Slough', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4166', 'GB-SOL', 'Solihull', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4167', 'GB-SOM', 'Somerset', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4168', 'GB-SAY', 'South Ayrshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4169', 'GB-SGC', 'South Gloucestershire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4170', 'GB-SLK', 'South Lanarkshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4171', 'GB-STY', 'South Tyneside', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4172', 'GB-STH', 'Southampton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4173', 'GB-SOS', 'Southend-on-Sea', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4174', 'GB-SWK', 'Southwark', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4175', 'GB-SHN', 'St. Helens', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4176', 'GB-STS', 'Staffordshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4177', 'GB-STG', 'Stirling', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4178', 'GB-SKP', 'Stockport', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4179', 'GB-STT', 'Stockton-on-Tees', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4180', 'GB-STE', 'Stoke-on-Trent', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4181', 'GB-STB', 'Strabane', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4182', 'GB-SFK', 'Suffolk', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4183', 'GB-SND', 'Sunderland', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4184', 'GB-SRY', 'Surrey', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4185', 'GB-STN', 'Sutton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4186', 'GB-SWA', 'Swansea [Abertawe GB-ATA]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4187', 'GB-SWD', 'Swindon', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4188', 'GB-TAM', 'Tameside', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4189', 'GB-TFW', 'Telford and Wrekin', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4190', 'GB-THR', 'Thurrock', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4191', 'GB-TOB', 'Torbay', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4192', 'GB-TOF', 'Torfaen [Tor-faen]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4193', 'GB-TWH', 'Tower Hamlets', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4194', 'GB-TRF', 'Trafford', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4195', 'GB-VGL', 'Vale of Glamorgan, The [Bro Morgannwg GB-BMG]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4196', 'GB-WKF', 'Wakefield', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4197', 'GB-WLL', 'Walsall', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4198', 'GB-WFT', 'Waltham Forest', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4199', 'GB-WND', 'Wandsworth', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4200', 'GB-WRT', 'Warrington', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4201', 'GB-WAR', 'Warwickshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4202', 'GB-WBK', 'West Berkshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4203', 'GB-WDU', 'West Dunbartonshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4204', 'GB-WLN', 'West Lothian', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4205', 'GB-WSX', 'West Sussex', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4206', 'GB-WSM', 'Westminster', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4207', 'GB-WGN', 'Wigan', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4208', 'GB-WIL', 'Wiltshire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4209', 'GB-WNM', 'Windsor and Maidenhead', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4210', 'GB-WRL', 'Wirral', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4211', 'GB-WOK', 'Wokingham', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4212', 'GB-WLV', 'Wolverhampton', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4213', 'GB-WOR', 'Worcestershire', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4214', 'GB-WRX', 'Wrexham [Wrecsam GB-WRC]', '198', '0', '0', '0');
INSERT INTO `cw_stateprov` VALUES ('4215', 'GB-YOR', 'York', '198', '0', '0', '0');

-- ----------------------------
-- Table structure for `cw_tax_groups`
-- ----------------------------
DROP TABLE IF EXISTS `cw_tax_groups`;
CREATE TABLE `cw_tax_groups` (
  `tax_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_group_name` varchar(150) DEFAULT NULL,
  `tax_group_archive` smallint(6) DEFAULT '0',
  `tax_group_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`tax_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_tax_groups
-- ----------------------------
INSERT INTO `cw_tax_groups` VALUES ('3', 'Computers', '0', 'PC080100');
INSERT INTO `cw_tax_groups` VALUES ('10', 'General Sales', '0', 'O9999999');
INSERT INTO `cw_tax_groups` VALUES ('14', 'Clothing', '0', 'PC030100');

-- ----------------------------
-- Table structure for `cw_tax_rates`
-- ----------------------------
DROP TABLE IF EXISTS `cw_tax_rates`;
CREATE TABLE `cw_tax_rates` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_rate_region_id` int(11) DEFAULT '0',
  `tax_rate_group_id` int(11) DEFAULT '0',
  `tax_rate_percentage` double DEFAULT '0',
  PRIMARY KEY (`tax_rate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_tax_rates
-- ----------------------------
INSERT INTO `cw_tax_rates` VALUES ('10', '5', '2', '8');
INSERT INTO `cw_tax_rates` VALUES ('13', '7', '5', '0');
INSERT INTO `cw_tax_rates` VALUES ('29', '8', '5', '12.345');
INSERT INTO `cw_tax_rates` VALUES ('21', '7', '3', '17.2');
INSERT INTO `cw_tax_rates` VALUES ('26', '2', '5', '10');
INSERT INTO `cw_tax_rates` VALUES ('31', '6', '9', '17.5');
INSERT INTO `cw_tax_rates` VALUES ('33', '1', '10', '10');
INSERT INTO `cw_tax_rates` VALUES ('35', '12', '10', '20');

-- ----------------------------
-- Table structure for `cw_tax_regions`
-- ----------------------------
DROP TABLE IF EXISTS `cw_tax_regions`;
CREATE TABLE `cw_tax_regions` (
  `tax_region_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_region_country_id` int(11) NOT NULL DEFAULT '0',
  `tax_region_state_id` int(11) NOT NULL DEFAULT '0',
  `tax_region_label` varchar(150) DEFAULT NULL,
  `tax_region_tax_id` varchar(50) DEFAULT NULL,
  `tax_region_show_id` tinyint(4) DEFAULT NULL,
  `tax_region_ship_tax_method` varchar(50) DEFAULT NULL,
  `tax_region_ship_tax_group_id` int(11) DEFAULT '0',
  PRIMARY KEY (`tax_region_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cw_tax_regions
-- ----------------------------
INSERT INTO `cw_tax_regions` VALUES ('1', '1', '0', 'US Tax', '', '0', 'Highest Item Taxed', '0');
INSERT INTO `cw_tax_regions` VALUES ('4', '3', '51', 'PST', '', '0', 'No Tax', '0');
INSERT INTO `cw_tax_regions` VALUES ('6', '5', '0', 'VAT', '1', '0', 'Highest Item Taxed', '0');
INSERT INTO `cw_tax_regions` VALUES ('7', '3', '56', 'No Tax', '2', '0', 'Highest Item Taxed', '0');
INSERT INTO `cw_tax_regions` VALUES ('9', '6', '0', 'Test123', '12345', '1', 'Highest Item Taxed', '0');
INSERT INTO `cw_tax_regions` VALUES ('12', '198', '0', 'standard vat', '', '0', 'Tax Group', '5');

-- ----------------------------
-- END DATABASE SETUP FILE
-- ----------------------------