@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')
<a class="btn btn-sm btn-info" href="{{ route('invoices.edit', $invoice) }}">Edit</a>
<form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Delete this invoice permanently? This cannot be undone. Invoices with recorded payments cannot be deleted.');">
@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>
@endif
