<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Create Ticket')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.tickets.index')); ?>"><?php echo e(__('Tickets')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Create')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <form action="<?php echo e(route('admin.tickets.store')); ?>" class="mt-3" method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-header"><h6><?php echo e(__('Ticket Information')); ?></h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Name')); ?></label>
                                <input class="form-control <?php echo e((!empty($errors->first('name')) ? 'is-invalid' : '')); ?>" type="text" name="name" required="" placeholder="<?php echo e(__('Name')); ?>">
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('name')); ?>

                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Email')); ?></label>
                                <input class="form-control <?php echo e((!empty($errors->first('email')) ? 'is-invalid' : '')); ?>" type="email" name="email" required="" placeholder="<?php echo e(__('Email')); ?>">
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('email')); ?>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Category')); ?></label>
                                <select class="form-control <?php echo e((!empty($errors->first('category')) ? 'is-invalid' : '')); ?>" name="category" required="">
                                    <option value=""><?php echo e(__('Select Category')); ?></option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('category')); ?>

                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Status')); ?></label>
                                <select class="form-control <?php echo e((!empty($errors->first('status')) ? 'is-invalid' : '')); ?>" name="status" required="">
                                    <option value=""><?php echo e(__('Select Status')); ?></option>
                                    <option value="In Progress"><?php echo e(__('In Progress')); ?></option>
                                    <option value="On Hold"><?php echo e(__('On Hold')); ?></option>
                                    <option value="Closed"><?php echo e(__('Closed')); ?></option>
                                </select>
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('status')); ?>

                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Subject')); ?></label>
                                <input class="form-control <?php echo e((!empty($errors->first('subject')) ? 'is-invalid' : '')); ?>" type="text" name="subject" required="" placeholder="<?php echo e(__('Subject')); ?>">
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('subject')); ?>

                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="require form-label"><?php echo e(__('Attachments')); ?> <small>(<?php echo e(__('You can select multiple files')); ?>)</small> </label>                                
                                <div class="choose-file form-group">
                                    <label for="file" class="form-label d-block">
                                        

                                        <input type="file" name="attachments[]" id="file" class="form-control mb-2 <?php echo e($errors->has('attachments') ? ' is-invalid' : ''); ?>" multiple=""  data-filename="multiple_file_selection" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                        <img src="" id="blah" width="20%"/>
                                        <div class="invalid-feedback">
                                            <?php echo e($errors->first('attachments.*')); ?>

                                        </div>
                                    </label>
                                </div>
                                <p class="multiple_file_selection mx-4"></p>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="require form-label"><?php echo e(__('Description')); ?></label>
                                <textarea name="description" class="form-control ckdescription <?php echo e((!empty($errors->first('description')) ? 'is-invalid' : '')); ?>"></textarea>
                                <div class="invalid-feedback">
                                    <?php echo e($errors->first('description')); ?>

                                </div>
                            </div>
                            <?php if(!$customFields->isEmpty()): ?>
                                <?php echo $__env->make('admin.customFields.formBuilder', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-end text-end">
                            <a class="btn btn-secondary btn-light btn-submit" href="<?php echo e(route('admin.tickets.index')); ?>"><?php echo e(__('Cancel')); ?></a>
                            <button class="btn btn-primary btn-submit ms-2" type="submit"><?php echo e(__('Submit')); ?></button>
                        </div>
                    </div>

              
                </div>
            </div>
            
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="//cdn.ckeditor.com/4.12.1/basic/ckeditor.js"></script>
    <script src="<?php echo e(asset('js/editorplaceholder.js')); ?>"></script>
    <script>
        $(document).ready(function () {
            $.each($('.ckdescription'), function (i, editor) {

                CKEDITOR.replace(editor, {
                    // contentsLangDirection: 'rtl',
                    extraPlugins: 'editorplaceholder',
                    editorplaceholder: editor.placeholder
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/fabuxrfq/support.fasttechnologies.pk/resources/views/admin/tickets/create.blade.php ENDPATH**/ ?>