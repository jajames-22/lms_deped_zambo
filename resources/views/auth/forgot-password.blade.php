<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <input type="email" name="email" placeholder="Enter your email" required>

    @error('email')
        <div>{{ $message }}</div>
    @enderror

    <button type="submit">Send Reset Link</button>
</form>