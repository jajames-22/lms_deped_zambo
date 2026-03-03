<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>Verify Your Email Address</h2>

    <p>
        Thanks for registering! Please check your email and click the verification link.
    </p>

    @if (session('message'))
        <p style="color: green;">
            {{ session('message') }}
        </p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">
            Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">
            Logout
        </button>
    </form>

</body>
</html>