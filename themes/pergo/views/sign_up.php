<?=Modules::run(get_theme()."/header")?>
<style>
hr {
    margin-top: 1rem;
    margin-bottom: 1rem;
}
</style>
<br>
<div class="auth-signup-form">
  <div class="form-signup">
    <form class="actionForm" action="<?=cn("auth/ajax_sign_up")?>" data-redirect="<?=cn('order/add')?>" method="POST">
      <div>
        <div class="card-title text-center">
          
          <h4 class=" text-white"><?=lang("register_now")?></h4>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-md-6">
              <div class="input-icon mb-3">
                <span class="input-icon-addon">
                  <i class="fe fe-user"></i>
                </span>
                <input type="text" class="form-control" name="first_name" placeholder="<?=lang("first_name")?>"  required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="input-icon mb-3">
                <span class="input-icon-addon">
                  <i class="fe fe-user"></i>
                </span>
                <input type="text" class="form-control" name="last_name" placeholder="<?=lang("last_name")?>" required>
              </div>  
            </div>
          </div>

          <div class="input-icon mb-3">
            <span class="input-icon-addon">
              <i class="fe fe-mail"></i>
            </span>
            <input type="email" class="form-control" name="email" placeholder="<?=lang("Email")?>" required>
          </div>  
          <div class="input-icon mb-3">
    <span class="input-icon-addon">
        <i class="fa fa-whatsapp"></i>
    </span>
    <input type="text" class="form-control" name="whatsapp_number" id="whatsapp_number"
           placeholder="Whatsapp Number" required pattern="^\+92\d{10}$" maxlength="13" 
           value="" onfocus="addPrefix()" onblur="removePrefix()" oninput="validateWhatsappNumber(event)">
</div>



<script>
  function addPrefix() {
    var input = document.getElementById('whatsapp_number');
    // Check if the input is empty and add +92 as prefix
    if (input.value === '') {
        input.value = '+92';
    }
}

function removePrefix() {
    var input = document.getElementById('whatsapp_number');
    // If the value is only +92, clear the field to show placeholder
    if (input.value === '+92') {
        input.value = '';
    }
}

function validateWhatsappNumber(event) {
    var input = event.target;
    var value = input.value;

    // If the input contains a number after +92, validate it
    if (value.startsWith('+92') && value.length > 3) {
        // Allow digits only after +92, and enforce max length of 13
        input.value = '+92' + value.slice(3).replace(/[^0-9]/g, ''); // Remove any non-numeric characters

        // Enforce the max length of 13 (including +92)
        if (input.value.length > 13) {
            input.value = input.value.slice(0, 13);
        }
    }

    // Ensure the number after +92 follows the valid format
    if (!/^\+92\d{10}$/.test(input.value)) {
        // Optionally, you can show an error message here
        input.setCustomValidity("Please enter a valid WhatsApp number with the +92 prefix");
    } else {
        input.setCustomValidity("");
    }
}

</script>

          <?php
            if (get_option('enable_signup_skype_field')) {
          ?>
          <div class="input-icon mb-3">
            <span class="input-icon-addon">
              <i class="fa fa-skype"></i>
            </span>
            <input type="text" class="form-control" name="skype_id" placeholder="<?=lang("Skype_id")?>" required>
          </div>    
          <?php } ?>      
          <div class="input-icon mb-3">
            <span class="input-icon-addon">
              <i class="fa fa-key"></i>
            </span>
            <input type="password" class="form-control" name="password" placeholder="<?=lang("Password")?>" required>
          </div>    

          <div class="input-icon mb-3">
            <span class="input-icon-addon">
              <i class="fa fa-key"></i>
            </span>
            <input type="password" class="form-control" name="re_password" placeholder="<?=lang("Confirm_password")?>" required>
          </div>

          <div class="input-icon mb-3">
            <span class="input-icon-addon">
              <i class="fe fe-clock"></i>
            </span>
            <select  name="timezone" class="form-control square">
              <?php $time_zones = tz_list();
                if (!empty($time_zones)) {
                  $location = get_location_info_by_ip(get_client_ip());
                  $user_timezone = $location->timezone;
                  if ($user_timezone == "" || $user_timezone == 'Unknow') {
                    $user_timezone = get_option("default_timezone", 'UTC');
                  }
                  foreach ($time_zones as $key => $time_zone) {
              ?>
              <option value="<?=$time_zone['zone']?>" <?=($user_timezone == $time_zone["zone"])? 'selected': ''?>><?=$time_zone['time']?></option>
              <?php }}?>
            </select>
          </div>
        </div>
        
        <?php 
        if(get_option("enable_affiliate") == "1"){?>
        
        <div class="input-icon mb-5">
            <span class="input-icon-addon">
              <i class="fa fa-handshake-o"></i>
            </span>
            <input type="text" class="form-control" name="referral" placeholder="<?=lang("Referral_id_(optional)")?>" value="<?=session("referral")?>">
        </div>
        <?php }?>
        
        <?php
          if (get_option('enable_goolge_recapcha') &&  get_option('google_capcha_site_key') != "" && get_option('google_capcha_secret_key') != "") {
        ?>
        <div class="form-group">
          <div class="g-recaptcha" data-sitekey="<?=get_option('google_capcha_site_key')?>"></div>
        </div>
        <?php } ?> 

        <div class="form-group">
          <label class="custom-control custom-checkbox">
            <input type="checkbox" name="terms" class="custom-control-input" />
            <span class="custom-control-label"><?=lang("i_agree_the")?> <a href="<?=cn('terms')?>" class="text-white"><?=lang("terms__policy")?></a></span>
          </label>
        </div>

        <div class="form-footer">
          <button type="submit" class="btn btn-pill btn-2 btn-block btn-submit btn-gradient"><?=lang("create_new_account")?></button>
        </div>
      </div>
    </form>
    <div class="text-center text-muted m-t-20">
      <?=lang("already_have_account")?> <a href="<?=cn('auth/login')?>" class="btn-sign-up"><?=lang("Login")?></a>
    </div>
  </div>
