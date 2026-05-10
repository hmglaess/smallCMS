<?php
/**
 * @var string $title
 * @var string $content
 * @var string $siteName
 * @var App\Services\NavigationService $navigationService
 */
?>
<nav>
    <div class="logo">💃 discofoxberlin.dance</div>
    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <ul class="menu" id="main-navigation">
        <!-- Navigation wird dynamisch geladen -->
        <?php echo $navigationService->generateNavigationHtml('main_menu'); ?>
    </ul>
</nav>