<?php
// Start session
session_start();

// Check if form is submitted and store order details in session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming the form passes 'service_name', 'link', 'quantity', 'total_charge'
    $_SESSION['order_details'] = [
        'service_name' => $_POST['service_name'] ?? '',
        'link' => $_POST['link'] ?? '',
        'quantity' => $_POST['quantity'] ?? '',
        'total_charge' => $_POST['total_charge'] ?? '',
    ];
}

// Check if order details exist in session for modal
$order_details = $_SESSION['order_details'] ?? null;
?>

<!-- Modal Structure -->
<div id="orderSummaryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderSummaryModalLabel">Order Summary</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php if ($order_details): ?>
          <p><strong>Service Name:</strong> <?= htmlspecialchars($order_details['service_name']) ?></p>
          <p><strong>Link:</strong> <?= htmlspecialchars($order_details['link']) ?></p>
          <p><strong>Quantity:</strong> <?= htmlspecialchars($order_details['quantity']) ?></p>
          <p><strong>Total Charge:</strong> <?= htmlspecialchars($order_details['total_charge']) ?></p>
        <?php else: ?>
          <p>No order details available.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Include jQuery and Bootstrap (or whatever framework you are using) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function () {
    // Check if order details exist in session and show the modal
    <?php if ($order_details): ?>
      $('#orderSummaryModal').modal('show');

      // Clear session after modal is shown
      $('#orderSummaryModal').on('hidden.bs.modal', function () {
        $.ajax({
          url: 'clear_session.php', // This file will clear the session (see below)
          method: 'POST',
          success: function (response) {
            console.log('Session cleared after modal close');
          }
        });
      });
    <?php endif; ?>
  });
</script>
