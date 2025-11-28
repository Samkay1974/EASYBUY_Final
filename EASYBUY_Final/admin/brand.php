<?php
require_once __DIR__ . '/../settings/core.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manage Brands</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a style="position: absolute; top: 20px; right: 20px;" href="../view/homepage.php" class="btn btn-outline-secondary">Back</a>
    </div>
    <h2 class="mb-4"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>'s Brand Manager</h2>


    <div class="card p-3 mb-4 shadow-sm">
        <form id="create-brand-form">
            <div class="mb-3">
                <label class="form-label">Brand Name</label>
                <input type="text" id="brand_name" name="brand_name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Add Brand</button>
        </form>
    </div>

    <div class="card p-3 shadow-sm">
        <h5>Your Brands</h5>
        <table class="table table-striped" id="brands-table">
            <thead><tr><th>ID</th><th>Brand Name</th><th>Actions</th></tr></thead>
            <tbody id="brands-body"></tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="edit-brand-form" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Brand</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_brand_id" name="brand_id">
        <div class="mb-3">
          <label class="form-label">Brand Name</label>
          <input id="edit_brand_name" name="brand_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/brand.js"></script>
</body>
</html>
