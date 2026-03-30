<!DOCTYPE html>
<html>
<head>
    <title>Module Invitation</title>
</head>
<body>
    <h2>Hello!</h2>
    <p>Your teacher has invited you to enroll in a new learning module: <strong>{{ $material->title }}</strong>.</p>
    
    <p>Please click the button below to officially enroll and access the materials.</p>
    
    <a href="{{ $enrollmentUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #a52a2a; color: white; text-decoration: none; border-radius: 5px;">
        Accept Invitation & Enroll
    </a>

    <p>If the button doesn't work, copy and paste this link into your browser:</p>
    <p>{{ $enrollmentUrl }}</p>
</body>
</html>