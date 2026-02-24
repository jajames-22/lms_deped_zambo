<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>

    <h2>Login</h2>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p style="color:red;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email">
        <br><br>

        <input type="password" name="password" placeholder="Password">
        <br><br>

        <button type="submit">Login</button>
    </form>

    <a href="/register">Create account</a>

</body>

</html>