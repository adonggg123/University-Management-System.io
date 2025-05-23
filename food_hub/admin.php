  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="uploads/img/images (2).jpg">
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <title>Canteen Admin</title>
  <style>
    
Notification System Styling
.notification {
  position: relative;
  cursor: pointer;
  margin-right: 20px;
}

.notification span {
  font-size: 22px;
  color: #555;
  transition: all 0.2s ease;
  margin-left: 700px;
}

.notification:hover span {
  color: #ff6b6b;
}

.notification-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #ff6b6b;
  color: white;
  font-size: 12px;
  height: 18px;
  width: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

#notificationList {
  position: absolute;
  top: 40px;
  right: -10px;
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  padding: 0;
  margin: 0;
  z-index: 1000;
  display: none;
  transition: all 0.3s ease;
}

.notification:hover #notificationList {
  display: block;
}

#notificationList li {
  list-style: none;
  padding: 12px 15px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  color: #333;
  display: flex;
  align-items: center;
  transition: background-color 0.2s;
}

#notificationList li:last-child {
  border-bottom: none;
}

#notificationList li:hover {
  background-color: #f9f9f9;
}

#notificationList li.unread {
  background-color: #f0f8ff;
  font-weight: 500;
}

#notificationList li.unread:before {
  content: '';
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #ff6b6b;
  border-radius: 50%;
  margin-right: 10px;
}

#notificationList li.read:before {
  content: '';
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #ddd;
  border-radius: 50%;
  margin-right: 10px;
}

#notificationList .notification-time {
  font-size: 11px;
  color: #888;
  margin-top: 4px;
}

#notificationList .notification-header {
  background-color: #f8f9fa;
  padding: 12px 15px;
  font-weight: bold;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

#notificationList .mark-all-read {
  font-size: 12px;
  color: #007bff;
  cursor: pointer;
  font-weight: normal;
}

.empty-notification {
  padding: 20px;
  text-align: center;
  color: #888;
  font-style: italic;
}

/* Toast notification for new incoming notifications */
.toast-notification {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: white;
  color: #333;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  animation: slideIn 0.3s forwards;
  z-index: 1001;
  border-left: 4px solid #ff6b6b;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-notification-content {
  margin-left: 15px;
}

.toast-notification h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
}

.toast-notification p {
  margin: 0;
  font-size: 14px;
}

.toast-notification .toast-icon {
  font-size: 24px;
  color: #ff6b6b;
}

.toast-notification .close-toast {
  margin-left: auto;
  cursor: pointer;
  font-size: 18px;
  color: #aaa;
}

</style>
  
</head>
<body>
  <div class="container">
    <header class="nav-bar">
      <div class="logo">
        <span>🍔</span> FoodHub
      </div>
      <div class="nav-right">
        <!-- <div class="message">
          <span><i class="fa fa-comment-o" aria-hidden="true"></i></span>
        </div> -->
         <div class="notification">
          <span><i class="fa fa-bell-o" aria-hidden="true"></i></span>
          <div class="notification-count" id="notificationCount"></div>
          <ul id="notificationList"></ul>
        </div> 
        
</div>

        <div class="user-profile">
          <div class="avatar">A</div>
          <span>Admin</span>
        </div>
      </div>
    </header>

    <div class="main-content">
      <div class="product-form-container">
        <h2 class="section-title">Add New Product</h2>
        <form id="productForm">
          <div class="form-group">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" id="productName" class="form-control" placeholder="Enter product name" required>
          </div>
          <div class="form-group">
            <label for="productPrice" class="form-label">Product Price (₱)</label>
            <input type="number" id="productPrice" class="form-control" placeholder="Enter price" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Product Image</label>
            <div class="file-input-container">
              <label for="productImage" class="file-input-label">
                <span>Choose an image file</span>
              </label>
              <input type="file" id="productImage" class="file-input" accept="image/png, image/jpeg, image/jpg">
            </div>
            <div class="image-preview" id="imagePreview"></div>
          </div>
          <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
      </div>

      <div class="product-list-container">
        

        <h2 class="section-title">Product List</h2>
        <i class="fa fa-search" aria-hidden="true"></i> 
        <input type="search" id="search" placeholder="🔍Search product">
      </div>
      <div class="product-grid" id="productList"></div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>
  <div class="orders-list" id="ordersList" style="display: none;"></div>

  <div class="messages-list" id="messagesList" style="display: none;"></div>
  <script>
   

    
