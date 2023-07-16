<?php $__env->startComponent('mail::message'); ?>
<?php echo e(__('Hello')); ?>, <?php echo e($ticket->name); ?>


<?php echo e(__('A request for support has been created and assigned')); ?> #<?php echo e($ticket->ticket_id); ?>. <?php echo e(__('A representative will follow-up with you as soon as possible. You can  view this ticket progress online.')); ?>


<?php $__env->startComponent('mail::button', ['url' => route('home.view',\Illuminate\Support\Facades\Crypt::encrypt($ticket->ticket_id))]); ?>
 <?php echo e(__('Check Your Ticket Now')); ?>

<?php echo $__env->renderComponent(); ?>

<?php echo e(__('Thanks')); ?>,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /home/fabuxrfq/support.fasttechnologies.pk/resources/views/email/create_ticket.blade.php ENDPATH**/ ?>