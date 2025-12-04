<?php

namespace Modules\SmsCore\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Service for importing phone numbers from files
 * Supports CSV and Excel formats
 */
class FileImportService
{
    /**
     * Import phone numbers from uploaded file
     * 
     * @param array $file PHP $_FILES array element
     * @return array Array of phone numbers
     * @throws \Exception If file processing fails
     */
    public static function import(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Erreur lors du téléchargement du fichier: ' . self::getUploadErrorMessage($file['error']));
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return self::importCSV($file['tmp_name']);

            case 'xlsx':
            case 'xls':
                return self::importExcel($file['tmp_name'], $extension);

            default:
                throw new \Exception('Format de fichier non supporté: ' . $extension);
        }
    }

    /**
     * Import from CSV file
     * 
     * @param string $filePath Path to CSV file
     * @return array Array of phone numbers
     */
    private static function importCSV(string $filePath): array
    {
        $numbers = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \Exception('Impossible d\'ouvrir le fichier CSV');
        }

        $isFirstRow = true;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip header row if it looks like a header
            if ($isFirstRow) {
                $isFirstRow = false;
                // Check if first cell looks like a phone number
                if (!empty($data[0]) && !self::looksLikePhoneNumber($data[0])) {
                    continue; // Skip header
                }
            }

            // First column should contain phone number
            if (!empty($data[0])) {
                $number = self::cleanPhoneNumber($data[0]);
                if ($number) {
                    $numbers[] = $number;
                }
            }
        }

        fclose($handle);

        return array_unique($numbers);
    }

    /**
     * Import from Excel file
     * 
     * @param string $filePath Path to Excel file
     * @param string $extension File extension (xlsx or xls)
     * @return array Array of phone numbers
     */
    private static function importExcel(string $filePath, string $extension): array
    {
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            // Fallback: treat as CSV
            return self::importCSV($filePath);
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $numbers = [];

            $isFirstRow = true;

            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Skip header row
                if ($isFirstRow) {
                    $isFirstRow = false;
                    if (!empty($cells[0]) && !self::looksLikePhoneNumber($cells[0])) {
                        continue;
                    }
                }

                // First column should contain phone number
                if (!empty($cells[0])) {
                    $number = self::cleanPhoneNumber($cells[0]);
                    if ($number) {
                        $numbers[] = $number;
                    }
                }
            }

            return array_unique($numbers);
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
        }
    }

    /**
     * Check if a string looks like a phone number
     * 
     * @param mixed $value Value to check
     * @return bool
     */
    private static function looksLikePhoneNumber($value): bool
    {
        if (is_numeric($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        // Remove common phone number characters
        $cleaned = preg_replace('/[\s\-\(\)\+]/', '', $value);

        // Check if mostly digits
        return strlen($cleaned) >= 6 && preg_match('/^\d+$/', $cleaned);
    }

    /**
     * Clean and normalize phone number
     * 
     * @param mixed $value Raw value from file
     * @return string|null Cleaned phone number or null if invalid
     */
    private static function cleanPhoneNumber($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Convert to string
        $number = (string)$value;

        // Remove whitespace, dashes, parentheses
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        // Keep only digits and plus sign
        $number = preg_replace('/[^\d\+]/', '', $number);

        // Must have at least 6 digits
        if (strlen(preg_replace('/[^\d]/', '', $number)) < 6) {
            return null;
        }

        return $number;
    }

    /**
     * Get human-readable upload error message
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private static function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier est trop volumineux';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été téléchargé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté le téléchargement';
            default:
                return 'Erreur inconnue';
        }
    }

    /**
     * Generate a CSV template file
     * 
     * @return string CSV content
     */
    public static function generateTemplate(): string
    {
        return "Téléphone,Nom\n" .
            "0708090102,Jean Dupont\n" .
            "0709101112,Marie Martin\n" .
            "+221771234567,Amadou Diallo\n" .
            "+33612345678,Pierre Lefebvre\n";
    }
}