document.getElementById("productForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const form = document.getElementById("productForm");
  const formData = new FormData(form);

  const name = document.getElementById("productName").value;
  const price = document.getElementById("productPrice").value;
  const imageFile = document.getElementById("productImage").files[0];

  formData.append("name", name);
  formData.append("price", price);
  formData.append("image", imageFile);

  fetch("add_product.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    if (data.trim() === "success") {
      alert("Product added successfully!");
      addProductToList(name, price, imageFile);
      form.reset();
      document.getElementById("imagePreview").innerHTML = "";
    } else {
      alert("Error adding product.");
    }
  })
  .catch(error => {
    console.error("Error:", error);
    alert("An error occurred.");
  });
});

// 👇 Function to add product to the grid
// 👇 Update the addProductToList function to include broadcasting the new product to the user page.
function addProductToList(name, price, imageFile) {
  const productList = document.getElementById("productList");

  const reader = new FileReader();
  reader.onload = function(event) {
    const imageUrl = event.target.result;

    const productCard = document.createElement("div");
    productCard.className = "product-card";
    productCard.innerHTML = `
      <div class="product-image">
        <img src="${imageUrl}" alt="${name}" class="product-image">
      </div>
      <div class="product-details">
        <div class="product-name">${name}</div>
        <div class="product-price">₱${parseFloat(price).toFixed(2)}</div>
        <div class="product-actions">
          <button class="btn btn-sm btn-edit" onclick="editProduct()">Edit</button>
          <button class="btn btn-sm btn-delete" onclick="deleteProduct()">Delete</button>
        </div>
      </div>
    `;
    
    // Append to admin page product list
    productList.appendChild(productCard);

    // Send new product data to user page via AJAX
    fetch("update_user_page.php", {
      method: "POST",
      body: JSON.stringify({
        name: name,
        price: price,
        image: imageUrl
      }),
      headers: {
        "Content-Type": "application/json"
      }
    }).then(res => res.json())
      .then(data => {
        console.log("User page updated:", data);
      })
      .catch(err => console.error("Error sending product to user page:", err));
  };

  reader.readAsDataURL(imageFile); // Converts image to base64 for preview
}


window.addEventListener("DOMContentLoaded", () => {
  fetch("fetch_products.php")
    .then(res => res.json())
    .then(products => {
      products.forEach(product => {
        displayProduct(product.name, product.price, product.image);
      });
    })
    .catch(err => console.error("Failed to fetch products:", err));
});

// function displayProduct(name, price, imageFilePath) {
//   const productList = document.getElementById("productList");

//   const productCard = document.createElement("div");
//   productCard.className = "product-card";
//   productCard.innerHTML = `
//     <img src="uploads/${imageFilePath}" alt="${name}" class="product-image">
//     <h3 class="product-name">${name}</h3>
//     <p class="product-price">₱${parseFloat(price).toFixed(2)}</p>
    
//   `;

//   productList.appendChild(productCard);
// }
function displayProduct(name, price, imageFilePath) {
  const productList = document.getElementById("productList");

  const productCard = document.createElement("div");
  productCard.className = "product-card";
  productCard.innerHTML = `
    <div class="product-image">
      <img src="uploads/${imageFilePath}" alt="${name}" class="product-image">
    </div>
    <div class="product-details">
      <div class="product-name">${name}</div>
      <div class="product-price">₱${parseFloat(price).toFixed(2)}</div>
      <div class="product-actions">
        <button class="btn btn-sm btn-edit" onclick="editProduct()">Edit</button>
        <button class="btn btn-sm btn-delete" onclick="deleteProduct()">Delete</button>
      </div>
    </div>
  `;

  productList.appendChild(productCard);
}


// function displayProduct(name, price, imageFilePath) {
//   const productList = document.getElementById("productList");

//   const productCard = document.createElement("div");
//   productCard.className = "product-card";
//   productCard.innerHTML = `
//     <img src="uploads/${imageFilePath}" alt="${name}" class="product-image">
//     <h3 class="product-name">${name}</h3>
//     <p class="product-price">₱${parseFloat(price).toFixed(2)}</p>
    
    
//   `;

