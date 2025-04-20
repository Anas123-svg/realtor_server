<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Property Evaluation Request</title>
</head>
<body>
    <h1>New Property Evaluation Request</h1>
    <p><strong>Name:</strong> {{ $emailData['userName'] }}</p>
    <p><strong>Email:</strong> {{ $emailData['userEmail'] }}</p>
    <p><strong>Phone:</strong> {{ $emailData['userPhone'] }}</p>
    <p><strong>Country:</strong> {{ $emailData['userCountry'] }}</p>
    <p><strong>Location:</strong> {{ $emailData['location'] }}</p>
    <p><strong>Deal Type:</strong> {{ $emailData['dealType'] }}</p>
    <p><strong>Property Type:</strong> {{ $emailData['propertyType'] }}</p>
    <p><strong>Property Title:</strong> {{ $emailData['propertyTitle'] }}</p>
    <p><strong>Property Size:</strong> {{ $emailData['propertySize'] }}</p>
    <p><strong>Bedrooms:</strong> {{ $emailData['bedrooms'] }}</p>
    <p><strong>Bathrooms:</strong> {{ $emailData['bathrooms'] }}</p>
    
    @if(!empty($emailData['images']))
        <h2>Property Images:</h2>
        <ul>
            @foreach($emailData['images'] as $image)
                <li><img src="{{ $image }}" alt="Property Image" style="width: 300px; height: auto;"/></li>
            @endforeach
        </ul>
    @endif

</body>
</html>
