$(document).ready(function () {

    // === LOAD CATEGORIES ===
    function loadCategories() {
        $.ajax({
            url: "../actions/fetch_category_action.php",
            type: "GET",
            dataType: "json",
            success: function (categories) {
                let tbody = $("#categoryTableBody");
                tbody.empty();

                if (!Array.isArray(categories) || categories.length === 0) {
                    tbody.append("<tr><td colspan='3' class='text-center'>No categories found.</td></tr>");
                } else {
                    categories.forEach(function (cat) {
                        tbody.append(`
                            <tr>
                                <td>${cat.cat_id}</td>
                                <td>${cat.cat_name}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm editBtn" 
                                        data-id="${cat.cat_id}" 
                                        data-name="${cat.cat_name}">Edit</button>
                                    <button class="btn btn-danger btn-sm deleteBtn" 
                                        data-id="${cat.cat_id}">Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Fetch error:", xhr.responseText);
                Swal.fire("Error", "Failed to fetch categories.", "error");
            }
        });
    }

    loadCategories();

    // === CREATE CATEGORY ===
    $("#create-category-form").submit(function (e) {
        e.preventDefault();
        let cat_name = $("#cat_name").val();

        if (!cat_name || cat_name.trim() === "") {
            Swal.fire("Error", "Category name is required!", "error");
            return;
        }

        $.ajax({
            url: "../actions/add_category_action.php",
            type: "POST",
            data: { cat_name: cat_name.trim() },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    Swal.fire("Success", response.message, "success");
                    $("#create-category-form")[0].reset();
                    loadCategories();
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            },
            error: function (xhr, status, error) {
                console.error("Add error:", xhr.responseText);
                Swal.fire("Error", "Could not add category, Category may already exist.", "error");
            }
        });
    });

    // === UPDATE CATEGORY ===
    $(document).on("click", ".editBtn", function () {
        let cat_id = $(this).data("id");
        let old_name = $(this).data("name");

        Swal.fire({
            title: "Edit Category",
            input: "text",
            inputValue: old_name,
            showCancelButton: true,
            confirmButtonText: "Update",
        }).then((result) => {
            if (result.isConfirmed) {
                let new_name = result.value.trim();

                if (new_name === "") {
                    Swal.fire("Error", "Category name cannot be empty!", "error");
                    return;
                }

                $.ajax({
                    url: "../actions/update_category_action.php",
                    type: "POST",
                    data: { cat_id: cat_id, cat_name: new_name },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire("Success", response.message, "success");
                            loadCategories();
                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    },
                    error: function (xhr) {
                        console.error("Update error:", xhr.responseText);
                        Swal.fire("Error", "Could not update category.", "error");
                    }
                });
            }
        });
    });

    // === DELETE CATEGORY ===
    $(document).on("click", ".deleteBtn", function () {
        let cat_id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the category permanently.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "I approve!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../actions/delete_category_action.php",
                    type: "POST",
                    data: { cat_id: cat_id },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire("Deleted!", response.message, "success");
                            loadCategories();
                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    },
                    error: function (xhr) {
                        console.error("Delete error:", xhr.responseText);
                        Swal.fire("Error", "Category could not be deleted.", "error");
                    }
                });
            }
        });
    });
});