</div><br>



<?=Modules::run(get_theme()."/footer", false)?>
<style>
 /* Main Container */
.auth-signup-form {
  width: 100%;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10px;
  background: rgba(0, 0, 0, 0.7);
}

/* Sign-Up Form Container */
.form-signup {
  width: 100%;
  max-width: 500px;
  border-radius: 8px;
  padding: 20px;
  background: #061d2b;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
  transition: all 0.3s ease;
  margin: 30px auto;
}

/* Site Logo */
.form-signup .site-logo {
  padding-bottom: 15px;
  text-align: center;
}

.form-signup .site-logo img {
  max-height: 50px;
  width: auto;
}

/* Section Title */
.form-signup h4 {
  color: #ffffff;
  font-weight: bold;
  margin-bottom: 15px;
  text-align: center;
  font-size: 18px;
}

/* Input Fields */
.form-signup input[type="text"],
.form-signup input[type="password"],
.form-signup input[type="email"],
.form-signup input[type="tel"],
.form-signup select {
  width: 100%;
  height: 40px;
  border: 1px solid #04a9f4;
  border-radius: 6px;
  padding: 0 12px;
  background: transparent;
  color: #ffffff;
  font-size: 14px;
  outline: none;
  transition: border-color 0.3s ease;
}

.form-signup input::placeholder {
  color: #b1b1b1;
  font-size: 13px;
}

.form-signup input:focus {
  border-color: #0082be;
}

/* Submit Button */
.form-signup .btn-submit {
  width: 100%;
  height: 45px;
  font-size: 15px;
  font-weight: bold;
  color: #ffffff;
  background: #04a9f4;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 5px 12px rgba(0, 0, 0, 0.2);
  outline: none;
}

.form-signup .btn-submit:hover {
  background: #0082be;
  transform: translateY(-2px);
  box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
}

.form-signup .btn-submit:active {
  transform: translateY(1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Checkbox */
.form-signup .custom-control {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.form-signup .custom-control input {
  margin-right: 8px;
}

.form-signup .custom-control-label {
  color: #ffffff;
  font-size: 13px;
}

.form-signup .custom-control-label a {
  color: #04a9f4;
  text-decoration: none;
}

.form-signup .custom-control-label a:hover {
  text-decoration: underline;
}

/* Google reCAPTCHA */
.form-signup .g-recaptcha {
  margin-bottom: 15px;
}

/* Already Have Account Link */
.form-signup .signup-link {
  text-align: center;
  margin-top: 15px;
  color: #ffffff;
  font-size: 13px;
}

.form-signup .signup-link a {
  color: #04a9f4;
  text-decoration: none;
  font-weight: 600;
}

.form-signup .signup-link a:hover {
  text-decoration: underline;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .form-signup {
    padding: 15px;
    margin: 20px;
  }

  .form-signup h4 {
    font-size: 16px;
  }

  .form-signup input,
  .form-signup .btn-submit {
    height: 40px;
    font-size: 13px;
  }

  .auth-signup-form {
    padding: 5px;
  }
}

@media (max-width: 480px) {
  .form-signup {
    max-width: 100%;
    padding: 10px;
  }

  .form-signup h4 {
    font-size: 15px;
  }

  .form-signup input,
  .form-signup .btn-submit {
    height: 35px;
    font-size: 12px;
  }

  .form-signup .signup-link {
    font-size: 12px;
  }
}


</style>