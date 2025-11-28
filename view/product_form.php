<form id="addProductForm" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="product_name" required>

    <label>Category</label>
    <select name="product_cat" required>
        <option value="">Select Category</option>
        <?php foreach ($cats as $c): ?>
            <option value="<?= $c['cat_id'] ?>"><?= $c['cat_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <label>Brand</label>
    <select name="product_brand" required>
        <option value="">Select Brand</option>
        <?php foreach ($brands as $b): ?>
            <option value="<?= $b['brand_id'] ?>"><?= $b['brand_name'] ?></option>
        <?php endforeach; ?>
    </select>

    <label>MOQ</label>
    <input type="number" name="moq" required>

    <label>Wholesale Price</label>
    <input type="number" name="wholesale_price" required>

    <label>Product Image</label>
    <input type="file" name="product_image" required>

    <button type="submit">Add Product</button>
</form>
