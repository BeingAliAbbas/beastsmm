 <h1><?= lang('Add Funds By Email') ?></h1>

    <form id="addFundsForm" method="POST" action="<?= cn('manual_funds/add') ?>">
        <label for="email_to"><?= lang('Email') ?></label>
        <input type="email" id="email_to" name="email_to" placeholder="<?= lang('Enter User Email') ?>" required><br>

        <label for="payment_method"><?= lang('Payment Method') ?></label>
        <select name="payment_method" id="payment_method" required>
            <option value="manual">Manual Payment</option>
            <option value="bonus">Bonus</option>
            <option value="other">Other</option>
            <?php foreach ($payments_defaut as $payment): ?>
                <option value="<?= $payment->type ?>"><?= $payment->name ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="funds"><?= lang('Funds') ?></label>
        <input type="number" id="funds" name="funds" placeholder="<?= lang('Enter Amount') ?>" required><br>

        <label for="transaction_id"><?= lang('Transaction ID (Optional)') ?></label>
        <input type="text" id="transaction_id" name="transaction_id" placeholder="<?= lang('Enter Transaction ID') ?>"><br>

        <button type="submit"><?= lang('Submit') ?></button>
    </form>

    <!-- You can add JavaScript to handle form submission or validation if needed -->
</body>
</html>