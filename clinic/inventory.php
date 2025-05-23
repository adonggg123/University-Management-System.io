<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #2A9D8F;
            --secondary-color: #4BA8A0;
            --accent-color: #A3D5D1;
            --light-color: #F1FAEE;
            --dark-color: #1D3A44;
            --success-color: #2A9D8F;
            --warning-color: #E9C46A;
            --danger-color: #E76F51;
            --header-height: 60px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F4F6F9;
            overflow-x: hidden;
            color: #264653;
        }

        .clinic-header {
            height: var(--header-height);
            background-color: white;
            border-bottom: 1px solid #E2E8F0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .brand-name {
            display: flex;
            align-items: center;
        }

        .brand-name img {
            width: 30px;
            height: 30px;
            margin-right: 8px;
        }

        .brand-name h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            height: calc(100vh - var(--header-height));
            width: var(--sidebar-width);
            background-color: white;
            transition: all 0.3s ease;
            z-index: 1020;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
            padding-top: 1rem;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .nav-link {
            color: #4A5568;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin: 0.3rem 0.8rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
            margin: 0.3rem auto;
            width: 48px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            margin-top: var(--header-height);
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            background-color: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .modal-content {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 1rem;
        }

        .modal-body {
            padding: 1.25rem;
            background-color: #F8FAFC;
        }

        .modal-footer {
            border-top: none;
            padding: 1rem;
            background-color: #F8FAFC;
        }

        .btn-primary, .btn-success, .btn-danger, .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2A7B6E);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #A43C3A);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096, #4A5568);
        }

        .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-secondary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #2A7B6E, var(--success-color));
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #A43C3A, var(--danger-color));
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #4A5568, #718096);
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background: var(--light-color);
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .sidebar-toggle:hover {
            color: var(--secondary-color);
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #F8FAFC;
            color: #4A5568;
            font-weight: 500;
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #E2E8F0;
        }

        .table-hover tbody tr:hover {
            background-color: var(--light-color);
        }

        .badge.bg-success {
            background-color: var(--success-color) !important;
        }

        .badge.bg-warning {
            background-color: var(--warning-color) !important;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .input-group-text {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
        }

        .form-control {
            border: 1px solid #E2E8F0;
            border-radius: 6px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        .form-select {
            border: 1px solid #E2E8F0;
            border-radius: 6px;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        #emptyState {
            color: #4A5568;
        }

        #emptyState i {
            font-size: 3rem;
        }

        .pagination .page-link {
            color: var(--primary-color);
            border: 1px solid #E2E8F0;
            margin: 0 2px;
            border-radius: 4px;
        }

        .pagination .page-link:hover {
            background-color: var(--light-color);
            border-color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        @media (max-width: 767.98px) {
            :root {
                --sidebar-width: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .sidebar.collapsed {
                width: 0;
            }

            .mobile-overlay {
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1019;
                display: none;
            }

            .mobile-overlay.show {
                display: block;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-end;
            }

            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .clinic-header {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .brand-name img {
                width: 24px;
                height: 24px;
            }

            .brand-name h5 {
                font-size: 1rem;
            }

            .card-title {
                font-size: 0.9rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <header class="clinic-header fixed-top d-flex align-items-center px-3">
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="sidebar-toggle me-2">
                <i class="bi bi-list"></i>
            </button>
            <div class="brand-name">
                <img src="Image/clinic.gif" alt="USTP Clinic Logo" class="logo" />
                <h5>USTP CLINIC</h5>
            </div>
        </div>
    </header>

    <nav class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="patient.php">
                    <i class="bi bi-people"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_appointment.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="inventory.php">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content" id="mainContent">
        <div class="container-fluid p-0">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="card-title mb-0">Inventory Management</h5>
                            <div class="d-flex gap-2 mt-2 mt-sm-0">
                                <button class="btn btn-primary btn-sm d-flex align-items-center" id="addItemBtn" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                    <i class="bi bi-plus-circle me-1"></i> <span class="d-none d-sm-inline">Add Item</span>
                                </button>
                                <button class="btn btn-success btn-sm d-flex align-items-center" onclick="downloadCSV()">
                                    <i class="bi bi-download me-1"></i> <span class="d-none d-sm-inline">Export</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-0 bg-light" id="searchInput" placeholder="Search items..." />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Date Added</th>
                                            <th class="action-col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <div id="emptyState" class="text-center py-5 d-none">
                                <i class="bi bi-inbox text-muted"></i>
                                <p class="mt-3 text-muted">No inventory items found</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </main>

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm">
                        <div class="mb-3">
                            <label for="itemName" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="itemName" required>
                        </div>
                        <div class="mb-3">
                            <label for="itemCategory" class="form-label">Category</label>
                            <select class="form-select" id="itemCategory" required>
                                <option value="" selected disabled>Select category</option>
                                <option value="Medications">Medications</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="itemQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="itemQuantity" min="0" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">Save Item</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this item?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const searchInput = document.getElementById('searchInput');
            const paginationContainer = document.getElementById('pagination');
            const itemsPerPage = 10;
            let currentPage = 1;

            // Sidebar toggle functionality
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('show');
                    mobileOverlay.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            });

            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                mobileOverlay.classList.remove('show');
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    mobileOverlay.classList.remove('show');
                    if (sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            function displayInventoryItems(items) {
                const tableBody = document.querySelector('tbody');
                const emptyState = document.getElementById('emptyState');

                tableBody.innerHTML = '';

                if (items.length === 0) {
                    emptyState.classList.remove('d-none');
                    return;
                }

                emptyState.classList.add('d-none');

                items.forEach(item => {
                    const row = document.createElement('tr');

                    let badgeClass = 'bg-success';
                    if (item.status === 'Low Stock') {
                        badgeClass = 'bg-warning';
                    } else if (item.status === 'Out of Stock') {
                        badgeClass = 'bg-danger';
                    }

                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.category}</td>
                        <td>${item.quantity}</td>
                        <td><span class="badge ${badgeClass}">${item.status}</span></td>
                        <td>${item.date_added}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${item.id}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${item.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;

                    tableBody.appendChild(row);
                });

                addActionButtonListeners();
            }

            function addActionButtonListeners() {
                document.querySelectorAll('.edit-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.getAttribute('data-id');
                        fetch(`inventory_api.php?id=${itemId}`)
                            .then(response => response.json())
                            .then(item => {
                                if (item.error) {
                                    alert(item.error);
                                    return;
                                }
                                document.getElementById('itemName').value = item.name;
                                document.getElementById('itemCategory').value = item.category;
                                document.getElementById('itemQuantity').value = item.quantity;
                                document.getElementById('addItemModalLabel').textContent = 'Edit Inventory Item';
                                document.getElementById('saveItemBtn').setAttribute('data-edit-id', itemId);
                                const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
                                addItemModal.show();
                            })
                            .catch(() => alert('Failed to fetch item details'));
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.getAttribute('data-id');
                        document.getElementById('confirmDeleteBtn').setAttribute('data-delete-id', itemId);
                        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                        deleteModal.show();
                    });
                });
            }

            function renderPagination(totalItems, currentPage) {
                const totalPages = Math.ceil(totalItems / itemsPerPage);
                paginationContainer.innerHTML = '';

                // Previous button
                const prevItem = document.createElement('li');
                prevItem.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevItem.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
                paginationContainer.appendChild(prevItem);

                // Page numbers (show limited range for better UX)
                const maxVisiblePages = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }

                if (startPage > 1) {
                    const firstPage = document.createElement('li');
                    firstPage.className = 'page-item';
                    firstPage.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
                    paginationContainer.appendChild(firstPage);
                    if (startPage > 2) {
                        const ellipsis = document.createElement('li');
                        ellipsis.className = 'page-item disabled';
                        ellipsis.innerHTML = `<span class="page-link">...</span>`;
                        paginationContainer.appendChild(ellipsis);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageItem = document.createElement('li');
                    pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
                    pageItem.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
                    paginationContainer.appendChild(pageItem);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const ellipsis = document.createElement('li');
                        ellipsis.className = 'page-item disabled';
                        ellipsis.innerHTML = `<span class="page-link">...</span>`;
                        paginationContainer.appendChild(ellipsis);
                    }
                    const lastPage = document.createElement('li');
                    lastPage.className = 'page-item';
                    lastPage.innerHTML = `<a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>`;
                    paginationContainer.appendChild(lastPage);
                }

                // Next button
                const nextItem = document.createElement('li');
                nextItem.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextItem.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
                paginationContainer.appendChild(nextItem);

                // Add event listeners to page links
                paginationContainer.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.getAttribute('data-page'));
                        if (page && !isNaN(page)) {
                            currentPage = page;
                            loadInventory(searchInput.value.trim(), currentPage);
                        }
                    });
                });
            }

            function loadInventory(searchTerm = '', page = 1) {
                const queryParams = new URLSearchParams();
                if (searchTerm) {
                    queryParams.append('search', searchTerm);
                }
                queryParams.append('page', page);
                queryParams.append('limit', itemsPerPage);

                fetch(`inventory_api.php?${queryParams.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        displayInventoryItems(data.items);
                        renderPagination(data.totalItems, data.currentPage);
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('Failed to load inventory: ' + error.message);
                    });
            }

            // Initial load
            loadInventory();

            // Search functionality
            searchInput.addEventListener('input', function() {
                currentPage = 1; // Reset to first page on search
                loadInventory(this.value.trim(), currentPage);
            });

            const addItemForm = document.getElementById('addItemForm');
            const saveItemBtn = document.getElementById('saveItemBtn');

            saveItemBtn.addEventListener('click', function() {
                const itemName = document.getElementById('itemName').value.trim();
                const itemCategory = document.getElementById('itemCategory').value;
                const itemQuantity = parseInt(document.getElementById('itemQuantity').value);

                if (!itemName || !itemCategory || isNaN(itemQuantity) || itemQuantity < 0) {
                    alert('Please fill out all fields correctly.');
                    return;
                }

                const editId = this.getAttribute('data-edit-id');
                const itemData = {
                    name: itemName,
                    category: itemCategory,
                    quantity: itemQuantity,
                    date_added: new Date().toISOString().split('T')[0]
                };

                const method = editId ? 'PUT' : 'POST';
                if (editId) {
                    itemData.id = editId;
                }

                fetch('inventory_api.php', {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(itemData)
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(result => {
                        if (result.error) {
                            alert(result.error);
                            return;
                        }
                        alert(method === 'POST' ? 'Item added successfully!' : 'Item updated successfully!');
                        addItemForm.reset();
                        document.getElementById('addItemModalLabel').textContent = 'Add New Inventory Item';
                        this.removeAttribute('data-edit-id');
                        const addItemModal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
                        addItemModal.hide();
                        loadInventory(searchInput.value.trim(), currentPage);
                    })
                    .catch(error => {
                        console.error('Save error:', error);
                        alert('Failed to save item: ' + error.message);
                    });
            });

            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            confirmDeleteBtn.addEventListener('click', function() {
                const deleteId = this.getAttribute('data-delete-id');

                fetch('inventory_api.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: deleteId })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(result => {
                        if (result.error) {
                            alert(result.error);
                            return;
                        }
                        alert('Item deleted successfully!');
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                        deleteModal.hide();
                        loadInventory(searchInput.value.trim(), currentPage);
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        alert('Failed to delete item: ' + error.message);
                    });
            });

            window.downloadCSV = function() {
                fetch('inventory_api.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        let csvContent = 'Item Name,Category,Quantity,Status,Date Added\n';

                        data.items.forEach(item => {
                            csvContent += `"${item.name.replace(/"/g, '""')}","${item.category}",${item.quantity},"${item.status}","${item.date_added}"\n`;
                        });

                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const url = URL.createObjectURL(blob);

                        const link = document.createElement('a');
                        link.setAttribute('href', url);
                        link.setAttribute('download', 'inventory_export.csv');
                        link.style.visibility = 'hidden';

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    })
                    .catch(error => {
                        console.error('Export error:', error);
                        alert('Failed to export inventory: ' + error.message);
                    });
            };
        });
    </script>
</body>
</html>
<?php $conn = null; ?>