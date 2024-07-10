<!DOCTYPE html>
<html>
<head>
    <title>Student Challan Generated</title>
</head>
<body style="background-color: black;
            padding:20px 10px 20px 15px">
    <h1 style="color:white">{{ $details['title'] }}</h1>
    <br>
    <p style="color:blueviolet; font-family:'Times New Roman', Times, serif">{{ $details['body'] }}</p>
    <br>
    <p>Ammount = {{$details['Fee']}}</p>
</body>
</html>
