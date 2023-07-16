<?php $__env->startComponent('mail::message'); ?>
<?php echo e(__('Hello')); ?>, <?php echo e($user->name); ?>


<?php echo e(__('A request for support has been created and assigned')); ?> #<?php echo e($ticket->ticket_id); ?>. <?php echo e(__('Please follow-up as soon as possible.')); ?>


<?php $__env->startComponent('mail::button', ['url' => route('admin.tickets.edit',$ticket->id)]); ?>
<?php echo e(__('Open Ticket Now')); ?>

<?php echo $__env->renderComponent(); ?>

<?php echo e(__('Thanks')); ?>,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /home/pbbarcou/support.pbbarcouncil.com/resources/views/email/create_ticket_admin.blade.php ENDPATH**/ ?>