
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <style>
    .fa-circle-check{ color: green; }

    /* Minimal payment option row */
    .pay-opt{ display:flex; align-items:center; gap:10px; padding:6px 4px; }
    .pay-icon-img{ width:22px; height:22px; border-radius:4px; object-fit:contain; display:inline-block; }
    .pay-sel{ display:inline-flex; align-items:center; gap:8px; }
    .arrow-rotate{ transform:rotate(180deg); }

    /* === Select2 Unified Fix & Style (tuned) === */
    :root{
      --sel-font-size: clamp(14px, 3.6vw, 16px);
      --sel-line-height: 1.3;
      --sel-border: #04a9f4;
      --sel-border-focus: #0390cf;
      --sel-bg: #ffffff;
      --sel-bg-alt: #f5fbfe;
      --sel-text: #000;
      --sel-placeholder: #666;
      --sel-highlight-bg: #00599e;
      --sel-highlight-text: #fff;
      --sel-selected-bg: #d3eefc;
      --sel-meta-color: #000000;
      --sel-scrollbar-thumb: #04a9f4;
      --sel-scrollbar-track: #cfe8f5;
    }
    /* Dark palette (apply by body.dark) */
    body.dark, body[data-theme="dark"]{
      --sel-bg: #151d25;
      --sel-bg-alt: #1e2a34;
      --sel-text: #e9edf0;
      --sel-placeholder: #9aa8b3;
      --sel-highlight-bg: #0d6ea8;
      --sel-highlight-text: #ffffff;
      --sel-selected-bg: #043549;
      --sel-meta-color: #7ea0b8;
      --sel-scrollbar-thumb: #0d6ea8;
      --sel-scrollbar-track: #122029;
    }

    .cat-icon{ margin-right:6px; width:18px; min-width:18px; text-align:center; font-size:15px; color:#000; line-height:1; }
    body.dark .cat-icon{ color:#58c9ff; }

    /* Container: exact width of parent */
    .select2-container,
    .select2,
    .select2-container[style]{
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box;
      display: block;
      font-size: var(--sel-font-size);
    }
    select.select2-hidden-accessible{ display:none!important; }

    .select2-container--default .select2-selection--single{
      position:relative; width:100%; max-width:100%; min-height:44px; height:auto; margin-top:4px;
      border:1px solid var(--sel-border); border-radius:6px; padding:8px 42px 8px 12px;
      font-size:var(--sel-font-size); line-height:var(--sel-line-height); font-weight:500;
      color:var(--sel-text); background:var(--sel-bg); display:flex; align-items:flex-start;
      box-sizing:border-box; overflow:hidden; transition:border-color .18s ease, box-shadow .18s ease;
    }
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default .select2-selection--single:focus-visible{
      border-color:var(--sel-border-focus); box-shadow:0 0 0 3px rgba(4,169,244,.25); outline:none;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{
      flex:1 1 auto; padding:0; margin:0; white-space:normal!important; line-height:var(--sel-line-height);
      word-break:break-word; overflow-wrap:anywhere; color:var(--sel-text)!important; font-size:16px; font-weight:600;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder{
      color:var(--sel-placeholder)!important; white-space:normal; word-break:break-word; overflow-wrap:anywhere; font-size:16px; font-weight:600;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{
      position:absolute; top:50%!important; right:10px; transform:translateY(-50%); height:auto; width:22px; display:flex; align-items:center; justify-content:center; pointer-events:none;
    }

    .select2-container--open{ z-index:10001!important; }

    /* Dropdown: match trigger width; do not force parent's width */
    .select2-container--default .select2-dropdown{
      width: 10% !important;    /* allow natural sizing */
      min-width: 100%;           /* never smaller than trigger */
      border:1px solid var(--sel-border); border-radius:6px; box-sizing:border-box; background:var(--sel-bg);
      height:auto!important; margin-top:2px; overflow:hidden; animation:selFade .12s ease;
    }
    .select2-container--default.select2-container--above .select2-dropdown{ margin-top:-2px; margin-bottom:2px; }
    @keyframes selFade{ from{opacity:0; transform:translateY(-4px);} to{opacity:1; transform:translateY(0);} }

    .select2-container .select2-search--dropdown{ padding:6px 8px 4px; background:var(--sel-bg-alt); }
    .select2-container .select2-search--dropdown .select2-search__field{
      border:1px solid var(--sel-border); padding:6px 8px; border-radius:4px; width:100%!important;
      font-size:var(--sel-font-size); color:var(--sel-text); background:var(--sel-bg); outline:none; box-shadow:none;
    }
    .select2-container .select2-search--dropdown .select2-search__field:focus{
      border-color:var(--sel-border-focus); box-shadow:0 0 0 2px rgba(4,169,244,.25);
    }

    /* Results height */
    .select2-container--default .select2-results > .select2-results__options{
      max-height: 280px !important;
      overflow-y:auto; overflow-x:hidden; margin:4px 0; padding:6px 0 8px; background:var(--sel-bg-alt);
      scrollbar-width:thin; scrollbar-color:var(--sel-scrollbar-thumb) var(--sel-scrollbar-track);
    }
    .select2-container--default .select2-results__options::-webkit-scrollbar{ width:10px; }
    .select2-container--default .select2-results__options::-webkit-scrollbar-track{ background:var(--sel-scrollbar-track); border-radius:6px; }
    .select2-container--default .select2-results__options::-webkit-scrollbar-thumb{ background:var(--sel-scrollbar-thumb); border-radius:6px; }

    .select2-container--default .select2-results__option{
      border:1px solid var(--sel-border); background:var(--sel-bg); color:var(--sel-text);
      border-radius:5px; margin:4px 10px; padding:10px 10px 10px 12px; font-size:var(--sel-font-size);
      line-height:1.25; font-weight:500; white-space:normal; word-break:break-word; overflow-wrap:anywhere;
      cursor:pointer; transition:background .15s ease, color .15s ease, border-color .15s ease; min-height:44px;
      display:flex; flex-direction:column; justify-content:center;
    }
    .select2-container--default .select2-results__option[aria-selected="true"]{
      background:var(--sel-selected-bg); color:var(--sel-text); border-color:var(--sel-border);
    }
    .select2-container--default .select2-results__option.select2-results__option--highlighted{
      background:var(--sel-highlight-bg); color:var(--sel-highlight-text)!important; border-color:var(--sel-highlight-bg);
    }

    @media (max-width:680px){
      .select2-container--default .select2-selection--single{ padding:10px 44px 10px 14px; border-radius:8px; }
      .select2-container--default .select2-results__option{ margin:4px 8px; padding:12px 12px; min-height:48px; }
      .select2-container--default .select2-dropdown{ border-radius:8px; }
      .select2-container .select2-search--dropdown .select2-search__field{ font-size:clamp(15px,4.5vw,17px); }
      .select2-container--default .select2-results > .select2-results__options{ max-height: 320px !important; }
    }
    @media (max-width:380px){
      .select2-container--default .select2-results__option{ margin:3px 6px; padding:10px 10px; }
      .select2-container--default .select2-results > .select2-results__options{ max-height: 360px !important; }
    }
  </style>
</head>

<!-- Add class="dark" so the dark variables apply (fixes white box on dark pages) -->
<body class="dark">

<div class="container-fluid mt-3">
  <?php if (get_option('add_funds_text','') != ''): ?>
    <div class="row"><div class="col-sm-12">
      <div class="card"><div class="card-body"><?= get_option('add_funds_text','') ?></div></div>
    </div></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-sm-12">
      <div class="accordion" id="faqAddFunds">
        <div class="card" style="background:transparent; border:none;">
          <div class="card-header" id="headingAddFunds" style="background:transparent; border:none; padding-left:0;">
            <h2 class="mb-0">
              <button class="btn btn-link text-white d-flex align-items-center" type="button"
                      data-toggle="collapse" data-target="#collapseAddFunds"
                      aria-expanded="false" aria-controls="collapseAddFunds"
                      style="font-size:1.3rem; text-decoration:none;">
                <span class="mr-2 arrow-icon" style="transition:transform .3s;"><i class="fa fa-chevron-down"></i></span>
                <i class="fa fa-question-circle mr-2"></i> How to add funds?
              </button>
            </h2>
          </div>
          <div id="collapseAddFunds" class="collapse" aria-labelledby="headingAddFunds" data-parent="#faqAddFunds">
            <div class="card-body" style="text-align:center;">
              <div style="display:inline-block; padding:4px; border-radius:24px; background:linear-gradient(to right,#05cbfd 0%,#203d9d 100%); margin-left:-26px;">
                <iframe width="315" height="570" src="https://www.youtube.com/embed/wnCQolxg7OY?si=IHJBmRhhcZPAlbHr"
                        title="YouTube Shorts" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        style="border-radius:20px; background:#fff; border:none; display:block;"></iframe>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /accordion -->
    </div>
  </div>

  <?php if (!empty($payments)): ?>
    <section class="add-funds m-t-30">
      <div class="row justify-content-md-center" id="result_ajaxSearch">
        <div class="col-md-8">
          <div class="card">
            <div class="d-flex align-items-center justify-content-center">
              <div class="tabs-list w-100 p-3">
                <div class="form-group mb-0 w-100">
                  <label for="paymentTypeDropdown" class="mb-2"><b>Please select a payment Method</b></label>
                  <select id="paymentTypeDropdown" class="form-control">
                    <option value="">Select Payment Type</option>
                    <?php foreach ($payments as $row): ?>
                      <option value="<?php echo htmlspecialchars($row->type); ?>">
                        <?php echo htmlspecialchars($row->name); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div class="tab-content">
                <?php foreach ($payments as $row): ?>
                  <div id="<?php echo htmlspecialchars($row->type); ?>" class="tab-pane fade">
                    <?php $this->load->view($row->type.'/index', ['payment_id'=>$row->id, 'payment_params'=>$row->params]); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="latest-transactions m-t-30">
    <div class="row justify-content-md-center">
      <div class="col-md-8 p-0">
        <div class="transaction-card">
          <div class="transaction-card-header">
            <h5 class="transaction-card-title text-white">Last 5 Transactions</h5>
          </div>
          <div class="transaction-card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-bordered" style="border-collapse:collapse; margin:0;">
                <thead>
                  <tr><th>No.</th><th>Transaction ID</th><th>Payment Method</th><th>Amount</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php if (!empty($transactions)):
                  $last_transactions = array_reverse(array_slice($transactions, 0, 5));
                  $i = 1;
                  foreach ($last_transactions as $transaction): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($transaction->transaction_id); ?></td>
                    <td>
                      <?php
                        if ($transaction->type == 'easypaisa') {
                          echo '<img src="https://ipg2.apps.net.pk/_next/static/media/13.49fc814f.png" alt="Easypaisa" class="payment-icon" />';
                        } elseif ($transaction->type == 'jazzcash') {
                          echo '<img src="https://ipg2.apps.net.pk/_next/static/media/1.593d5ab6.png" alt="JazzCash" class="payment-icon" />';
                        } else {
                          echo 'Manual';
                        }
                      ?>
                    </td>
                    <td><?php echo $currency_symbol . number_format((float)$transaction->amount, 2); ?></td>
                    <td>
                      <span style="color: <?php echo ((int)$transaction->status === 1) ? '#05d0a1' : 'red'; ?>;">
                        <?php echo ((int)$transaction->status === 1) ? 'Paid' : 'Unpaid'; ?>
                      </span>
                    </td>
                    <td><?php echo isset($transaction->created) ? date('Y-m-d H:i:s', strtotime($transaction->created)) : 'Date not available'; ?></td>
                  </tr>
                  <?php endforeach; else: ?>
                  <tr><td colspan="6" class="text-center">No transactions found.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div><!-- /table-responsive -->
          </div>
        </div>
      </div>
    </div>
  </section>

</div><!-- /container-fluid -->

<!-- jQuery (must be before Select2 and Bootstrap JS) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS (for collapse/accordion) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function ($) {
  $(function () {
    // Accordion arrow
    $('#collapseAddFunds')
      .on('show.bs.collapse', function(){ $(this).prev().find('.arrow-icon').addClass('arrow-rotate'); })
      .on('hide.bs.collapse', function(){ $(this).prev().find('.arrow-icon').removeClass('arrow-rotate'); });

    var $dd = $('#paymentTypeDropdown');
    if (!$dd.length || typeof $.fn.select2 !== 'function') return;

    // Visuals (no extra metadata)
    function getPaymentVisual(type){
      type = (type||'').toLowerCase();
      if (type === 'easypaisa') return { img:'https://ipg2.apps.net.pk/_next/static/media/13.49fc814f.png', label:'Easypaisa' };
      if (type === 'jazzcash')  return { img:'https://ipg2.apps.net.pk/_next/static/media/1.593d5ab6.png',  label:'JazzCash'  };
      if (type === 'manual')    return { icon:'fa-regular fa-clipboard',            label:'Manual'    };
      return { icon:'fa-solid fa-wallet', label: type ? (type.charAt(0).toUpperCase()+type.slice(1)) : '' };
    }

    function templateResult(opt){
      if (!opt.id) return opt.text;
      var $el = $(opt.element), vis = getPaymentVisual($el.val());
      var label = $.trim($el.text()) || vis.label || opt.text;
      var iconHtml = vis.img ? '<img class="pay-icon-img" src="'+vis.img+'" alt="">' : '<i class="'+(vis.icon||'')+'"></i>';
      return $('<div class="pay-opt">'+ iconHtml +'<span class="pay-label">'+ $('<span>').text(label).html() +'</span></div>');
    }

    function templateSelection(opt){
      if (!opt.id) return opt.text;
      var $el = $(opt.element), vis = getPaymentVisual($el.val());
      var label = $.trim($el.text()) || vis.label || opt.text;
      var iconHtml = vis.img ? '<img class="pay-icon-img" src="'+vis.img+'" alt="">' : '<i class="'+(vis.icon||'')+'"></i>';
      return $('<span class="pay-sel">'+ iconHtml +'<span class="pay-label">'+ $('<span>').text(label).html() +'</span></span>');
    }

    function activatePane(type){
      $('.tab-pane').removeClass('in active show');
      if (type) $('#'+type).addClass('in active show');
    }

    $dd.select2({
      placeholder: 'Select Payment Type',
      allowClear: false,
      width: '100%',
      minimumResultsForSearch: Infinity,
      templateResult: templateResult,
      templateSelection: templateSelection,
      escapeMarkup: function(m){ return m; },
      dropdownParent: $(document.body),   // avoid parent width forcing
      dropdownAutoWidth: true             // natural width, not parent's width
    })
    .on('change', function(){
      var v = $(this).val();
      activatePane(v);
      try { localStorage.setItem('selectedPaymentType', v || ''); } catch(e){}
    })
    .on('select2:open', function(){
      // Safety: keep dropdown at least as wide as the trigger
      var w = $dd.data('select2').$container.outerWidth();
      $('.select2-container--open .select2-dropdown').css('min-width', w);
    });

    // Restore selection, if any
    try {
      var prev = localStorage.getItem('selectedPaymentType');
      if (prev && $dd.find('option[value="'+prev+'"]').length) {
        $dd.val(prev).trigger('change');
      }
    } catch(e) {}
  });
})(jQuery);
</script>
