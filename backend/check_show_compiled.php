<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Report Card - '.e($reportCard->student->full_name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Report Card - '.e($reportCard->student->full_name).'']); ?>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="<?php echo e(route('admin.report-cards.index')); ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7v18"/></svg>
                Back
            </a>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">Report Card</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                <?php echo e($reportCard->student->full_name); ?> &middot; <?php echo e($reportCard->student->admission_no); ?> &middot; <?php echo e($reportCard->student->schoolClass->name); ?>

            </p>
        </div>
        <div class="flex gap-2">
            <?php if($reportCard->canEdit() && $reportCard->isApproved()): ?>
                <form method="POST" action="<?php echo e(route('admin.report-cards.publish', $reportCard->id)); ?>" onsubmit="return confirm('Publish this report card?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium">Publish</button>
                </form>
            <?php endif; ?>
            <a href="<?php echo e(route('admin.report-cards.download', $reportCard->id)); ?>" class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium">Download PDF</a>
        </div>
    </div>

    <?php if(session('status')): ?>
        <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'success','class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','class' => 'mb-6']); ?>
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="flex-1 text-sm font-medium text-success-800 dark:text-success-300"><?php echo e(session('status')); ?></div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="p-6">
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 border-b border-neutral-200 dark:border-dark-border pb-6">
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Student Name</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white"><?php echo e($reportCard->student->full_name); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Admission No.</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white"><?php echo e($reportCard->student->admission_no); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Class</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white"><?php echo e($reportCard->student->schoolClass->name); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Term / Session</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white"><?php echo e($reportCard->term->name); ?></p>
                </div>
            </div>

            
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-3">Academic Results</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-dark-border">
                        <thead class="bg-neutral-50 dark:bg-dark-surface">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Subject</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">CA</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Exam</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Grade</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-dark-border bg-white dark:bg-dark-surface">
                            <?php $__empty_1 = true; $__currentLoopData = $reportCard->student->results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $gradeClass = match($result->grade) {
                                        'A1', 'B2', 'B3' => 'text-success-600 dark:text-success-400',
                                        'C4', 'C5', 'C6' => 'text-warning-600 dark:text-warning-400',
                                        default => 'text-danger-600 dark:text-danger-400',
                                    };
                                ?>
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-neutral-900 dark:text-white"><?php echo e($result->classSubject->subject->name); ?></td>
                                    <td class="px-4 py-3 text-center text-sm text-neutral-600 dark:text-neutral-400"><?php echo e($result->ca_score ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-sm text-neutral-600 dark:text-neutral-400"><?php echo e($result->exam_score ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-sm font-medium text-neutral-900 dark:text-white"><?php echo e($result->total ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center text-sm font-semibold <?php echo e($gradeClass); ?>">
                                        <?php echo e($result->grade ?? '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400"><?php echo e($result->remark ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No results recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Class Teacher Remark</p>
                    <p class="text-sm text-neutral-900 dark:text-white"><?php echo e($reportCard->class_teacher_remark ?? 'No remarks.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Affective Domain</p>
                    <p class="text-sm text-neutral-900 dark:text-white"><?php echo e($reportCard->affective_domain ?? 'Not specified.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Psychomotor Assessment</p>
                    <p class="text-sm text-neutral-900 dark:text-white"><?php echo e($reportCard->psychomotor_assessment ?? 'Not specified.'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1">Health Remarks</p>
                    <p class="text-sm text-neutral-900 dark:text-white"><?php echo e($reportCard->health_remarks ?? 'Not specified.'); ?></p>
                </div>
            </div>

            
            <div class="border-t border-neutral-200 dark:border-dark-border pt-6">
                <h3 class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-4">Principal Review</h3>

                <form method="POST" action="<?php echo e(route('admin.report-cards.approve', $reportCard->id)); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    
                    <div>
                        <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Principal Remark</label>
                        <textarea name="principal_remark" rows="3" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"><?php echo e(old('principal_remark', $reportCard->principal_remark)); ?></textarea>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Promotion Decision</label>
                            <select name="promotion_decision" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <option value="" <?php echo e(old('promotion_decision', $reportCard->promotion_decision) === '' ? 'selected' : ''); ?>>Select decision</option>
                                <option value="promoted" <?php echo e(old('promotion_decision', $reportCard->promotion_decision) === 'promoted' ? 'selected' : ''); ?>>Promoted</option>
                                <option value="repeated" <?php echo e(old('promotion_decision', $reportCard->promotion_decision) === 'repeated' ? 'selected' : ''); ?>>Repeated</option>
                                <option value="transferred" <?php echo e(old('promotion_decision', $reportCard->promotion_decision) === 'transferred' ? 'selected' : ''); ?>>Transferred</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Position in Class</label>
                            <input type="number" name="position_in_class" min="1" value="<?php echo e(old('position_in_class', $reportCard->position_in_class)); ?>" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Total Students in Class</label>
                            <input type="number" name="total_students_in_class" min="1" value="<?php echo e(old('total_students_in_class', $reportCard->total_students_in_class)); ?>" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Next Term Begins</label>
                            <input type="date" name="next_term_begins" value="<?php echo e(old('next_term_begins', $reportCard->next_term_begins ?? now()->format('Y-m-d'))); ?>" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-neutral-200 dark:border-dark-border">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Review Date</label>
                            <input type="date" name="review_date" value="<?php echo e(old('review_date', now()->format('Y-m-d'))); ?>" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Principal Signature</label>
                            <input type="text" name="principal_signature" placeholder="e.g. John Doe" value="<?php echo e(old('principal_signature')); ?>" class="w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Save Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>