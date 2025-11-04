<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?=lang("Update Order Status")?></h3>
            </div>
            <div class="card-body">
                <form id="update-order-form">
                    <div class="form-group">
                        <label class="form-label"><?=lang("Order ID")?></label>
                        <input type="text" class="form-control" name="order_id" id="order_id" placeholder="<?=lang("Enter Order ID")?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block"><?=lang("Update Status")?></button>
                    </div>
                </form>
                <div id="result" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $("#update-order-form").on('submit', function(e){
        e.preventDefault();
        var order_id = $("#order_id").val();
        if (order_id == "") {
            $("#result").html('<div class="alert alert-danger">Please enter an Order ID</div>');
            return false;
        }
        
        $("#result").html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '<?=cn("api_provider/update_order_status/")?>'+order_id,
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    var details = response.details;
                    var html = '<div class="alert alert-success">'+response.message+'</div>';
                    html += '<table class="table table-bordered">';
                    html += '<tr><td>Order ID</td><td>'+details.order_id+'</td></tr>';
                    html += '<tr><td>Old Status</td><td>'+details.old_status+'</td></tr>';
                    html += '<tr><td>New Status</td><td>'+details.new_status+'</td></tr>';
                    if (details.start_count) html += '<tr><td>Start Count</td><td>'+details.start_count+'</td></tr>';
                    if (details.remains) html += '<tr><td>Remains</td><td>'+details.remains+'</td></tr>';
                    html += '<tr><td>API Status</td><td>'+details.api_status+'</td></tr>';
                    html += '</table>';
                    $("#result").html(html);
                } else {
                    $("#result").html('<div class="alert alert-danger">'+response.message+'</div>');
                }
            },
            error: function(){
                $("#result").html('<div class="alert alert-danger">An error occurred while processing your request</div>');
            }
        });
    });
});
</script>