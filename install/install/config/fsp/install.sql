-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 01, 2021 at 01:34 PM
-- Server version: 5.7.35-cll-lve
-- PHP Version: 7.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smmprode_smart`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_providers`
--

CREATE TABLE `api_providers` (
  `id` int(10) UNSIGNED NOT NULL,
  `ids` text COLLATE utf8mb4_unicode_ci,
  `uid` int(11) DEFAULT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `balance` decimal(15,5) DEFAULT NULL,
  `currency_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` int(1) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `ids` text,
  `uid` int(11) DEFAULT NULL,
  `name` text,
  `desc` text,
  `image` text,
  `sort` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `childpanels`
--

CREATE TABLE `childpanels` (
  `id` int(11) NOT NULL,
  `ids` text NOT NULL,
  `uid` int(11) NOT NULL,
  `child_key` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `domain` varchar(191) DEFAULT NULL,
  `password` text,
  `charge` decimal(15,4) DEFAULT NULL,
  `status` enum('active','processing','refunded','disabled','terminated') NOT NULL DEFAULT 'processing',
  `renewal_date` date NOT NULL,
  `changed` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `ids` text,
  `uid` int(11) DEFAULT NULL,
  `question` text,
  `answer` longtext,
  `sort` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `general_custom_page`
--

CREATE TABLE `general_custom_page` (
  `id` int(11) NOT NULL,
  `ids` text,
  `pid` int(1) DEFAULT '1',
  `position` int(1) DEFAULT '0',
  `name` text,
  `slug` text,
  `image` text,
  `description` longtext,
  `content` longtext,
  `status` int(1) DEFAULT '1',
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `general_file_manager`
--

CREATE TABLE `general_file_manager` (
  `id` int(11) NOT NULL,
  `ids` text CHARACTER SET utf8mb4,
  `uid` int(11) DEFAULT NULL,
  `file_name` text CHARACTER SET utf8mb4,
  `file_type` text CHARACTER SET utf8mb4,
  `file_ext` text CHARACTER SET utf8mb4,
  `file_size` text CHARACTER SET utf8mb4,
  `is_image` text CHARACTER SET utf8mb4,
  `image_width` int(11) DEFAULT NULL,
  `image_height` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `general_file_manager`
--

INSERT INTO `general_file_manager` (`id`, `ids`, `uid`, `file_name`, `file_type`, `file_ext`, `file_size`, `is_image`, `image_width`, `image_height`, `created`) VALUES
(316, 'd6e5a8ef10ce29508da74572c62c33e0', 60, '77decce2678be03ad868adfc3f5fbea7.png', 'image/png', 'png', '254.05', '1', 1920, 480, '2020-08-17 01:18:57'),
(317, 'c450ec537e9f8cc6fd6d132cb20b90a5', 60, '04147513de125ca924bee8c046ac41e3.png', 'image/png', 'png', '254.05', '1', 1920, 480, '2020-08-17 01:19:15'),
(318, 'af2c8358e6557fef5813fb250ec885eb', 3, '0a9d1e94be80b389788b39b25d88cfa2.png', 'image/png', 'png', '7.78', '1', 167, 72, '2020-11-27 18:36:45'),
(319, 'e27b78234ba5e3c5967900df67c2ae4b', 3, '53531f3ecbfac29da29f819e1070224a.png', 'image/png', 'png', '7.78', '1', 167, 72, '2020-11-27 18:36:48'),
(320, '7f294b76a7655ca2de38b677c002518d', 3, 'b55f609b788b624cd4a4ef03919086af.png', 'image/png', 'png', '7.78', '1', 167, 72, '2020-11-27 18:36:53'),
(321, '454a76b25d27975bce0cba2d51f6307f', 3, '816af90b1b2b15411e73504f5fbf8217.png', 'image/png', 'png', '7.67', '1', 167, 72, '2020-11-27 18:37:14');

-- --------------------------------------------------------

--
-- Table structure for table `general_lang`
--

CREATE TABLE `general_lang` (
  `id` int(11) NOT NULL,
  `ids` varchar(100) DEFAULT NULL,
  `lang_code` varchar(10) DEFAULT NULL,
  `slug` text,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `general_lang_list`
--

CREATE TABLE `general_lang_list` (
  `id` int(11) NOT NULL,
  `ids` varchar(225) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `country_code` varchar(225) DEFAULT NULL,
  `is_default` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `general_lang_list`
--

INSERT INTO `general_lang_list` (`id`, `ids`, `code`, `country_code`, `is_default`, `status`, `created`) VALUES
(3, '39892bf7f5748191c6fefeeada1951e5', 'en', 'GB', 1, 1, '2020-08-30 07:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `general_news`
--

CREATE TABLE `general_news` (
  `id` int(10) UNSIGNED NOT NULL,
  `ids` text COLLATE utf8mb4_unicode_ci,
  `uid` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` int(1) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_options`
--

CREATE TABLE `general_options` (
  `id` int(11) NOT NULL,
  `name` text,
  `value` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `general_options`
--

INSERT INTO `general_options` (`id`, `name`, `value`) VALUES
(67, 'enable_https', '1'),
(68, 'enable_disable_homepage', '0'),
(69, 'website_desc', 'Smart 3.4 Modified By AbusalehInfotech                                                                     '),
(70, 'website_keywords', 'Smart 3.4 Modified By AbusalehInfotech                                                                     '),
(71, 'website_title', 'FinalSmmPanel'),
(72, 'website_favicon', '/assets/uploads/userde8627f75ba1abcfafd00a0e75ad189105cfdc21/50a91a576d9a1490f774a71a73e46a17.png'),
(73, 'embed_head_javascript', ''),
(74, 'website_logo', '/assets/uploads/userde8627f75ba1abcfafd00a0e75ad189105cfdc21/50a91a576d9a1490f774a71a73e46a17.png'),
(75, 'website_logo_white', '/assets/uploads/userde8627f75ba1abcfafd00a0e75ad189105cfdc21/8a18e54c46cf51f48af027f21e2815fb.png'),
(176, 'cookies_policy_page', '<p><strong>Lorem Ipsum</strong></p><p>Lorem ipsum dolor sit amet, in eam consetetur consectetuer. Vivendo eleifend postulant ut mei, vero maiestatis cu nam. Qui et facer mandamus, nullam regione lucilius eu has. Mei an vidisse facilis posidonium, eros minim deserunt per ne.</p><p>Duo quando tibique intellegam at. Nec error mucius in, ius in error legendos reformidans. Vidisse dolorum vulputate cu ius. Ei qui stet error consulatu.</p><p>Mei habeo prompta te. Ignota commodo nam ei. Te iudico definitionem sed, placerat oporteat tincidunt eu per, stet clita meliore usu ne. Facer debitis ponderum per no, agam corpora recteque at mel.</p>'),
(76, 'enable_service_list_no_login', '1'),
(77, 'disable_signup_page', '0'),
(78, 'notification_popup_content', '<p xss=\"removed\"><b>Hello Guys</b></p>\r\n<p></p>\r\n<p></p>\r\n<p></p>'),
(79, 'is_cookie_policy_page', ''),
(80, 'enable_api_tab', '1'),
(81, 'whatsapp_number', '1234567890'),
(82, 'contact_email', 'official.abusaleh@gmail.com'),
(83, 'contact_work_hour', '24/7 Hours'),
(84, 'social_facebook_link', 'https://no1scripts.store'),
(85, 'social_twitter_link', 'https://no1scripts.store'),
(86, 'social_instagram_link', 'https://no1scripts.store'),
(87, 'social_pinterest_link', 'https://no1scripts.store'),
(88, 'social_tumblr_link', 'https://no1scripts.store'),
(89, 'social_youtube_link', 'https://no1scripts.store'),
(90, 'copy_right_content', 'Copyright © 2021 FinalSmmPanel'),
(91, 'embed_javascript', '	\r\n&lt;style&gt;\r\n  #snowflakeContainer {\r\n    position: absolute;\r\n    left: 0px;\r\n    top: 0px;\r\n    display: none;\r\n  }\r\n\r\n  .snowflake {\r\n    position: fixed;\r\n    background-color: #CCC;\r\n    user-select: none;\r\n    z-index: 1000;\r\n    pointer-events: none;\r\n    border-radius: 50%;\r\n    width: 10px;\r\n    height: 10px;\r\n  }\r\n&lt;/style&gt;\r\n\r\n&lt;div id=&quot;snowflakeContainer&quot;&gt;\r\n  &lt;span class=&quot;snowflake&quot;&gt;&lt;/span&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;script&gt;\r\n  // Array to store our Snowflake objects\r\n  var snowflakes = [];\r\n\r\n  // Global variables to store our browser&#039;s window size\r\n  var browserWidth;\r\n  var browserHeight;\r\n\r\n  // Specify the number of snowflakes you want visible\r\n  var numberOfSnowflakes = 50;\r\n\r\n  // Flag to reset the position of the snowflakes\r\n  var resetPosition = false;\r\n\r\n  // Handle accessibility\r\n  var enableAnimations = false;\r\n  var reduceMotionQuery = matchMedia(&quot;(prefers-reduced-motion)&quot;);\r\n\r\n  // Handle animation accessibility preferences \r\n  function setAccessibilityState() {\r\n    if (reduceMotionQuery.matches) {\r\n      enableAnimations = false;\r\n    } else { \r\n      enableAnimations = true;\r\n    }\r\n  }\r\n  setAccessibilityState();\r\n\r\n  reduceMotionQuery.addListener(setAccessibilityState);\r\n\r\n  //\r\n  // It all starts here...\r\n  //\r\n  function setup() {\r\n    if (enableAnimations) {\r\n      window.addEventListener(&quot;DOMContentLoaded&quot;, generateSnowflakes, false);\r\n      window.addEventListener(&quot;resize&quot;, setResetFlag, false);\r\n    }\r\n  }\r\n  setup();\r\n\r\n  //\r\n  // Constructor for our Snowflake object\r\n  //\r\n  function Snowflake(element, speed, xPos, yPos) {\r\n    // set initial snowflake properties\r\n    this.element = element;\r\n    this.speed = speed;\r\n    this.xPos = xPos;\r\n    this.yPos = yPos;\r\n    this.scale = 1;\r\n\r\n    // declare variables used for snowflake&#039;s motion\r\n    this.counter = 0;\r\n    this.sign = Math.random() &lt; 0.5 ? 1 : -1;\r\n\r\n    // setting an initial opacity and size for our snowflake\r\n    this.element.style.opacity = (.1 + Math.random()) / 3;\r\n  }\r\n\r\n  //\r\n  // The function responsible for actually moving our snowflake\r\n  //\r\n  Snowflake.prototype.update = function () {\r\n    // using some trigonometry to determine our x and y position\r\n    this.counter += this.speed / 5000;\r\n    this.xPos += this.sign * this.speed * Math.cos(this.counter) / 40;\r\n    this.yPos += Math.sin(this.counter) / 40 + this.speed / 30;\r\n    this.scale = .5 + Math.abs(10 * Math.cos(this.counter) / 20);\r\n\r\n    // setting our snowflake&#039;s position\r\n    setTransform(Math.round(this.xPos), Math.round(this.yPos), this.scale, this.element);\r\n\r\n    // if snowflake goes below the browser window, move it back to the top\r\n    if (this.yPos &gt; browserHeight) {\r\n      this.yPos = -50;\r\n    }\r\n  }\r\n\r\n  //\r\n  // A performant way to set your snowflake&#039;s position and size\r\n  //\r\n  function setTransform(xPos, yPos, scale, el) {\r\n    el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0) scale(${scale}, ${scale})`;\r\n  }\r\n\r\n  //\r\n  // The function responsible for creating the snowflake\r\n  //\r\n  function generateSnowflakes() {\r\n\r\n    // get our snowflake element from the DOM and store it\r\n    var originalSnowflake = document.querySelector(&quot;.snowflake&quot;);\r\n\r\n    // access our snowflake element&#039;s parent container\r\n    var snowflakeContainer = originalSnowflake.parentNode;\r\n    snowflakeContainer.style.display = &quot;block&quot;;\r\n\r\n    // get our browser&#039;s size\r\n    browserWidth = document.documentElement.clientWidth;\r\n    browserHeight = document.documentElement.clientHeight;\r\n\r\n    // create each individual snowflake\r\n    for (var i = 0; i &lt; numberOfSnowflakes; i++) {\r\n\r\n      // clone our original snowflake and add it to snowflakeContainer\r\n      var snowflakeClone = originalSnowflake.cloneNode(true);\r\n      snowflakeContainer.appendChild(snowflakeClone);\r\n\r\n      // set our snowflake&#039;s initial position and related properties\r\n      var initialXPos = getPosition(50, browserWidth);\r\n      var initialYPos = getPosition(50, browserHeight);\r\n      var speed = 5 + Math.random() * 40;\r\n\r\n      // create our Snowflake object\r\n      var snowflakeObject = new Snowflake(snowflakeClone,\r\n        speed,\r\n        initialXPos,\r\n        initialYPos);\r\n      snowflakes.push(snowflakeObject);\r\n    }\r\n\r\n    // remove the original snowflake because we no longer need it visible\r\n    snowflakeContainer.removeChild(originalSnowflake);\r\n\r\n    moveSnowflakes();\r\n  }\r\n\r\n  //\r\n  // Responsible for moving each snowflake by calling its update function\r\n  //\r\n  function moveSnowflakes() {\r\n\r\n    if (enableAnimations) {\r\n      for (var i = 0; i &lt; snowflakes.length; i++) {\r\n        var snowflake = snowflakes[i];\r\n        snowflake.update();\r\n      }      \r\n    }\r\n\r\n    // Reset the position of all the snowflakes to a new value\r\n    if (resetPosition) {\r\n      browserWidth = document.documentElement.clientWidth;\r\n      browserHeight = document.documentElement.clientHeight;\r\n\r\n      for (var i = 0; i &lt; snowflakes.length; i++) {\r\n        var snowflake = snowflakes[i];\r\n\r\n        snowflake.xPos = getPosition(50, browserWidth);\r\n        snowflake.yPos = getPosition(50, browserHeight);\r\n      }\r\n\r\n      resetPosition = false;\r\n    }\r\n\r\n    requestAnimationFrame(moveSnowflakes);\r\n  }\r\n\r\n  //\r\n  // This function returns a number between (maximum - offset) and (maximum + offset)\r\n  //\r\n  function getPosition(offset, size) {\r\n    return Math.round(-1 * offset + Math.random() * (size + 2 * offset));\r\n  }\r\n\r\n  //\r\n  // Trigger a reset of all the snowflakes&#039; positions\r\n  //\r\n  function setResetFlag(e) {\r\n    resetPosition = true;\r\n  }\r\n&lt;/script&gt;'),
(92, 'enable_notification_popup', '0'),
(93, 'enable_goolge_recapcha', '0'),
(94, 'enable_signup_skype_field', '0'),
(95, 'default_price_percentage_increase', '30'),
(96, 'new_currecry_rate', '74'),
(97, 'currency_decimal_separator', 'dot'),
(98, 'currency_thousand_separator', 'comma'),
(99, 'currency_symbol', '₹'),
(100, 'currency_decimal', '4'),
(101, 'default_header_skin', 'cosmic-fusion'),
(102, 'enable_news_announcement', '1'),
(103, 'default_limit_per_page', '100'),
(104, 'get_features_option', '0'),
(105, 'is_verification_new_account', '1'),
(106, 'is_welcome_email', '1'),
(107, 'is_new_user_email', '1'),
(108, 'is_active_paypal', '0'),
(109, 'is_active_stripe', '0'),
(110, 'is_active_2checkout', '0'),
(111, 'is_active_manual', '1'),
(112, 'enable_explication_service_symbol', '0'),
(113, 'enable_drip_feed', '1'),
(114, 'enable_attentions_orderpage', ''),
(115, 'is_maintenance_mode', '0'),
(116, 'website_name', 'FinalSmmPanel'),
(117, 'currency_code', 'INR'),
(118, 'auto_rounding_x_decimal_places', '2'),
(119, 'is_auto_currency_convert', '1'),
(120, 'payment_transaction_min', '30'),
(121, 'payment_environment', 'live'),
(122, 'paypal_chagre_fee', '0'),
(123, 'paypal_client_id', ''),
(124, 'paypal_client_secret', ''),
(125, 'stripe_chagre_fee', '4'),
(126, 'stripe_publishable_key', ''),
(127, 'stripe_secret_key', ''),
(128, 'twocheckout_chagre_fee', '4'),
(129, '2checkout_publishable_key', ''),
(130, '2checkout_private_key', ''),
(131, '2checkout_seller_id', ''),
(132, 'manual_payment_content', '&lt;p style=&quot;text-align: center;&quot;&gt;&lt;strong&gt;For Manual Payment Contact On WhatsApp&lt;/strong&gt;&lt;/p&gt;\r\n&lt;p style=&quot;text-align: center;&quot;&gt;&lt;/p&gt;'),
(196, 'whatsapp_number', '1234567890'),
(133, 'is_active_paytm', '1'),
(134, 'paytm_payment_environment', 'PROD'),
(135, 'paytm_chagre_fee', '0'),
(136, 'paytm_payment_transaction_min', '10'),
(137, 'paytm_currency_rate_to_usd', '1'),
(138, 'paytm_merchant_id', 'dfyttE26595422359624'),
(139, 'paytm_merchant_key', 'uniceoYb!2F1X4L6'),
(140, 'default_home_page', 'monoka'),
(141, 'default_timezone', 'Asia/Kolkata'),
(142, 'is_clear_ticket', '1'),
(143, 'default_clear_ticket_days', '10'),
(144, 'default_min_order', ''),
(145, 'default_max_order', ''),
(146, 'default_price_per_1k', ''),
(147, 'default_drip_feed_runs', '10'),
(148, 'default_drip_feed_interval', '30'),
(149, 'google_capcha_site_key', ''),
(150, 'google_capcha_secret_key', ''),
(151, 'is_ticket_notice_email_admin', '1'),
(152, 'is_ticket_notice_email', '1'),
(153, 'email_password_recovery_subject', ' Password Recovery'),
(154, 'email_password_recovery_content', '<p>Hi {{<strong>user_firstnane}}!</strong></p>\r\n<p>somebody ( hopefully your requested New password your account.</p>\r\n<p>No change have been made to your account yet.</p>\r\n<p>you can reset your password by click this link:{{recovery_password_link}}</p>\r\n<p>If you did not request a password reset, no further action is requested</p>\r\n<p></p>\r\n<p>Thanks !</p>'),
(155, 'email_from', 'admin@no1scripts.store'),
(156, 'email_name', 'AbusalehInfotech'),
(157, 'email_protocol_type', 'php_mail'),
(158, 'smtp_server', 'clicksmmpanel.com'),
(159, 'smtp_port', '465'),
(160, 'smtp_username', 'admin@clicksmmpanel.com'),
(161, 'smtp_password', 'Bilal4050978@'),
(162, 'smtp_encryption', 'ssl'),
(163, 'is_payment_notice_email', '1'),
(164, 'is_order_notice_email', '1'),
(165, 'terms_content', ''),
(166, 'policy_content', ''),
(167, 'verification_email_subject', ' Please validate your account'),
(168, 'verification_email_content', '<p><strong>Welcome </strong></p>\r\n<p>Hello <strong>{{user_firstname}}</strong>!</p>\r\n<p> Thank you for joining! We\'re glad to have you as community member, and we\'re stocked for you to start exploring our service.  If you don\'t verify your address, you won\'t be able to create a User Account.</p>\r\n<p>  All you need to do is activate your account by click this link: <br>  {{activation_link}} </p>\r\n<p>Thanks and Best Regards!</p>'),
(169, 'email_welcome_email_subject', ' Getting Started with Our Service!'),
(170, 'email_welcome_email_content', '<p><strong>Welcome </strong></p>\r\n<p>Hello <strong>{{user_firstname}}</strong>!</p>\r\n<p>Congratulations! <br>You have successfully signed up for our service - {{website_name}} with follow data</p>\r\n<ul>\r\n<li>Firstname: {{user_firstname}}</li>\r\n<li>Lastname: {{user_lastname}}</li>\r\n<li>Email: {{user_email}}</li>\r\n<li>Timezone: {{user_timezone}}</li>\r\n</ul>\r\n<p>We want to exceed your expectations, so please do not hesitate to reach out at any time if you have any questions or concerns. We look to working with you.</p>\r\n<p>Best Regards,</p>'),
(171, 'email_new_registration_subject', 'New Registration'),
(172, 'email_new_registration_content', '<p>Hi Admin!</p>\r\n<p>Someone signed up in  with follow data</p>\r\n<ul>\r\n<li>Firstname {{user_firstname}}</li>\r\n<li>Lastname: {{user_lastname}}</li>\r\n<li>Email: {{user_email}}</li>\r\n<li>Timezone: {{user_timezone}}</li>\r\n</ul>'),
(173, 'email_payment_notice_subject', '  Thank You! Deposit Payment Received'),
(174, 'email_payment_notice_content', '<p xss=\"removed\">Hi<strong> {{user_firstname}}! </strong></p>\r\n<p>We\'ve just received your final remittance and would like to thank you. We appreciate your diligence in adding funds to your balance in our service.</p>\r\n<p>It has been a pleasure doing business with you. We wish you the best of luck.</p>\r\n<p>Thanks and Best Regards!</p>'),
(175, 'defaut_auto_sync_service_setting', '{\"price_percentage_increase\":50,\"sync_request\":1,\"new_currency_rate\":\"76\",\"is_enable_sync_price\":1,\"is_convert_to_new_currency\":1}'),
(177, ' - SmartPanel', ''),
(178, 'refill_expiry_days', '30'),
(179, 'enable_affiliate', '1'),
(180, 'affiliate_notice', 'Do not make spam accounts to generate referrals else your account can get banned.'),
(181, 'affiliate_bonus', '10'),
(182, 'email_new_refill_subject', 'New Refill Request'),
(183, 'email_new_refill_content', '<p>Hi Admin!</p> <p>Someone signed up in  with follow data</p> <ul> <li>Firstname {{user_firstname}}</li> <li>Lastname: {{user_lastname}}</li> <li>Email: {{user_email}}</li> </ul>'),
(184, 'social_playstore_link', ''),
(185, 'social_appstore_link', ''),
(186, 'social_whatsapp_link', ''),
(187, 'social_messenger_link', ''),
(188, 'social_telegram_link', ''),
(189, 'enable_custom_home', '1'),
(190, 'custom_home', ''),
(191, 'is_childpanel_status', '1'),
(192, 'childpanel_price', '500'),
(193, 'ns1', 'ns1.fsphost.xyz'),
(194, 'ns2', 'ns2.fsphost.xyz'),
(195, 'childpanel_desc', 'Helllo Guys I am AbusalehInfotech'),
(197, 'admin_note_order', ''),
(198, 'contact_tel', '+12345678'),
(199, 'position_select', 'Right');

-- --------------------------------------------------------

--
-- Table structure for table `general_purchase`
--

CREATE TABLE `general_purchase` (
  `id` int(11) NOT NULL,
  `ids` text,
  `pid` text,
  `purchase_code` text,
  `version` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `general_purchase`
--

INSERT INTO `general_purchase` (`id`, `ids`, `pid`, `purchase_code`, `version`) VALUES
(1, '0bd767eca1df93ad4adce458b0e7d8d7', '23595718', 'purchase_code', '3.4');

-- --------------------------------------------------------

--
-- Table structure for table `general_sessions`
--

CREATE TABLE `general_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `general_sessions`
--

INSERT INTO `general_sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('f544d0b848a9398f11d6df153306c36a3afb74a4', '157.37.203.208', 1635753776, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353735333736343b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d),
('f5c8ac100272121a7cdd3a958f325393ec41d972', '157.37.203.208', 1635753764, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353735333736343b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d7569647c733a313a2231223b757365725f63757272656e745f696e666f7c613a353a7b733a343a22726f6c65223b733a353a2261646d696e223b733a353a22656d61696c223b733a31333a224653504041444d494e2e434f4d223b733a31303a2266697273745f6e616d65223b733a333a22465350223b733a393a226c6173745f6e616d65223b733a353a2241646d696e223b733a383a2274696d657a6f6e65223b733a31343a224166726963612f416269646a616e223b7d),
('ac4dd61bc4944ec467cbb2d3bf5dc76bdebff7c3', '157.37.203.208', 1635752314, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353735323331343b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d7569647c733a313a2231223b757365725f63757272656e745f696e666f7c613a353a7b733a343a22726f6c65223b733a353a2261646d696e223b733a353a22656d61696c223b733a31333a224653504041444d494e2e434f4d223b733a31303a2266697273745f6e616d65223b733a333a22465350223b733a393a226c6173745f6e616d65223b733a353a2241646d696e223b733a383a2274696d657a6f6e65223b733a31343a224166726963612f416269646a616e223b7d),
('9b6e8c442fb65038900878de0e289cffd9a477ba', '157.37.203.208', 1635750022, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353735303032323b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d),
('461236f346b49f09f7f8cd08a2127a27b0863433', '157.38.122.43', 1635752741, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353734393639333b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d),
('c21bebb4f20f0fd99a010129bfcd82520ef2df44', '157.37.203.208', 1635751708, 0x5f5f63695f6c6173745f726567656e65726174657c693a313633353735313730383b6c616e6743757272656e747c4f3a383a22737464436c617373223a373a7b733a323a226964223b733a313a2233223b733a333a22696473223b733a33323a223339383932626637663537343831393163366665666565616461313935316535223b733a343a22636f6465223b733a323a22656e223b733a31323a22636f756e7472795f636f6465223b733a323a224742223b733a31303a2269735f64656661756c74223b733a313a2231223b733a363a22737461747573223b733a313a2231223b733a373a2263726561746564223b733a31393a22323032302d30382d33302030373a34303a3034223b7d7569647c733a313a2231223b757365725f63757272656e745f696e666f7c613a353a7b733a343a22726f6c65223b733a353a2261646d696e223b733a353a22656d61696c223b733a31333a224653504041444d494e2e434f4d223b733a31303a2266697273745f6e616d65223b733a333a22465350223b733a393a226c6173745f6e616d65223b733a353a2241646d696e223b733a383a2274696d657a6f6e65223b733a31343a224166726963612f416269646a616e223b7d);

-- --------------------------------------------------------

--
-- Table structure for table `general_subscribers`
--

CREATE TABLE `general_subscribers` (
  `id` int(11) NOT NULL,
  `ids` text,
  `first_name` text,
  `last_name` text,
  `email` text,
  `ip` text,
  `country` varchar(255) DEFAULT NULL,
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `general_transaction_logs`
--

CREATE TABLE `general_transaction_logs` (
  `id` int(11) NOT NULL,
  `ids` text,
  `uid` int(11) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `type` text,
  `transaction_id` text,
  `txn_fee` double DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `data` text,
  `amount` float DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `general_users`
--

CREATE TABLE `general_users` (
  `id` int(11) NOT NULL,
  `ids` text,
  `role` enum('admin','user') DEFAULT 'user',
  `login_type` text,
  `first_name` text,
  `last_name` text,
  `email` text,
  `password` text,
  `timezone` text,
  `more_information` text,
  `settings` longtext,
  `desc` longtext,
  `balance` decimal(15,4) DEFAULT '0.0000',
  `custom_rate` int(11) NOT NULL DEFAULT '0',
  `affiliate_bal_available` decimal(15,4) DEFAULT '0.0000',
  `affiliate_bal_transferred` decimal(15,4) DEFAULT '0.0000',
  `api_key` varchar(191) DEFAULT NULL,
  `spent` varchar(225) DEFAULT NULL,
  `activation_key` text,
  `affiliate_id` varchar(191) NOT NULL,
  `referral_id` varchar(191) DEFAULT NULL,
  `reset_key` text,
  `history_ip` text,
  `status` int(1) DEFAULT '1',
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


--
-- Dumping data for table `general_users`
--

INSERT INTO `general_users` (`id`, `ids`, `role`, `login_type`, `first_name`, `last_name`, `email`, `password`, `timezone`, `more_information`, `settings`, `desc`, `balance`, `custom_rate`, `api_key`, `spent`, `activation_key`, `reset_key`, `history_ip`, `status`, `changed`, `created`) VALUES
(1, 'e7ace76210625c6880498190c0af2d58', 'admin', NULL, 'admin_first_name', 'admin_last_name', 'admin_email', 'admin_password', 'admin_timezone', NULL, NULL, NULL, 0.0000, 0, NULL, NULL, 'c4a78c5172c30e669bb05d9dse48d6f5', 'c2f495cbb8f0d16a140a5f5142fa85af', '103.36.82.67', 1, NULL, NULL);



-- --------------------------------------------------------

--
-- Table structure for table `general_users_price`
--

CREATE TABLE `general_users_price` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `general_users_price`
--

INSERT INTO `general_users_price` (`id`, `uid`, `service_id`, `service_price`) VALUES
(1, 19, 56, 1.8),
(2, 17, 56, 1.8),
(3, 708, 887, 1.15),
(4, 386, 887, 1.15),
(5, 442, 941, 0.8),
(6, 442, 934, 1.3),
(7, 738, 966, 0.7),
(8, 735, 1619, 0.34),
(9, 58, 1481, 1.28),
(10, 58, 1482, 1.62);

-- --------------------------------------------------------

--
-- Table structure for table `general_user_block_ip`
--

CREATE TABLE `general_user_block_ip` (
  `id` int(11) NOT NULL,
  `ids` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `description` text,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `general_user_logs`
--

CREATE TABLE `general_user_logs` (
  `id` int(11) NOT NULL,
  `ids` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `country` text,
  `type` int(1) DEFAULT '1' COMMENT '1 - login, 0 - logout',
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `general_user_logs`
--

INSERT INTO `general_user_logs` (`id`, `ids`, `uid`, `ip`, `country`, `type`, `created`) VALUES
(0, '1dd507340d9eea20ad6edb2bd7f008bd', 1, '157.37.203.208', 'India', 0, '2021-10-31 21:02:44');

-- --------------------------------------------------------

--
-- Table structure for table `general_user_mail_logs`
--

CREATE TABLE `general_user_mail_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `ids` text COLLATE utf8mb4_unicode_ci,
  `uid` int(11) DEFAULT NULL,
  `received_uid` int(11) DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) NOT NULL,
  `ids` text CHARACTER SET utf8,
  `type` enum('direct','api') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `cate_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_order_id` int(11) DEFAULT NULL,
  `service_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `api_provider_id` int(11) DEFAULT NULL,
  `api_service_id` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_order_id` int(11) DEFAULT '0',
  `uid` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_refill` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `usernames` text COLLATE utf8mb4_unicode_ci,
  `username` text COLLATE utf8mb4_unicode_ci,
  `hashtags` text COLLATE utf8mb4_unicode_ci,
  `hashtag` text COLLATE utf8mb4_unicode_ci,
  `media` text COLLATE utf8mb4_unicode_ci,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `sub_posts` int(11) DEFAULT NULL,
  `sub_min` int(11) DEFAULT NULL,
  `sub_max` int(11) DEFAULT NULL,
  `sub_delay` int(11) DEFAULT NULL,
  `sub_expiry` text COLLATE utf8mb4_unicode_ci,
  `sub_response_orders` text COLLATE utf8mb4_unicode_ci,
  `sub_response_posts` text COLLATE utf8mb4_unicode_ci,
  `sub_status` enum('Active','Paused','Completed','Expired','Canceled') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge` decimal(15,4) DEFAULT NULL,
  `formal_charge` decimal(15,4) DEFAULT NULL,
  `profit` decimal(15,4) DEFAULT NULL,
  `status` enum('active','completed','processing','inprogress','pending','partial','canceled','refunded','awaiting','error','fail') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `start_counter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remains` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `is_drip_feed` int(1) DEFAULT '0',
  `runs` int(11) DEFAULT '0',
  `interval` int(2) DEFAULT '0',
  `dripfeed_quantity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `note` text COLLATE utf8mb4_unicode_ci,
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `refill_status` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `name` varchar(225) NOT NULL,
  `min` double NOT NULL,
  `max` double NOT NULL,
  `new_users` int(1) NOT NULL DEFAULT '0' COMMENT '1:Allowed, 0: Not Allowed',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '1 -> ON, 0 -> OFF',
  `params` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `type`, `name`, `min`, `max`, `new_users`, `status`, `params`) VALUES
(13, 'paypal', 'Paypal Checkout', 10, 1000, 1, 1, '{\"type\":\"paypal\",\"name\":\"Paypal Checkout\",\"min\":\"10\",\"max\":\"1000\",\"new_users\":\"1\",\"status\":\"1\",\"take_fee_from_user\":\"1\",\"option\":{\"environment\":\"live\",\"client_id\":\"ARuVpd_oPz4AAzeXZ9JZcoXLv4UNrwpkuQrQl3wsIyaMqJKPhRw0xkR9MA881xVenJdl5TpvE45hMPZs\",\"secret_key\":\"EOZxzZO4J-tSQttLbCCOwibC6ksRHqKkLhGPZ7UV4t7Ua18WpVQ5S1NqX4AcXwCkJHMX6sd3f7jsfe33\"}}'),
(14, 'stripe', 'Stripe Checkout', 10, 10000, 1, 1, '{\"type\":\"stripe\",\"name\":\"Stripe Checkout\",\"min\":\"10\",\"max\":\"100000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"3\",\"environment\":\"live\",\"public_key\":\"pk_live_WTxA1Zw8dFXqRRE36yzy57gg00BXKsBN2i\",\"secret_key\":\"sk_live_lkobgCZxAtJeOT8MZy7GiYhn00jlc4L0F2\"}}'),
(110, 'perfectmoney', 'Perfect Money USD', 20, 1000, 1, 1, '{\"type\":\"perfectmoney\",\"name\":\"Perfect Money USD\",\"min\":\"5\",\"max\":\"100\",\"new_users\":\"1\",\"status\":\"0\",\"option\":{\"tnx_fee\":\"0\",\"usd_wallet\":\"\",\"alternate_pass\":\"\"}'),
(129, 'paytm', 'Paytm CheckOut', 10, 10000, 1, 1, '{\"type\":\"paytm\",\"name\":\"Paytm CheckOut\",\"min\":\"10\",\"max\":\"10000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"2\",\"environment\":\"PROD\",\"paytm_mid\":\"yhGaPK21364483087044\",\"merchant_key\":\"0SvR5osptrWTH2hb\",\"rate_to_usd\":\"1\"}}'),
(130, 'paytmqr', 'PaytmQR', 10, 10000, 1, 1, '{\"type\":\"paytmqr\",\"name\":\"PaytmQR\",\"min\":\"10\",\"max\":\"10000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"0\",\"environment\":\"PROD\",\"paytmqr_mid\":\"yhGaPK21364483087044\",\"rate_to_usd\":\"1\"}}'),
(131, 'razorpay', 'Razorpay CheckOut', 10, 10000, 1, 1, '{\"type\":\"razorpay\",\"name\":\"Razorpay CheckOut\",\"min\":\"10\",\"max\":\"10000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"0\",\"environment\":\"TEST\",\"public_key\":\"rzp_test_NMocLJe0cP8lCu\",\"secret_key\":\"zj4OeJd9yj7Bp2scGbYL75ua\",\"rate_to_usd\":\"1\"}}'),
(132, 'coinbase', 'Coinbase', 1, 1000, 1, 1, '{\"type\":\"coinbase\",\"name\":\"Coinbase\",\"min\":\"1\",\"max\":\"1000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"0\",\"environment\":\"PROD\",\"api_key\":\"88a5-4d5a-9196-018f9825424e\",\"rate_to_usd\":\"1\"}}'),
(155, 'cashmaal', 'Cashmaal', 5, 100, 1, 1, '{\"type\":\"cashmaal\",\"name\":\"Cashmaal\",\"min\":\"5\",\"max\":\"100\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"0\",\"merchant_key\":\"\",\"ipn_key\":\"\",\"currency_code\":\"USD\",\"rate_to_usd\":\"168\"}}'),
(1302, 'toyyibpay', 'ToyyibPay Checkout', 20, 1000, 1, 1, '{\"type\":\"toyyibpay\",\"name\":\"ToyyibPay Checkout\",\"min\":\"5\",\"max\":\"1000\",\"new_users\":\"1\",\"status\":\"1\",\"option\":{\"tnx_fee\":\"0\",\"environment\":\"live\",\"secret_key\":\"q5c32072-fu5h-ugv1-5iqp-gjkyaa1g1hsn\",\"category_code\":\"bertyznv\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `payments_bonus`
--

CREATE TABLE `payments_bonus` (
  `id` int(11) NOT NULL,
  `ids` varchar(100) DEFAULT NULL,
  `payment_id` int(11) NOT NULL,
  `bonus_from` double NOT NULL,
  `percentage` double NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `ids` text,
  `uid` int(11) DEFAULT NULL,
  `cate_id` int(11) DEFAULT NULL,
  `name` text,
  `desc` text,
  `price` decimal(15,4) DEFAULT NULL,
  `original_price` decimal(15,4) DEFAULT NULL,
  `min` int(50) DEFAULT NULL,
  `max` int(50) DEFAULT NULL,
  `add_type` enum('manual','api') DEFAULT 'manual',
  `type` varchar(100) DEFAULT 'default',
  `api_service_id` varchar(200) DEFAULT NULL,
  `api_provider_id` int(11) DEFAULT NULL,
  `dripfeed` int(1) DEFAULT '0',
  `status` int(1) DEFAULT '1',
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(10) UNSIGNED NOT NULL,
  `ids` text COLLATE utf8mb4_unicode_ci,
  `uid` int(11) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','pending','closed','answered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `user_read` double NOT NULL DEFAULT '0',
  `admin_read` double NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `changed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `ids` text COLLATE utf8mb4_unicode_ci,
  `uid` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `changed` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_providers`
--
ALTER TABLE `api_providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`uid`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `childpanels`
--
ALTER TABLE `childpanels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_custom_page`
--
ALTER TABLE `general_custom_page`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_file_manager`
--
ALTER TABLE `general_file_manager`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_lang`
--
ALTER TABLE `general_lang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_lang_list`
--
ALTER TABLE `general_lang_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_news`
--
ALTER TABLE `general_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`uid`);

--
-- Indexes for table `general_options`
--
ALTER TABLE `general_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_purchase`
--
ALTER TABLE `general_purchase`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_sessions`
--
ALTER TABLE `general_sessions`
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `general_subscribers`
--
ALTER TABLE `general_subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_transaction_logs`
--
ALTER TABLE `general_transaction_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_users`
--
ALTER TABLE `general_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_users_price`
--
ALTER TABLE `general_users_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments_bonus`
--
ALTER TABLE `payments_bonus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_id_foreign` (`uid`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_messages_user_id_foreign` (`uid`),
  ADD KEY `ticket_messages_ticket_id_foreign` (`ticket_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_providers`
--
ALTER TABLE `api_providers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `childpanels`
--
ALTER TABLE `childpanels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_custom_page`
--
ALTER TABLE `general_custom_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `general_file_manager`
--
ALTER TABLE `general_file_manager`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;

--
-- AUTO_INCREMENT for table `general_lang`
--
ALTER TABLE `general_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_lang_list`
--
ALTER TABLE `general_lang_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `general_news`
--
ALTER TABLE `general_news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_options`
--
ALTER TABLE `general_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `general_purchase`
--
ALTER TABLE `general_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `general_subscribers`
--
ALTER TABLE `general_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_transaction_logs`
--
ALTER TABLE `general_transaction_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `general_users`
--
ALTER TABLE `general_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_users_price`
--
ALTER TABLE `general_users_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1303;

--
-- AUTO_INCREMENT for table `payments_bonus`
--
ALTER TABLE `payments_bonus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
