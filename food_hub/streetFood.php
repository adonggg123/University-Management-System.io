<!--<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FoodHub</title>
  <link rel="icon" href="img/images (2).jpg"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
  <link rel="stylesheet" href="srtFood.css"/>
  <style>
     .checkoutForm {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .checkoutForm form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
    }

    .checkoutForm form input,
    .checkoutForm form select,
    .checkoutForm form button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
    }

    .checkoutForm .submit {
      background-color: #E8BC0E;
      color: #000;
    }
/*
    .purchaseHistory {
      display: none;
      padding: 20px;
      background: #f9f9f9;
    }

    .purchaseHistory div {
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 8px;
      background: white;
    } */
  </style>
</head>
<body>
  <header>
    <div class="title"><a href="index.php">FoodHub</a></div>
    <input type="search" placeholder="Search" id="search"/>
    <i class="fa fa-search" aria-hidden="true"></i>
    <div class="icon-cart">
      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 15a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0h8m-8 0-1-4m9 4a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-9-4h10l2-7H3m2 7L3 4m0 0-.792-3H1"/>
      </svg>
      <span>0</span>
    </div>
  </header>

  <div class="container">
    <h1>Products</h1>
    <div class="listProduct"></div>
  </div>

  <div class="cartTab">
    <h1>Shopping Cart</h1>
    <div class="listCart"></div>
    <div class="btn">
      <button class="close">CLOSE</button>
      <button class="checkOut">Check Out</button>
    </div>
  </div>

 <div class="checkoutForm">
    <form id="checkoutForm">
      <h2>Checkout</h2>
      <input type="text" name="customer_name" placeholder="Your Name" required>
      <input type="text" name="room_number" placeholder="Room Number" required>
      <select name="payment_method" required>
        <option value="">Select Payment Method</option>
        <option value="cash">Cash</option>
        <option value="gcash">Gcash</option>
        <option value="cod">Cash on Delivery</option>
      </select>
      <button type="submit" class="submit">Order</button>
      <button type="button" id="cancelCheckout">Cancel</button>
    </form>
  </div>

   <div class="receipt" style="display: none; padding: 20px; border: 1px solid #ccc; background: #fff;">
    <h2>Receipt</h2>
    <p id="receiptName"></p>
    <p id="receiptRoom"></p>
    <p id="receiptPayment"></p>
    <h3>Items:</h3>
    <ul id="receiptItems"></ul>
    <p id="receiptTotal"></p>
    <button onclick="window.print()">Print Receipt</button>
  </div>

  <div class="purchaseHistory">
    <h2>My Purchases</h2>
    <div id="purchaseList"></div>
  </div>

  <script>
    let listProductHTML = document.querySelector('.listProduct');
    let listCartHTML = document.querySelector('.listCart');
    let iconCart = document.querySelector('.icon-cart');
    let iconCartSpan = document.querySelector('.icon-cart span');
    let body = document.querySelector('body');
    let closeCart = document.querySelector('.close');
    let products = [];
    let cart = [];
    const searchInput = document.getElementById('search');

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
                    <div class="price">&#8369;${product.price}</div>
                    <button class="addCart">Add To Cart</button>
                `;
                productDiv.querySelector('.addCart').addEventListener('click', () => addToCart(product.id));
                listProductHTML.appendChild(productDiv);
            });
        } else {
            listProductHTML.innerHTML = "<p>No products found.</p>";
        }
    }

    iconCart.addEventListener('click', () => {
        body.classList.toggle('showCart');
    });

    closeCart.addEventListener('click', () => {
        body.classList.toggle('showCart');
    });

    const addDataToHTML = () => {
        listProductHTML.innerHTML = '';
        if (products.length > 0) {
            products.forEach(product => {
                let newProduct = document.createElement('div');
                newProduct.dataset.id = product.id || Date.now(); // fallback id
                newProduct.classList.add('item');
                newProduct.innerHTML = `
                    <img src="${product.image}" alt="">
                    <h2>${product.name}</h2>
                    <div class="price">₱${product.price}</div>
                    <button class="addCart">Add To Cart</button>`;
                listProductHTML.appendChild(newProduct);
            });
        }
    }

    listProductHTML.addEventListener('click', (event) => {
        let positionClick = event.target;
        if (positionClick.classList.contains('addCart')) {
            let id_product = positionClick.parentElement.dataset.id;
            addToCart(id_product);
        }
    });

    const addToCart = (product_id) => {
        let positionThisProductInCart = cart.findIndex((value) => value.product_id == product_id);
        if (cart.length <= 0) {
            cart = [{ product_id: product_id, quantity: 1 }];
        } else if (positionThisProductInCart < 0) {
            cart.push({ product_id: product_id, quantity: 1 });
        } else {
            cart[positionThisProductInCart].quantity += 1;
        }
        addCartToHTML();
        addCartToMemory();
    }

    const addCartToMemory = () => {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    const addCartToHTML = () => {
        listCartHTML.innerHTML = '';
        let totalQuantity = 0;
        if (cart.length > 0) {
            cart.forEach(item => {
                totalQuantity += item.quantity;
                let newItem = document.createElement('div');
                newItem.classList.add('item');
                newItem.dataset.id = item.product_id;

                let positionProduct = products.findIndex((value) => value.id == item.product_id || value.id == parseInt(item.product_id));
                let info = products[positionProduct];
                if (!info) return;

                newItem.innerHTML = `
                    <div class="image">
                        <img src="${info.image}">
                    </div>
                    <div class="name">${info.name}</div>
                    <div class="totalPrice">₱${info.price * item.quantity}</div>
                    <div class="quantity">
                        <span class="minus"><</span>
                        <span>${item.quantity}</span>
                        <span class="plus">></span>
                    </div>`;
                listCartHTML.appendChild(newItem);
            });
        }
        iconCartSpan.innerText = totalQuantity;
    }

    listCartHTML.addEventListener('click', (event) => {
        let positionClick = event.target;
        if (positionClick.classList.contains('minus') || positionClick.classList.contains('plus')) {
            let product_id = positionClick.parentElement.parentElement.dataset.id;
            let type = positionClick.classList.contains('plus') ? 'plus' : 'minus';
            changeQuantityCart(product_id, type);
        }
    });

    const changeQuantityCart = (product_id, type) => {
        let positionItemInCart = cart.findIndex((value) => value.product_id == product_id);
        if (positionItemInCart >= 0) {
            if (type === 'plus') {
                cart[positionItemInCart].quantity++;
            } else {
                cart[positionItemInCart].quantity--;
                if (cart[positionItemInCart].quantity <= 0) {
                    cart.splice(positionItemInCart, 1);
                }
            }
        }
        addCartToHTML();
        addCartToMemory();
    }

    const initApp = () => {
        // Load cart from localStorage (do not reset on reload)
        const storedCart = localStorage.getItem('cart');
        if (storedCart) {
            cart = JSON.parse(storedCart);
            addCartToHTML();
        }

        // Load products from localStorage
        const storedProducts = localStorage.getItem('products');
        if (storedProducts) {
            products = JSON.parse(storedProducts);
            products = products.map((p, i) => ({ ...p, id: p.id || (i + 1) }));
            addDataToHTML();
        } else {
            console.warn('No products found in localStorage.');
        }
    }

    initApp();
</script>

</body>
</html>-->
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$mysqli = new mysqli('localhost', 'root', 'quest4inno@server', 'university_management_system');

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$query = "SELECT id, name, price, image FROM products_fh";
$result = $mysqli->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $mysqli->error]);
    exit;
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
//echo json_encode($products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FoodHub</title>
  <link rel="icon" href="Uploads/img/images (2).jpg"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"/>
  <link rel="stylesheet" href="srtFood.css"/>
  <style>
    .checkoutForm {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      background-color: rgba(0, 0, 0, 0.5);
    }
    .checkoutForm form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
    }
    .checkoutForm form input,
    .checkoutForm form select,
    .checkoutForm form button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
    }
    .checkoutForm .submit {
      background-color: #E8BC0E;
      color: #000;
    }
  </style>
</head>
<body>
  <header>
    <div class="title"><a href="index.php">FoodHub</a></div>
    <input type="search" placeholder="Search" id="search"/>
    <i class="fa fa-search" aria-hidden="true"></i>
    <div class="icon-cart">
      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 15a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0h8m-8 0-1-4m9 4a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-9-4h10l2-7H3m2 7L3 4m0 0-.792-3H1"/>
      </svg>
      <span>0</span>
    </div>
  </header>

  <div class="container">
    <h1>Products</h1>
    <div class="listProduct"></div>
  </div>

  <div class="cartTab">
    <h1>Shopping Cart</h1>
    <div class="listCart"></div>
    <div class="btn">
      <button class="close">CLOSE</button>
      <button class="checkOut">Check Out</button>
    </div>
  </div>

  <div class="checkoutForm">
    <form id="checkoutForm">
      <h2>Checkout</h2>
      <input type="text" name="customer_name" placeholder="Your Name" required>
      <input type="text" name="room_number" placeholder="Room Number" required>
      <select name="payment_method" required>
        <option value="">Select Payment Method</option>
        <option value="gcash">Gcash</option>
        <option value="cod">Cash on Delivery</option>
      </select>
      <div id="gcashDetails" style="display: none;">
        <input type="text" name="gcash_number" placeholder="Gcash Number" pattern="[0-9]{11}" title="Enter 11-digit Gcash number"/>
        <input type="text" name="gcash_reference" placeholder="Reference Number"/>
      </div>
      <div id="orderSummary" style="margin: 1rem 0;">
        <div id="productSummaryList"></div>
        <p><strong>Total Items:</strong> <span id="summaryQuantity">0</span></p>
        <p><strong>Total Price:</strong> ₱<span id="summaryPrice">0.00</span></p>
      </div>
      <button type="submit" class="submit">Order</button>
      <button type="button" id="cancelCheckout">Cancel</button>
    </form>
  </div>

  <script>
    let listProductHTML = document.querySelector('.listProduct');
    let listCartHTML = document.querySelector('.listCart');
    let iconCart = document.querySelector('.icon-cart');
    let iconCartSpan = document.querySelector('.icon-cart span');
    let body = document.querySelector('body');
    let closeCart = document.querySelector('.close');
    let products = [];
    let cart = [];
    const searchInput = document.getElementById('search');
    const checkoutFormContainer = document.querySelector('.checkoutForm');
    const checkOutButton = document.querySelector('.checkOut');
    const cancelCheckoutButton = document.getElementById('cancelCheckout');
    const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
    const gcashDetails = document.getElementById('gcashDetails');

    checkOutButton.addEventListener('click', () => {
      updateCheckoutSummary();
      checkoutFormContainer.style.display = 'flex';
    });

    cancelCheckoutButton.addEventListener('click', () => {
      checkoutFormContainer.style.display = 'none';
    });

    paymentMethodSelect.addEventListener('change', () => {
      if (paymentMethodSelect.value === 'gcash') {
        gcashDetails.style.display = 'block';
        gcashDetails.querySelector('input[name="gcash_number"]').required = true;
        gcashDetails.querySelector('input[name="gcash_reference"]').required = true;
      } else {
        gcashDetails.style.display = 'none';
        gcashDetails.querySelector('input[name="gcash_number"]').required = false;
        gcashDetails.querySelector('input[name="gcash_reference"]').required = false;
      }
    });

    document.getElementById('checkoutForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const order = {
        customer_name: formData.get('customer_name'),
        room_number: formData.get('room_number'),
        payment_method: formData.get('payment_method'),
        gcash_number: formData.get('gcash_number') || null,
        gcash_reference: formData.get('gcash_reference') || null,
        items: cart.map(item => ({
          product_id: item.product_id,
          quantity: item.quantity
        }))
      };

      fetch('submit_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(order)
      })
      .then(res => {
        if (!res.ok) throw new Error('Failed to submit order');
        return res.text();
      })
      .then(message => {
        alert(message);
        this.reset();
        checkoutFormContainer.style.display = 'none';
        cart = [];
        localStorage.removeItem('cart');
        addCartToHTML();
      })
      .catch(error => {
        console.error('Error saving order:', error);
        alert("Failed to submit order. Please try again.");
      });
    });

    function updateCheckoutSummary() {
      let totalItems = 0;
      let totalPrice = 0;
      const summaryList = document.getElementById('productSummaryList');
      summaryList.innerHTML = '';

      cart.forEach(item => {
        const product = products.find(p => p.id == item.product_id || p.id == parseInt(item.product_id));
        if (product) {
          totalItems += item.quantity;
          totalPrice += product.price * item.quantity;

          const itemDiv = document.createElement('div');
          itemDiv.style.marginBottom = '5px';
          itemDiv.innerHTML = `${product.name} × ${item.quantity} = ₱${(product.price * item.quantity).toFixed(2)}`;
          summaryList.appendChild(itemDiv);
        }
      });

      document.getElementById('summaryQuantity').innerText = totalItems;
      document.getElementById('summaryPrice').innerText = totalPrice.toFixed(2);
    }

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
            <img src="Uploads/${product.image}" alt="${product.name}">
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

    iconCart.addEventListener('click', () => {
      body.classList.toggle('showCart');
    });

    closeCart.addEventListener('click', () => {
      body.classList.toggle('showCart');
    });

    const addDataToHTML = () => {
      displayFilteredProducts(products);
    }

    const addToCart = (product_id) => {
      let positionThisProductInCart = cart.findIndex((value) => value.product_id == product_id);
      if (cart.length <= 0) {
        cart = [{ product_id: product_id, quantity: 1 }];
      } else if (positionThisProductInCart < 0) {
        cart.push({ product_id: product_id, quantity: 1 });
      } else {
        cart[positionThisProductInCart].quantity += 1;
      }
      addCartToHTML();
      addCartToMemory();
    }

    const addCartToMemory = () => {
      localStorage.setItem('cart', JSON.stringify(cart));
    }

    const addCartToHTML = () => {
      listCartHTML.innerHTML = '';
      let totalQuantity = 0;
      if (cart.length > 0) {
        cart.forEach(item => {
          totalQuantity += item.quantity;
          let newItem = document.createElement('div');
          newItem.classList.add('item');
          newItem.dataset.id = item.product_id;

          let positionProduct = products.findIndex((value) => value.id == item.product_id || value.id == parseInt(item.product_id));
          let info = products[positionProduct];
          if (!info) return;

          newItem.innerHTML = `
            <div class="image"><img src="Uploads/${info.image}">
