<?php
// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in
require_login();

// Check if record ID is provided
if (!isset($_GET['id'])) {
    die('<div class="alert alert-danger">Record ID is required</div>');
}

$record_id = sanitize($_GET['id']);
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] === 'admin');

// Fetch the record
if ($is_admin) {
    $stmt = db_query("
        SELECT * FROM annex4_damaged_houses 
        WHERE id = ?
    ", [$record_id]);
} else {
    $stmt = db_query("
        SELECT * FROM annex4_damaged_houses 
        WHERE id = ? AND created_by = ?
    ", [$record_id, $user_id]);
}

$record = $stmt->fetch();

if (!$record) {
    die('<div class="alert alert-danger">Record not found or access denied</div>');
}

// Calculate total for initial load
$calculated_total = $record['totally_damaged'] + $record['partially_damaged'];

// Generate CSRF token
$csrf_token = generate_token();
?>

<form id="editRecordForm" method="POST" action="../api/annex4_update.php">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
    
    <div class="row g-3">
        <div class="col-md-6">
            <label for="edit_totally_damaged" class="form-label">Totally Damaged</label>
            <input type="number" class="form-control" id="edit_totally_damaged" name="totally_damaged" 
                   value="<?php echo $record['totally_damaged']; ?>" min="0" required>
        </div>
        <div class="col-md-6">
            <label for="edit_partially_damaged" class="form-label">Partially Damaged</label>
            <input type="number" class="form-control" id="edit_partially_damaged" name="partially_damaged" 
                   value="<?php echo $record['partially_damaged']; ?>" min="0" required>
        </div>
        <div class="col-md-6">
            <label for="edit_total_damaged" class="form-label">Total Damaged</label>
            <input type="number" class="form-control" id="edit_total_damaged" name="total_damaged" 
                   value="<?php echo $calculated_total; ?>" min="0" readonly style="background-color: #f8f9fa;">
        </div>
        <div class="col-md-6">
            <label for="edit_cost" class="form-label">Cost</label>
            <input type="text" class="form-control" id="edit_cost" name="cost" 
                   value="<?php echo htmlspecialchars($record['cost']); ?>" placeholder="Enter cost">
        </div>
        <div class="col-12">
            <label for="edit_remarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="edit_remarks" name="remarks" rows="3" 
                      placeholder="Enter remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
        </div>
    </div>
    
    <div class="modal-footer mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Update Record
        </button>
    </div>
</form>

<script>
// Simple and reliable calculation function
function calculateTotal() {
    const totally = parseInt(document.getElementById('edit_totally_damaged').value) || 0;
    const partially = parseInt(document.getElementById('edit_partially_damaged').value) || 0;
    const total = totally + partially;
    
    document.getElementById('edit_total_damaged').value = total;
    console.log('Calculated total:', total, '(Totally:', totally, '+ Partially:', partially, ')');
}

// Initialize when the script loads (for modal content)
function initializeCalculations() {
    console.log('Initializing damage calculations...');
    
    // Get input elements
    const totallyInput = document.getElementById('edit_totally_damaged');
    const partiallyInput = document.getElementById('edit_partially_damaged');
    
    if (totallyInput && partiallyInput) {
        // Remove any existing event listeners by cloning
        totallyInput.replaceWith(totallyInput.cloneNode(true));
        partiallyInput.replaceWith(partiallyInput.cloneNode(true));
        
        // Get new references
        const newTotally = document.getElementById('edit_totally_damaged');
        const newPartially = document.getElementById('edit_partially_damaged');
        
        // Add event listeners
        newTotally.addEventListener('input', calculateTotal);
        newTotally.addEventListener('change', calculateTotal);
        newPartially.addEventListener('input', calculateTotal);
        newPartially.addEventListener('change', calculateTotal);
        
        // Calculate initial total
        calculateTotal();
        
        console.log('Damage calculations initialized successfully');
    } else {
        console.error('Required input elements not found');
    }
}

// Call initialization immediately
initializeCalculations();

// Also set up form submission handler
document.getElementById('editRecordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Ensure total is calculated before submission
    calculateTotal();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text();
        }
    })
    .then(data => {
        if (data) {
            try {
                const result = JSON.parse(data);
                if (result.success) {
                    alert('Record updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to update record'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (e) {
                location.reload();
            }
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Additional safety: recalculate on any keyup as well
document.addEventListener('keyup', function(e) {
    if (e.target.id === 'edit_totally_damaged' || e.target.id === 'edit_partially_damaged') {
        calculateTotal();
    }
});
</script>