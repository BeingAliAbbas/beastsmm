<style>
    /* Unique styles for Sadapay language toggle */
    .sadlang-btn {
        background-color: #e8e8e8;
        border: none;
        padding: 6px 12px;
        margin-left: 5px;
        cursor: pointer;
        font-weight: bold;
        border-radius: 5px;
        transition: background 0.2s;
    }
    .sadlang-btn.active {
        background-color: #00ffff;
        color: #222;
    }

    .ff {
        background: #164964;
        border: 1px solid #00ffff;
        padding: 1rem;
        border-radius: 3px;
        margin-bottom: 1rem;
    }
    .instruction-box {
        background: #164964;
        color: white;
        padding: 1rem;
        border-radius: 5px;
        border: 1px solid #00ffff;
        margin-bottom: 20px;
    }
    .urdu-rtl {
        direction: rtl;
        text-align: right;
    }
    .urdu-rtl ol {
        padding-right: 20px;
    }
    .urdu-rtl ol li {
        margin-bottom: 5px;
    }
    .copy-icon {
        cursor: pointer;
        color: #00ffff;
        margin-left: 8px;
    }
    .copy-msg {
        font-size: 12px;
        color: #00ff99;
        display: none;
    }
</style>

<?php
  $option                 = get_value($payment_params, 'option');
  $min_amount             = get_value($payment_params, 'min');
  $max_amount             = get_value($payment_params, 'max');
  $type                   = get_value($payment_params, 'type');
  $tnx_fee                = get_value($option, 'tnx_fee');
  $currency_rate_to_usd   = get_value($option, 'rate_to_usd');
  $currency_code          = get_value($option, 'currency_code');
  $account_number         = get_value($payment_params, 'number');
  $holder_name            = get_value($payment_params, 'holder');
  $whatsapp_number        = get_value($payment_params, 'whatsapp');
  $email                  = get_value($payment_params, 'email');
?>

