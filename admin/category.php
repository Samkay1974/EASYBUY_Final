<?php
require_once __DIR__ . '/../settings/core.php';

// To check if logged in
if (!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit;
}

// Check if admin
if (!isAdmin()) {
    header("Location: ../login/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
</head>
<body class="p-5">
    <div class="container">
        <?php if (isLoggedIn()): ?>
        <h2 class="mb-4"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>'s Category Manager</h2>
        <a style="position: absolute; top: 20px; right: 20px;" href="../view/homepage.php" class="btn btn-outline-secondary">Back</a>
				<?php endif; ?>

        <!-- This section is to create the category and puts into the table -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="create-category-form">
                    <div class="mb-3">
                        <label for="cat_name" class="form-label">Category Name</label>
                        <input type="text" id="cat_name" name="cat_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Category</button>
                </form>
            </div>
        </div>

        <!-- This section retrieves and displays the categories created by the user-->
        <div class="card">
            <div class="card-body">
                <h5>Your Categories</h5>
                <table class="table table-bordered" id="categories-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                    <!-- Filled up by what user enters -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Update-->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form id="edit-category-form" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="edit_category_id" name="category_id">
            <div class="mb-3">
              <label for="edit_category_name" class="form-label">Category Name</label>
              <input type="text" id="edit_category_name" name="cat_name" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/category.js?v=<?php echo time(); ?>"></script>

</body>
</html>
