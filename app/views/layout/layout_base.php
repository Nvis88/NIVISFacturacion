<?php require __DIR__ . '/header.php'; ?>
<?php require __DIR__ . '/navbar.php'; ?>

<div class="d-flex">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
        <?= $contenido ?>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>