//   productList.appendChild(productCard);
// }
// Function to fetch notifications
  function fetchNotifications() {
    fetch('get_notifications.php')
      .then(res => res.json())
      .then(data => {
        const list = document.getElementById('notificationList');
        const countElem = document.getElementById('notificationCount');
        list.innerHTML = '';

        let unreadCount = 0;

        if (data.length === 0) {
          list.innerHTML = '<li>No new notifications</li>';
          countElem.style.display = 'none';
        } else {
          data.forEach(notif => {
            const li = document.createElement('li');
            li.textContent = notif.message;
            list.appendChild(li);
            if (!notif.is_read || notif.is_read == 0) {
              unreadCount++;
            }
          });

          if (unreadCount > 0) {
            countElem.textContent = unreadCount;
            countElem.style.display = 'block';
          } else {
            countElem.style.display = 'none';
          }
        }
      })
      .catch(err => {
        console.error('Failed to fetch notifications:', err);
      });
  }

  // Refresh notification


// Check for new notifications every 10 seconds
setInterval(fetchNotifications, 10000);
fetchNotifications();

function loadOrders() {
    fetch('get_orders.php')
      .then(res => res.json())
      .then(orders => {
        const container = document.getElementById('ordersList');
        container.innerHTML = '<h2 class="section-title">Orders</h2>';

        if (orders.length === 0) {
          container.innerHTML += '<p>No orders yet.</p>';
          return;
        }

        orders.forEach(order => {
          const orderDiv = document.createElement('div');
          orderDiv.className = 'order';

          let itemsHtml = order.items.map(item =>
            `<li>${item.product_name} x${item.quantity}</li>`
          ).join('');

          orderDiv.innerHTML = `
            <h3>Order #${order.id}</h3>
            <p><strong>Name:</strong> ${order.customer_name}</p>
            <p><strong>Room:</strong> ${order.room_number}</p>
            <p><strong>Payment:</strong> ${order.payment_method}</p>
            <p><strong>Date:</strong> ${order.created_at}</p>
            <ul>${itemsHtml}</ul>
            <hr>
          `;

          container.appendChild(orderDiv);
        });
      })
      .catch(err => console.error('Failed to load orders:', err));
  }

  // Load orders/messages on DOM ready
  window.addEventListener("DOMContentLoaded", () => {
    loadOrders();
    loadMessages(); // assuming this function is also already defined
  });

function loadMessages() {
  fetch('get_messages.php')
    .then(res => res.json())
    .then(messages => {
      const container = document.getElementById('messagesList');
      container.innerHTML = '<h2 class="section-title">Messages</h2>';

      if (messages.length === 0) {
        container.innerHTML += '<p>No messages yet.</p>';
        return;
      }

      messages.forEach(msg => {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message-card';

        msgDiv.innerHTML = `
          <p><strong>Name:</strong> ${msg.name}</p>
          <p><strong>Email:</strong> ${msg.email}</p>
          <p><strong>Message:</strong> ${msg.message}</p>
          <p><small>${msg.created_at}</small></p>
          <hr>
        `;

        container.appendChild(msgDiv);
      });
    })
    .catch(err => console.error('Failed to load messages:', err));
}
// Modified: Toggle orders panel when notification bell is clicked
 document.querySelector('.notification').addEventListener('click', () => {
  fetch('mark_notifications_read.php', { method: 'POST' })
    .then(() => {
      document.getElementById('notificationCount').style.display = 'none';
      fetchNotifications();

      const ordersList = document.getElementById("ordersList");

      // If hidden, show and load orders
      if (ordersList.style.display === "none") {
        ordersList.style.display = "block";
        loadOrders(); // 👈 This ensures the orders are loaded
      } else {
        ordersList.style.display = "none";
      }
    });
});


  // Message icon toggle (already correct)
  document.querySelector(".message").addEventListener("click", () => {
    const messageList = document.getElementById("messagesList");
    messageList.style.display = messageList.style.display === "none" ? "block" : "none";
  });



   function deleteProduct(index) {
      if (confirm('Are you sure you want to delete this product?')) {
        products.splice(index, 1);
        localStorage.setItem('products', JSON.stringify(products));
        renderProductList();
        showToast('Product deleted successfully');
      }
    }
    
    function editProduct(index) {
      const product = products[index];
      document.getElementById('productName').value = product.name;
      document.getElementById('productPrice').value = product.price;
      imagePreview.innerHTML = `<img src="${product.image}" alt="Preview">`;
      
      // Change button text to indicate edit mode
      document.querySelector('#productForm .btn-primary').textContent = 'Update Product';
      
      editMode = true;
      editProductIndex = index;
      
      // Scroll to form
      document.querySelector('.product-form-container').scrollIntoView({ behavior: 'smooth' });
    }
    
    document.getElementById('productForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const name = document.getElementById('productName').value;
      const price = parseFloat(document.getElementById('productPrice').value);
      const imageFile = document.getElementById('productImage').files[0];
      
      if (editMode && !imageFile) {
        // If in edit mode and no new image is selected, use the existing image
        const updatedProduct = {
          name,
          price,
          image: products[editProductIndex].image
        };
        
        products[editProductIndex] = updatedProduct;
        localStorage.setItem('products', JSON.stringify(products));
        
        showToast('Product updated successfully');
        resetForm();
        renderProductList();
        return;
      }
      
      if (!imageFile) {
        showToast('Please upload an image', 'error');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = function() {
        const image = reader.result;
        
        if (editMode) {
          // Update existing product
          products[editProductIndex] = { name, price, image };
          showToast('Product updated successfully');
        } else {
          // Add new product
          const newProduct = { name, price, image };
          products.push(newProduct);
          showToast('Product added successfully');
        }
        
        localStorage.setItem('products', JSON.stringify(products));
        resetForm();
        renderProductList();
      };
      
      reader.readAsDataURL(imageFile);
    });
