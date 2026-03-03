<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>

    @if (session('status'))
        <div>{{ session('status') }}</div>
    @endif

    @error('email')
        <div>{{ $message }}</div>
    @enderror
</form>