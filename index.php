<?php
include 'db_conn.php';

        // Handle add supply
        if (isset($_POST['add_supply'])) {
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $stmt = $conn->prepare("INSERT INTO supplies (item_name, quantity) VALUES (?, ?)");
            $stmt->bind_param("si", $item_name, $quantity);
            $stmt->execute();
            $note = "New supply added: $item_name ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: index.php');
            exit;
        }

        // Handle purchase in/out
        if (isset($_POST['transaction'])) {
            $supply_id = $_POST['supply_id'];
            $quantity = $_POST['quantity'];
            $action = $_POST['action'];
            $adjustment = ($action === 'purchase_in') ? '+' : '-';
            $conn->query("UPDATE supplies SET quantity = quantity $adjustment $quantity WHERE id = $supply_id");
            $row = $conn->query("SELECT item_name FROM supplies WHERE id = $supply_id")->fetch_assoc();
            $note = ucfirst(str_replace('_', ' ', $action)) . ": {$row['item_name']} ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: index.php');
            exit;
        }

        // Handle borrow request
        if (isset($_POST['borrow_request'])) {
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $conn->query("INSERT INTO borrow_requests (item_name, quantity) VALUES ('$item_name', '$quantity')");
            $note = "Borrow request: $item_name ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: index.php');
            exit;
        }

        if (isset($_POST['delete_supply'])) {
            $delete_id = (int)$_POST['delete_id'];

            $check = $conn->query("SELECT quantity FROM supplies WHERE id = $delete_id LIMIT 1");

            if ($check && $check->num_rows > 0) {
                $row = $check->fetch_assoc();
                $quantity = (int)$row['quantity'];

                if ($quantity >= 0) {
                    $conn->query("DELETE FROM supplies WHERE id = $delete_id");
                    echo "<script>alert('Supply deleted successfully.'); window.location.href='index.php';</script>";
                } else {
                    echo "<script>alert('Cannot delete. Quantity must be zero. Current: $quantity'); window.location.href='index.php';</script>";
                }
            } else {
                echo "<script>alert('Supply not found.'); window.location.href='index.php';</script>";
            }
        }       
        
        if (isset($_GET['view_notifications'])) {
        $conn->query("UPDATE notifications SET status = 'read' WHERE status = 'unread'");
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>University Management System</title>
        <link rel="stylesheet" href="style1.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="supply.css?v=<?php echo time(); ?>">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJtJ7tJkPmcV9f9fGvGkUuJkqMX6IQWuK/4hDh3KpWwW9Dptf4U/JpP4OmVZ" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0v8FqI1P+8zUuK0CptlX+u0xP1z5DiH1ua7Tgpm2U4B7w+My" crossorigin="anonymous"></script>

  <style>
    .content { display: none; padding: 20px; }
    .content.active { display: block; }
  </style>

</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <img src="USTP-logo-circle.png" alt="">
    </div>
    <div class="logo1">
      <ul class="menu">
        <li class="active"><a href="#dashboard"><i class='bx bxs-dashboard'></i><span>Dashboard Content</span></a></li>
        <li><a href="#profile"><i class='bx bxs-user'></i><span>Profile</span></a></li>
        <li><a href="#alumni"><i class='bx bxs-school'></i><span>Alumni Office</span></a></li>
        <li><a href="#security"><i class="fas fa-lock"></i><span>Security</span></a></li>
        <li><a href="#admin"><i class="fas fa-graduation-cap"></i><span>Administration & Scholarship</span></a></li>
        <li><a href="#cashier"><i class="fas fa-cash-register"></i><span>Cashier</span></a></li>
        <li><a href="#supply"><i class="fas fa-boxes"></i><span>Supply</span></a></li>
        <li><a href="#library"><i class='bx bx-library'></i><span>Library</span></a></li>
        <li><a href="#clinic"><i class='bx bxs-clinic'></i><span>Clinic</span></a></li>
        <li><a href="#canteen"><i class="fas fa-utensils"></i><span>Canteen</span></a></li>
        <li class="logout"><a href="#logout"><i class='bx bx-log-out'></i><span>Log-out</span></a></li>
      </ul>
    </div>
  </div>

    <div class="main--content">
    <div class="header--wrapper">
        <div class="header--title">
        <h2 id="current-title">University Management System</h2>
        </div>
        <div class="user--info"> <!-- fixed missing '<' -->
        <div class="search--box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search">
        </div>
        <img src="jude.jpg" alt="">
        </div>
    </div>

        <div id="dashboard" class="content active">
            <h3>Dashboard Content</h3>
            <div class="card--container">
                <h3 class="main--title">
                    <span>Today's Data</span>
                </h3>
                <div class="card--wrapper">
                    <div class="payment--card light-blue">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-dollar-sign icon dark-blue"></i>
                        </div>
                        <span class="card--detail">**** **** **** 3484</span>
                    </div>
                    <div class="payment--card light-purple">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-list icon dark-purple"></i>
                        </div>
                        <span class="card--detail">**** **** **** 5542</span>
                    </div>
                    <div class="payment--card light-red">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-users icon dark-red"></i>
                        </div>
                        <span class="card--detail">**** **** **** 8896</span>
                    </div>
                    <div class="payment--card light-green">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-check icon dark-green"></i>
                        </div>
                        <span class="card--detail">**** **** **** 7745</span>
                    </div>
                </div>
            </div>
            <div class="tabular--wrapper">
                <h3 class="main--title">Finance Data</h3>
                <div class="table--container">
                    <table class="sample-table">
                        <thead>
                            <tr>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                            </tr>
                            <tbody>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>                                                                                                                                                                                                                                                                                                                   
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7">Total: $000.000</td>
                                </tr>
                            </tfoot>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    <div id="profile" class="content">
      <h3>Profile</h3>
      <p>User profile info here.</p>
    </div>
    <div id="alumni" class="content">
      <h3>Alumni Office</h3>
      <p>Alumni office content.</p>
    </div>
    <div id="security" class="content">
      <h3>Security</h3>
      <p>Security department details.</p>
    </div>
    <div id="admin" class="content">
      <h3>Administration & Scholarship</h3>
      <p>Admin content here.</p>
    </div>
    <div id="cashier" class="content">
      <h3>Cashier</h3>
      <p>Cashier section content.</p>
    </div>

    <div id="supply" class="content">
            <div class="container-fluid mt-4">
                <h2>Inventory Management</h2>

                <div class="row align-items-start">
                <!-- Add New Supply Form -->
                <div class="col-md-6">
                    <h4>Add New Supply</h4>
                    <form action="index.php" method="POST" class="custom-form">
                        <div class="mb-3">
                            <label for="item_name">Item Name</label>
                            <input type="text" name="item_name" id="item_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required>
                        </div>
                        <button type="submit" name="add_supply" class="btn btn-primary w-100">Add Supply</button>
                    </form>
                    
                </div>
                <?php
                $unread_count = $conn->query("SELECT COUNT(*) AS count FROM notifications WHERE status = 'unread'")->fetch_assoc()['count'];
                ?>
                <div class="notification-wrapper text-end mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#borrowRequestModal">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge" id="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Purchase In/Out Form -->
                <div class="col-md-6">
                    <h4>Purchase In/Out</h4>
                    <form class="custom-form" action="index.php" method="POST">
                        <div class="mb-3">
                            <label for="supply_id">Select Item</label>
                            <select name="supply_id" class="form-select" required>
                                <?php
                                $result = $conn->query("SELECT * FROM supplies");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['item_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity2">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="action">Action</label>
                            <select name="action" class="form-select" required>
                                <option value="purchase_in">Purchase In</option>
                                <option value="purchase_out">Purchase Out</option>
                            </select>
                        </div>
                        <button type="submit" name="transaction" class="btn btn-primary w-100">Submit Transaction</button>
                    </form>
                </div>
            </div>


                <!-- Right Section -->
                    <h4 class="supply">All Supplies</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped supply-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $supply_list = $conn->query("SELECT * FROM supplies");
                                while ($row = $supply_list->fetch_assoc()) {
                                    echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['item_name']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td>
                                                <form method='POST' action='index.php' onsubmit='return confirm(\"Are you sure?\")'>
                                                    <input type='hidden' name='delete_id' value='{$row['id']}'>
                                                    <button type='submit' name='delete_supply' class='icon-button'>
                                                        <i class='fas fa-trash-can'></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!--<script>
                    function confirmDelete(quantity) {
                        if (quantity != 0) {
                            alert("Cannot delete. Quantity must be zero.");
                            return false;
                        }
                        return confirm("Are you sure you want to delete this supply?");
                    }
                    </script>-->    

                   <!-- Modal for Borrow Requests -->
                    <div class="modal fade" id="borrowRequestModal" tabindex="-1" aria-labelledby="borrowRequestModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="borrowRequestModalLabel">Borrow Requests</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Item Name</th>
                                                    <th>Quantity</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $borrow_list = $conn->query("SELECT * FROM borrow_requests ORDER BY id DESC");
                                                while ($row = $borrow_list->fetch_assoc()) {
                                                    echo "<tr>
                                                            <td>{$row['id']}</td>
                                                            <td>{$row['item_name']}</td>
                                                            <td>{$row['quantity']}</td>
                                                            <td>{$row['created_at']}</td>
                                                        </tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const borrowModal = document.getElementById('borrowRequestModal');

            borrowModal.addEventListener('shown.bs.modal', function () {
                // Remove notification badge
                fetch('mark_notifications_read.php').then(() => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) badge.remove();
                });

                // Auto-close modal after 5 seconds
                setTimeout(function () {
                    const modalInstance = bootstrap.Modal.getInstance(borrowModal);
                    if (modalInstance) modalInstance.hide();
                }, 5000);
            });
        });
        </script>

            <!-- Bootstrap JS (optional for dropdowns/modal etc) -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>

    <div id="library" class="content">
      <h3>Library</h3>
      <p>Library resources and tools.</p>
    </div>

    <div id="clinic" class="content">
      <h3>Clinic</h3>
      <p>Clinic services and health info.</p>
    </div>
    
    <div id="canteen" class="content">
            
            <div class="container">
                <div class="nav-bar">
                    
                    <h1>🍔Canteen</h1>
                    <nav>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#About">About</a></li>
                            <!--<li><a href="#category">Category</a></li>-->
                            <li><a href="#menu">Menu</a></li>
                            <li><a href="/services.html">Service</a></li>
                            <!--<li><a href="/streetFood.html"><img src="img2/cart-44-256.png"></a></li>-->
                            <li><button><a class="btn-contct" href="#contact">Contact Us</a></button> </li>
                        </ul>
                    </nav>
                    
                </div>
                <div class="row">
                    <div class="col">
                        <h1>Foodhub</h1>
                        <span>Make your day great with our special food!<br> <p>Welcome to our canteen where every bite tells a story and every bite sparks a joy </p></span>
                        
                        
                            <div class="btn">
                                <div class="order">
                                    <button><a href="streetFood.php">Order Now<i class="fa fa-cart-plus" aria-hidden="true"></i></a></button>
                                </div>
                            <div class="contact">
                            </div>
                        </div>
                    </div>
                    <div class="img">
                        <img src="img/filipino-street-food-kwek-kwek1.jpg">
                    </div>
                </div>
            </div>
            
        </section>

        <!--<section id="category">
            <div class="header">
                <div class="main">
                    <h1>Category</h1>
                    <div class="cards">
                        <div>
                            <button class="link1"><a href="/streetFood.html">Street Food</a></button>
                        </div>
                        <div>
                            <button class="link2"><a href="/drink.html">Drinks</a></button>
                        </div>
                        <div>
                            <button class="link3"><a href="">Fast Food</a></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>-->

        <section id="About">
            <div class="about"> 
                <div class="about-left">
                    <h1>About Us</h1><hr>
                    <p>Welcome to FoodHub Your Go-To Canteen at USTP Oroquieta!
                        Located right in the heart of USTP Oroquieta, FoodHub is the ultimate hangout spot for students and staff looking for delicious, affordable meals. We serve a wide variety of favorites – from tasty street foods to refreshing drinks, and everything in between!
                        Whether you're in the mood for a quick bite between classes or just chilling with friends, FoodHub is here to satisfy your cravings without breaking the bank. Come for the food, stay for the vibes!
                        </p>
                </div>
                    <div class="about-right">
                        <img src="img2/canteen.jpg">
                    </div>
            </div>
            
        </seection>
        
        <section id="menu">
            <div class="menu-container menu-container2">
                <h1>MENU</h1>
                <ul class="menu-content">
                    <li class="item">
                        <img src="img/burger.png" alt="Burger">
                        <div class="description">
                            <h4>Burger</h4>
                            <p>Burger is a savory sandwich made with a meat patty, fresh veggies, and sauces, served in a soft bun.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    
                    <li class="item">
                        <img src="img/soft.png" alt="Soft drinks">
                        <div class="description">
                            <h4>Soft Drinks</h4>
                            <p>Soft drinks are carbonated, sweetened beverages available in various flavors, perfect for a refreshing treat.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/20347187.png" alt="Kikiam">
                        <div class="description">
                            <h4>Kikiam</h4>
                            <p>Kikiam is a popular Filipino street food made of seasoned meat or fish paste wrapped in bean curd skin, then deep-fried until crispy.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/images (1).png" alt="Fish flat">
                        <div class="description">
                            <h4>Fishflat</h4>
                            <p>Fish flat is a Filipino street food made of seasoned fish paste shaped flat, then deep-fried until golden and crispy.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/nuggets.png" alt="Nuggets">
                        <div class="description">
                            <h4>Nuggets</h4>
                            <p>Nuggets are bite-sized pieces of seasoned chicken, coated in breading and deep-fried, popular as a tasty and easy street food snack.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/shomai.png" alt="Shomai">
                        <div class="description">
                            <h4>Fried / Steam Siomai</h4>
                            <p>Fried/Steam Siomai is a Filipino street food of tasty pork or shrimp dumplings, either steamed or deep-fried, and served with soy sauce or chili sauce.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/tempura.png" alt="tempura">
                        <div class="description">
                            <h4>Tempura</h4>
                            <p>Tempura is a popular street food made of fish, shrimp, or vegetables coated in light batter and deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/squid-roll.png" alt="Squid roll">
                        <div class="description">
                            <h4>Squidroll</h4>
                            <p>Squid roll is a street food snack made of seasoned squid paste rolled in batter and deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item"> 
                        <img src="img/cheese-stick.png" alt="Cheese stick">
                        <div class="description">
                            <h4>cheese Stick</h4>
                            <p>Cheese stick is a street food snack made of cheese wrapped in lumpia wrapper, then deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        
                            <img src="img/squid-ball.png" alt="Squid ball">
                        <div class="description">
                            <h4>Squid Ball</h4>
                            <p>Squid balls are bite-sized, seasoned squid meat balls, battered and deep-fried until crispy, often served with dipping sauces.</p>
                            <span>&#8369; 10</span>
                        </div>
                        
                    </li>
                    <li class="item">
                        <img src="img/kwek-kwek.png" alt="Kwek-kwek">
                        <div class="description">
                            <h4>Kwek-Kwek</h4>
                            <p>Kwek-kwek is a Filipino street food made of quail eggs coated in orange batter and deep-fried, usually served with vinegar or spicy sauce.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/lemon.png" alt="Lemonade">
                        <div class="description">
                            <h4>Blue Lemonade</h4>
                            <p>Blue lemonade is a tangy, refreshing drink with a vibrant blue twist.</p>
                            <span>&#8369; 10</span>
                        </div>
                    <li class="item">
                        <img src="img/cocumber.png" alt="Cocumber juice">
                        <div class="description">
                            <h4>Cocumber Juice</h4>
                            <p>Cucumber juice is a cool, refreshing drink made from fresh cucumber, offering a light and hydrating flavor.</p>
                            <span>&#8369; 10</span>
                        </div>
                        <li class="item">
                            <img src="img/TASTY-MEATY-HOTDOG-WITH-CHEESE-JUMBO-500G-2.png" alt="Jumbo hotdog">
                            <div class="description">
                                <h4>Hotdog Jumbo</h4>
                                <p>Hotdog Jumbo is a large, skewered hotdog coated in batter and deep-fried for a crispy, tasty snack.</p>
                                <span>&#8369; 10</span>
                            </div>
                        </li>
                    
                    <li class="item">
                        <img src="img/hotdog-balls.png" alt="Hotdog balls">
                        <div class="description">
                            <h4>Hotdog balls</h4>
                            <p>Hotdog balls are bite-sized hotdogs coated in batter and deep-fried until crispy, perfect for snacks or dipping.</p>
                            <span>&#8369; 10</span>
                            
                        </div>
                    </li>
                </ul>
            </div>
        </section>
   <section id="contact">
       <div class="box-container">
           <form action="https://api.web3forms.com/submit" method="POST" class="contact-left" >
               
               
                   <div class="title"><h1>Contact Us</h1><hr></div>
                   <input type="hidden" name="access_key" value="767d8709-aba6-424a-ab34-009f6b75ec72">
                   <input type="text" name="name" placeholder="Your name" required class="input"><br>
                   <input type="email" name="email" placeholder="Your email" required class="input"><br>
                   <textarea name="message"placeholder="Your message" required class="input"></textarea><br>
                   <button type="submit">Send Message<i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
              
               
           </form>
           <div class="contact-right">
               <img src="img/74a6b3f3514e6b37aa1baf5b8d42c493.png">
           </div>
       </div>
   </section>
   <footer>
       <div class="heading">   
           <div class="text">
               <p>USTP Mobod, Oroquieta City</p>
               <span>Philippines</span>
               <ul>
                   <li>
                       <span><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-instagram" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-google" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-instagram" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-twitter" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-envelope-o" aria-hidden="true"></i>
                       </span>
                       
                   </li>
               </ul>
               <p>Copyright &copy; All Right Reserved</p>
               <a href="#home">Bact to top</a>
           </div>
       </div>
   </footer>
   <script src="app.js"></script>
    </div>
    <div id="logout" class="content">
      <h3>Logged Out</h3>
      <p>You have logged out successfully.</p>
    </div>
  </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const links = document.querySelectorAll('.menu a');
        const contents = document.querySelectorAll('.content');

        links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = link.getAttribute('href').substring(1);

            contents.forEach(c => c.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
        });
    });
    </script>


</body>
</html>
