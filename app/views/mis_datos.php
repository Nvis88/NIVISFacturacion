<?php require __DIR__ . '/layout/header.php'; ?>
<?php require __DIR__ . '/layout/navbar.php'; ?>

<?php
$usuario = $_SESSION['usuario'] ?? null;
?>

<div class="container py-4">
    <h3 class="mb-4">Mis Datos</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">CUIT</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['CUIT']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Apellidos</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Apellidos']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombres</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Nombres']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Razon Social</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['RazonSocial']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['Email']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Régimen</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Regimen']) ?>" readonly>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>