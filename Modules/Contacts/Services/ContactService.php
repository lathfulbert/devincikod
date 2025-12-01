<?php

namespace Modules\Contacts\Services;

use Modules\Contacts\Models\Contact;
use App\Core\Database\Database;

class ContactService
{
    public function __construct(protected FieldPersonalizationService $personalizationService) {}

    /**
     * Create a new contact
     */
    public function create(array $data): Contact
    {
        $contact = new Contact();

        // Set basic fields
        $contact->phone = $data['phone'];
        $contact->first_name = $data['first_name'];
        $contact->last_name = $data['last_name'] ?? null;
        $contact->email = $data['email'] ?? null;
        $contact->is_active = $data['is_active'] ?? 1;

        // Set custom fields if provided
        if (!empty($data['custom_fields'])) {
            $contact->setCustomFields($data['custom_fields']);
        }

        // Set tags if provided
        if (!empty($data['tags'])) {
            $contact->setTags($data['tags']);
        }

        $contact->save();
        return $contact;
    }

    /**
     * Update an existing contact
     */
    public function update(Contact $contact, array $data): Contact
    {
        // Update basic fields
        if (isset($data['phone'])) $contact->phone = $data['phone'];
        if (isset($data['first_name'])) $contact->first_name = $data['first_name'];
        if (isset($data['last_name'])) $contact->last_name = $data['last_name'];
        if (isset($data['email'])) $contact->email = $data['email'];
        if (isset($data['is_active'])) $contact->is_active = $data['is_active'];

        // Update custom fields if provided
        if (isset($data['custom_fields'])) {
            $contact->setCustomFields($data['custom_fields']);
        }

        // Update tags if provided
        if (isset($data['tags'])) {
            $contact->setTags($data['tags']);
        }

        $contact->save();
        return $contact;
    }

    /**
     * Delete a contact
     */
    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    /**
     * Find contact by phone number
     */
    public function findByPhone(string $phone): ?Contact
    {
        return Contact::query()
            ->where('phone', $phone)
            ->first();
    }

    /**
     * Search contacts
     */
    public function search(array $filters = []): array
    {
        $query = Contact::query();

        // Search by name or phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereGroup(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $query->orderBy($orderBy, $orderDir);

        return $query->get();
    }

    /**
     * Get contact count
     */
    public function getCount(array $filters = []): int
    {
        $query = Contact::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->count();
    }

    /**
     * Import contacts from CSV data
     */
    public function importFromCsv(array $rows, array $columnMapping): array
    {
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $data = $this->mapCsvRow($row, $columnMapping);

                if (empty($data['phone'])) {
                    $errors[] = "Ligne " . ($index + 1) . ": Numéro de téléphone manquant";
                    continue;
                }

                // Check if contact exists
                $existing = $this->findByPhone($data['phone']);

                if ($existing) {
                    $this->update($existing, $data);
                    $updated++;
                } else {
                    $this->create($data);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'total' => $imported + $updated
        ];
    }

    /**
     * Map CSV row to contact data based on column mapping
     */
    protected function mapCsvRow(array $row, array $columnMapping): array
    {
        $data = [
            'custom_fields' => []
        ];

        foreach ($columnMapping as $csvColumn => $contactField) {
            $value = $row[$csvColumn] ?? '';

            // Basic fields
            if (in_array($contactField, ['phone', 'first_name', 'last_name', 'email'])) {
                $data[$contactField] = $value;
            }
            // Custom fields
            elseif (strpos($contactField, 'custom.') === 0) {
                $slug = str_replace('custom.', '', $contactField);
                $data['custom_fields'][$slug] = $value;
            }
        }

        return $data;
    }
}
