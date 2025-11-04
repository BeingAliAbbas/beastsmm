<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-refresh"></i> Bulk Status Update Results
                </h3>
                <div class="card-options">
                    <a href="<?=cn('api_provider/update_order_status')?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Form
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-success mb-4">
                    <strong><?=$result['message']?></strong>
                    <p>Total orders processed: <?=$result['count']?></p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>API Order ID</th>
                                <th>Old Status</th>
                                <th>New Status</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($result['updates'] as $update): ?>
                            <tr>
                                <td><?=$update['order_id']?></td>
                                <td><?=$update['api_order_id']?></td>
                                <td><?=$update['old_status']?></td>
                                <td><?=$update['new_status']?></td>
                                <td>
                                    <?php if($update['success']): ?>
                                        <span class="badge badge-success">Success</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                    <div class="small text-muted"><?=$update['message']?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="<?=cn('api_provider')?>" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to API Providers
                    </a>
                    <a href="<?=cn('api_provider/update_order_status')?>" class="btn btn-outline-primary">
                        <i class="fa fa-refresh"></i> Update Another Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>