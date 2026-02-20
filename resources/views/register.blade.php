<!DOCTYPE html>
<html>
<head>
    <title>Register Account</title>
</head>
<body>

    <h2>Register Account</h2>
<!-- 
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif -->

    <form action="/students/register" method="POST">
        @csrf

        <label>First Name:</label><br>
        <input type="text" name="firstName" value="{{ old('firstName') }}"><br><br>
        @error('firstName')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Last Name:</label><br>
        <input type="text" name="lastName" value="{{ old('lastName') }}"><br><br>
        @error('lastName')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>
        @error('email')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        
        <label>Password:</label><br>
        <input type="password" name="password" value="{{ old('password') }}" ><br><br>
        @error('password')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        
        <label>Confirm Password:</label><br>
        <input type="password" name="conPass" value="{{ old('conPass') }}"><br><br>
        @error('conPass')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Submit</button>
    </form>

    
<h2>registers List</h2>

<table>
    <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Created At</th>
    </tr>

    @foreach($registers as $register)
    <tr>
        <td>{{ $register->id }}</td>
        <td>{{ $register->firstName }}</td>
        <td>{{ $register->lastName }}</td>
        <td>{{ $register->email }}</td>
        <td>{{ $register->password }}</td>
        <td>{{ $register->created_at }}</td>
    </tr>
    @endforeach

</table>

</body>
</html>