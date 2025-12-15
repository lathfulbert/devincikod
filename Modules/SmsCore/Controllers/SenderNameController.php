<?php

namespace Modules\SmsCore\Controllers;

use App\Core\Application;
use Modules\SmsCore\Models\SenderName;
use Modules\Users\Models\User;

class SenderNameController
{
    private Application $app;

    public function __construct()
    {
        $this->app = Application::getInstance();
    }

    /**
     * List all sender names (Admin)
     */
    public function index()
    {
        $senderNames = SenderName::orderBy('created_at', 'desc')->get();

        echo view('SmsCore/sms/sender-names/index', [
            'title' => 'Sender Names Management',
            'senderNames' => $senderNames
        ]);
    }

    /**
     * Create new sender name form
     */
    public function create()
    {
        echo view('SmsCore/sms/sender-names/create', [
            'title' => 'Add New Sender Name'
        ]);
    }

    /**
     * Store new sender name
     */
    public function store()
    {
        $name = strtoupper(trim($_POST['name'] ?? ''));
        $operator = trim($_POST['operator'] ?? '');
        $status = $_POST['status'] ?? 'pending';
        $notes = trim($_POST['notes'] ?? '');

        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Sender name is required';
            redirect('/admin/sms/sender-names/create');
            exit;
        }

        if (strlen($name) > 11) {
            $_SESSION['error'] = 'Sender name must be 11 characters or less';
            redirect('/admin/sms/sender-names/create');
            exit;
        }

        // Check if already exists
        $existing = SenderName::where('name', $name)->first();
        if ($existing) {
            $_SESSION['error'] = 'This sender name already exists';
            redirect('/admin/sms/sender-names/create');
            exit;
        }

        // Create sender name
        $senderName = new SenderName();
        $senderName->name = $name;
        $senderName->operator = $operator;
        $senderName->status = $status;
        $senderName->notes = $notes;
        $senderName->created_by = $_SESSION['user']['id'] ?? null;

        if ($status === 'approved') {
            $senderName->validation_date = date('Y-m-d');
        }

        $senderName->save();

        $_SESSION['success'] = 'Sender name created successfully';
        redirect('/admin/sms/sender-names');
        exit;
    }

    /**
     * Edit sender name form
     */
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $senderName = SenderName::find($id);

        if (!$senderName) {
              $_SESSION['error'] = 'Sender name not found';
              redirect('/admin/sms/sender-names');
              exit;
        }

        echo view('SmsCore/sms/sender-names/edit', [
            'title' => 'Edit Sender Name',
            'senderName' => $senderName
        ]);
    }

    /**
     * Update sender name
     */
    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        $senderName = SenderName::find($id);

        if (!$senderName) {
            $_SESSION['error'] = 'Sender name not found';
            redirect('/admin/sms/sender-names');
            exit;
        }

        $senderName->name = strtoupper(trim($_POST['name'] ?? $senderName->name));
        $senderName->operator = trim($_POST['operator'] ?? '');
        $senderName->status = $_POST['status'] ?? 'pending';
        $senderName->is_active = isset($_POST['is_active']) ? 1 : 0;
        $senderName->notes = trim($_POST['notes'] ?? '');

        // Si une date de validation est soumise, on la prend, sinon on applique la logique existante
        if (!empty($_POST['validation_date'])) {
            $senderName->validation_date = $_POST['validation_date'];
        } elseif ($senderName->status === 'approved' && !$senderName->validation_date) {
            $senderName->validation_date = date('Y-m-d');
        }

        $senderName->save();

        $_SESSION['success'] = 'Sender name updated successfully';
        redirect('/admin/sms/sender-names');
        exit;
    }

    /**
     * Delete sender name
     */
    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        $senderName = SenderName::find($id);

        if (!$senderName) {
            $_SESSION['error'] = 'Sender name not found';
            redirect('/admin/sms/sender-names');
            exit;
        }

        $senderName->delete();

        $_SESSION['success'] = 'Sender name deleted successfully';
        redirect('/admin/sms/sender-names');
        exit;
    }

    /**
     * Manage user assignments
     */
    public function assignUsers()
    {
        $id = (int)($_GET['id'] ?? 0);
        $senderName = SenderName::find($id);

        if (!is_object($senderName)) {
            $_SESSION['error'] = 'Sender name introuvable ou corrompu.';
            redirect('/admin/sms/sender-names');
            exit;
        }

        // Get all users
        $users = User::where('is_active', 1)->orderBy('username', 'asc')->get();

        // Get assigned users
        $assignedUsers = $senderName->getAssignedUsers();
        $assignedUserIds = array_column($assignedUsers, 'id');

        echo view('SmsCore/sms/sender-names/assign', [
            'title' => 'Assign Users - ' . $senderName->name,
            'senderName' => $senderName,
            'users' => $users,
            'assignedUserIds' => $assignedUserIds
        ]);
    }

    /**
     * Save user assignments
     */
    public function saveAssignments()
    {
        $id = (int)($_POST['sender_name_id'] ?? 0);
        $userIds = $_POST['user_ids'] ?? [];
        $assignedBy = $_SESSION['user']['id'] ?? 0;

        $senderName = SenderName::find($id);

        if (!$senderName) {
              $_SESSION['error'] = 'Sender name not found';
              redirect('/admin/sms/sender-names');
              exit;
        }

        // Remove all existing assignments and add new ones
        $db = \App\Core\Database\Database::getInstance();

        try {
            $db->beginTransaction();

            // Remove all existing
            $db->query("DELETE FROM user_sender_names WHERE sender_name_id = ?", [$id]);

            // Add new assignments
            if (!empty($userIds)) {
                foreach ($userIds as $userId) {
                    SenderName::assignToUser($id, (int)$userId, $assignedBy);
                }
            }

            $db->commit();
            $_SESSION['success'] = 'User assignments saved successfully';
        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['error'] = 'Failed to save assignments: ' . $e->getMessage();
        }

        redirect('/admin/sms/sender-names');
        exit;
    }

    /**
     * Bulk assign sender names to a user
     */
    public function bulkAssignToUser()
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $senderNameIds = $_POST['sender_name_ids'] ?? [];
        $assignedBy = $_SESSION['user']['id'] ?? 0;

        if (!$userId) {
            $_SESSION['error'] = 'User ID is required';
                redirect('/admin/sms/sender-names');
            exit;
        }

        $success = SenderName::syncForUser($userId, array_map('intval', $senderNameIds), $assignedBy);

        if ($success) {
            $_SESSION['success'] = 'Sender names assigned to user successfully';
        } else {
            $_SESSION['error'] = 'Failed to assign sender names';
        }

            redirect('/admin/sms/sender-names');
        exit;
    }

    /**
     * API: Get sender names for current user (for select dropdown)
     */
    public function apiGetUserSenderNames()
    {
        header('Content-Type: application/json');

        // Get authenticated user from middleware context
        $user = $_REQUEST['auth_user'] ?? $_REQUEST['api_user'] ?? null;
        $userId = $user ? $user->id : ($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $senderNames = SenderName::getForUser($userId);

        echo json_encode([
            'success' => true,
            'data' => array_map(function($sn) {
                return [
                    'id' => $sn->id,
                    'name' => $sn->name,
                    'operator' => $sn->operator
                ];
            }, $senderNames)
        ]);
        exit;
    }
}