window.addEventListener("DOMContentLoaded", () => {
  loadOrders();
  loadMessages();
});


function checkNewOrders() {
    fetch('check_new_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.new_orders > 0) {
                document.getElementById('order-notification').textContent = `🛎️ ${data.new_orders} New Order(s)!`;
                document.getElementById('order-notification').style.display = 'block';
            } else {
                document.getElementById('order-notification').style.display = 'none';
            }
        })
        .catch(error => console.error('Error checking orders:', error));
}

// Check every 10 seconds
setInterval(checkNewOrders, 10000);

// Optional: check immediately on load
checkNewOrders();
function editProduct(id) {
      fetch(`get_product.php?id=${id}`)
        .then(res => res.json())
        .then(product => {
          document.getElementById("productId").value = product.id;
          document.getElementById("productName").value = product.name;
          document.getElementById("productPrice").value = product.price;
          document.getElementById("imagePreview").innerHTML = `<img src="uploads/${product.image}" alt="Preview">`;
        });
    }

    function deleteProduct(id) {
      if (confirm("Are you sure you want to delete this product?")) {
        fetch(`delete_product.php?id=${id}`, { method: 'DELETE' })
          .then(res => res.text())
          .then(data => {
            alert(data);
            loadProducts();
          });
      }
    }

    window.addEventListener("DOMContentLoaded", loadProducts);

</script>
</body>
</html>  

<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="img/images (2).jpg">
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
   <title>Canteen Admin</title>
  
