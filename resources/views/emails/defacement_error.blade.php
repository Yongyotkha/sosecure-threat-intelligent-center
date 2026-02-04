<!DOCTYPE html>
<html>
<head>
    <title>Web Defacement Check Error</title>
</head>
<body>
    <h2>Web Defacement Check Failed</h2>
    <p><strong>Site:</strong> {{ $w->name }}</p>
    <p><strong>URL:</strong> <a href="{{ $w->url }}">{{ $w->url }}</a></p>
    <p><strong>Time:</strong> {{ now()->toDateTimeString() }}</p>
    
    <hr>
    <h3>Error Details:</h3>
    <pre style="background: #f8f9fa; padding: 10px; border: 1px solid #ddd; color: red;">{{ $errorMessage }}</pre>
    
    <p>Please check the system logs or the website availability.</p>
</body>
</html>
