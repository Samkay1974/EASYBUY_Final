<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/brand_controller.php';
require_once __DIR__ . '/../controllers/category_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

// Get all products, brands, and categories for search
$allProducts = get_all_products_ctr();
$allBrands = get_all_brands_ctr();
$allCategories = get_all_categories_ctr();

// Get search parameters
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$brand_filter = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Filter products
$filteredProducts = $allProducts;

if (!empty($search_term) || $brand_filter > 0 || $category_filter > 0) {
    $filteredProducts = [];
    foreach ($allProducts as $product) {
        $match = true;
        
        // Search by name, brand, or category
        if (!empty($search_term)) {
            $search_lower = strtolower($search_term);
            $name_match = strpos(strtolower($product['product_name']), $search_lower) !== false;
            $brand_match = isset($product['brand_name']) && strpos(strtolower($product['brand_name']), $search_lower) !== false;
            $cat_match = isset($product['cat_name']) && strpos(strtolower($product['cat_name']), $search_lower) !== false;
            
            if (!$name_match && !$brand_match && !$cat_match) {
                $match = false;
            }
        }
        
        // Filter by brand
        if ($brand_filter > 0 && $product['product_brand'] != $brand_filter) {
            $match = false;
        }
        
        // Filter by category
        if ($category_filter > 0 && $product['product_cat'] != $category_filter) {
            $match = false;
        }
        
        if ($match) {
            $filteredProducts[] = $product;
        }
    }
}

