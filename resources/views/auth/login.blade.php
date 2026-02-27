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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input type="email" name="email" value="{{ old('email') }}">
        <br></br>
        @error('email')
            <div>{{ $message }}</div>
        @enderror

        <br>
        <input type="password" name="password">
        <br>
        @error('password')
            <div>{{ $message }}</div>
        @enderror

        <br>
        <input type="checkbox" name="remember"> Remember me
        <br>
        <button type="submit">Login</button>
    </form>

    <a href="/register">Create account</a>

</body>

</html>