</head>
<body>
  <div class="container">
    Header Navigation 
    <header class="nav-bar">
      <div class="logo">
        <span>🍔</span> FoodHub
      </div>
      <div class="nav-right">
        <div class="notification">
          <span><i class="fa fa-bell-o" aria-hidden="true"></i></span>
          <div class="notification-count"></div>
        </div>
        <div class="user-profile">
          <div class="avatar">A</div>
          <span>Admin</span>
        </div>
      </div>
    </header>
    
     Main Content 
    <div class="main-content">
    Add Product Form 
      <div class="product-form-container">
        <h2 class="section-title">Add New Product</h2>
        <form id="productForm">
          <div class="form-group">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" id="productName" class="form-control" placeholder="Enter product name" required>
          </div>
          
          <div class="form-group">
            <label for="productPrice" class="form-label">Product Price (₱)</label>
            <input type="number" id="productPrice" class="form-control" placeholder="Enter price" step="0.01" min="0" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Product Image</label>
            <div class="file-input-container">
              <label for="productImage" class="file-input-label">
                <span>Choose an image file</span>
              </label>
              <input type="file" id="productImage" class="file-input" accept="image/png, image/jpeg, image/jpg">
            </div>
            <div class="image-preview" id="imagePreview">
             <span class="preview-placeholder">Image here</span>
              <img src="" alt="Image">
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
      </div>
      
      Product List 
      <div class="product-list-container">
        <h2 class="section-title">Product List</h2>
        <i class="fa fa-search" aria-hidden="true"></i>
        <input type="search" id="search" placeholder=" Search product">
      </div>
        <div class="product-grid" id="productList">
           Products will be displayed here 
        </div>
     
    </div>
  </div>
  
 Toast Notifications 
  <div class="toast-container" id="toastContainer"></div>

  <script>

    const searchInput = document.getElementById('search');
    searchInput.addEventListener('input', () => {
      const searchValue = searchInput.value.toLowerCase();
      const filtered = products.filter(product =>
        product.name.toLowerCase().includes(searchValue)
      );
      displayFilteredProducts(filtered);
    });

    function displayFilteredProducts(filteredProducts) {
      listProductHTML.innerHTML = '';
      if (filteredProducts.length > 0) {
        filteredProducts.forEach(product => {
          const productDiv = document.createElement('div');
          productDiv.classList.add('item');
          productDiv.dataset.id = product.id;
          productDiv.innerHTML = `
            <img src="${product.image}" alt="${product.name}">
            <h2>${product.name}</h2>
            <div class="price">₱${product.price.toFixed(2)}</div>
            <button class="addCart">Add To Cart</button>
          `;
          productDiv.querySelector('.addCart').addEventListener('click', () => addToCart(product.id));
          listProductHTML.appendChild(productDiv);
        });
      } else {
        listProductHTML.innerHTML = "<p>No products found.</p>";
      }
    }
    // Helper functions
    function showToast(message, type = 'success') {
      const toastContainer = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.textContent = message;
      
      toastContainer.appendChild(toast);
      
      // Auto remove after 3 seconds
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
          toastContainer.removeChild(toast);
        }, 300);
      }, 3000);
    }
    
    // Image preview functionality
    const productImage = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');
    
    productImage.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.innerHTML = `<span class="preview-placeholder">Image preview will appear here</span>`;
      }
    });
    
    // Product management
    let products = JSON.parse(localStorage.getItem('products')) || [];
    let editMode = false;
    let editProductIndex = null;
    
    function renderProductList() {
      const productList = document.getElementById('productList');
      
      if (products.length === 0) {
        productList.innerHTML = `<div class="no-products">No products added yet</div>`;
        return;
      }
      
      productList.innerHTML = '';
      products.forEach((product, index) => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
          <div class="product-image">
            <img src="${product.image}" alt="${product.name}">
          </div>
          <div class="product-details">
            <div class="product-name">${product.name}</div>
            <div class="product-price">₱${product.price.toFixed(2)}</div>
            <div class="product-actions">
              <button class="btn btn-sm btn-edit" onclick="editProduct(${index})">Edit</button>
              <button class="btn btn-sm btn-delete" onclick="deleteProduct(${index})">Delete</button>
            </div>
          </div>
        `;
        productList.appendChild(productCard);
      });
    }
    
    function deleteProduct(index) {
      if (confirm('Are you sure you want to delete this product?')) {
        products.splice(index, 1);
        localStorage.setItem('products', JSON.stringify(products));
        renderProductList();
        showToast('Product deleted successfully');
      }
    }
    
    function editProduct(index) {
      const product = products[index];
      document.getElementById('productName').value = product.name;
      document.getElementById('productPrice').value = product.price;
      imagePreview.innerHTML = `<img src="${product.image}" alt="Preview">`;
      
      // Change button text to indicate edit mode
      document.querySelector('#productForm .btn-primary').textContent = 'Update Product';
      
      editMode = true;
      editProductIndex = index;
      
      // Scroll to form
      document.querySelector('.product-form-container').scrollIntoView({ behavior: 'smooth' });
    }
    
    document.getElementById('productForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const name = document.getElementById('productName').value;
      const price = parseFloat(document.getElementById('productPrice').value);
      const imageFile = document.getElementById('productImage').files[0];
      
      if (editMode && !imageFile) {
        // If in edit mode and no new image is selected, use the existing image
        const updatedProduct = {
          name,
          price,
          image: products[editProductIndex].image
        };
        
        products[editProductIndex] = updatedProduct;
        localStorage.setItem('products', JSON.stringify(products));
        
        showToast('Product updated successfully');
        resetForm();
        renderProductList();
        return;
      }
      
      if (!imageFile) {
        showToast('Please upload an image', 'error');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = function() {
        const image = reader.result;
        
        if (editMode) {
          // Update existing product
          products[editProductIndex] = { name, price, image };
          showToast('Product updated successfully');
        } else {
          // Add new product
          const newProduct = { name, price, image };
          products.push(newProduct);
          showToast('Product added successfully');
        }
        
        localStorage.setItem('products', JSON.stringify(products));
        resetForm();
        renderProductList();
      };
      
      reader.readAsDataURL(imageFile);
    });
    
    function resetForm() {
      document.getElementById('productForm').reset();
      imagePreview.innerHTML = `<span class="preview-placeholder">Image preview will appear here</span>`;
      document.querySelector('#productForm .btn-primary').textContent = 'Add Product';
      editMode = false;
      editProductIndex = null;
    }
    
    // Initialize the application
    renderProductList();
    window.addEventListener('load', () => {
  if (localStorage.getItem('newOrderNotification') === 'true') {
    alert('📦 New order received!');
    localStorage.setItem('newOrderNotification', 'false');
  }
});

// Change to use a backend (API) for product management

// Fetch products from the database (GET request)
function renderProductList() {
  fetch('products.php')
    .then(response => response.json())
    .then(products => {
      const productList = document.getElementById('productList');
      productList.innerHTML = '';

      if (products.length === 0) {
        productList.innerHTML = "<div class='no-products'>No products added yet</div>";
        return;
      }

      products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
          <div class="product-image">
            <img src="${product.image}" alt="${product.name}">
          </div>
          <div class="product-details">
            <div class="product-name">${product.name}</div>
            <div class="product-price">₱${parseFloat(product.price).toFixed(2)}</div>
            <div class="product-actions">
              <button class="btn btn-sm btn-edit" onclick="editProduct(${product.id})">Edit</button>
              <button class="btn btn-sm btn-delete" onclick="deleteProduct(${product.id})">Delete</button>
            </div>
          </div>
        `;
        productList.appendChild(productCard);
      });
    });
}

