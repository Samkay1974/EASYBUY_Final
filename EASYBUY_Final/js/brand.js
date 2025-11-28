$(document).ready(function () {

    // Load all brands
    function loadBrands() {
        $.ajax({
            url: "../actions/fetch_brand_action.php",
            type: "GET",
            dataType: "json",
            success: function (brands) {
                let tbody = $("#brands-body");
                tbody.empty();

                if (!Array.isArray(brands) || brands.length === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">No brands found.</td></tr>');
                    return;
                }

                brands.forEach(b => {
                    tbody.append(`
                        <tr>
                            <td>${b.brand_id}</td>
                            <td>${b.brand_name}</td>
                            <td>
                                <button class="btn btn-warning btn-sm editBtn" data-id="${b.brand_id}" data-name="${b.brand_name}">Edit</button>
                                <button class="btn btn-danger btn-sm deleteBtn" data-id="${b.brand_id}">Delete</button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function (xhr) {
                Swal.fire("Error", "Could not load brands", "error");
                console.error(xhr.responseText);
            }
        });
    }

    loadBrands();

    // === CREATE ===
    $("#create-brand-form").submit(function (e) {
        e.preventDefault();
        let brand_name = $("#brand_name").val().trim();

        if (brand_name === "") {
            Swal.fire("Error", "Brand name is required!", "error");
            return;
        }

        $.ajax({
            url: "../actions/add_brand_action.php",
            type: "POST",
            data: { brand_name: brand_name },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire("Success", res.message, "success");
                    $("#create-brand-form")[0].reset();
                    loadBrands();
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            },
            error: function (xhr) {
                Swal.fire("Error", "Server error", "error");
                console.error(xhr.responseText);
            }
        });
    });

    // === UPDATE ===
    $(document).on("click", ".editBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");

        $("#edit_brand_id").val(id);
        $("#edit_brand_name").val(name);
        let modal = new bootstrap.Modal(document.getElementById("editBrandModal"));
        modal.show();
    });

    $("#edit-brand-form").submit(function (e) {
        e.preventDefault();
        let brand_id = $("#edit_brand_id").val();
        let brand_name = $("#edit_brand_name").val().trim();

        if (brand_name === "") {
            Swal.fire("Error", "Brand name cannot be empty!", "error");
            return;
        }

        $.ajax({
            url: "../actions/update_brand_action.php",
            type: "POST",
            data: { brand_id: brand_id, brand_name: brand_name },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire("Updated", res.message, "success");
                    $("#editBrandModal").modal("hide");
                    loadBrands();
                } else {
                    Swal.fire("Error", res.message, "error");
                }
            },
            error: function (xhr) {
                Swal.fire("Error", "Server error", "error");
                console.error(xhr.responseText);
            }
        });
    });

    // === DELETE ===
    $(document).on("click", ".deleteBtn", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently delete the brand.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!"
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../actions/delete_brand_action.php",
                    type: "POST",
                    data: { brand_id: id },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === "success") {
                            Swal.fire("Deleted!", res.message, "success");
                            loadBrands();
                        } else {
                            Swal.fire("Error", res.message, "error");
                        }
                    },
                    error: function (xhr) {
                        Swal.fire("Error", "Server error", "error");
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
});
