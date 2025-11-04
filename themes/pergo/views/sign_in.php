<?=Modules::run(get_theme()."/header")?>  

 <section class="banner">
      <div class="container">
        
        <div class="row">
          <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12" data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="150">
            <div class="contents">
            <h1 class="desktop-only-header">
              BEAST SMM | PAKISTAN's BEST SMM PANEL PROVIDER
            </h1>
            <p class="desktop-only-paragraph">
              SMM Panel: Best SMM Panel in Pakistan | BEAST SMM PANEL
              <br>
              <b>BEAST SMM PROVIDER</b> Providing <b>Best SMM Panel Pakistan</b> with multiple <b>cheapest</b> Social Media Services as Superior.
            </p>

            </div>
          </div>       

	
<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12" data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="300">
  <div class="form-login">
    <form class="actionForm" action="<?=cn("auth/ajax_sign_in")?>" data-redirect="<?=cn('order/add')?>" method="POST">
      <div>
        <div class="form-group">
          <?php

            if (isset($_COOKIE["cookie_email"])) {
              $cookie_email = encrypt_decode($_COOKIE["cookie_email"]);
            }

            if (isset($_COOKIE["cookie_pass"])) {
              $cookie_pass = encrypt_decode($_COOKIE["cookie_pass"]);
            }

          ?>
          <!-- Email Input with Label -->
<div class="input-group mb-5">
  <label style="display: block; font-size: 14px; font-weight: 600; color: #ffffff; margin-bottom: 5px;">
    <?=lang("Email")?>
  </label>
  <input type="email" class="form-control1" name="email" 
         style="width: 100%; height: 40px; padding: 0 15px; border: 1px solid #04a9f4; border-radius: 6px; background: transparent; color: #ffffff; font-size: 14px; outline: none;" 
         placeholder="<?=lang("Email")?>" 
         value="<?=(isset($cookie_email) && $cookie_email != "") ? $cookie_email : ""?>" required>
</div>    

<!-- Password Input with Label -->
<div class="input-group mb-5">
  <label style="display: block; font-size: 14px; font-weight: 600; color: #ffffff; margin-bottom: 5px;">
    <?=lang("Password")?>
  </label>
  <input type="password" class="form-control" name="password" 
         style="width: 100%; height: 40px; padding: 0 15px; border: 1px solid #04a9f4; border-radius: 6px; background: transparent; color: #ffffff; font-size: 14px; outline: none;" 
         placeholder="<?=lang("Password")?>" 
         value="<?=(isset($cookie_pass) && $cookie_pass != "") ? $cookie_pass : ""?>" required>
</div>


        </div>

        <div class="form-group">
          <label class="custom-control custom-checkbox">
            <input type="checkbox" name="remember" class="custom-control-input" <?=(isset($cookie_email) && $cookie_email != "") ? "checked" : ""?>>
			<span class="custom-control-label"><div class="text-left"><?=lang("remember_me")?></span>
            <a href="<?=cn("auth/forgot_password")?>" class="float-right small btn-forgot-pass"><?=lang("forgot_password")?></a></div>
          </label>
        </div>
        <div class="form-footer">
          <button type="submit" class=" btn-submit btn-gradient btn-hover color-6"><?=lang("Login")?></button>
        </div>
      </div>
    </form>
    <?php if(!get_option('disable_signup_page')){ ?>
    <div class="text-center text-muted m-t-20">
      <?=lang("dont_have_account_yet")?> 
	  <a href="<?=cn('auth/signup')?>" class="btn-sign-up"><?=lang("Sign_Up")?></a>
    </div>
	<?php }; ?>
  </div>
