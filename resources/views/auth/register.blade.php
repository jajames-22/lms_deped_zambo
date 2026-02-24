<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
</head>

<body>

    <h2>Register</h2>

    <form method="POST" action="/register">
        @csrf

        <label for="first_name">First Name: </label>
        <input type="text" name="first_name" placeholder="Ex. Juan" value="{{ old('first_name') }}">
        <br>
        @error('first_name')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        <label for="middle_name">Middle Name: </label>
        <input type="text" name="middle_name" placeholder="Ex. Perez" value="{{ old('middle_name') }}">
        <br>
        @error('middle_name')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        <label for="last_name">Last Name: </label>
        <input type="text" name="last_name" placeholder="Ex. Dela Cruz" value="{{ old('last_name') }}">
        <br>
        @error('last_name')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        <label for="suffix">Suffix: </label>
        <input type="text" name="suffix" placeholder="Ex. (optional) Jr."value="{{ old('suffix') }}">
        <br>
        @error('suffix')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        
        <label for="email">Email: </label>
        <input type="email" name="email" placeholder="Ex. xxx@deped.gov.ph" value="{{ old('email') }}">
        <br>
        @error('email')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        
        <label for="password">Password: </label>
        <input type="password" name="password" placeholder="Password"  value="{{ old('password') }}">
        <br>
        @error('password')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>


        <label for="password_confirmation">Confirm Password: </label>
        <input type="password" name="password_confirmation" placeholder="Re-enter your Password" value="{{ old('password_confirmation') }}">
        <br>
        @error('password_confirmation')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        <br>

        <button type="submit">Register</button>
    </form>

    <a href="/login">Already have account? Login</a>

</body>

</html>