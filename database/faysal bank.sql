-- Faysal Bank module sample SQL for payment method registration

INSERT INTO `payments_method` (`type`, `name`, `params`, `status`)
VALUES
('faysalbank', 'Faysal Bank', 
'{
    "name":"Faysal Bank",
    "type":"faysalbank",
    "min":"100",
    "max":"100000",
    "holder":"Your Account Holder Name",
    "number":"Your Faysal Bank Account Number",
    "whatsapp":"Your WhatsApp Number",
    "email":"your@email.com",
    "option":{
        "tnx_fee":"0",
        "currency_code":"PKR",
        "rate_to_usd":"1",
        "faysalbank_mid":"MID_FROM_BANK",
        "environment":"production"
    }
}', 1);

-- WhatsApp Config Table (if not present)
CREATE TABLE IF NOT EXISTS `whatsapp_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `admin_phone` varchar(32) NOT NULL,
  `api_key` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
);

-- Example insert for WhatsApp config
INSERT INTO `whatsapp_config` (`url`, `admin_phone`, `api_key`)
VALUES
('https://your-whatsapp-api-url.com/send', '923001234567', 'your-whatsapp-api-key');