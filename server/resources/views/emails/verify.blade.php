<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Email</title>
</head>

<body style="background-color: black;
             padding:20px 10px 20px 15px">
    <h1 style="color:white">{{ $details['title'] }}</h1>
    <br>
    <p style="color:blueviolet; font-family:'Times New Roman', Times, serif">{{ $details['body'] }}</p>
    <br>
    <center><a href="{{ $details['verification_url'] }}"><button class="button-20" role="button"
                style=" appearance: button;
                background-color: #4D4AE8;
                background-image: linear-gradient(180deg, rgba(255, 255, 255, .15), rgba(255, 255, 255, 0));
                border: 1px solid #4D4AE8;
                border-radius: 1rem;
                box-shadow: rgba(255, 255, 255, 0.15) 0 1px 0 inset,rgba(46, 54, 80, 0.075) 0 1px 1px;
                box-sizing: border-box;
                color: #FFFFFF;
                cursor: pointer;
                display: inline-block;
                font-family: Inter,sans-serif;
                font-size: 1rem;
                font-weight: 500;
                line-height: 1.5;
                margin: 0;
                padding: .5rem 1rem;
                text-align: center;
                text-transform: none;
                transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                user-select: none;
                -webkit-user-select: none;
                touch-action: manipulation;
                vertical-align: middle;">Click
                here!</button></a></center>
</body>

</html>
