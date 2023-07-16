
<form class="px-3" method="post" action="<?php echo e(route('admin.test.email.send')); ?>" id="test_email">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="mail_driver" value="<?php echo e($data['mail_driver']); ?>" />
    <input type="hidden" name="mail_host" value="<?php echo e($data['mail_host']); ?>" />
    <input type="hidden" name="mail_port" value="<?php echo e($data['mail_port']); ?>" />
    <input type="hidden" name="mail_username" value="<?php echo e($data['mail_username']); ?>" />
    <input type="hidden" name="mail_password" value="<?php echo e($data['mail_password']); ?>" />
    <input type="hidden" name="mail_encryption" value="<?php echo e($data['mail_encryption']); ?>" />
    <input type="hidden" name="mail_from_address" value="<?php echo e($data['mail_from_address']); ?>" />
    <input type="hidden" name="mail_from_name" value="<?php echo e($data['mail_from_name']); ?>" />
    <div class="row">
        <div class="col-md-12 mb-3">
            <label for="email" class="form-label"><?php echo e(__('E-Mail Address')); ?></label>
            <input type="text" class="form-control" id="email" name="email" required/>
        </div>
        <div class="col-md-12 mb-2 text-end">
            <label id="email_sending" style="display: none;"><i class="fas fa-clock"></i></label>
            <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
            <input type="submit" value="<?php echo e(__('Send Test Mail')); ?>" class="btn-create btn btn-primary">
        </div>
    </div>
</form>

<?php /**PATH /home/fabuxrfq/support.fasttechnologies.pk/resources/views/admin/users/test_email.blade.php ENDPATH**/ ?>