<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body dash_card_height">
                                    <div class="theme-avtar bg-primary">
                                        <i class="fas fa-list-alt"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2"><?php echo e(__('Total')); ?></p>
                                    <h6 class="mb-3"><?php echo e(__('Categories')); ?></h6>
                                    <h3 class="mb-0"><?php echo e($categories); ?></h3>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body dash_card_height">
                                    <div class="theme-avtar bg-info">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2"><?php echo e(__('Open')); ?></p>
                                    <h6 class="mb-3"><?php echo e(__('Tickets')); ?></h6>
                                    <h3 class="mb-0"><?php echo e($open_ticket); ?> </h3>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body dash_card_height">
                                    <div class="theme-avtar bg-warning">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2"><?php echo e(__('Closed')); ?></p>
                                    <h6 class="mb-3"><?php echo e(__('Tickets')); ?></h6>
                                    <h3 class="mb-0"><?php echo e($close_ticket); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card">
                                <div class="card-body dash_card_height">
                                    <div class="theme-avtar bg-danger">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <p class="text-muted text-sm mt-4 mb-2"><?php echo e(__('Total')); ?></p>
                                    <h6 class="mb-3"><?php echo e(__('Agents')); ?></h6>
                                    <h3 class="mb-0"><?php echo e($agents); ?></h3>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-5">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('Tickets by Category')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <div id="categoryPie"></div>
                                </div>
                               
                                
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo e(__('This Year Tickets')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="chartBar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>

    <script>
        (function() {
            var chartBarOptions = {
                series: [{
                    name: '<?php echo e(__("Tickets")); ?>',
                    // data: [40, 20, 60, 15, 50, 65, 20, 40, 20, 60, 15, 50]
                    data: <?php echo json_encode(array_values($monthData)); ?>

                }, ],

                chart: {
                    height: 150,
                    type: 'area',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: <?php echo json_encode(array_keys($monthData)); ?>,
                    title: {
                        text: '<?php echo e(__('Months')); ?>'
                    }
                },
                colors: ['#ffa21d', '#FF3A6E'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                markers: {
                    size: 4,
                    colors: ['#ffa21d', '#FF3A6E'],
                    opacity: 0.9,
                    strokeWidth: 2,
                    hover: {
                        size: 7,
                    }
                },
                yaxis: {
                    title: {
                        text: '<?php echo e(__('Tickets')); ?>'
                    },
                    tickAmount: 3,
                    min: 10,
                    max: 70,
                }
            };
            var arChart = new ApexCharts(document.querySelector("#chartBar"), chartBarOptions);
            arChart.render();
        })();
        (function() {
            var categoryPieOptions = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: <?php echo json_encode($chartData['value']); ?>,
                colors: <?php echo json_encode($chartData['color']); ?>,
                labels: <?php echo json_encode($chartData['name']); ?>,
                legend: {
                    show: true
                }
            };
            var categoryPieChart = new ApexCharts(document.querySelector("#categoryPie"), categoryPieOptions);
            categoryPieChart.render();
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\fasttech\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>