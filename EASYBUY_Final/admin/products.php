<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/brand_controller.php';
require_once __DIR__ . '/../controllers/category_controller.php';

if (!isLoggedIn() || !check_user_role(1)) {
    header('Location: ../login/login.php'); exit;
}

$user_id = get_user_id();
$myProducts = get_products_by_user_ctr($user_id);
$brands = get_all_brands_ctr();      
$categories = get_all_categories_ctr();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Products - EasyBuy</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="../css/styles.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding-bottom: 50px;
    }
    .main-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      padding: 30px;
      margin-top: 30px;
    }
    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .page-header h2 {
      margin: 0;
      font-weight: 600;
    }
    .product-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: none;
      border-radius: 15px;
      overflow: hidden;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .product-card img {
      transition: transform 0.3s ease;
    }
    .product-card:hover img {
      transform: scale(1.05);
    }
    .btn-add {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 12px 30px;
      border-radius: 25px;
      font-weight: 600;
      transition: transform 0.2s ease;
    }
    .btn-add:hover {
      transform: scale(1.05);
    }
    .form-card {
      border-radius: 15px;
      border: 2px dashed #667eea;
      background: #f8f9ff;
      animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .modal-content {
      border-radius: 15px;
      border: none;
    }
    .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px 15px 0 0;
    }
    .modal-header .btn-close {
      filter: invert(1);
    }
    .price-tag {
      color: #667eea;
      font-size: 1.5rem;
      font-weight: 700;
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: #667eea;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="main-container">
      <div class="page-header">
        <h2><i class="fas fa-box me-2"></i>My Products</h2>
        <a href="../view/all_product.php" class="btn btn-light"><i class="fas fa-eye me-2"></i>View All Products</a>
      </div>

      <?php if (empty($myProducts)): ?>
        <div class="empty-state">
          <i class="fas fa-box-open"></i>
          <h4>You have no products yet</h4>
          <p>Use the form below to create your first product and start selling!</p>
        </div>
      <?php else: ?>
        <div class="mb-4">
          <button type="button" class="btn btn-add text-white" onclick="addProductForm()">
            <i class="fas fa-plus me-2"></i>Add New Product
          </button>
        </div>

        <div class="row g-4">
          <?php foreach($myProducts as $p): ?>
            <div class="col-md-4 col-lg-3">
              <div class="card product-card h-100 shadow-sm">
                <div style="position: relative; overflow: hidden;">
                  <img src="../uploads/products/<?php echo htmlspecialchars($p['product_image']); ?>" 
                       class="card-img-top" 
                       style="height:220px;object-fit:cover;"
                       onerror="this.src='https://via.placeholder.com/300x220?text=No+Image'">
                </div>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title mb-2" style="font-size: 1.1rem; font-weight: 600;">
                    <?php echo htmlspecialchars($p['product_name']); ?>
                  </h5>
                  <div class="mb-2">
                    <span class="badge bg-secondary me-1"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($p['brand_name'] ?? 'N/A'); ?></span>
                    <span class="badge bg-info"><i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($p['cat_name'] ?? 'N/A'); ?></span>
                  </div>
                  <p class="text-muted small mb-2">
                    <i class="fas fa-shopping-cart me-1"></i>MOQ: <?php echo (int)$p['moq']; ?> units
                  </p>
                  <p class="price-tag mb-3">GH₵ <?php echo number_format($p['wholesale_price'],2); ?></p>
                  <div class="mt-auto d-flex gap-2">
                    <button class="btn btn-sm btn-warning flex-fill" 
                            onclick="loadProductForEdit(<?php echo $p['product_id']; ?>, '<?php echo addslashes($p['product_name']); ?>', <?php echo $p['product_brand']; ?>, <?php echo $p['product_cat']; ?>, <?php echo $p['moq']; ?>, <?php echo $p['wholesale_price']; ?>, '<?php echo htmlspecialchars($p['product_image']); ?>')">
                      <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <button class="btn btn-sm btn-danger flex-fill" 
                            onclick="confirmDelete(<?php echo $p['product_id']; ?>)">
                      <i class="fas fa-trash me-1"></i>Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <div id="formArea" style="display:<?php echo empty($myProducts) ? 'block' : 'none'; ?>;">
      <div class="card form-card mt-4">
        <div class="card-body p-4">
          <h5 class="card-title mb-4"><i class="fas fa-plus-circle me-2"></i>Add New Product</h5>
          <form id="productForm" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label fw-bold">Product Name</label>
              <input name="product_name" class="form-control form-control-lg" placeholder="Enter product name" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Brand</label>
                <select name="product_brand" class="form-select form-select-lg" required>
                  <option value="">Select brand</option>
                  <?php foreach($brands as $b): ?>
                    <option value="<?php echo $b['brand_id']; ?>"><?php echo htmlspecialchars($b['brand_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Category</label>
                <select name="product_cat" class="form-select form-select-lg" required>
                  <option value="">Select category</option>
                  <?php foreach($categories as $c): ?>
                    <option value="<?php echo $c['cat_id']; ?>"><?php echo htmlspecialchars($c['cat_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">MOQ (Minimum Order Quantity)</label>
                <input name="moq" type="number" class="form-control form-control-lg" min="1" placeholder="e.g. 10" required>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Wholesale Price (GH₵)</label>
                <input name="wholesale_price" type="number" step="0.01" class="form-control form-control-lg" placeholder="0.00" required>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Product Image</label>
                <input name="product_image" type="file" class="form-control form-control-lg" accept="image/*" required>
                <small class="text-muted">Accepted: JPG, PNG, GIF</small>
              </div>
            </div>
            <div class="d-flex gap-2 mt-4">
              <button type="button" onclick="submitProductForm()" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Save Product
              </button>
              <button type="button" onclick="addProductForm()" class="btn btn-secondary btn-lg">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editProductModalLabel"><i class="fas fa-edit me-2"></i>Edit Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="editProductForm" enctype="multipart/form-data">
            <div class="modal-body">
              <input type="hidden" id="edit_product_id" name="product_id">
              
              <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" id="edit_product_name" name="product_name" class="form-control" required>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Brand</label>
                  <select id="edit_product_brand" name="product_brand" class="form-select" required>
                    <option value="">Select brand</option>
                    <?php foreach($brands as $b): ?>
                      <option value="<?php echo $b['brand_id']; ?>"><?php echo htmlspecialchars($b['brand_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Category</label>
                  <select id="edit_product_cat" name="product_cat" class="form-select" required>
                    <option value="">Select category</option>
                    <?php foreach($categories as $c): ?>
                      <option value="<?php echo $c['cat_id']; ?>"><?php echo htmlspecialchars($c['cat_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">MOQ</label>
                  <input type="number" id="edit_moq" name="moq" class="form-control" min="1" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Wholesale Price</label>
                  <input type="number" id="edit_wholesale_price" name="wholesale_price" step="0.01" class="form-control" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Product Image (Leave empty to keep current image)</label>
                <input type="file" name="product_image" class="form-control" accept="image/*">
                <div id="currentImage" class="mt-2"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/product.js"></script>
  <script>
    // Handle edit form submission
    document.getElementById('editProductForm')?.addEventListener('submit', function(e) {
      e.preventDefault();
      submitEditForm();
    });
  </script>
</body>
</html>