</div>

		  
		  
		  
		  
        </div>
      </div>
    </section>
        




 <section class="banner"  id="home">
      <div class="container">
        <div class="animatation-box-1">
          <img class="animated icon1" src="<?=BASE?>themes/pergo/assets/images/icon_red_circle.png">
          <img class="animated icon2" src="<?=BASE?>themes/pergo/assets/images/icon_yellow_tri.png">
          <img class="animated icon3" src="<?=BASE?>themes/pergo/assets/images/icon_yellow_circle.png">
        </div>
        <div class="row">
          <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12" data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="450">
            <div class="contents">
            <h1 class="desktop-only-header">
                BEAST SMM | PAKISTAN's BEST SMM PANEL PROVIDER | Cheapest SMM Panel Pakistan
              </h1>
              <p class="desktop-only-paragraph">
                SMM Panel: Best SMM Panel in Pakistan | BEAST SMM PANEL
                <br>
                <b>BEAST SMM PROVIDER</b> Providing <b>Best SMM Panel Pakistan</b> with multiple <b>cheapest</b> Social Media Services as Superior <b>SMM Panel in Pakistan Provider</b> of Major Social Media Platforms like Facebook, Instagram, Youtube, Twitter &amp; Many More
              </p>
              <div class="head-button m-t-40">
                <a href="<?=cn('auth/signup')?>" class="btn btn-pill btn-outline-primary sign-up btn-lg"><?=lang("get_start_now")?></a>
              </div>
            </div>
          </div>          
          <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 box-image" style="margin-top: 250px;" data-aos="fade-up" data-aos-easing="ease-in" data-aos-delay="400">
            <div class="animation-2">
              <img class="intro-img" src="<?=BASE?>themes/pergo/assets/images/girl_and_desk.png" alt="girl-laptop">
              <img class="animated icon-1" src="<?=BASE?>themes/pergo/assets/images/icon_emoji_smile.png" alt="Emoji Smile">
              <img class="animated icon-2" src="<?=BASE?>themes/pergo/assets/images/icon_white_like.png" alt="Like icon">
              <img class="animated icon-3" src="<?=BASE?>themes/pergo/assets/images/icon_red_heart.png" alt="Red Heart Fill">
              <img class="animated icon-4" src="<?=BASE?>themes/pergo/assets/images/purple-like.png" alt="Like Icon">
              <img class="animated icon-5" src="<?=BASE?>themes/pergo/assets/images/icon_instagram.png" alt="Instagram icon">
              <img class="animated icon-6" src="<?=BASE?>themes/pergo/assets/images/icon_facebook_circle.png" alt="Facebook Icon">
              <img class="animated icon-7" src="<?=BASE?>themes/pergo/assets/images/icon_twitter.png" alt="Twitter">
              <img class="animated icon-10" src="<?=BASE?>themes/pergo/assets/images/icon_white_heart.png" alt="White Heart Unfill">
              <img class="animated icon-tree" src="<?=BASE?>themes/pergo/assets/images/tree.png" alt="tree">

            </div>
          </div>
        </div>
      </div>
    </section>
    

    <section class="about-area" style="margin-top: 250px;">
      <div class="container">
        <div class="row">
          <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12 text-center" data-aos="fade-left" data-aos-easing="ease-in" data-aos-delay="200">
            <div class="intro-img">
              <img class="img-fluid" src="<?=BASE?>themes/pergo/assets/images/best_service.png" alt="">
            </div>
          </div>

          <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="200" style="margin-top: 220px;">
            <div class="contents text-center">
              <h2 class="head-title">
                <?=lang("best_smm_marketing_services")?>
              </h2>
              <p class="text-white">
                <?=lang("best_smm_marketing_services_desc")?>
              </p>
            </div>
          </div>          
        </div>
      </div>
    </section>

    <section class="our-services text-center" id="features">
      <div class="container">
        <div class="row" >
          <div class="col-md-12 mx-auto" data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="200">
            <div class="contents">
              <div class="head-title">
                <?=lang("What_we_offer")?>
              </div>
              <div class="border-line">
                <hr>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="400">
            <div class="feature-item text-center">
              <div class="animation-box">
                <i class="fe fe-calendar icon"></i>
              </div>
              <h3><?=lang("Resellers")?></h3>
              <p class="text-black"><?=lang("you_can_resell_our_services_and_grow_your_profit_easily_resellers_are_important_part_of_smm_panel")?>
              </p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="600">
            <div class="feature-item">
              <div class="animation-box">
                <i class="fe fe-phone-call icon"></i>
              </div>
              <h3><?=lang("Supports")?></h3>
              <p class="text-black"><?=lang("technical_support_for_all_our_services_247_to_help_you")?></p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="800">
            <div class="feature-item">
              <div class="animation-box">
                <i class="fe fe-star icon"></i>
              </div>
              
              <h3><?=lang("high_quality_services")?></h3>
              <p class="text-black"><?=lang("get_the_best_high_quality_services_and_in_less_time_here")?></p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="1000">
            <div class="feature-item">
              <div class="animation-box">
                <i class="fe fe-upload-cloud icon"></i>
              </div>
              <h3><?=lang("Updates")?></h3>
              <p class="text-black"><?=lang("services_are_updated_daily_in_order_to_be_further_improved_and_to_provide_you_with_best_experience")?></p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="1200">
            <div class="feature-item">
              <div class="animation-box">
                <i class="fe fe-share-2 icon"></i>
              </div>
              <h3><?=lang("api_support")?></h3>
              <p class="text-black"><?=lang("we_have_api_support_for_panel_owners_so_you_can_resell_our_services_easily")?></p>
			  </a>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-easing="ease-in" data-aos-delay="1400">
            <div class="feature-item">
              <div class="animation-box">
                <i class="fe fe-dollar-sign icon"></i>
              </div>
              <h3><?=lang("secure_payments")?></h3>
              <p class="text-black"><?=lang("we_have_a_popular_methods_as_paypal_and_many_more_can_be_enabled_upon_request")?></p>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section class="reviews text-center">
      <div class="container">
        <div class="row " data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="200">
          <div class="col-md-12 mx-auto">
            <div class="contents">
              <div class="head-title text-white">
                <?=lang("what_people_say_about_us")?>
              </div>
              <span class="text-yellow"><?=lang("our_service_has_an_extensive_customer_roster_built_on_years_worth_of_trust_read_what_our_buyers_think_about_our_range_of_service")?></span>
              <div class="border-line">
                <hr>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card item">
              <div class="person-info">
                <h3 class="name"><?=lang("client_one")?></h3>
                <span class="text-black"><?=lang("client_one_jobname")?></span>
              </div>
              <div class="card-body">
                <p class="desc text-black">
                  <?=lang('client_one_comment')?>
                </p>
                <div class="star-icon">
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card item">
              <div class="person-info">
                <h3 class="name"><?=lang('client_two')?></h3>
                <span class="text-black"><?=lang('client_two_jobname')?></span>
              </div>
              <div class="card-body">
                <p class="desc text-black">
                  <?=lang('client_two_comment')?>
                </p>
                <div class="star-icon">
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                </div>
              </div>
            </div>
          </div>          
          <div class="col-md-4">
            <div class="card item">
              <div class="person-info">
                <h3 class="name"><?=lang('client_three')?></h3>
                <span class="text-black"><?=lang('client_three_jobname')?></span>
              </div>
              <div class="card-body">
                <p class="desc text-black">
                  <?=lang('client_three_comment')?>
                  
                </p>
                <div class="star-icon">
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                  <span><i class="fa fa-star"></i></span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section class="section-3 subscribe-form">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <form class="form actionFormWithoutToast" action="<?php echo cn("client/subscriber"); ?>" data-redirect="<?php echo cn(); ?>" method="POST">
              <div class="content text-center">
                <h1 class="title"><?php echo lang("newsletter"); ?></h1>
                <p><?php echo lang("fill_in_the_ridiculously_small_form_below_to_receive_our_ridiculously_cool_newsletter"); ?></p>
              </div>
              <div class="input-group">
                <input type="email" name="email" class="form-control email" placeholder="Enter Your email" required>
                <button class="input-group-append btn btn-pill btn-gradient btn-signin btn-submit" type="submit">
                  <?php echo lang("subscribe_now"); ?>
                </button>
              </div>
              <div class="form-group m-t-20">
                <div id="alert-message" class="alert-message-reponse"></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <div class="modal-infor">
      <div class="modal" id="notification">
        <div class="modal-dialog">
          <div class="modal-content">

            <div class="modal-header">
              <h4 class="modal-title"><i class="fe fe-bell"></i> <?=lang("Notification")?></h4>
              <button type="button" class="close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <?=get_option('notification_popup_content')?>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal"><?=lang("Close")?></button>
            </div>
          </div>
        </div>
      </div>
    </div>






<?=Modules::run(get_theme()."/footer")?>