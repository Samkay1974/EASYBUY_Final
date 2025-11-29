# Deployment Notes - Image Uploads

## Server Upload Configuration

When deploying to the server, image uploads are handled automatically through the `settings/upload_config.php` helper.

### How It Works

The system uses **direct file uploads** to the server's existing `uploads/` directory structure. This works the same way on both localhost and the server.

1. **File Upload Process**:
   - Files are validated (type, size, etc.)
   - Unique filenames are generated (timestamp + random hash)
   - Files are saved directly to `uploads/products/` directory
   - The filename is stored in the database

2. **Directory Structure**:
   - Main uploads directory: `uploads/`
   - Product images: `uploads/products/`
   - The system automatically creates the `products` subdirectory if it doesn't exist

### Configuration

The upload directories are configured in `settings/upload_config.php`:
```php
define('UPLOADS_DIR', 'uploads/');
define('PRODUCTS_UPLOAD_DIR', 'uploads/products/');
```

### Testing

After deployment, test image uploads by:
1. Adding a new product with an image
2. Editing an existing product with a new image
3. Verify images appear correctly in the product listings

### Troubleshooting

If uploads fail:
1. **Check directory permissions**: The `uploads/` directory and `uploads/products/` subdirectory must be writable by the web server
2. **Check directory existence**: Ensure the `uploads/` directory exists in the project root
3. **Check server error logs**: Look for detailed error messages about file uploads
4. **Verify file types**: Only JPG, JPEG, PNG, GIF, and WEBP files are allowed
5. **Check PHP upload settings**: Verify `upload_max_filesize` and `post_max_size` in php.ini are sufficient