</div>
            <div class="name">${info.name}</div>
            <div class="totalPrice">₱${(info.price * item.quantity).toFixed(2)}</div>
            <div class="quantity">
              <span class="minus"><</span>
              <span>${item.quantity}</span>
              <span class="plus">></span>
            </div>
          `;
          listCartHTML.appendChild(newItem);
        });
      }
      iconCartSpan.innerText = totalQuantity;
    }

    listCartHTML.addEventListener('click', (event) => {
      let positionClick = event.target;
      if (positionClick.classList.contains('minus') || positionClick.classList.contains('plus')) {
        let product_id = positionClick.parentElement.parentElement.dataset.id;
        let type = positionClick.classList.contains('plus') ? 'plus' : 'minus';
        changeQuantityCart(product_id, type);
      }
    });

    const changeQuantityCart = (product_id, type) => {
      let positionItemInCart = cart.findIndex((value) => value.product_id == product_id);
      if (positionItemInCart >= 0) {
        if (type === 'plus') {
          cart[positionItemInCart].quantity++;
        } else {
          cart[positionItemInCart].quantity--;
          if (cart[positionItemInCart].quantity <= 0) {
            cart.splice(positionItemInCart, 1);
          }
        }
      }
      addCartToHTML();
      addCartToMemory();
    }

    const initApp = () => {
      fetch('get_products.php')
        .then(response => {
          if (!response.ok) throw new Error('Failed to fetch products');
          return response.json();
        })
        .then(data => {
          products = data.map((product, i) => ({
            ...product,
            price: parseFloat(product.price),
            id: product.id || (i + 1)
          }));
          addDataToHTML();
        })
        .catch(error => {
          console.error('Failed to fetch products:', error);
          alert("Failed to load products.");
        });

      const storedCart = localStorage.getItem('cart');
      if (storedCart) {
        cart = JSON.parse(storedCart);
      } else {
        cart = [];
      }

      addCartToHTML();
    }

    initApp();

    fetch('get_products.php')
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('product-container');
      container.innerHTML = '';
      data.forEach(product => {
        container.innerHTML += `
          <div class="product">
            <img src="${product.image}" alt="${product.name}" width="100">
            <h3>${product.name}</h3>
            <p>₱${product.price}</p>
          </div>
        `;
      });
    })
    .catch(error => {
      console.error('Failed to load products.', error);
    });
  </script>
</body>
</html>