$cartCount = isLoggedIn() ? get_cart_count_ctr() : 0;
$cartItemCount = isLoggedIn() ? get_cart_item_count_ctr() : 0;

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Products - EasyBuy</title>
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
      height: 100%;
    }
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .product-card img {
      transition: transform 0.3s ease;
    }
    .product-card:hover img {
      transform: scale(1.1);
    }
    .price-tag {
      color: #667eea;
      font-size: 1.5rem;
      font-weight: 700;
    }
    .badge-custom {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
    }
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #6c757d;
    }
    .empty-state i {
      font-size: 5rem;
      margin-bottom: 20px;
      color: #667eea;
      opacity: 0.5;
    }
    .btn-view {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      transition: transform 0.2s ease;
    }
    .btn-view:hover {
      transform: scale(1.05);
      color: white;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="main-container">
      <div class="page-header">
        <div>
          <h2><i class="fas fa-shopping-bag me-2"></i>All Products</h2>
          <small class="d-block mt-1 opacity-75">
            <?php echo count($filteredProducts); ?> of <?php echo count($allProducts); ?> product(s)
          </small>
        </div>
        <div>
          <?php if (isLoggedIn()): ?>
            <a href="../view/homepage.php" class="btn btn-light me-2"><i class="fas fa-home me-2"></i>Home</a>
            <?php 
            // Check for pending collaboration orders
            $has_pending_collab_payment = false;
            if (isLoggedIn()) {
                require_once __DIR__ . '/../controllers/order_controller.php';
                require_once __DIR__ . '/../controllers/collaboration_controller.php';
                $user_id = get_user_id();
                $collaborations = get_user_collaborations_ctr($user_id);
                foreach ($collaborations as $collab) {
                    $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
                    if ($collab_order && $collab_order['payment_status'] == 'pending') {
                        $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $user_id);
                        if (!$member_payment || $member_payment['payment_status'] != 'paid') {
                            $has_pending_collab_payment = true;
                            break;
                        }
                    }
                }
            }
            $totalBadgeCount = $cartItemCount + ($has_pending_collab_payment ? 1 : 0);
            ?>
            <a href="cart.php" class="btn btn-light me-2 position-relative">
              <i class="fas fa-shopping-cart me-2"></i>Cart
              <?php if ($totalBadgeCount > 0): ?> 
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge">
                  <?php echo $totalBadgeCount; ?> 
                </span>
              <?php endif; ?>
            </a>
            <?php if (check_user_role(1)): ?>
              <a href="../admin/products.php" class="btn btn-light"><i class="fas fa-box me-2"></i>My Products</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="../index.php" class="btn btn-light"><i class="fas fa-home me-2"></i>Home</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
              <label class="form-label"><i class="fas fa-search me-2"></i>Search</label>
              <input type="text" name="search" class="form-control" 
                     placeholder="Search by name, brand, or category..." 
                     value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label"><i class="fas fa-tag me-2"></i>Brand</label>
              <select name="brand" class="form-select">
                <option value="0">All Brands</option>
                <?php foreach ($allBrands as $brand): ?>
                  <option value="<?php echo $brand['brand_id']; ?>" 
                          <?php echo $brand_filter == $brand['brand_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label"><i class="fas fa-folder me-2"></i>Category</label>
              <select name="category" class="form-select">
                <option value="0">All Categories</option>
                <?php foreach ($allCategories as $category): ?>
                  <option value="<?php echo $category['cat_id']; ?>" 
                          <?php echo $category_filter == $category['cat_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['cat_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-2"></i>Filter
              </button>
            </div>
          </form>
          <?php if (!empty($search_term) || $brand_filter > 0 || $category_filter > 0): ?>
            <div class="mt-3">
              <a href="all_product.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-2"></i>Clear Filters
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if (empty($filteredProducts)): ?>
        <div class="empty-state">
          <i class="fas fa-box-open"></i>
          <h4>No Products Found</h4>
          <p><?php echo !empty($search_term) || $brand_filter > 0 || $category_filter > 0 ? 'Try adjusting your search or filters.' : 'Check back later for new products!'; ?></p>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach($filteredProducts as $p): ?>
            <div class="col-6 col-md-4 col-lg-3">
              <div class="card product-card shadow-sm">
                <div style="position: relative; overflow: hidden; height: 220px;">
                  <img src="../uploads/<?php echo htmlspecialchars($p['product_image']); ?>" 
                       class="card-img-top w-100 h-100" 
                       style="object-fit:cover;"
                       onerror="this.src='https://via.placeholder.com/300x220?text=No+Image'">
                </div>
                <div class="card-body d-flex flex-column">
                  <h6 class="card-title mb-2" style="font-size: 1.1rem; font-weight: 600; min-height: 2.5rem;">
                    <?php echo htmlspecialchars($p['product_name']); ?>
                  </h6>
                  <div class="mb-2">
                    <span class="badge bg-secondary me-1">
                      <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($p['brand_name'] ?? 'N/A'); ?>
                    </span>
                    <span class="badge bg-info">
                      <i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($p['cat_name'] ?? 'N/A'); ?>
                    </span>
                  </div>
                  <p class="text-muted small mb-2">
                    <i class="fas fa-shopping-cart me-1"></i>MOQ: <?php echo (int)$p['moq']; ?> units
                  </p>
                  <?php if (isset($p['wholesaler_name'])): ?>
                    <p class="text-muted small mb-2">
                      <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($p['wholesaler_name']); ?>
                  
                    </p>
                  <?php endif; ?>
                  <p class="price-tag mb-3">GH₵ <?php echo number_format($p['wholesale_price'],2); ?></p>
                  <div class="mt-auto">
                    <?php if (isLoggedIn()): ?>
                      <div class="mb-2">
                        <label class="form-label small">MOQ Units:</label>
                        <input type="number" class="form-control form-control-sm" 
                               id="qty_<?php echo $p['product_id']; ?>" 
                               value="1" min="1">
                        <small class="text-muted">1 unit = <?php echo $p['moq']; ?> items</small>
                      </div>
                      <button class="btn btn-success w-100 mb-2" 
                              onclick="addToCart(<?php echo $p['product_id']; ?>, <?php echo $p['moq']; ?>)">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                      </button>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                      <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="btn btn-view flex-fill">
                        <i class="fas fa-eye me-2"></i>View Details
                      </a>
                      <?php if (isLoggedIn() && check_user_role(0)): ?>
                        <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline-primary flex-fill">
                          <i class="fas fa-users me-2"></i>Collaborate
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    async function addToCart(productId, moq) {
      const qtyInput = document.getElementById('qty_' + productId);
      const moqQuantity = parseInt(qtyInput.value) || 1; // This is MOQ units, not individual items
      
      if (moqQuantity < 1) {
        Swal.fire('Error!', 'Quantity must be at least 1 MOQ unit', 'error');
        return;
      }
      
      const actualUnits = moqQuantity * moq;
      
      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('quantity', moqQuantity); // Store MOQ quantity, not actual units
      
      try {
        const res = await fetch('../actions/add_to_cart.php', {
          method: 'POST',
          body: formData
        });
        const json = await res.json();
        
        if (json.status === 'success') {
          Swal.fire('Success!', json.message + ' (' + actualUnits + ' items added)', 'success');
          updateCartBadge(json.cart_count);
        } else {
          Swal.fire('Error!', json.message, 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
      }
    }
    
    function updateCartBadge(count) {
      let badge = document.getElementById('cartBadge');
      if (count > 0) {
        if (!badge) {
          const cartLink = document.querySelector('a[href="cart.php"]');
          if (cartLink) {
            badge = document.createElement('span');
            badge.id = 'cartBadge';
            badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
            cartLink.appendChild(badge);
          }
        }
        if (badge) {
          badge.textContent = count;
        }
      } else {
        if (badge) {
          badge.remove();
        }
      }
    }
    
    // Update cart badge on page load
    <?php if (isLoggedIn()): ?>
    fetch('../actions/get_cart_count.php')
      .then(res => res.json())
      .then(data => updateCartBadge(data.cart_count));
    <?php endif; ?>
  </script>
</body>
</html>
