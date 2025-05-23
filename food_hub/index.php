<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/img/images (2).jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <title>FoodHub</title>
    <style>
   
    #hours{
        display: flex;
        gap: 50px;
        align-items: center;
    }
    .hours-container {
      max-width: 700px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      text-align: center;
      width: 600px;
      margin-left: 110px;
    }

    .hours-container h1 {
      color: #e67e22;
      font-size: 2.5em;
      margin-bottom: 10px;
    }

    p.description {
      font-size: 1.1em;
      color: #555;
      margin-bottom: 30px;
      width: 600px;
    }

    .schedule {
      text-align: left;
      font-size: 1.1em;
      line-height: 1.8;
    }

    .day {
      display: flex;
      justify-content: space-between;
      border-bottom: 3px dashed #eee;
      padding: 8px 0;
    }

    @media (max-width: 600px) {
      .day {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
    <section id="home">
       
        <div class="container">
            <div class="nav-bar">
                
                <h1>🍔Foodhub</h1>
                <nav>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#About">About</a></li>
                        <!--<li><a href="#category">Category</a></li>-->
                        <li><a href="#hours">Open hours</a></li>
                        <li><a href="#menu">Product</a></li>
                        <li><a href="purchase-history.php">Purchase History</a></li>
                        
                        <!--<li><a href="/streetFood.html"><img src="img2/cart-44-256.png"></a></li>-->
                        <li><a class="btn-contct" href="#contact">Contact Us</a> </li>
                    </ul>
                </nav>
                
            </div>
            <div class="row">
                <div class="col">
                    <!--<h1>Foodhub</h1>-->
                    <span>Make your day great with our special food!<br> <p>Welcome to our canteen where every bite tells a story and every bite sparks a joy </p></span>
                   
                    
                       <div class="btn">
                        <div class="order">
                            <button><a href="streetFood.php">Order Now → <!--<i class="fa fa-cart-plus" aria-hidden="true">--></i></a></button>
                        </div>
                        <div class="contact">
                            
                        </div>
                       </div>
                    
                </div>
               <!-- <div class="img">
                    <img src="img/filipino-street-food-kwek-kwek1.jpg">
                </div>-->
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
                    
                <img src="uploads/img2/canteen.jpg">
    
            </div>
        </div>
        
    </seection>
    <section id="hours">
    <div class="hours-left">
        <div class="hours-container">
            <h1>Open Hours</h1>
            

            <div class="schedule">
            <div class="day">
                <span>Monday – Friday</span>
                <span>7:00 AM – 5:00 PM</span>
            </div>
            <div class="day">
                <span>Saturday</span>
                <span>8:00 AM – 5:00 PM</span>
            </div>
            <div class="day">
                <span>Sunday</span>
                <span>Closed</span>
            </div>
            </div>
    </div>
    </div>
 <div class="hours-right">
 <p class="description">
      Welcome to FoodHub's Open Hours! We're here to serve delicious meals and snacks throughout the day. Check our schedule beside so you never miss a bite!
    </p>
 </div>
    </section>
    <section id="menu">
        <div class="menu-container">
            <h1>Our Product</h1>
            <!--<input type="search" id="search" placeholder="🔍Search product">-->
            <ul class="menu-content">
                <li class="item">
                    <img src="uploads/burger.png" alt="Burger">
                    <div class="description">
                        <h4>Burger</h4>
                        <p>Burger is a savory sandwich made with a meat patty, fresh veggies, and sauces, served in a soft bun.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                
                <li class="item">
                    <img src="uploads/img/soft.png" alt="Soft drinks">
                    <div class="description">
                        <h4>Soft Drinks</h4>
                        <p>Soft drinks are carbonated, sweetened beverages available in various flavors, perfect for a refreshing treat.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/20347187.png" alt="Kikiam">
                    <div class="description">
                        <h4>Kikiam</h4>
                       <p>Kikiam is a popular Filipino street food made of seasoned meat or fish paste wrapped in bean curd skin, then deep-fried until crispy.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/images (1).png" alt="Fish flat">
                    <div class="description">
                        <h4>Fishflat</h4>
                        <p>Fish flat is a Filipino street food made of seasoned fish paste shaped flat, then deep-fried until golden and crispy.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/nuggets.png" alt="Nuggets">
                    <div class="description">
                        <h4>Nuggets</h4>
                        <p>Nuggets are bite-sized pieces of seasoned chicken, coated in breading and deep-fried, popular as a tasty and easy street food snack.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/shomai.png" alt="Shomai">
                    <div class="description">
                        <h4>Fried / Steam Siomai</h4>
                        <p>Fried/Steam Siomai is a Filipino street food of tasty pork or shrimp dumplings, either steamed or deep-fried, and served with soy sauce or chili sauce.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/tempura.png" alt="tempura">
                    <div class="description">
                        <h4>Tempura</h4>
                      <p>Tempura is a popular street food made of fish, shrimp, or vegetables coated in light batter and deep-fried until crispy and golden.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <li class="item">
                    <img src="uploads/img/squid-roll.png" alt="Squid roll">
                    <div class="description">
                        <h4>Squidroll</h4>
                        <p>Squid roll is a street food snack made of seasoned squid paste rolled in batter and deep-fried until crispy and golden.</p>
                        <span>&#8369; 10</span>
                    </div>
                </li>
                <!--<li class="item"> 
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
                <li class="item">
                    <img src="img/hotdog-balls.png" alt="Sky flakes">
                    <div class="description">
                        <h4>Sky flakes</h4>
                        <p>Hotdog balls are bite-sized hotdogs coated in batter and deep-fried until crispy, perfect for snacks or dipping.</p>
                        <span>&#8369; 10</span>
                        
                    </div>
                </li>-->
                
            </ul>
            <button class="view"><a href="product.php">View More →</a></button>
        </div>
    </section>
    <section id="contact">
        <div class="box-container">
            <!-- <form action="https://api.web3forms.com/submit" method="POST" class="contact-left" > -->
             <!-- <form action="contact.php" method="POST" class="contact-left">    -->
              <form action="https://api.web3forms.com/submit" action="contact.php" method="POST" class="contact-left">  
                    <div class="title"><h1>Contact Us</h1><hr></div>
                    <input type="hidden" name="access_key" value="767d8709-aba6-424a-ab34-009f6b75ec72">
                    <input type="text" name="name" placeholder="Your name" required class="input"><br>
                    <input type="email" name="email" placeholder="Your email" required class="input"><br>
                    <textarea name="message"placeholder="Your message" required class="input"></textarea><br>
                    <button type="submit">Send Message<i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
               
                
            </form>
            <div class="contact-right">
                <img src="uploads/img/74a6b3f3514e6b37aa1baf5b8d42c493.png">
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
    <script>
    const searchInput = document.getElementById("search");
    const menuItems = document.querySelectorAll(".menu-content .item");

    searchInput.addEventListener("input", function () {
        const query = this.value.toLowerCase();

        menuItems.forEach(function (item) {
            const itemName = item.querySelector("h4").textContent.toLowerCase();
            if (itemName.includes(query)) {
                item.style.display = "flex"; // or "block" depending on your layout
            } else {
                item.style.display = "none";
            }
        });
    });
</script>

</body>
</html>