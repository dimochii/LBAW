@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('register') }}">
    {{ csrf_field() }}

    <!-- Name Field -->
    <label for="name">Name</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
    @if ($errors->has('name'))
      <span class="error">
          {{ $errors->first('name') }}
      </span>
    @endif

    <!-- Username Field -->
    <label for="username">Username</label>
    <input id="username" type="text" name="username" value="{{ old('username') }}" required>
    @if ($errors->has('username'))
      <span class="error">
          {{ $errors->first('username') }}
      </span>
    @endif

    <!-- Email Field -->
    <label for="email">E-Mail Address</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    @if ($errors->has('email'))
      <span class="error">
          {{ $errors->first('email') }}
      </span>
    @endif

    <!-- Password Field -->
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @if ($errors->has('password'))
      <span class="error">
          {{ $errors->first('password') }}
      </span>
    @endif

    <!-- Confirm Password Field -->
    <label for="password-confirm">Confirm Password</label>
    <input id="password-confirm" type="password" name="password_confirmation" required>

    <!-- Birth Date Field -->
    <label for="birth_date">Birth Date</label>
    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required>
    @if ($errors->has('birth_date'))
      <span class="error">
          {{ $errors->first('birth_date') }}
      </span>
    @endif

    <!-- Submit Button -->
    <button type="submit">
      Register
    </button>

    <!-- Redirect to Login -->
    <a class="button button-outline" href="{{ route('login') }}">Login</a>
</form>
@endsection
