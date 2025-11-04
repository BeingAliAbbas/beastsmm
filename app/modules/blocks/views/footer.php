<!-- Footer Start -->
<div class="footer footer_top">
  <div class="container m-t-60 m-b-50">
    <div class="row">
      <!-- Logo and Language Selector -->
      <div class="col-lg-4">
        <div class="site-logo m-b-30">
          <a href="<?=cn()?>" class="m-r-20">
            <img src="<?=get_option('website_logo_white', BASE."assets/images/logo-white.png")?>" alt="Website logo">
          </a>
          <?php
            $redirect = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
          ?>
          <?php if (!empty($languages)): ?>
            <select class="footer-lang-selector ajaxChangeLanguage" name="ids" data-url="<?=cn('language/set_language/')?>" data-redirect="<?=$redirect?>">
              <?php foreach ($languages as $row): ?>
                <option value="<?=$row->ids?>" <?=(!empty($lang_current) && $lang_current->code == $row->code) ? 'selected' : '' ?>><?=language_codes($row->code)?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-4">
        <h4 class="title"><?=lang("Quick_links")?></h4>
        <div class="row">
          <div class="col-6 col-md-3">
            <ul class="list-unstyled quick-link">
              <li><a href="<?=cn()?>"><?=lang("Home")?></a></li>
              <?php if (!session('uid')): ?>
                <li><a href="<?=cn('auth/login')?>"><?=lang("Login")?></a></li>
                <li><a href="<?=cn('auth/signup')?>"><?=lang("Sign_Up")?></a></li>
              <?php else: ?>
                <li><a href="<?=cn('services')?>"><?=lang("Services")?></a></li>
                <li><a href="<?=cn('tickets')?>"><?=lang("Tickets")?></a></li>  
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-6 col-md-3">
            <ul class="list-unstyled quick-link">
              <li><a href="<?=cn('terms')?>"><?=lang("terms__conditions")?></a></li>
              <?php if (get_option('is_cookie_policy_page')): ?>
                <li><a href="<?=cn('cookie-policy')?>"><?=lang("Cookie_Policy")?></a></li>
              <?php endif; ?>
              <?php if (get_option('enable_api_tab')): ?>
                <li><a href="<?=cn('api/docs')?>"><?=lang("api_documentation")?></a></li>
              <?php endif; ?>
              <li><a href="<?=cn('faq')?>"><?=lang("FAQs")?></a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-4">
        <h4 class="title"><?=lang("contact_informations")?></h4>
        <ul class="list-unstyled">
          <li><?=lang("Whatsapp Number")?>: <?=get_option('whatsapp_number',"+12345678")?></li>
          <li><?=lang("Email")?>: <?=get_option('contact_email',"do-not-reply@smartpanel.com")?></li>
          <li><?=lang("working_hour")?>: <?=get_option('contact_work_hour',"Mon - Sat 09 am - 10 pm")?></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Footer Bottom -->
<footer class="footer footer_bottom">
  <div class="container">
    <div class="row align-items-center justify-content-between">
      <!-- Social Icons -->
      <div class="col-auto">
        <ul class="list-inline mb-0">
          <?php if (get_option('social_facebook_link')): ?>
            <li class="list-inline-item"><a href="<?=get_option('social_facebook_link')?>" target="_blank" class="btn btn-icon btn-facebook"><i class="fa fa-facebook"></i></a></li>
          <?php endif; ?>
          <?php if (get_option('social_twitter_link')): ?>
            <li class="list-inline-item"><a href="<?=get_option('social_twitter_link')?>" target="_blank" class="btn btn-icon btn-twitter"><i class="fa fa-twitter"></i></a></li>
          <?php endif; ?>
          <?php if (get_option('social_instagram_link')): ?>
            <li class="list-inline-item"><a href="<?=get_option('social_instagram_link')?>" target="_blank" class="btn btn-icon btn-instagram"><i class="fa fa-instagram"></i></a></li>
          <?php endif; ?>
          <?php if (get_option('social_youtube_link')): ?>
            <li class="list-inline-item"><a href="<?=get_option('social_youtube_link')?>" target="_blank" class="btn btn-icon btn-youtube"><i class="fa fa-youtube"></i></a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Copyright Info -->
      <div class="col-auto text-center text-lg-right">
        <span><?=get_option('copy_right_content', "Copyright &copy; 2020 - SmartPanel")?> <?=(get_role("admin")) ? $version : "" ?></span>
        <?php if(get_role("admin")): ?>
          <div><a href="https://beastsmm.xyz" target="_blank">Powered By <span>beastsmm.xyz</span></a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</footer>
<!-- Footer End -->