// Add or Update Product (POST request)
document.getElementById('productForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const name = document.getElementById('productName').value;
  const price = parseFloat(document.getElementById('productPrice').value);
  const imageFile = document.getElementById('productImage').files[0];
  const formData = new FormData();
  formData.append('name', name);
  formData.append('price', price);

  if (imageFile) {
    formData.append('productImage', imageFile);
  }

  // If editing, append the product ID
  if (editMode && editProductIndex !== null) {
    formData.append('id', products[editProductIndex].id);
  }

  fetch('products.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    showToast(data.message);
    renderProductList();
    resetForm();
  })
  .catch(error => console.error('Error:', error));
});

// Delete Product (DELETE request)
function deleteProduct(id) {
  if (confirm('Are you sure you want to delete this product?')) {
    const formData = new FormData();
    formData.append('id', id);

    fetch('products.php', {
      method: 'DELETE',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      showToast(data.message);
      renderProductList();
    })
    .catch(error => console.error('Error:', error));
  }
}

// Show toast notifications
function showToast(message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => {
      toastContainer.removeChild(toast);
    }, 300);
  }, 3000);
}

// Image preview functionality (same as before)
document.getElementById('productImage').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreview').innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(file);
  }
});

// Initialize the product list on page load
window.addEventListener('load', () => {
  renderProductList();
});




  </script>
</body>
</html> -->

