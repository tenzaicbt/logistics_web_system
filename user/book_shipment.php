require_once '../includes/auth.php';

require_role(['user', 'employer']); // both allowed
enforce_permission('book_shipment', 'can_create'); // check permission
