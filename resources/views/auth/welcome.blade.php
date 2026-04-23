<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Scheduling System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .hero {
            height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
                url('{{ asset("images/school.jpg") }}') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }

        .hero-content {
            max-width: 750px;
            padding: 20px;
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .hero-desc {
            font-size: 1.1rem;
            line-height: 1.8;
            color: rgba(255,255,255,0.9);
            margin-bottom: 35px;
        }

        .login-btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 40px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: 0.25s ease;
        }

        .login-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            color: #fff;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
            }

            .hero-desc {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>

<div class="hero">
    <div class="hero-content">

        <div class="hero-title">
            Class Scheduling System
        </div>

        <div class="hero-desc">
            A web-based prototype designed to manage Senior High School scheduling,
            including sections, teachers, subjects, and timetable generation for
            Grade 11 and Grade 12 with a more organized and efficient academic workflow.
        </div>

        <a href="{{ route('login') }}" class="login-btn">
            Admin Login
        </a>

    </div>
</div>

</body>
</html>