<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="icon" href="Uploads/img/images (2).jpg"/>
  <link rel="stylesheet" href="admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
  <title>Canteen Admin</title>
  <style>
    .notification {
      position: relative;
      margin-right: 15px;
      cursor: pointer;
      padding: 5px;
    }
    .notification .fa {
      font-size: 20px;
    }
    .notification-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background: red;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      display: none;
    }
    .orders-list {
      background: #f9f9f9;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <div class="container">
    <header class="nav-bar">
      <div class="logo">
        <span>🍔</span> FoodHub
      </div>
      <div class="nav-right">
        <div class="message">
          <span><i class="fa fa-comment-o" aria-hidden="true"></i></span>
        </div>
        <div class="notification" id="notificationBell" data-role="notification">
          <i class="fa fa-bell-o" aria-hidden="true"></i>
          <div class="notification-count" id="notificationCount">0</div>
        </div>
        <div class="user-profile">
          <div class="avatar">A</div>
          <span>Admin</span>
        </div>
      </div>
    </header>

    <div class="main-content">
      <div class="product-form-container">
        <h2 class="section-title">Add New Product</h2>
        <form id="productForm">
          <div class="form-group">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" id="productName" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="productPrice" class="form-label">Product Price (₱)</label>
            <input type="number" id="productPrice" class="form-control" step="0.01" min="0" required>
          </div>
          <div class="form-group" id="productImageGroup">
            <label class="form-label">Product Image</label>
            <input type="file" class="file-input" accept="image/png, image/jpeg, image/jpg">
            <div class="image-preview" id="imagePreview"></div>
          </div>
          <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
      </div>

      <div class="product-list-container">
        <h2 class="section-title">Product List</h2>
        <input type="search" id="search" placeholder="🔍Search product">
        <div class="product-grid" id="productList"></div>
      </div>

      <div class="orders-list" id="ordersList" style="display: none;"></div>
      <div class="messages-list" id="messagesList" style="display: none;"></div>
    </div>
  </div>

  <script>
    function loadOrders() {
      fetch('get_orders.php')
        .then(res => {
          if (!res.ok) throw new Error('Failed to fetch orders');
          return res.json();
        })
        .then(orders => {
          const container = document.getElementById('ordersList');
          container.innerHTML = '<h2 class="section-title">Orders</h2>';

          if (orders.length === 0) {
            container.innerHTML += '<p>No orders yet.</p>';
            return;
          }

          orders.forEach(order => {
            const orderDiv = document.createElement('div');
            orderDiv.className = 'order';

            const itemsHtml = order.items.map(item =>
              `<li>${item.product_name} x${item.quantity}</li>`
            ).join('');

            orderDiv.innerHTML = `
              <h3>Order #${order.id}</h3>
              <p><strong>Name:</strong> ${order.customer_name}</p>
              <p><strong>Room:</strong> ${order.room_number}</p>
              <p><strong>Payment:</strong> ${order.payment_method}</p>
              ${order.gcash_number ? `<p><strong>Gcash Number:</strong> ${order.gcash_number}</p>` : ''}
              ${order.gcash_reference ? `<p><strong>Gcash Reference:</strong> ${order.gcash_reference}</p>` : ''}
              <p><strong>Date:</strong> ${order.created_at}</p>
              <ul>${itemsHtml}</ul>
              <hr>
            `;

            container.appendChild(orderDiv);
          });
        })
        .catch(err => {
          console.error('Failed to load orders:', err);
          document.getElementById('ordersList').innerHTML += '<p>Error loading orders.</p>';
        });
    }

    function checkNewOrders() {
      fetch('check_new_orders.php')
        .then(response => {
          if (!response.ok) throw new Error('Failed to check new orders');
          return response.json();
        })
        .then(data => {
          const notification = document.getElementById('notificationCount');
          if (data.new_orders > 0) {
            notification.textContent = data.new_orders;
            notification.style.display = 'block';
          } else {
            notification.style.display = 'none';
            notification.textContent = '0';
          }
        })
        .catch(error => console.error('Error checking orders:', error));
    }

    // Toggle orders list and image input visibility only
    document.addEventListener('click', (event) => {
      const bell = event.target.closest('#notificationBell');
      if (bell) {
        event.preventDefault();
        event.stopPropagation();

        const ordersList = document.getElementById('ordersList');
        const notificationCount = document.getElementById('notificationCount');
        const fileInputGroup = document.getElementById('productImageGroup');

        const isOrdersVisible = ordersList.style.display === 'block';

        ordersList.style.display = isOrdersVisible ? 'none' : 'block';
        fileInputGroup.style.display = isOrdersVisible ? 'block' : 'none';

        if (!isOrdersVisible) loadOrders();

        notificationCount.style.display = 'none';
        notificationCount.textContent = '0';
      }
    });

    window.addEventListener('DOMContentLoaded', () => {
      checkNewOrders();
      setInterval(checkNewOrders, 10000); // Check every 10 seconds
    });
  </script>
</body>
</html>-->

