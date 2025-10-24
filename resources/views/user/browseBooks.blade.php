<!-- This is for see all the available books for the user. -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Browse Books - BookStore</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* --- Global Styles --- */
        /* Navbar styling */
        .admin-navbar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
            font-size: 1.25rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa !important;

        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }


        body {
            background: linear-gradient(135deg, #556a7eff 0%, #556a7eff 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Book card container */
        .book-card {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.08);
        }

        /* Book image wrapper for consistent aspect ratio */
        .book-image-wrap {
            position: relative;
            width: 100%;
            /* Use 11:16 ratio to match common 220x320 placeholder */
            aspect-ratio: 11 / 16;
            background-color: #f8f9fa;
            overflow: hidden;
        }
        /* Fallback for browsers without aspect-ratio support */
        .book-image-wrap::before {
            content: "";
            display: block;
            padding-top: 145.45%; /* 16/11 * 100% */
        }
        .book-image-wrap img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Book title */
        .book-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Book author */
        .book-author {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        /* Book genre */
        .book-genre {
            font-size: 0.85rem;
            color: #3b82f6;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        /* Book condition */
        .book-condition {
            font-size: 0.85rem;
            color: #374151;
            margin-bottom: 0.25rem;
        }

        /* Book price */
        .book-price {
            font-size: 1rem;
            font-weight: 600;
            color: #16a34a;
        }

        /* Search & filter box */
        .bg-white.rounded-lg {
            border-radius: 10px !important;
            background-color: #fff;
        }

        .shadow-sm {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
        }

        /* Search input focus effect */
        input[type="text"],
        select {
            transition: all 0.2s ease-in-out;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        /* Buttons */
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* Empty results alert */
        .alert-info {
            background-color: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        .alert-info h4 {
            font-weight: 600;
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
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('books.index') }}"><i class="fas fa-book me-2"></i>View My Books</a></li>
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-home me-2"></i>User Dashboard</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
    <main class="container py-5">
        <h1 class="mb-4 text-center text-white">Browse Available Books</h1>
        <!-- Search and Filters -->
        <div class="bg-white rounded shadow-sm border p-4 mb-4">
            <form action="{{ route('books.browse') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Books</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Search by title, author, genre...">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Category</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="fiction" {{ request('category') == 'fiction' ? 'selected' : '' }}>Fiction</option>
                            <option value="non-fiction" {{ request('category') == 'non-fiction' ? 'selected' : '' }}>Non-Fiction</option>
                            <option value="mystery" {{ request('category') == 'mystery' ? 'selected' : '' }}>Mystery</option>
                            <option value="romance" {{ request('category') == 'romance' ? 'selected' : '' }}>Romance</option>
                            <option value="science-fiction" {{ request('category') == 'science-fiction' ? 'selected' : '' }}>Science Fiction</option>
                            <option value="fantasy" {{ request('category') == 'fantasy' ? 'selected' : '' }}>Fantasy</option>
                            <option value="biography" {{ request('category') == 'biography' ? 'selected' : '' }}>Biography</option>
                            <option value="history" {{ request('category') == 'history' ? 'selected' : '' }}>History</option>
                            <option value="self-help" {{ request('category') == 'self-help' ? 'selected' : '' }}>Self Help</option>
                            <option value="business" {{ request('category') == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="technology" {{ request('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                            <option value="educational" {{ request('category') == 'educational' ? 'selected' : '' }}>Educational</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="mb-6">
            <p class="text-gray-600">
                @if($books->total() > 0)
                Showing {{ $books->firstItem() }} to {{ $books->lastItem() }} of {{ $books->total() }} books
                @if($search || request('category'))
                @if($search && request('category'))
                matching "{{ $search }}" in {{ ucfirst(str_replace('-', ' ', request('category'))) }} category
                @elseif($search)
                matching "{{ $search }}"
                @elseif(request('category'))
                in {{ ucfirst(str_replace('-', ' ', request('category'))) }} category
                @endif
                @endif
                @else
                @if($search || request('category'))
                @if($search && request('category'))
                No books found matching "{{ $search }}" in {{ ucfirst(str_replace('-', ' ', request('category'))) }} category
                @elseif($search)
                No books found matching "{{ $search }}"
                @elseif(request('category'))
                No books found in {{ ucfirst(str_replace('-', ' ', request('category'))) }} category
                @endif
                @else
                No books available for rental at the moment
                @endif
                @endif
            </p>
        </div>
        <div class="row">
            @forelse($books as $book)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="book-image-wrap">
                        <img
                            src="{{ $book->image_path ? asset($book->image_path) : 'https://via.placeholder.com/220x320?text=No+Image' }}"
                            alt="{{ $book->title }}"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='https://via.placeholder.com/220x320?text=No+Image';"
                        >
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">{{ $book->title }}</h5>
                        <div class="small text-muted mb-1">by {{ $book->author }}</div>
                        <div class="small text-primary mb-1">{{ $book->genre }}</div>
                        <div class="small mb-1">Condition: {{ ucfirst($book->condition) }}</div>
                        <div class="fw-bold text-success mb-2">${{ number_format($book->rental_price_per_day, 2) }}/day</div>
                        <a href="{{ route('books.show', $book) }}" class="btn btn-primary w-100 mt-auto">View Details</a>
                        <a href="{{ route('books.rent', $book) }}" class="btn btn-success w-100 mt-2">Rent Now</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <h4>No books available for rental at the moment.</h4>
                </div>
            </div>
            @endforelse
        </div>
        <div class="d-flex justify-content-center mt-4">
            @if ($books->hasPages())
                <nav>
                    <ul class="pagination pagination-lg">
                        {{-- Previous Page Link --}}
                        @if ($books->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $books->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($books->links()->elements[0] as $page => $url)
                            @if ($page == $books->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($books->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $books->nextPageUrl() }}" rel="next">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>