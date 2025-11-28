// Toggle add product form visibility
function addProductForm() {
    const formArea = document.getElementById('formArea');
    if (formArea) {
        const isHidden = formArea.style.display === 'none' || formArea.style.display === '';
        formArea.style.display = isHidden ? 'block' : 'none';
        // Scroll to form when showing
        if (isHidden) {
            setTimeout(() => {
                formArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
}

// Submit new product form
async function submitProductForm() {
    const form = document.getElementById('productForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="button"]');
    const originalText = submitBtn ? submitBtn.textContent : 'Save Product';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }

    try {
        const res = await fetch('../actions/add_product_action.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        
        if (json.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Success!', json.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                alert(json.message);
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error!', json.message, 'error');
            } else {
                alert(json.message);
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
        } else {
            alert('An error occurred. Please try again.');
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
}

// Load product data into edit modal
function loadProductForEdit(productId, productName, brandId, catId, moq, price, image) {
    // Escape product name for use in JavaScript
    const escapedName = productName.replace(/'/g, "\\'").replace(/"/g, '\\"');
    
    document.getElementById('edit_product_id').value = productId;
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_product_brand').value = brandId;
    document.getElementById('edit_product_cat').value = catId;
    document.getElementById('edit_moq').value = moq;
    document.getElementById('edit_wholesale_price').value = price;
    
    // Show current image if exists
    const currentImageDiv = document.getElementById('currentImage');
    if (currentImageDiv) {
        if (image) {
            currentImageDiv.innerHTML = `<p class="text-muted small">Current image: <img src="../uploads/products/${image}" style="max-width:100px;max-height:100px;object-fit:cover;border-radius:8px;" class="mt-2" onerror="this.style.display='none'"></p>`;
        } else {
            currentImageDiv.innerHTML = '';
        }
    }
    
    // Reset file input
    const fileInput = document.querySelector('#editProductForm input[type="file"]');
    if (fileInput) {
        fileInput.value = '';
    }
    
    // Show modal
    const editModalEl = document.getElementById('editProductModal');
    if (editModalEl) {
        const editModal = new bootstrap.Modal(editModalEl);
        editModal.show();
    }
}

// Submit edit product form
async function submitEditForm() {
    const form = document.getElementById('editProductForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : 'Update Product';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
    }

    try {
        const res = await fetch('../actions/edit_product_action.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        
        if (json.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Success!', json.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                alert(json.message);
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error!', json.message, 'error');
            } else {
                alert(json.message);
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
        } else {
            alert('An error occurred. Please try again.');
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
}

// Delete product with confirmation
async function confirmDelete(productId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('product_id', productId);

                try {
                    const res = await fetch('../actions/delete_product_action.php', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    
                    if (json.status === 'success') {
                        Swal.fire('Deleted!', json.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', json.message, 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
                }
            }
        });
    } else {
        if (confirm('Are you sure you want to delete this product?')) {
            const formData = new FormData();
            formData.append('product_id', productId);

            const res = await fetch('../actions/delete_product_action.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();
            alert(json.message);
            if (json.status === 'success') window.location.reload();
        }
    }
}
