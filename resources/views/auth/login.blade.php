<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #f5f7fa;">

<div class="container vh-100 d-flex align-items-center justify-content-center">

    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">

        <h4 class="mb-3 text-center fw-bold">Admin Login</h4>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                Login
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('welcome') }}" class="text-muted small">
                    ← Back to Home
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>