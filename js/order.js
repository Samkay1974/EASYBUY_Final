/**
 * Order Management JavaScript
 * Handles order-related frontend interactions
 */

/**
 * Cancel order with confirmation
 */
function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to cancel action
            window.location.href = '../actions/cancel_order_action.php?order_id=' + orderId;
        }
    });
}

/**
 * Place order from cart
 */
function placeOrder(collaborationId = null) {
    Swal.fire({
        title: 'Place Order?',
        html: 'Are you sure you want to place this order?<br><br><strong>Note:</strong> After placing your order, the wholesaler will contact you via phone call for more information about your order before you make payment.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, place order!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            let url = '../actions/place_order_action.php';
            if (collaborationId) {
                url += '?collaboration_id=' + collaborationId;
            }
            window.location.href = url;
        }
    });
}

/**
 * Place order for collaboration (when collaboration reaches 100%)
 */
function placeCollaborationOrder(collaborationId) {
    Swal.fire({
        title: 'Place Collaboration Order?',
        text: 'This will place an order for the entire collaboration group. Are you sure?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, place order!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/place_order_action.php?collaboration_id=' + collaborationId;
        }
    });
}

/**
 * Show order details in a modal or alert
 */
function showOrderDetails(orderId) {
    // This can be expanded to fetch and display order details via AJAX
    Swal.fire({
        title: 'Order Details',
        text: 'Order #' + orderId,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