<div class="add-funds-form-content">
  <form class="form actionAddFundsForm" action="#" method="POST">
    <div class="row">
      <div class="col-md-12">

        <!-- Logo Section -->
        <div class="for-group text-center">
          <img src="/assets/images/payments/sadapay.png" alt="Sadapay" width="90%">
          <p class="p-t-10">
            <small>You can deposit funds with Sadapay and they will be automatically added into your account within 1 hour!</small>
          </p>
        </div>

        <!-- Language Toggle Buttons (Sadapay only) -->
        <div style="text-align: right; margin-bottom: 10px;">
          <button type="button" onclick="toggleSadLanguage('urdu')" class="sadlang-btn active" id="sad-btn-urdu">اردو</button>
          <button type="button" onclick="toggleSadLanguage('english')" class="sadlang-btn" id="sad-btn-english">English</button>
        </div>

        <!-- Urdu Instructions (Default) -->
        <div id="sad-instructions-urdu" class="instruction-box urdu-rtl">
          <h5 style="text-align: right;">ادائیگی کے مراحل:</h5>
          <ol>
            <li>اوپر دیا گیا صداق اکاؤنٹ نمبر کاپی کریں۔</li>
            <li>اپنی صداق ایپ کھولیں اور مطلوبہ رقم اس اکاؤنٹ میں منتقل کریں۔</li>
            <li>اس فارم میں واپس آ کر ٹرانزیکشن آئی ڈی اور بھیجی گئی رقم درج کریں، پھر سبمٹ پر کلک کریں۔</li>
          </ol>
        </div>

        <!-- English Instructions -->
        <div id="sad-instructions-english" class="instruction-box" style="display:none;">
          <h5>Payment Steps:</h5>
          <ol>
            <li>Copy the Sadapay account number shown above.</li>
            <li>Open your Sadapay app and transfer the desired amount to this account.</li>
            <li>Return to this form, enter the transaction ID and amount, then click Submit.</li>
          </ol>
        </div>

        <!-- Account Info -->
        <fieldset class="ff mt-1">
          <center>
            <div class="for-group text-center text-uppercase">
              <strong>SADAPAY ACCOUNT TITLE:</strong>
              <h2>
                <span id="sadHolderName"><?php echo $holder_name; ?></span>
                <i class="fa fa-copy copy-icon" onclick="copyToClipboard('sadHolderName', this)" title="Copy Title"></i>
                <span class="copy-msg">Copied!</span>
              </h2>
            </div>

            <div class="for-group text-center">
              <strong>SADAPAY ACCOUNT NUMBER:</strong>
              <h2>
                <span id="sadAccountNumber"><?php echo $account_number; ?></span>
                <i class="fa fa-copy copy-icon" onclick="copyToClipboard('sadAccountNumber', this)" title="Copy Number"></i>
                <span class="copy-msg">Copied!</span>
              </h2>
            </div>
          </center>
        </fieldset>

        <!-- Form Fields -->
        <div class="form-group">
          <label>Amount (PKR)</label>
          <input class="form-control square" type="number" name="amount" placeholder="1">
        </div>

        <div class="form-group">
          <label>Transaction ID</label>
          <input class="form-control square" type="number" name="transaction_id" placeholder="1234567890" required>
        </div>

        <!-- Notes -->
        <div class="form-group">
          <label><?php echo lang("note"); ?></label>
          <ul>
            <?php if ($tnx_fee > 0) { ?>
              <li><?=lang("transaction_fee")?>: <strong><?php echo $tnx_fee; ?>%</strong></li>
            <?php } ?>
            <li><?=lang("Minimal_payment")?>: <strong><?php echo $min_amount.$currency_code; ?></strong></li>
            <?php if ($max_amount > 0) { ?>
              <li><?=lang("Maximal_payment")?>: <strong><?php echo $max_amount.$currency_code; ?></strong></li>
            <?php } ?>
            <?php if ($currency_rate_to_usd > 1) { ?>
              <li><?=lang("currency_rate")?>: 1USD = <strong><?php echo $currency_rate_to_usd; ?></strong><?php echo $currency_code; ?></li>
            <?php } ?>
            <li><?php echo lang("clicking_return_to_shop_merchant_after_payment_successfully_completed"); ?></li>
          </ul>
        </div>

        <!-- Submit Button -->
        <div class="form-actions left">
          <input type="hidden" name="payment_id" value="<?php echo $payment_id; ?>">
          <input type="hidden" name="payment_method" value="<?php echo $type; ?>">
          <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1" style="border-radius: 5px !important; background-color: #04a9f4; color: #fff; min-width: 120px; margin-right: 5px; margin-top: 15px; margin-bottom: 5px;">
            <?=lang("Pay")?>
          </button>
        </div>
      </div>  
    </div>
  </form>
</div>

<hr>

<!-- YouTube Video (optional, you can customize the video link or remove this section) -->
<div class="container1">
  <div class="row">
    <div class="col-lg-12 col-md-12">
      <div class="hs-responsive-embed-youtube">
        <iframe src="https://www.youtube.com/embed/vkPSlUXV9jA" frameborder="0" allowfullscreen=""></iframe>
      </div>
    </div>
  </div>
</div>

<style>
  .hs-responsive-embed-youtube {
    position: relative;
    width: 80%;
    max-width: 800px;
    padding-top: 56.25%;
    margin: 0 auto;
    border: 3px solid #00ffff;
    border-radius: 8px;
    overflow: hidden;
  }
  .hs-responsive-embed-youtube iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }
</style>

<script>
function toggleSadLanguage(lang) {
    const urdu = document.getElementById('sad-instructions-urdu');
    const english = document.getElementById('sad-instructions-english');
    const btnUrdu = document.getElementById('sad-btn-urdu');
    const btnEnglish = document.getElementById('sad-btn-english');

    if (lang === 'urdu') {
        urdu.style.display = 'block';
        english.style.display = 'none';
        btnUrdu.classList.add('active');
        btnEnglish.classList.remove('active');
    } else {
        urdu.style.display = 'none';
        english.style.display = 'block';
        btnUrdu.classList.remove('active');
        btnEnglish.classList.add('active');
    }
    if (event) event.preventDefault();
    if (event) event.stopPropagation();
    return false;
}

function copyToClipboard(elementId, iconEl) {
    const text = document.getElementById(elementId).innerText;
    const tempInput = document.createElement('input');
    document.body.appendChild(tempInput);
    tempInput.value = text.trim();
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);

    const msg = iconEl.nextElementSibling;
    msg.style.display = 'inline';
    setTimeout(() => {
      msg.style.display = 'none';
    }, 1500);
}
</script>