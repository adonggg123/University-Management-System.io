<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/images (2).png">
    <title>FoodHub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cedarville+Cursive&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Miniver&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Cedarville+Cursive&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Miniver&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');
            *{
        padding: 0;
        margin: 0;
        } 

        body{
            background: url(img2/negger.jpg);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 100vh;
            font-family: 'Poppins';
    
        }
        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
        }
        h1{
        margin-bottom: 20px;
        text-align: center;
        color: #fb8500;
        background-color: #0b1215;
        height: 30%;
        place-content: center;
        }
        a
        {
            text-decoration: none;
            color: #fb8500;
           
        }
        .card{
        border: 2px solid #0b1215;
        width: 400px;
        height: 300px;
        border-radius: 3px;
        color: #f6f6f6;
        box-shadow:0px 8px 10px rgba(0,0,0,0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        }
        .card .time
        {
            padding: 9px;
            align-items: center;
            display: flex;
            line-height: 2em;
        }
        .card .time span
        {
            text-align: end;
        }
        .card .time p 
        {
            align-items: center;
        }
        .header{
        margin-top: 1px;
        }
        .text {
        background:#0b1215 ;
        min-height: 30px;
        width: 900px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            
            <div class="card">
               <h1><a href="/index.html">Open Hours</a></h1>
               <div class="time">
                <div class="weeks">
                    <p>Mon-Fri &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 07.00am-05.00pm</p>
                    <p>Saturday &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 07.00am-05.00pm</p>
                    <p>Sunday</p>
                </div>
               
              
               </div>
            </div>
        </div>
    </div>
</body>
</html>