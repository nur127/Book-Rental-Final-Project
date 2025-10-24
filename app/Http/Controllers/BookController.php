<?php
namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookController extends Controller
{
    // Display a listing of the user's books
    public function index()
    {
        $user = Auth::user();
        $search = request('search');
        $category = request('category');
    $status = request('status');

        $query = Book::query();
        if (!$user || $user->role !== 'admin') {
            $query->where('lender_id', Auth::id());
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('author', 'LIKE', "%{$search}%")
                  ->orWhere('genre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        if ($category) {
            $query->where('genre', $category);
        }
        if ($status && in_array($status, ['available', 'rented', 'maintenance', 'unavailable'])) {
            $query->where('status', $status);
        }
        $books = $query->latest()->paginate(12)->appends(['search' => $search, 'category' => $category]);
        // Stats for the user's books (as lender)
        $base = Book::where('lender_id', Auth::id());
        $stats = [
            'total' => (clone $base)->count(),
            'available' => (clone $base)->where('status', 'available')->count(),
            'rented' => (clone $base)->where('status', 'rented')->count(),
        ];
        return view('user.myBooks', compact('books', 'search', 'category', 'status', 'stats'));
    }

    // Create view removed; route excluded.

    // Store a newly created book in storage
    public function store(Request $request)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admin can add books.');
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'genre' => 'required|string|max:100',
            'description' => 'nullable|string',
            'condition' => 'required|in:new,good,fair,poor',
            'rental_price_per_day' => 'required|numeric|min:0.01|max:999.99',
            'security_deposit' => 'required|numeric|min:0|max:9999.99',
            'lender_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($request->hasFile('image')) {
            // Save to public/upload similar to provided logic
            $dest = public_path('upload');
            if (!is_dir($dest)) {
                mkdir($dest, 0777, true);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($dest, $imageName);
            $validated['image_path'] = 'upload/' . $imageName; // stored relative path under public
        }
        $validated['status'] = 'available';
        Book::create($validated);
        return redirect()->route('books.index')->with('success', 'Book added successfully!');
    }

    // Display the specified book
    public function show(Book $book)
    {
        return view('user.show', compact('book'));
    }

    // Show the form for editing the specified book
    public function edit(Book $book)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return redirect()->route('books.index')->with('error', 'Only admin can edit books.');
        }
        $users = User::orderBy('name')->get(['id', 'name']);
        return view('books.edit', compact('book', 'users'));
    }

    // Update the specified book in storage
    public function update(Request $request, Book $book)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return redirect()->route('books.index')->with('error', 'Only admin can update books.');
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn,' . $book->id,
            'genre' => 'required|string|max:100',
            'description' => 'nullable|string',
            'condition' => 'required|in:new,good,fair,poor',
            'rental_price_per_day' => 'required|numeric|min:0.01|max:999.99',
            'security_deposit' => 'required|numeric|min:0|max:9999.99',
            'status' => 'required|in:available,rented,maintenance,unavailable',
            'lender_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($request->hasFile('image')) {
            // Remove previous image from public if exists
            if (!empty($book->image_path)) {
                $old = public_path($book->image_path);
                if (is_file($old)) {
                    @unlink($old);
                }
            }
            $dest = public_path('upload');
            if (!is_dir($dest)) {
                mkdir($dest, 0777, true);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($dest, $imageName);
            $validated['image_path'] = 'upload/' . $imageName;
        }
        $book->update($validated);
        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    // Remove the specified book from storage
    public function destroy(Book $book)
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return redirect()->route('books.index')->with('error', 'Only admin can delete books.');
        }
        if ($book->status === 'rented') {
            return redirect()->route('books.index')->with('error', 'Cannot delete a book that is currently rented.');
        }
        if (!empty($book->image_path)) {
            $old = public_path($book->image_path);
            if (is_file($old)) {
                @unlink($old);
            }
        }
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
    }

    // Display available books for browsing (for borrowers)
    public function browse(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100'
        ]);
        $query = Book::where('status', 'available')
            ->with('lender');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('genre', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        if ($category) {
            $query->where('genre', $category);
        }
        $books = $query->latest()->paginate(12)->appends(['search' => $search, 'category' => $category]);
        return view('user.browseBooks', compact('books', 'search', 'category'));
    }

    // Borrower home page - same as browse but for home route
    public function home(Request $request)
    {
        $search = $request->get('search');
        $query = Book::where('status', 'available')
            ->with('lender');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('author', 'LIKE', "%{$search}%")
                    ->orWhere('genre', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        $books = $query->latest()->paginate(12);
        $totalBooks = Book::where('status', 'available')->count();
        return view('user.home', compact('books', 'search', 'totalBooks'));
    }

    // Show the rent form for a book (user action)
    public function showRentForm(Book $book)
    {
        if ($book->status !== 'available') {
            return redirect()->route('books.browse')->with('error', 'Book is not available for rent.');
        }
        return view('books.rent', compact('book'));
    }

    // Process the rent request (wallet check, confirmation, rental creation)
    public function processRent(Request $request, Book $book)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = User::findOrFail(Auth::id());
        if ($user->role !== 'user') {
            return redirect()->route('books.browse')->with('error', 'Only users can rent books.');
        }
        if ($book->status !== 'available') {
            return redirect()->route('books.browse')->with('error', 'Book is not available for rent.');
        }
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:30',
        ]);
        $days = (int) $validated['days'];
        $dailyRate = (float) $book->rental_price_per_day;
        $deposit = (float) $book->security_deposit;
        $rentalCost = $dailyRate * $days; // cost excluding deposit
        $depositRequired = $deposit; // charge only deposit upfront
        if ($user->wallet < $depositRequired) {
            return redirect()->route('books.rent', $book->id)
                ->with('error', 'Insufficient wallet balance. Please add funds to your wallet.');
        }
        if (!$request->has('confirm')) {
            // Keep totalCost for backward compatibility in view; it now represents upfront charge (deposit only)
            $totalCost = $depositRequired;
            return view('books.rent_confirm', compact('book', 'days', 'totalCost'));
        }
        if ($book->lender_id === $user->id) {
            return redirect()->route('books.browse')->with('error', 'You cannot rent your own book.');
        }
        DB::transaction(function () use ($user, $book, $days, $dailyRate, $deposit, $rentalCost, $depositRequired) {
            // Deduct only the security deposit upfront
            $user->wallet = (float)$user->wallet - (float)$depositRequired;
            $user->save();

            // Mark book as rented
            $book->status = 'rented';
            $book->save();

            // If there's a pending rental for this book by this user, update it; else create a new active rental
            $pending = \App\Models\Rental::where('book_id', $book->id)
                ->where('borrower_id', $user->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($pending) {
                $pending->fill([
                    'lender_id' => $book->lender_id,
                    'rental_start_date' => Carbon::today(),
                    'rental_end_date' => Carbon::today()->addDays((int) $days),
                    'rental_days' => (int) $days,
                    'daily_rate' => $dailyRate,
                    'total_amount' => $rentalCost + $deposit,
                    'security_deposit' => $deposit,
                    'status' => 'active',
                ])->save();
            } else {
                \App\Models\Rental::create([
                    'book_id' => $book->id,
                    'borrower_id' => $user->id,
                    'lender_id' => $book->lender_id,
                    'rental_start_date' => Carbon::today(),
                    'rental_end_date' => Carbon::today()->addDays((int) $days),
                    'rental_days' => (int) $days,
                    'daily_rate' => $dailyRate,
                    // Store total as rental cost + deposit for settlement later
                    'total_amount' => $rentalCost + $deposit,
                    'security_deposit' => $deposit,
                    'status' => 'active',
                ]);
            }
        });
        return redirect()->route('books.browse')->with('success', 'Book rented successfully!');
    }

    // Handle book return: set book available, complete rental, transfer funds
    public function returnBook($rentalId)
    {
        $rental = \App\Models\Rental::findOrFail($rentalId);
        $book = $rental->book;
        $borrower = $rental->borrower;
        $lender = $rental->lender;
        if ($rental->status !== 'active') {
            return redirect()->back()->with('error', 'This rental is not active.');
        }
        // Compute settlement amounts
        $securityDeposit = (float) $rental->security_deposit;
        $rentalCost = (float) $rental->total_amount - $securityDeposit; // cost excluding deposit
        $extraDue = max(0.0, $rentalCost - $securityDeposit);

        // If deposit doesn't fully cover rental, ensure borrower can pay extra
        if ($extraDue > 0 && (float)$borrower->wallet < $extraDue) {
            return redirect()->back()->with('error', 'Insufficient wallet to settle the outstanding rental amount. Please add funds and try again.');
        }

        DB::transaction(function () use ($rental, $book, $borrower, $lender, $securityDeposit, $rentalCost, $extraDue) {
            $book->status = 'available';
            $book->save();

            // Settle payments
            $lender->wallet = (float) $lender->wallet;
            $borrower->wallet = (float) $borrower->wallet;

            if ($securityDeposit >= $rentalCost) {
                // Deposit covers rental: pay lender from deposit portion, refund remainder to borrower
                $lender->wallet += $rentalCost;
                $borrower->wallet += ($securityDeposit - $rentalCost);
            } else {
                // Deposit partially covers rental: charge borrower the difference and pay full rental to lender
                $borrower->wallet -= $extraDue;
                $lender->wallet += $rentalCost;
            }

            $lender->save();
            $borrower->save();

            // Complete rental record
            $rental->status = 'completed';
            $rental->actual_return_date = Carbon::now();
            $rental->save();
        });
        return redirect()->back()->with('success', 'Book returned successfully!');
    }

    // List of books rented by the user (as borrower)
    public function myRentals(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $rentals = \App\Models\Rental::where('borrower_id', $user->id)
            ->with(['book.lender'])
            ->latest()
            ->paginate(12);
        return view('user.myRentals', compact('rentals'));
    }
}
