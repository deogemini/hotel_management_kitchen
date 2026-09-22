<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use App\Models\Lodge;
use Illuminate\Http\Request;
class ExpenseController extends Controller {
    public function index() {
        $expenses = $this->query()->latest('spent_at')->get();
        return view('expenses.index', compact('expenses'));
    }
    public function create() { return view('expenses.create', ['lodges' => Lodge::orderBy('name')->get()]); }
    public function store(Request $request) {
        $data = $request->validate(['category'=>'required|string|max:100','description'=>'required|string|max:255','amount'=>'required|numeric|min:0.01','payment_method'=>'required|in:Cash,Mobile money,Card','spent_at'=>'required|date','lodge_id'=>'nullable|exists:lodges,id']);
        $data['lodge_id'] = auth()->user()->lodge_id ?: ($data['lodge_id'] ?? null);
        if (! $data['lodge_id']) {
            return back()->withErrors(['lodge_id' => 'Select a lodge for this expense.'])->withInput();
        }
        $data['created_by'] = auth()->id();
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success','Expense recorded successfully.');
    }
    public function destroy(Expense $expense) {
        abort_unless(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner', 403);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
    private function query() { return (auth()->user()?->hasRole('hotel_manager') ?? false) ? Expense::query() : Expense::where('lodge_id', auth()->user()?->lodge_id); }
}
