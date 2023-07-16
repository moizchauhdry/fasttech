<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Ticket')); ?> - <?php echo e($ticket->ticket_id); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('style'); ?>
    <style>
        @media (max-width: 767px) {
            .auth-layout-wrap .auth-content {
                min-width: 100%;
            }
        }
        @media (min-width: 768px) {
            .auth-layout-wrap .auth-content {
                min-width: 90%;
            }
        }
        @media (min-width: 1024px) {
            .auth-layout-wrap .auth-content {
                min-width: 50%;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="//cdn.ckeditor.com/4.12.1/basic/ckeditor.js"></script>
    <script src="<?php echo e(asset('js/editorplaceholder.js')); ?>"></script>
    <script>
        CKEDITOR.replace('reply_description', {
            // contentsLangDirection: 'rtl',
            extraPlugins: 'editorplaceholder',
            editorplaceholder: '<?php echo e(__('Start Text here..')); ?>'
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="auth-wrapper auth-v1">
    <div class="bg-auth-side bg-primary"></div>
    <div class="auth-content">

        <div class="row align-items-center justify-content-center text-start">
            <div class="col-xl-12 text-start">
                <div class="mx-3 mx-md-5">
                    <div class="card-header">
                        <h5 class="text-white"><?php echo e(__('Ticket')); ?> - <?php echo e($ticket->ticket_id); ?></h5>
                    </div>
                </div>
                
                <div class="card p-4">
                    <?php echo csrf_field(); ?>
                    <div class="card mb-3">
                        <div class="card-header"><h6><?php echo e($ticket->name); ?> <small>(<?php echo e($ticket->created_at->diffForHumans()); ?>)</small></h6></div>
                        <div class="card-body w-100">
                            <div>
                                <p><?php echo $ticket->description; ?></p>
                            </div>
                            <?php
                                $attachments=json_decode($ticket->attachments);
                            ?>
                            <?php if(!is_null($attachments) && count($attachments)>0): ?>
                                <div class="m-1 ml-3">
                                    <b><?php echo e(__('Attachments')); ?> :</b>
                                    <ul class="list-group list-group-flush">
                                        <?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="list-group-item px-0">
                                                <?php echo e($attachment); ?><a download="mr-2" href="<?php echo e(asset(Storage::url('tickets/'.$ticket->ticket_id."/".$attachment))); ?>" class="edit-icon py-1 ml-2" title="<?php echo e(__('Download')); ?>"><i class="fa fa-download ms-2"></i></a>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $__currentLoopData = $ticket->conversions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="card mb-3">
                            <div class="card-header"><h6><?php echo e($conversion->replyBy()->name); ?> <small>(<?php echo e($conversion->created_at->diffForHumans()); ?>)</small></h6></div>
                            <div class="card-body w-100">
                                <div><?php echo $conversion->description; ?></div>
                                <?php
                                    $attachments=json_decode($conversion->attachments);
                                ?>
                                <?php if(count($attachments)): ?>
                                    <div class="m-1">
                                        <b><?php echo e(__('Attachments')); ?> :</b>
                                        <ul class="list-group list-group-flush">
    
                                            <?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li class="list-group-item px-0">
                                                    <?php echo e($attachment); ?><a download="mr-2" href="<?php echo e(asset(Storage::url('tickets/'.$ticket->ticket_id."/".$attachment))); ?>" class="edit-icon py-1 ml-2" title="<?php echo e(__('Download')); ?>"><i class="fa fa-download ms-2"></i></a>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
                    <?php if($ticket->status != 'Closed'): ?>
                        <div class="card mb-3">
                            <div class="card-body w-100">
                                <form method="post" action="<?php echo e(route('home.reply',$ticket->ticket_id)); ?>" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label class="require form-label"><?php echo e(__('Description')); ?></label>
                                            <textarea name="reply_description" class="form-control <?php echo e($errors->has('reply_description') ? ' is-invalid' : ''); ?>"><?php echo e(old('reply_description')); ?></textarea>
                                            <div class="invalid-feedback">
                                                <?php echo e($errors->first('reply_description')); ?>

                                            </div>
                                        </div>
                                        <div class="form-group col-md-12 file-group">
                                            <label class="require form-label"><?php echo e(__('Attachments')); ?></label>
                                            <label class="form-label"><small>(<?php echo e(__('You can select multiple files')); ?>)</small></label>
                                            <div class="choose-file form-group">
                                                <label for="file" class="form-label">
                                                    <div><?php echo e(__('Choose File Here')); ?></div>
                                                    <input type="file" class="form-control <?php echo e($errors->has('reply_attachments') ? 'is-invalid' : ''); ?>" multiple="" name="reply_attachments[]" id="file" data-filename="multiple_reply_file_selection">
                                                    <div class="invalid-feedback">
                                                        <?php echo e($errors->first('reply_attachments')); ?>

                                                    </div>
                                                </label>
                                                <p class="multiple_reply_file_selection"></p>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div class="form-group col-md-12">
                                        <div class="text-center">
                                            <input type="hidden" name="status" value="In Progress"/>
                                            <button class="btn btn-submit btn-primary btn-block mt-2"><?php echo e(__('Submit')); ?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-blue font-weight-bold text-center mb-0"><?php echo e(__('Ticket is closed you cannot replay.')); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
        
                </div>
            </div>
        </div>

    </div>
</div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script>
        // for Choose file
        $(document).on('change', 'input[type=file]', function () {
            var names = '';
            var files = $('input[type=file]')[0].files;

            for (var i = 0; i < files.length; i++) {
                names += files[i].name + '<br>';
            }
            $('.' + $(this).attr('data-filename')).html(names);
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/fabuxrfq/support.fasttechnologies.pk/resources/views/show.blade.php ENDPATH**/ ?>