<!-- This page is for viewing and editing user profile information. -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Profile - BookStore</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #556a7eff 0%, #556a7eff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Navbar */
        .admin-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
            color: #fff !important;
        }

        .navbar-nav .nav-link,
        .navbar-nav .dropdown-item {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar-nav .dropdown-menu {
            background: #34495e;
        }

        .navbar-nav .dropdown-item:hover {
            background: #667eea;
            color: #fff !important;
        }
        /* End navbar */
        .profile-card {
            border: none;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            margin: 40px auto;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .profile-info h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .profile-info p {
            color: #495057;
            margin-bottom: 0.25rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #495057;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-book me-2"></i>BookStore
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto"></ul>
                <ul class="navbar-nav">
                    <li class="nav-item me-2 align-self-center">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-wallet me-1"></i>
                            ${{ number_format(Auth::user()->wallet ?? 0, 2) }}
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user-edit me-2"></i>My Profile</a></li>
                            @if(Auth::user() && Auth::user()->role === 'user')
                                <li><a class="dropdown-item" href="{{ route('books.index') }}"><i class="fas fa-book me-2"></i>View My Books</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i>User Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <div class="profile-card text-center">
            <img src="{{ Auth::user()->avatar ?? 'https://via.placeholder.com/100?text=Avatar' }}" alt="Avatar" class="profile-avatar">
            <div class="profile-info">
                <h2>{{ Auth::user()->name }}</h2>
                <p><i class="fas fa-envelope me-2"></i>{{ Auth::user()->email }}</p>
                <p><i class="fas fa-calendar-alt me-2"></i>Joined: {{ Auth::user()->created_at->format('F d, Y') }}</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary mt-3"><i class="fas fa-edit me-1"></i>Edit Profile</a>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
