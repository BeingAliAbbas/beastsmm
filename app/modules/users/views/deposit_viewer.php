<?php
// Database connection
$host = 'localhost';
$dbname = 'beastsmm_ali';
$username = 'beastsmm_ali';
$password = 'ra6efcTo[4z#';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if UID is provided
if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
    $uid = intval($_GET['uid']);

    // Query to fetch the total deposit sum for the given user ID
    $query = "SELECT SUM(amount) as total_deposit FROM general_transaction_logs WHERE uid = ? AND status = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && $result['total_deposit'] !== null) {
        $total_deposit = number_format($result['total_deposit'], 2);
    } else {
        $total_deposit = "No deposits found.";
    }
} else {
    $total_deposit = "Invalid user ID.";
}
?>

<!-- Modal to show total deposit -->
<div id="depositModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Total Deposit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?= 'Total Deposit: ' . $total_deposit ?>
      </div>
    </div>
  </div>
</div>

<!-- Script to trigger the modal automatically -->
<script>
  $(document).ready(function() {
    $('#depositModal').modal('show');
  });
</script>
