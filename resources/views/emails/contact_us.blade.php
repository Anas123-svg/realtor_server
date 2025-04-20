<!DOCTYPE html>
<html>
<head>
    <title>Contact Message</title>
</head>
<body>
    <h2>New Contact Message</h2>
    <p><strong>Name:</strong> {{ $userName }}</p>
    <p><strong>Email:</strong> {{ $userEmail }}</p>
    <p><strong>Phone:</strong> {{ $userPhone }}</p>
    <p><strong>Country:</strong> {{ $userCountry }}</p>
    <p><strong>City:</strong> {{ $userCity }}</p>
    <p><strong>Message:</strong> {{ $userMessage }}</p>
    @if($reason)
        <p><strong>Reason:</strong> {{ $reason }}</p>
    @endif
</body>
</html>
