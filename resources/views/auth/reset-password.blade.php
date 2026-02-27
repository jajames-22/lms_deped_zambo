<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <input type="email" name="email" value="{{ $email }}" required>

    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" required>

    @error('password')
        <div>{{ $message }}</div>
    @enderror

    <button type="submit">Reset Password</button>
</form>