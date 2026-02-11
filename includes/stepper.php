<?php

/**
 * Renders a 5-step progress stepper for the AAR registration process
 * @param int $currentStep 1-5 representing the current step
 */
function render_stepper($currentStep)
{
    $steps = [
        1 => 'Part 1',
        2 => 'Safe From Harm',
        3 => 'Payment Guide',
        4 => 'Upload Receipt',
        5 => 'Finish'
    ];
    $stepCount = count($steps);
    $progressPercent = $stepCount > 1
        ? max(0, min(100, (($currentStep - 1) / ($stepCount - 1)) * 100))
        : 0;
?>
    <!-- Progress Stepper -->
    <div class="mb-8">
        <div class="relative max-w-4xl mx-auto px-4">
            <div class="absolute left-6 right-6 top-6 h-1 bg-gray-200 rounded">
                <div class="h-1 bg-green-600 rounded" style="width: <?= $progressPercent ?>%;"></div>
            </div>
            <div class="flex items-start justify-between">
                <?php foreach ($steps as $num => $label): ?>
                    <?php
                    $isCompleted = $num < $currentStep;
                    $isCurrent = $num === $currentStep;
                    $circleClass = $isCompleted ? 'bg-green-600 text-white' : ($isCurrent ? 'bg-green-600 text-white ring-4 ring-green-100' : 'bg-gray-200 text-gray-500');
                    $labelClass = $isCurrent ? 'text-green-700 font-semibold' : ($isCompleted ? 'text-gray-700' : 'text-gray-500');
                    ?>
                    <!-- Step Item -->
                    <div class="flex flex-col items-center">
                        <div class="<?= $circleClass ?> w-12 h-12 rounded-full flex items-center justify-center font-semibold text-lg transition-all duration-300 relative z-10">
                            <?php if ($isCompleted): ?>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <?= $num ?>
                            <?php endif; ?>
                        </div>

                        <!-- Label -->
                        <span class="text-sm mt-3 text-center <?= $labelClass ?> transition-colors duration-300">
                            <?= $label ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php
}
?>