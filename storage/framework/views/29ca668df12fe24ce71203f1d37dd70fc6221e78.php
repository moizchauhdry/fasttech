<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Tickets')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Tickets')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('multiple-action-button'); ?>
    <div class="row justify-content-end">
        <div class="col-md-4">                
            <select class="form-select" id="projects" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                <option value="<?php echo e(route('admin.tickets.index')); ?>"><?php echo e(__('All Tickets')); ?></option>
                <option value="<?php echo e(route('admin.tickets.index', 'in-progress')); ?>" <?php if($status == 'in-progress'): ?> selected <?php endif; ?>><?php echo e(__('In Progress')); ?></option>
                <option value="<?php echo e(route('admin.tickets.index', 'on-hold')); ?>" <?php if($status == 'on-hold'): ?> selected <?php endif; ?>><?php echo e(__('On Hold')); ?></option>
                <option value="<?php echo e(route('admin.tickets.index', 'closed')); ?>" <?php if($status == 'closed'): ?> selected <?php endif; ?>><?php echo e(__('Closed')); ?></option>
            </select>
        </div>
        <div class="col-auto">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create-tickets')): ?>
                <div class="btn btn-sm btn-primary btn-icon m-1 float-end ms-2"  data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(__('Create Ticket')); ?>">
                    <a href="<?php echo e(route('admin.tickets.create')); ?>" class=""><i class="ti ti-plus text-white"></i></a>
                </div>
            <?php endif; ?>
            <div class="btn btn-sm btn-primary btn-icon m-1 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e(__('Export Tickets CSV file')); ?>">
                <a href="<?php echo e(route('tickets.export')); ?>" class="" ><i class="ti ti-file-export text-white"></i></a>            
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <?php if(session()->has('ticket_id') || session()->has('smtp_error')): ?>
                <div class="alert alert-info">
                    <?php if(session()->has('ticket_id')): ?>
                        <?php echo Session::get('ticket_id'); ?>

                        <?php echo e(Session::forget('ticket_id')); ?>

                    <?php endif; ?>
                    <?php if(session()->has('smtp_error')): ?>
                        <?php echo Session::get('smtp_error'); ?>

                        <?php echo e(Session::forget('smtp_error')); ?>

                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table id="pc-dt-simple" class="table">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th><?php echo e(__('Ticket ID')); ?></th>
                                <th class="w-10"><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Email')); ?></th>
                                <th><?php echo e(__('Subject')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Created')); ?></th>
                                <th class="text-end me-3"><?php echo e(__('Action')); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <th scope="row"><?php echo e(++$index); ?></th>
                                    <td class="Id sorting_1">
                                        <a class="btn btn-outline-primary" href="<?php echo e(route('admin.tickets.edit',$ticket->id)); ?>">
                                            <?php echo e($ticket->ticket_id); ?>

                                        </a>
                                    </td>
                                    <td><span class="white-space"><?php echo e($ticket->name); ?></span></td>
                                    <td><?php echo e($ticket->email); ?></td>
                                    <td><span class="white-space"><?php echo e($ticket->subject); ?></span></td>
                                    <td><span class="badge badge-white p-2 px-3 rounded fix_badge" style="background: <?php echo e($ticket->color); ?>;"><?php echo e($ticket->category_name); ?></span></td>
                                    <td><span class="badge fix_badge <?php if($ticket->status == 'In Progress'): ?>bg-warning <?php elseif($ticket->status == 'On Hold'): ?> bg-danger <?php else: ?> bg-success <?php endif; ?>  p-2 px-3 rounded"><?php echo e(__($ticket->status)); ?></span></td>
                                    <td><?php echo e($ticket->created_at->diffForHumans()); ?></td>
                                    <td class="text-end">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reply-tickets')): ?>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="<?php echo e(route('admin.tickets.edit', $ticket->id)); ?>" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('Reply')); ?>"> <span class="text-white"> <i class="ti ti-corner-up-left"></i></span></a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete-tickets')): ?>
                                            <div class="action-btn bg-danger ms-2">
                                                <form method="POST" action="<?php echo e(route('admin.tickets.destroy',$ticket->id)); ?>" id="user-form-<?php echo e($ticket->id); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-toggle="tooltip"
                                                    title='Delete'>
                                                        <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pbbarcou/support.pbbarcouncil.com/resources/views/admin/tickets/index.blade.php ENDPATH**/ ?>