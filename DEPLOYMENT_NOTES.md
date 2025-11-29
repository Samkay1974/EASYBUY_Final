# Deployment Notes - Image Uploads

## Server Upload Configuration

When deploying to the server, image uploads are handled automatically through the `settings/upload_config.php` helper.

### How It Works

1. **On Localhost**: Images are uploaded directly to the `uploads/` directory (works as before)

2. **On Server**: 
   - First attempts to upload via the server's upload endpoint at: `http://169.239.251.102:442/~samuel.ninson/upload.php`
   - If that fails, falls back to direct upload (if directory has write permissions)
   - If both fail, returns a clear error message

### Configuration

The upload endpoint URL is configured in `settings/upload_config.php`:
```php
define('SERVER_UPLOAD_URL', 'http://169.239.251.102:442/~samuel.ninson/upload.php');
```

If you need to change this URL, update it in `settings/upload_config.php`.

### Manual Upload Fallback

If automatic uploads fail on the server, you can:
1. Upload images manually via: http://169.239.251.102:442/~samuel.ninson/upload.php
2. Note the filename of the uploaded image
3. The system will automatically use images in the `uploads/` directory

### Testing

After deployment, test image uploads by:
1. Adding a new product with an image
2. Editing an existing product with a new image
3. Verify images appear correctly in the product listings

### Troubleshooting

If uploads fail:
1. Check that the `uploads/` directory exists and has proper permissions
2. Verify the upload endpoint URL is correct
3. Check server error logs for detailed error messages
4. Ensure cURL is enabled on the server (required for server upload method)

